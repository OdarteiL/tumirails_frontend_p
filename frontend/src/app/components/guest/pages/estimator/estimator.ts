import { Component, computed, signal, effect, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule, DollarSign, Zap, Sun, Loader2, Plus, Trash2, X, Hash } from 'lucide-angular';
import { EstimationsService } from '../../../../services/estimations.service';
import { GuestEstimationRequest, GuestEstimationResponse, GuestEstimation } from '../../../../models/estimation.model';
import { catchError, of } from 'rxjs';

interface Appliance {
  name: string;
  wattage: number;
  quantity: number;
  daily_usage_hours: number;
}

@Component({
  selector: 'app-estimator',
  standalone: true,
  imports: [CommonModule, FormsModule, LucideAngularModule],
  templateUrl: './estimator.html',
  styleUrl: './estimator.css'
})
export class EstimatorComponent {
  private estimationsService = inject(EstimationsService);
  private errorTimeout?: any;

  readonly DollarSign = DollarSign;
  readonly Zap = Zap;
  readonly Sun = Sun;
  readonly Loader2 = Loader2;
  readonly Plus = Plus;
  readonly Trash2 = Trash2;
  readonly X = X;
  readonly Hash = Hash;

  // Modal and Ref Code state
  showRefCodeModal = signal(true);
  tempRefCode = '';
  refCode = signal<string | null>(null);

  // Estimation state
  isLoading = signal(false);
  error = signal<string | null>(null);
  showValidation = signal(false);
  estimationResult = signal<GuestEstimation | null>(null);

  // Appliance state
  appliances = signal<Appliance[]>([
    { name: 'Refrigerator', wattage: 150, quantity: 1, daily_usage_hours: 24 },
    { name: 'LED Lighting', wattage: 10, quantity: 10, daily_usage_hours: 6 }
  ]);

  commonAppliances = [
    { name: 'Air Conditioner', wattage: 1500 },
    { name: 'Refrigerator', wattage: 150 },
    { name: 'Electric Water Heater', wattage: 3000 },
    { name: 'Electric Oven/Stove', wattage: 2000 },
    { name: 'TV/Electronics', wattage: 100 },
    { name: 'LED Lighting', wattage: 10 },
    { name: 'Ceiling Fan', wattage: 75 },
    { name: 'Laptop/PC', wattage: 65 },
    { name: 'Washing Machine', wattage: 500 },
    { name: 'Microwave', wattage: 1200 }
  ];

  // Output mappings from backend result
  monthlyUsage = computed(() => this.estimationResult()?.monthly_kwh || '0.00');
  monthlyBill = computed(() => this.estimationResult()?.estimated_monthly_cost || '0.00');
  dailyKwh = computed(() => this.estimationResult()?.daily_kwh || '0.00');
  totalWatts = computed(() => this.estimationResult()?.total_watts || '0.00');

  // These are still derived from backend results for UX purposes 
  // (though backend might provide them in a fuller recommendation API eventually)
  estimatedSavings = computed(() => {
    const bill = Number(this.monthlyBill());
    return (bill * 0.30875).toFixed(2);
  });

  estimatedOutput = computed(() => {
    const usage = Number(this.monthlyUsage());
    return Math.round(usage * 0.616);
  });

  estimatedCost = computed(() => {
    const output = this.estimatedOutput();
    return (output * 65.5).toFixed(2); // Updated cost factor for premium feel
  });

  constructor() { }

  calculateEstimation() {
    this.showValidation.set(true);
    const apps = this.appliances();

    // Basic validation
    const hasInvalid = apps.some(a => !this.isApplianceValid(a));
    if (hasInvalid) {
      this.setError('Please fix the errors in your appliance list before submitting.');
      return;
    }

    if (apps.length === 0) {
      this.setError('Please add at least one appliance.');
      return;
    }

    this.isLoading.set(true);
    this.error.set(null);

    const request: GuestEstimationRequest = {
      appliances: apps.map(a => ({
        name: a.name,
        wattage: a.wattage,
        quantity: a.quantity,
        daily_usage_hours: a.daily_usage_hours
      }))
    };

    this.estimationsService.createGuestEstimation(request).pipe(
      catchError(err => {
        console.error('Estimation error:', err);
        this.setError('Failed to calculate estimation. Please check your inputs.');
        this.isLoading.set(false);
        return of(null);
      })
    ).subscribe(result => {
      this.isLoading.set(false);
      if (result && result.success) {
        this.estimationResult.set(result.data);
        if (result.data.ref_code) {
          this.refCode.set(result.data.ref_code);
        }
      }
    });
  }

  addAppliance(suggestion?: any) {
    this.appliances.update(apps => [
      ...apps,
      {
        name: suggestion?.name || '',
        wattage: suggestion?.wattage || 100,
        quantity: 1,
        daily_usage_hours: 4
      }
    ]);
  }

  removeAppliance(index: number) {
    this.appliances.update(apps => apps.filter((_, i) => i !== index));
  }

  updateAppliance(index: number, field: keyof Appliance, value: string | number) {
    this.appliances.update(apps => {
      const newApps = [...apps];
      (newApps[index] as any)[field] = field === 'name' ? value : Number(value);
      return newApps;
    });
  }

  isApplianceValid(app: Appliance): boolean {
    return !!(
      app.name.trim() &&
      app.wattage > 0 &&
      app.quantity >= 1 &&
      app.daily_usage_hours >= 1 &&
      app.daily_usage_hours <= 24
    );
  }

  private setError(message: string) {
    this.error.set(message);
    if (this.errorTimeout) {
      clearTimeout(this.errorTimeout);
    }
    this.errorTimeout = setTimeout(() => {
      this.error.set(null);
      this.showValidation.set(false);
    }, 10000);
  }

  handleRefCodeSubmit() {
    if (!this.tempRefCode.trim()) return;

    this.isLoading.set(true);
    this.error.set(null);
    this.estimationsService.getGuestEstimationByCode(this.tempRefCode).subscribe({
      next: (res: GuestEstimationResponse) => {
        this.isLoading.set(false);
        if (res.success) {
          this.estimationResult.set(res.data);
          this.refCode.set(res.data.ref_code || this.tempRefCode);

          // Try to reconstruct appliances from breakdown if available
          if (res.data.appliances_breakdown) {
            this.appliances.set(res.data.appliances_breakdown.map((a: any) => ({
              name: a.name,
              wattage: a.watts,
              quantity: a.quantity,
              daily_usage_hours: a.daily_usage_hours
            })));
          }
          this.showRefCodeModal.set(false);
        }
      },
      error: (err: any) => {
        this.isLoading.set(false);
        this.setError('Reference code not found or invalid.');
      }
    });
  }

  closeModal() {
    this.showRefCodeModal.set(false);
  }
}
