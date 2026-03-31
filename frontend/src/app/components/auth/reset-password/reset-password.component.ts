import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.css']
})
export class ResetPasswordComponent {
  form: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');
  backendErrors = signal<Record<string, string[]>>({});

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    const token = this.route.snapshot.queryParamMap.get('token') ?? '';
    const email = this.route.snapshot.queryParamMap.get('email') ?? '';

    this.form = this.fb.group({
      email: [email, [Validators.required, Validators.email]],
      token: [token, Validators.required],
      password: ['', [Validators.required, Validators.minLength(8)]],
      password_confirmation: ['', Validators.required]
    }, { validators: this.passwordMatchValidator });
  }

  passwordMatchValidator(form: FormGroup) {
    const p = form.get('password');
    const c = form.get('password_confirmation');
    return p && c && p.value !== c.value ? { passwordMismatch: true } : null;
  }

  getFieldError(field: string): string | null {
    const errors = this.backendErrors();
    return errors[field] ? errors[field][0] : null;
  }

  onSubmit(): void {
    if (this.form.invalid) return;
    this.isLoading.set(true);
    this.errorMessage.set('');
    this.backendErrors.set({});

    this.authService.resetPassword(this.form.value).subscribe({
      next: () => {
        this.router.navigate(['/login'], { queryParams: { reset: 'success' } });
      },
      error: (err) => {
        if (err.error?.errors) this.backendErrors.set(err.error.errors);
        this.errorMessage.set(err.error?.error || 'Failed to reset password. The link may have expired.');
        this.isLoading.set(false);
      }
    });
  }
}
