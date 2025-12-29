<?php

namespace App\Actions\Recommendation;

use App\Actions\Currency\FormatCurrencyAction;
use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\HardwareType;

class GenerateRecommendationsAction
{
    protected ?FormatCurrencyAction $currencyFormatter = null;

    private function getCurrencyFormatter(): FormatCurrencyAction
    {
        if ($this->currencyFormatter === null) {
            $this->currencyFormatter = new FormatCurrencyAction();
        }

        return $this->currencyFormatter;
    }

    /**
     * Execute generation with sane defaults. Returns an array of recommendation objects.
     */
    public function execute(Estimation $estimation, array $options = []): array
    {
        // action performs generation directly; services should delegate to this action
        $topN = (int) ($options['top_n'] ?? 5);
        $beam = (int) ($options['beam'] ?? 20);
        $singleProviderFirst = $options['single_provider_first'] ?? true;
        $limit = (int) ($options['limit'] ?? 5);

        $types = HardwareType::all()->keyBy('key');

        // Load top-N candidates per component (cheap heuristic: price asc, then rating desc, then efficiency)
        $loadCandidates = function ($key) use ($types, $topN) {
            if (! isset($types[$key])) {
                return collect();
            }

            $items = Hardware::byType($types[$key]->id)
                ->available()
                ->verified()
                ->with('owner')
                ->get()
                ->sortBy(function ($h) {
                    $price = $h->price ?? 0;
                    $rating = optional($h->owner && method_exists($h->owner, 'providerDetail') ? $h->owner->providerDetail()->first() : null)->rating ?? 0;
                    $eff = $h->specs['efficiency'] ?? 0;

                    return $price - ($rating * 0.01) - ($eff * 0.001);
                })
                ->values()
                ->take($topN);

            return $items;
        };

        $panels = $loadCandidates('solar_panel');
        $inverters = $loadCandidates('inverter');
        $batteries = $loadCandidates('battery');
        $controllers = $loadCandidates('charge_controller');

        if ($panels->isEmpty() || $inverters->isEmpty() || $batteries->isEmpty() || $controllers->isEmpty()) {
            return [];
        }

        $dailyKwh = max(1, $estimation->monthly_kwh / 30);
        $desiredPanelCount = max(2, ceil(($dailyKwh * 1.3) / (5.5 * 0.45)));
        $inverterKw = max(1, ceil($estimation->total_watts * 1.25 / 1000));
        $batteryKwh = max(5, ceil($dailyKwh * 1.5));

        $results = [];
        // ensure class-level formatter is available to all helpers
        $this->currencyFormatter = new FormatCurrencyAction();

        // Try single-provider configurations first (cheaper to compute)
        if ($singleProviderFirst) {
            $singleProviderConfigs = $this->singleProviderConfigs([$panels, $inverters, $batteries, $controllers], $desiredPanelCount, $inverterKw, $batteryKwh);
            foreach ($singleProviderConfigs as $cfg) {
                $results[] = $cfg;
                if (count($results) >= $limit) {
                    return $results;
                }
            }
        }

        // Beam search across components to create mixed-provider configs
        $candidatesPerCategory = [$panels, $inverters, $batteries, $controllers];

        $partials = [
            'components' => [],
            'price' => 0.0,
            'owners' => [],
        ];

        $partialsList = [$partials];

        foreach ($candidatesPerCategory as $idx => $candidates) {
            $newPartials = [];
            foreach ($partialsList as $partial) {
                foreach ($candidates as $candidate) {
                    $new = $partial;
                    $role = ['solar_panels', 'inverter', 'battery', 'charge_controller'][$idx];
                    $count = $role === 'solar_panels' ? $desiredPanelCount : ($role === 'battery' ? max(1, ceil($batteryKwh / ($candidate->specs['capacity_kwh'] ?? 10))) : 1);
                    $unitPrice = $candidate->price ?? 0;
                    $subtotal = $unitPrice * $count;
                    $new['components'][$role] = [
                        'hardware' => $candidate,
                        'count' => $count,
                        'subtotal' => $subtotal,
                    ];
                    $new['price'] += $subtotal;

                    $ownerKey = $this->ownerKeyFor($candidate);
                    $new['owners'][$ownerKey] = $this->ownerInfoFor($candidate);

                    $newPartials[] = $new;
                }
            }

            usort($newPartials, function ($a, $b) {
                if ($a['price'] == $b['price']) {
                    $ar = $this->avgOwnerRating($a['owners']);
                    $br = $this->avgOwnerRating($b['owners']);

                    return $br <=> $ar;
                }

                return $a['price'] <=> $b['price'];
            });

            $partialsList = array_slice($newPartials, 0, $beam);
        }

        foreach ($partialsList as $partial) {
            // compute string architecture for solar panels when available
            if (! empty($partial['components']['solar_panels']['hardware'] ?? null)) {
                $panel = $partial['components']['solar_panels']['hardware'];
                $inv = $partial['components']['inverter']['hardware'] ?? null;
                $ctrl = $partial['components']['charge_controller']['hardware'] ?? null;
                $desired = $partial['components']['solar_panels']['count'] ?? 0;
                $arch = $this->calculateStringArchitecture($panel, $desired, $inv, $ctrl);
                $partial['components']['solar_panels']['string_architecture'] = $arch;
            }

            $config = $this->formatPartialConfig($partial);
            $results[] = $config;
        }

        // Final sorting: price ascending, tie-break by average provider rating (desc)
        usort($results, function ($a, $b) {
            if (($a['total_price'] ?? 0) == ($b['total_price'] ?? 0)) {
                $ar = $this->avgProvidersRating($a['providers'] ?? []);
                $br = $this->avgProvidersRating($b['providers'] ?? []);

                return $br <=> $ar;
            }

            return ($a['total_price'] ?? 0) <=> ($b['total_price'] ?? 0);
        });

        return array_slice($results, 0, $limit);
    }

