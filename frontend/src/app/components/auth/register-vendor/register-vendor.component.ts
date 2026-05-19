import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-register-vendor',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './register-vendor.component.html',
  styleUrls: ['../register/register.component.css']
})
export class RegisterVendorComponent {
  registerForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');
  backendErrors = signal<Record<string, string[]>>({});

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.registerForm = this.fb.group({
      first_name: ['', Validators.required],
      last_name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: [''],
      company_name: ['', Validators.required],
      business_registration: ['', Validators.required],
      service_areas: ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required]
    }, { validators: this.passwordMatchValidator });
  }

  passwordMatchValidator(form: FormGroup): { [key: string]: boolean } | null {
    const password = form.get('password');
    const confirm = form.get('password_confirmation');
    if (!password || !confirm) return null;
    return password.value === confirm.value ? null : { passwordMismatch: true };
  }

  getFieldError(fieldName: string): string | null {
    const errors = this.backendErrors();
    return errors[fieldName] ? errors[fieldName][0] : null;
  }

  onSubmit(): void {
    if (this.registerForm.valid) {
      this.isLoading.set(true);
      this.errorMessage.set('');
      this.backendErrors.set({});

      const formValue = this.registerForm.value;
      const payload = {
        ...formValue,
        service_areas: formValue.service_areas.split(',').map((s: string) => s.trim())
      };

      this.authService.registerProvider(payload).subscribe({
        next: () => this.router.navigate(['/vendor/dashboard']),
        error: (error) => {
          if (error.error?.errors) this.backendErrors.set(error.error.errors);
          this.errorMessage.set(error.error?.message || 'Registration failed');
          this.isLoading.set(false);
        }
      });
    }
  }
}
