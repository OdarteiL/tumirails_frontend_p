Recommendation service — simplified

What changed (very short):
- Old: recommendations returned a single `provider` and often assumed hardware came from one provider.
- New: recommendations include a `providers` list (per-component attribution) and optional heuristics to prefer single-provider configurations first.

Analogy: "Building a solar kit is like planning a dinner party"
- Pieces (solar panels, inverter, battery, controller) are like courses (starter, main, dessert, drink).
- Each course has several specialist vendors (providers). You can either buy the whole meal from one caterer (single-provider configuration) or mix-and-match the best items from different vendors (multi-provider configuration).
- The service first checks if one caterer can provide everything reasonably (fast, cheap), then considers mixed menus from top vendors (a bounded search, like asking the top 5 caterers for their best dish and combining them).

Key implementation points (what I added):
- Per-component provider attribution: each component in the returned `components` has a `provider` block. The top-level `providers` array lists the distinct providers used in the configuration.
- No backward-compat `Provider` model: we rely purely on `User`/`Organisation` + their `ProviderDetail` rows.
- Pre-selection (top-N): for each component category we pre-select the top `N` candidates (default N=5). The sort uses price, provider rating and a light efficiency tie-breaker.
- Single-provider-first: we generate single-provider configs first (default true) — cheap to compute and often more desirable for buyers.
- Bounded mixed search (beam search): we combine candidates using a beam width (default 20) to keep the Cartesian product bounded. This yields good mixed-provider options without exponential blow-up.
- Final sorting: results are sorted by `total_price` (ascending) with a tie-breaker on average provider rating (descending).
- Electrical sizing: the system computes panel count, inverter kW and battery kWh using simple, defensible formulas (daily kWh -> panels, inverter sized with a safety margin, battery sized for days of backup). The code includes clearer places to inject more accurate Vmp/Isc/string calculations if needed.
- Currency formatting: prices are formatted to 2 decimals; consider swapping this to a locale-aware helper if you must handle other currencies/locales.
 - Electrical sizing: the system computes panel count, inverter kW and battery kWh using simple, defensible formulas (daily kWh -> panels, inverter sized with a safety margin, battery sized for days of backup). Additionally, the recommendation now computes a string architecture for the selected panels using panel Vmp/Voc/Imp (or Isc) and inverter/controller MPPT and Voc constraints. The returned per-component data includes `string_architecture` with `series`, `parallel`, and `controller_current_a` estimates.
 - Currency formatting: prices are formatted using a centralized currency action/service which reads the active `Country` (seeded with Ghana by default) and formats amounts using the active country's `currency_code`. If PHP's `intl` extension is available it will prefer localized formatting; otherwise it falls back to `CURRENCY amount` style (e.g. `GHS 1,234.56`). The recommendation output now includes `total_price_formatted` alongside numeric `total_price`.

Why this is safer/scalable:
- Limits candidates per category (top-N) so large catalogs won't explode compute time.
- Beam search keeps the search focused on plausible combos rather than trying every possible mix.
- Single-provider-first returns practical options quickly and is a common UX expectation.

Outputs (shape):
- `providers`: array of provider objects used in the recommendation (each has `id`, `company_name`, `rating`, `verified`).
- `components`: object keyed by role (`solar_panels`, `inverter`, `battery`, `charge_controller`) with per-component `hardware_id`, `name`, `count`, `unit_price`, `subtotal`, `specs`, `rationale`, and `provider`.
- `total_price`, `currency`.

Notes & next steps (recommended):
- Replace the simplistic electrical sizing with a proper string-architecture calculator that uses panel Vmp/Isc and Vmp of inverters to determine string counts and controller current.
- Add a locale-aware currency helper and centralize formatting.
- Add sample responses to OpenAPI showing mixed-provider outputs.
- Optionally implement a more advanced search (beam with dynamic heuristics or A* style search) if you need better solution quality at scale.

Defaults used in implementation (configurable via `options` passed to the action):
- `top_n`: 5
- `beam`: 20
- `single_provider_first`: true
- `limit`: 5 (max recommendations returned)

If you'd like, I can:
- Commit the changes and push the branch.
- Add an action-level unit test and a currency helper next.
- Produce example OpenAPI response snippets for the new structure.