    private function singleProviderConfigs(array $categories, int $panelCount, int $inverterKw, int $batteryKwh): array
    {
        $ownersPerCategory = [];
        foreach ($categories as $i => $cat) {
            $ownersPerCategory[$i] = [];
            foreach ($cat as $item) {
                $ownersPerCategory[$i][$this->ownerKeyFor($item)][] = $item;
            }
        }

        $ownerKeysLists = array_map(fn ($a) => array_keys($a), $ownersPerCategory);
        if (empty($ownerKeysLists)) {
            return [];
        }
        $common = array_intersect(...$ownerKeysLists);
        $commonOwners = array_values($common);

        $results = [];
        foreach ($commonOwners as $ownerKey) {
            $chosen = [];
            $total = 0;
            foreach ($ownersPerCategory as $i => $map) {
                $items = $map[$ownerKey] ?? [];
                if (empty($items)) {
                    $chosen = [];
                    break;
                }
                usort($items, fn ($a, $b) => ($a->price <=> $b->price));
                $item = $items[0];
                $role = ['solar_panels', 'inverter', 'battery', 'charge_controller'][$i];
                $count = $role === 'solar_panels' ? $panelCount : ($role === 'battery' ? max(1, ceil($batteryKwh / ($item->specs['capacity_kwh'] ?? 10))) : 1);
                $subtotal = $item->price * $count;
                $chosen[$role] = ['hardware' => $item, 'count' => $count, 'subtotal' => $subtotal];
                $total += $subtotal;
            }

            if (empty($chosen)) {
                continue;
            }

            $owners = [$ownerKey => $this->ownerInfoFor($chosen['solar_panels']['hardware'])];
            $results[] = $this->formatPartialConfig(['components' => $chosen, 'price' => $total, 'owners' => $owners]);
        }

        return $results;
    }

    private function avgOwnerRating(array $owners): float
    {
        if (empty($owners)) {
            return 0.0;
        }
        $sum = 0;
        $n = 0;
        foreach ($owners as $o) {
            $sum += $o['rating'] ?? 0;
            $n++;
        }

        return $n ? $sum / $n : 0.0;
    }

