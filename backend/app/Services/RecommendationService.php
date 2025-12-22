<?php

namespace App\Services;

use App\Models\Estimation;
use App\Models\Hardware;
use App\Models\HardwareType;

class RecommendationService
{
    public function generateRecommendations(Estimation $estimation): array
    {
        $types = HardwareType::all()->keyBy('key');
        $recommendations = [];

        // Get hardware by type
        $panels = Hardware::byType($types['solar_panel']->id)->available()->verified()->with('provider')->get();
        $inverters = Hardware::byType($types['inverter']->id)->available()->verified()->with('provider')->get();
        $batteries = Hardware::byType($types['battery']->id)->available()->verified()->with('provider')->get();
        $controllers = Hardware::byType($types['charge_controller']->id)->available()->verified()->with('provider')->get();

        // If any category is empty, return empty recommendations
        if ($panels->isEmpty() || $inverters->isEmpty() || $batteries->isEmpty() || $controllers->isEmpty()) {
            return [];
        }

        // Calculate requirements
        $dailyKwh = max(1, $estimation->monthly_kwh / 30);
        $panelCount = max(2, ceil(($dailyKwh * 1.3) / (5.5 * 0.45)));
        $inverterKw = max(1, ceil($estimation->total_watts * 1.25 / 1000));
        $batteryKwh = max(5, ceil($dailyKwh * 1.5));

        // Generate one configuration per provider combination
        $providerCombinations = [];
        foreach ($panels as $panel) {
            foreach ($inverters as $inverter) {
                foreach ($batteries as $battery) {
                    foreach ($controllers as $controller) {
                        $key = $panel->provider_id.'-'.$inverter->provider_id.'-'.$battery->provider_id.'-'.$controller->provider_id;
                        if (! isset($providerCombinations[$key])) {
                            $config = $this->buildConfiguration($panel, $inverter, $battery, $controller, $panelCount, $inverterKw, $batteryKwh);
                            $recommendations[] = $config;
                            $providerCombinations[$key] = true;
                        }
                    }
                }
            }
        }

        // Sort and limit to top 5
        usort($recommendations, function ($a, $b) {
            if ($a['total_price'] == $b['total_price']) {
                return $b['provider']['rating'] <=> $a['provider']['rating'];
            }

            return $a['total_price'] <=> $b['total_price'];
        });

        return array_slice($recommendations, 0, 5);
    }

    private function isValidConfiguration($panel, $inverter, $battery, $controller, $panelCount, $inverterKw, $batteryKwh): bool
    {
        $panelPower = $panel->specs['power_watts'] ?? 450;
        $inverterPower = $inverter->specs['power_kw'] ?? 5;
        $batteryCapacity = $battery->specs['capacity_kwh'] ?? 10;
        $controllerCurrent = $controller->specs['current_amps'] ?? 60;

        // Basic compatibility checks
        $totalPanelPower = ($panelPower * $panelCount) / 1000; // Convert to kW
        $inverterCapacityOk = $totalPanelPower <= ($inverterPower * 1.3); // Allow 30% oversizing
        $batteryCapacityOk = $batteryCapacity >= ($batteryKwh * 0.7); // Allow 30% undersizing
        $controllerOk = $controllerCurrent >= 30; // Minimum 30A controller

        return $inverterCapacityOk && $batteryCapacityOk && $controllerOk;
    }

    private function buildConfiguration($panel, $inverter, $battery, $controller, $panelCount, $inverterKw, $batteryKwh): array
    {
        $panelPower = $panel->specs['power_watts'] ?? 450;
        $actualPanelCount = max(1, ceil($panelCount * $panelPower / 450));
        $batteryCount = max(1, ceil($batteryKwh / ($battery->specs['capacity_kwh'] ?? 10)));

        $components = [
            'solar_panels' => [
                'hardware_id' => $panel->id,
                'name' => $panel->name,
                'count' => $actualPanelCount,
                'unit_price' => number_format($panel->price, 2),
                'subtotal' => number_format($panel->price * $actualPanelCount, 2),
                'specs' => $panel->specs,
                'rationale' => "Selected for {$panelPower}W output and {$panel->specs['efficiency']}% efficiency",
            ],
            'inverter' => [
                'hardware_id' => $inverter->id,
                'name' => $inverter->name,
                'count' => 1,
                'unit_price' => number_format($inverter->price, 2),
                'subtotal' => number_format($inverter->price, 2),
                'specs' => $inverter->specs,
                'rationale' => "Sized for {$inverter->specs['power_kw']}kW capacity with {$inverter->specs['efficiency']}% efficiency",
            ],
            'battery' => [
                'hardware_id' => $battery->id,
                'name' => $battery->name,
                'count' => $batteryCount,
                'unit_price' => number_format($battery->price, 2),
                'subtotal' => number_format($battery->price * $batteryCount, 2),
                'specs' => $battery->specs,
                'rationale' => "Provides {$battery->specs['capacity_kwh']}kWh storage for 1.5 days backup",
            ],
            'charge_controller' => [
                'hardware_id' => $controller->id,
                'name' => $controller->name,
                'count' => 1,
                'unit_price' => number_format($controller->price, 2),
                'subtotal' => number_format($controller->price, 2),
                'specs' => $controller->specs,
                'rationale' => 'MPPT controller for optimal charging efficiency',
            ],
        ];

        $totalPrice = ($panel->price * $actualPanelCount) + $inverter->price + ($battery->price * $batteryCount) + $controller->price;

        return [
            'provider' => [
                'id' => $panel->provider->id,
                'company_name' => $panel->provider->company_name,
                'rating' => $panel->provider->rating,
                'verified' => $panel->provider->verified,
            ],
            'components' => $components,
            'total_price' => $totalPrice,
            'currency' => 'GHS',
        ];
    }
}
