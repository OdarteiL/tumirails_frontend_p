import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  template: `
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
      <div class="card" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
          <h2 style="font-size: 1.875rem; font-weight: bold; margin-bottom: 1rem;">
            Create your account
          </h2>
        </div>
        <form [formGroup]="registerForm" (ngSubmit)="onSubmit()">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
              <label for="first_name" class="form-label">First Name</label>
              <input
                id="first_name"
                type="text"
                formControlName="first_name"
                class="form-input"
                [style.border-color]="registerForm.get('first_name')?.invalid && registerForm.get('first_name')?.touched ? '#dc2626' : ''"
              />
            </div>
            <div class="form-group" style="margin-bottom: 0;">
              <label for="last_name" class="form-label">Last Name</label>
              <input
                id="last_name"
                type="text"
                formControlName="last_name"
                class="form-input"
                [style.border-color]="registerForm.get('last_name')?.invalid && registerForm.get('last_name')?.touched ? '#dc2626' : ''"
              />
            </div>
          </div>
          
          <div class="form-group">
            <label for="other_names" class="form-label">Other Names (Optional)</label>
            <input
              id="other_names"
              type="text"
              formControlName="other_names"
              class="form-input"
            />
          </div>
          
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              type="email"
              formControlName="email"
              class="form-input"
              [style.border-color]="registerForm.get('email')?.invalid && registerForm.get('email')?.touched ? '#dc2626' : ''"
            />
          </div>
          
          <div class="form-group">
            <label for="phone" class="form-label">Phone (Optional)</label>
            <input
              id="phone"
              type="tel"
              formControlName="phone"
              class="form-input"
            />
          </div>
          
          <div class="form-group">
            <label for="address" class="form-label">Address (Optional)</label>
            <textarea
              id="address"
              formControlName="address"
              class="form-input"
              rows="2"
            ></textarea>
          </div>
          
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              type="password"
              formControlName="password"
              class="form-input"
              [style.border-color]="registerForm.get('password')?.invalid && registerForm.get('password')?.touched ? '#dc2626' : ''"
            />
            <div *ngIf="registerForm.get('password')?.invalid && registerForm.get('password')?.touched" class="error">
              Password must be at least 8 characters
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input
              id="password_confirmation"
              type="password"
              formControlName="password_confirmation"
              class="form-input"
              [style.border-color]="registerForm.get('password_confirmation')?.invalid && registerForm.get('password_confirmation')?.touched ? '#dc2626' : ''"
            />
            <div *ngIf="registerForm.get('password_confirmation')?.invalid && registerForm.get('password_confirmation')?.touched" class="error">
              Please confirm your password
            </div>
            <div *ngIf="registerForm.hasError('passwordMismatch') && registerForm.get('password_confirmation')?.touched" class="error">
              Passwords do not match
            </div>
          </div>

          <div *ngIf="errorMessage()" class="error text-center mb-4">
            {{ errorMessage() }}
          </div>

          <button
            type="submit"
            [disabled]="registerForm.invalid || isLoading()"
            class="btn btn-primary"
            style="width: 100%;"
          >
            {{ isLoading() ? 'Creating account...' : 'Create account' }}
          </button>

          <div class="text-center mt-4">
            <p style="font-size: 0.875rem; color: #6b7280;">
              Already have an account?
              <a routerLink="/login" style="color: #4f46e5; text-decoration: none;">
                Sign in
              </a>
            </p>
          </div>
        </form>
      </div>
    </div>
  `
})
export class RegisterComponent {
  registerForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');

  passwordMatchValidator(form: FormGroup): { [key: string]: boolean } | null {
    const password = form.get('password');
    const passwordConfirmation = form.get('password_confirmation');
    
    if (!password || !passwordConfirmation) {
      return null;
    }
    
    return password.value === passwordConfirmation.value ? null : { passwordMismatch: true };
  }

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.registerForm = this.fb.group({
      first_name: ['', Validators.required],
      last_name: ['', Validators.required],
      other_names: [''],
      email: ['', [Validators.required, Validators.email]],
      phone: [''],
      address: [''],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required]
    }, { validators: this.passwordMatchValidator });
  }

  onSubmit(): void {
    if (this.registerForm.valid) {
      this.isLoading.set(true);
      this.errorMessage.set('');

      this.authService.register(this.registerForm.value).subscribe({
        next: () => {
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          this.errorMessage.set(error.error?.message || 'Registration failed');
          this.isLoading.set(false);
        }
      });
    }
  }
}