    private function formatPartialConfig(array $partial): array
    {
        $components = [];
        foreach ($partial['components'] as $role => $info) {
            $hw = $info['hardware'];
            $formatter = $this->getCurrencyFormatter();
            $unitMeta = $formatter->formatMeta(floatval($hw->price));
            $subtotalMeta = $formatter->formatMeta(floatval($info['subtotal']));

            $components[$role] = [
                'hardware_id' => $hw->id,
                'name' => $hw->name,
                'count' => $info['count'],
                'unit_price' => $unitMeta['amount'],
                'unit_price_formatted' => $unitMeta['formatted'],
                'subtotal' => $subtotalMeta['amount'],
                'subtotal_formatted' => $subtotalMeta['formatted'],
                'specs' => $hw->specs,
                'rationale' => $hw->specs['efficiency'] ?? '',
                'provider' => $this->ownerInfoFor($hw),
            ];
        }

        $providers = array_values(array_map(fn ($o) => $o, $partial['owners']));

        // Pick a primary provider for backward-compatibility and simple displays.
        // Prefer verified providers, then higher rating.
        $primaryProvider = null;
        if (! empty($providers)) {
            usort($providers, function ($a, $b) {
                $av = $a['verified'] ? 1 : 0;
                $bv = $b['verified'] ? 1 : 0;
                if ($av === $bv) {
                    return $b['rating'] <=> $a['rating'];
                }

                return $bv <=> $av;
            });
            $primaryProvider = $providers[0];
        }

        $total = array_sum(array_map(fn ($c) => floatval($c['subtotal']), $components));
        $totalMeta = $this->getCurrencyFormatter()->formatMeta($total);

        return [
            'provider' => $primaryProvider,
            'providers' => $providers,
            'components' => $components,
            'total_price' => $totalMeta['amount'],
            'total_price_formatted' => $totalMeta['formatted'],
            'currency' => $totalMeta['currency'],
        ];
    }

