import { Component, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule, DollarSign, Zap, Sun } from 'lucide-angular';

interface Appliance {
  name: string;
  power: string;
  checked: boolean;
}

@Component({
  selector: 'app-estimator',
  standalone: true,
  imports: [CommonModule, FormsModule, LucideAngularModule],
  templateUrl: './estimator.html',
  styleUrl: './estimator.css'
})
export class EstimatorComponent {
  readonly DollarSign = DollarSign;
  readonly Zap = Zap;
  readonly Sun = Sun;

  // Signals for form state
  monthlyUsage = signal(500);
  monthlyBill = signal(200);

  appliances = signal<Appliance[]>([
    { name: 'Air Conditioner', power: '1.5 kW', checked: false },
    { name: 'Refrigerator', power: '0.2 kW', checked: true },
    { name: 'Electric Water Heater', power: '3 kW', checked: false },
    { name: 'Electric Oven/Stove', power: '2 kW', checked: false },
    { name: 'TV/Electronics', power: '0.3 kW', checked: true },
    { name: 'LED Lighting', power: '0.1 kW', checked: false }
  ]);

  // Computed values based on inputs
  estimatedSavings = computed(() => {
    // Simple calculation logic: ~30% savings based on bill
    return (this.monthlyBill() * 0.30875).toFixed(2);
  });

  estimatedOutput = computed(() => {
    // Logic: Output proportional to usage (e.g., covering ~60% of needs)
    return Math.round(this.monthlyUsage() * 0.616);
  });

  estimatedCost = computed(() => {
    // Logic: Cost based on system size needed for output
    // Roughly 16.5 GHS per kWh capacity, multiplied by factors
    return (Number(this.estimatedOutput()) * 16.5).toFixed(2);
  });

  // Helper to handle range input changes
  onUsageChange(event: Event) {
    const value = (event.target as HTMLInputElement).value;
    this.monthlyUsage.set(Number(value));
  }
}
