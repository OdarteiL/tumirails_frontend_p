import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { LucideAngularModule, Target, ShieldCheck, BarChart3, Handshake, DollarSign, Award } from 'lucide-angular';
import { AuthService } from '../../../../services/auth.service';

@Component({
  selector: 'app-installers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './installers.html',
  styleUrl: './installers.css'
})
export class InstallersComponent {
  readonly Target = Target;
  readonly ShieldCheck = ShieldCheck;
  readonly BarChart3 = BarChart3;
  readonly Handshake = Handshake;
  readonly DollarSign = DollarSign;
  readonly Award = Award;

  applicationForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');
  successMessage = signal('');
  backendErrors = signal<Record<string, string[]>>({});

  // Ghana regions for service areas
  ghanaRegions = [
    'Greater Accra',
    'Ashanti',
    'Western',
    'Eastern',
    'Central',
    'Northern',
    'Upper East',
    'Upper West',
    'Volta',
    'Oti',
    'Bono',
    'Bono East',
    'Ahafo',
    'Western North',
    'North East',
    'Savannah'
  ];

  benefits = [
    {
      title: 'Access Qualified Leads',
      description: 'Connect with a growing network of homeowners and businesses actively seeking solar solutions and energy products.',
      icon: Target
    },
    {
      title: 'Ensure Regulatory Compliance',
      description: "Stay updated with Ghana's energy regulations and integrate seamlessly with Tumirails' compliant marketplace infrastructure.",
      icon: ShieldCheck
    },
    {
      title: 'Gain Performance Analytics',
      description: 'Utilize powerful dashboards to track sales, monitor installation projects, and optimize your business strategy.',
      icon: BarChart3
    },
    {
      title: 'Build Trusted Partnerships',
      description: "Join a community of certified professionals and grow your reputation within Ghana's decentralized energy sector.",
      icon: Handshake
    },
    {
      title: 'Streamlined Payments & Finance',
      description: 'Benefit from secure, efficient payment processing and transparent financial reporting within the Tumirails ecosystem.',
      icon: DollarSign
    },
    {
      title: 'Showcase Expertise & Certifications',
      description: 'Highlight your unique skills and official certifications to attract more high-value clients and build trust.',
      icon: Award
    }
  ];

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.applicationForm = this.fb.group({
      // User details
      first_name: ['', Validators.required],
      last_name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required],
      address: [''],

      // Business details
      company_name: [''],
      partnershipType: ['', Validators.required],

      // Installer-specific
      license_number: [''],
      years_experience: [''],

      // Provider-specific
      business_registration: [''],

      // Common fields
      service_areas: [[], Validators.required],
      certifications: ['']
    });

    // Update validators based on partnership type
    this.applicationForm.get('partnershipType')?.valueChanges.subscribe(type => {
      this.updateValidators(type);
    });
  }

  updateValidators(type: string) {
    const licenseControl = this.applicationForm.get('license_number');
    const yearsControl = this.applicationForm.get('years_experience');
    const brnControl = this.applicationForm.get('business_registration');

    // Clear all validators first
    licenseControl?.clearValidators();
    yearsControl?.clearValidators();
    brnControl?.clearValidators();

    if (type === 'installer' || type === 'both') {
      licenseControl?.setValidators([Validators.required]);
      yearsControl?.setValidators([Validators.required, Validators.min(0)]);
    }

    if (type === 'vendor' || type === 'both') {
      brnControl?.setValidators([Validators.required]);
    }

    licenseControl?.updateValueAndValidity();
    yearsControl?.updateValueAndValidity();
    brnControl?.updateValueAndValidity();
  }

  getFieldError(fieldName: string): string | null {
    const errors = this.backendErrors();
    return errors[fieldName] ? errors[fieldName][0] : null;
  }

  toggleServiceArea(region: string) {
    const currentAreas = this.applicationForm.get('service_areas')?.value || [];
    const index = currentAreas.indexOf(region);

    if (index > -1) {
      currentAreas.splice(index, 1);
    } else {
      currentAreas.push(region);
    }

    this.applicationForm.patchValue({ service_areas: currentAreas });
  }

  isServiceAreaSelected(region: string): boolean {
    const currentAreas = this.applicationForm.get('service_areas')?.value || [];
    return currentAreas.includes(region);
  }

  onSubmit() {
    if (this.applicationForm.valid) {
      this.isLoading.set(true);
      this.errorMessage.set('');
      this.successMessage.set('');
      this.backendErrors.set({});

      const formValue = this.applicationForm.value;
      const partnershipType = formValue.partnershipType;

      // Parse certifications from textarea to array
      const certifications = formValue.certifications
        ? formValue.certifications.split('\n').map((cert: string) => cert.trim()).filter((cert: string) => cert)
        : [];

      if (partnershipType === 'installer') {
        this.registerAsInstaller(formValue, certifications);
      } else if (partnershipType === 'vendor') {
        this.registerAsProvider(formValue, certifications);
      } else if (partnershipType === 'both') {
        // For "both", register as installer first (you can adjust this logic)
        this.registerAsInstaller(formValue, certifications);
      }
    } else {
      // Mark all fields as touched to show errors
      Object.keys(this.applicationForm.controls).forEach(key => {
        const control = this.applicationForm.get(key);
        control?.markAsTouched();
      });
    }
  }

  private registerAsInstaller(formValue: Record<string, unknown>, certifications: string[]) {
    const payload = {
      first_name: formValue['first_name'] as string,
      last_name: formValue['last_name'] as string,
      email: formValue['email'] as string,
      phone: formValue['phone'] as string,
      password: formValue['password'] as string,
      password_confirmation: formValue['password_confirmation'] as string,
      address: formValue['address'] as string || undefined,
      company_name: formValue['company_name'] as string || undefined,
      license_number: formValue['license_number'] as string,
      service_areas: formValue['service_areas'] as string[],
      certifications: certifications.length > 0 ? certifications : undefined,
      years_experience: parseInt(formValue['years_experience'] as string, 10)
    };

    this.authService.registerInstaller(payload).subscribe({
      next: () => {
        this.successMessage.set('Installer registration successful! Redirecting to dashboard...');
        setTimeout(() => this.router.navigate(['/dashboard']), 2000);
      },
      error: (error: { error?: { errors?: Record<string, string[]>; message?: string; error?: string } }) => {
        if (error.error?.errors) {
          this.backendErrors.set(error.error.errors);
        }
        this.errorMessage.set(error.error?.message || error.error?.error || 'Registration failed');
        this.isLoading.set(false);
      }
    });
  }

  private registerAsProvider(formValue: Record<string, unknown>, certifications: string[]) {
    const payload = {
      first_name: formValue['first_name'] as string,
      last_name: formValue['last_name'] as string,
      email: formValue['email'] as string,
      phone: formValue['phone'] as string,
      password: formValue['password'] as string,
      password_confirmation: formValue['password_confirmation'] as string,
      address: formValue['address'] as string || undefined,
      company_name: formValue['company_name'] as string || undefined,
      business_registration: formValue['business_registration'] as string,
      service_areas: formValue['service_areas'] as string[],
      certifications: certifications.length > 0 ? certifications : undefined
    };

    this.authService.registerProvider(payload).subscribe({
      next: () => {
        this.successMessage.set('Provider registration successful! Redirecting to dashboard...');
        setTimeout(() => this.router.navigate(['/dashboard']), 2000);
      },
      error: (error: { error?: { errors?: Record<string, string[]>; message?: string; error?: string } }) => {
        if (error.error?.errors) {
          this.backendErrors.set(error.error.errors);
        }
        this.errorMessage.set(error.error?.message || error.error?.error || 'Registration failed');
        this.isLoading.set(false);
      }
    });
  }

  // Smooth scroll to form
  scrollToForm() {
    document.getElementById('application-form')?.scrollIntoView({ behavior: 'smooth' });
  }
}