    /**
     * Calculate a realistic string architecture for panels given constraints.
     * Returns array with 'series', 'parallel', 'controller_current', and notes.
     */
    public function calculateStringArchitecture($panel, int $desiredPanels, $inverter = null, $controller = null): array
    {
        // panel specs: expect 'vmp', 'voc', 'imp' or 'isc' in specs
        $specs = $panel->specs ?? [];
        $vmp = floatval($specs['vmp'] ?? ($specs['voc'] ?? 0) * 0.8);
        $voc = floatval($specs['voc'] ?? ($specs['vmp'] ?? 0) * 1.2);
        $imp = floatval($specs['imp'] ?? ($specs['isc'] ?? 0));

        // inverter/controller constraints
        $mppt_min = $inverter->specs['mppt_v_min'] ?? ($controller->specs['mppt_v_min'] ?? 0);
        $mppt_max = $inverter->specs['mppt_v_max'] ?? ($controller->specs['mppt_v_max'] ?? 1000);
        $voc_max = $inverter->specs['voc_max'] ?? ($controller->specs['voc_max'] ?? 1000);
        $controller_max_current = $controller->specs['max_input_current'] ?? null;

        // cold temperature multiplier for Voc (approx -10C = +1.25 factor safety)
        $cold_factor = 1.25;

        // determine feasible series range using MPPT and Voc constraints
        $min_series_by_mppt = ($mppt_min > 0 && $vmp > 0) ? (int) ceil($mppt_min / max(0.0001, $vmp)) : 1;
        $max_series_by_mppt = ($mppt_max > 0 && $vmp > 0) ? (int) floor($mppt_max / max(0.0001, $vmp)) : PHP_INT_MAX;
        $max_series_by_voc = ($voc_max > 0 && $voc > 0) ? (int) floor($voc_max / ($voc * $cold_factor)) : PHP_INT_MAX;

        $min_series = max(1, $min_series_by_mppt);
        $max_series = max(1, min($max_series_by_mppt, $max_series_by_voc));

        // If constraints are impossible, relax to best-effort choices
        if ($min_series > $max_series) {
            $max_series = max(1, min($max_series_by_mppt ?: PHP_INT_MAX, $max_series_by_voc ?: PHP_INT_MAX));
            $min_series = 1;
        }

        // Prefer a series count that places Vmp*series near mid-MPPT when possible,
        // but never exceed the desired panel count.
        $series = 1;
        if ($mppt_min > 0 && $mppt_max > 0) {
            $target_v = ($mppt_min + $mppt_max) / 2.0;
            $best = null;
            $bestDiff = null;
            for ($s = $min_series; $s <= $max_series; $s++) {
                if ($s > $desiredPanels) {
                    break;
                }
                $diff = abs($vmp * $s - $target_v);
                if ($best === null || $diff < $bestDiff) {
                    $best = $s;
                    $bestDiff = $diff;
                }
            }
            $series = $best ?? min($max_series, max(1, (int) floor($desiredPanels / 2)));
        } else {
            $series = min($max_series, max(1, (int) floor($desiredPanels / 2)));
        }

        if ($series < 1) {
            $series = 1;
        }

        // parallel strings to reach desired panel count
        $parallel = (int) ceil($desiredPanels / max(1, $series));

        // controller current = Imp per string * parallel * safety margin (1.2)
        $safety = 1.2;
        $imp_per_string = $imp > 0 ? ($imp) : 0;
        $controller_current = $imp_per_string * $parallel * $safety;

        // If a controller with max_input_current is present and current exceeds it,
        // try to increase series (reducing parallel) up to max_series to satisfy the limit.
        if ($controller_max_current !== null && $controller_current > $controller_max_current) {
            for ($s = $series + 1; $s <= $max_series; $s++) {
                $p = (int) ceil($desiredPanels / max(1, $s));
                $c = $imp_per_string * $p * $safety;
                if ($c <= $controller_max_current) {
                    $series = $s;
                    $parallel = $p;
                    $controller_current = $c;
                    break;
                }
            }
        }

        return [
            'series' => $series,
            'parallel' => $parallel,
            'imp_per_string' => $imp_per_string,
            'controller_current_a' => round($controller_current, 2),
            'notes' => [
                'max_series_by_mppt' => $max_series_by_mppt,
                'max_series_by_voc_cold' => $max_series_by_voc,
                'cold_factor' => $cold_factor,
            ],
        ];
    }

    private function ownerKeyFor($item): string
    {
        if (isset($item->owner_type) && isset($item->owner_id) && $item->owner_type && $item->owner_id) {
            return $item->owner_type.':'.$item->owner_id;
        }

        return 'unknown:0';
    }

    private function ownerInfoFor($item): array
    {
        if (isset($item->owner_type) && isset($item->owner_id) && $item->owner_type && $item->owner_id) {
            try {
                $owner = $item->owner;
                if ($owner) {
                    if ($owner instanceof \App\Models\User && method_exists($owner, 'providerDetail')) {
                        $pd = $owner->providerDetail()->first();

                        return [
                            'id' => $owner->id,
                            'company_name' => $pd->company_name ?? ($owner->first_name ?? 'Unknown'),
                            'rating' => $pd->rating ?? 0.0,
                            'verified' => $pd->verified ?? false,
                        ];
                    }

                    if ($owner instanceof \App\Models\Organisation && method_exists($owner, 'organisationProviderDetail')) {
                        $od = $owner->organisationProviderDetail()->first();

                        return [
                            'id' => $owner->id,
                            'company_name' => $od->company_name ?? ($owner->name ?? 'Organisation'),
                            'rating' => $od->rating ?? 0.0,
                            'verified' => $od->verified ?? false,
                        ];
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return [
            'id' => null,
            'company_name' => 'Unknown',
            'rating' => 0.0,
            'verified' => false,
        ];
    }

    private function avgProvidersRating(array $providers): float
    {
        if (empty($providers)) {
            return 0.0;
        }
        $sum = 0;
        $n = 0;
        foreach ($providers as $p) {
            $sum += $p['rating'] ?? 0;
            $n++;
        }

        return $n ? $sum / $n : 0.0;
    }
}
