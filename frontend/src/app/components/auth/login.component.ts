import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  template: `
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
      <div class="card" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
          <h2 style="font-size: 1.875rem; font-weight: bold; margin-bottom: 1rem;">
            Sign in to Tumi Configurator
          </h2>
        </div>
        <form [formGroup]="loginForm" (ngSubmit)="onSubmit()">
          <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              type="email"
              formControlName="email"
              class="form-input"
              [style.border-color]="loginForm.get('email')?.invalid && loginForm.get('email')?.touched ? '#dc2626' : ''"
            />
            <div *ngIf="loginForm.get('email')?.invalid && loginForm.get('email')?.touched" class="error">
              Email is required and must be valid
            </div>
          </div>
          
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input
              id="password"
              type="password"
              formControlName="password"
              class="form-input"
              [style.border-color]="loginForm.get('password')?.invalid && loginForm.get('password')?.touched ? '#dc2626' : ''"
            />
            <div *ngIf="loginForm.get('password')?.invalid && loginForm.get('password')?.touched" class="error">
              Password is required
            </div>
          </div>

          <div *ngIf="errorMessage()" class="error text-center mb-4">
            {{ errorMessage() }}
          </div>

          <button
            type="submit"
            [disabled]="loginForm.invalid || isLoading()"
            class="btn btn-primary"
            style="width: 100%;"
          >
            {{ isLoading() ? 'Signing in...' : 'Sign in' }}
          </button>

          <div class="text-center mt-4">
            <p style="font-size: 0.875rem; color: #6b7280;">
              Don't have an account?
              <a routerLink="/register" style="color: #4f46e5; text-decoration: none;">
                Sign up
              </a>
            </p>
          </div>
        </form>
      </div>
    </div>
  `
})
export class LoginComponent {
  loginForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required]
    });
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.isLoading.set(true);
      this.errorMessage.set('');

      this.authService.login(this.loginForm.value).subscribe({
        next: () => {
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          this.errorMessage.set(error.error?.message || 'Login failed');
          this.isLoading.set(false);
        }
      });
    }
  }
}