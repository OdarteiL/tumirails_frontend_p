import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { LucideAngularModule, Eye, EyeOff } from 'lucide-angular';

@Component({
  selector: 'app-register-installer',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule, LucideAngularModule],
  templateUrl: './register-installer.component.html',
  styleUrls: ['../register/register.component.css']
})
export class RegisterInstallerComponent {
  registerForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');
  backendErrors = signal<Record<string, string[]>>({});
  showPassword = false;
  readonly Eye = Eye;
  readonly EyeOff = EyeOff;

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
      license_number: ['', Validators.required],
      service_areas: ['', Validators.required],
      years_experience: [0, [Validators.required, Validators.min(0)]],
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

      this.authService.registerInstaller(payload).subscribe({
        next: () => this.router.navigate(['/installer/dashboard']),
        error: (error) => {
          if (error.error?.errors) this.backendErrors.set(error.error.errors);
          this.errorMessage.set(error.error?.message || 'Registration failed');
          this.isLoading.set(false);
        }
      });
    }
  }
}
