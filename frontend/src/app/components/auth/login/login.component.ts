import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { NavbarComponent } from '../../guest/navbar/navbar';
import { FooterComponent } from '../../guest/footer/footer';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule, NavbarComponent, FooterComponent],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  loginForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');
  backendErrors = signal<Record<string, string[]>>({});

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

  getFieldError(fieldName: string): string | null {
    const errors = this.backendErrors();
    return errors[fieldName] ? errors[fieldName][0] : null;
  }

  onSubmit(): void {
    if (this.loginForm.valid) {
      this.isLoading.set(true);
      this.errorMessage.set('');
      this.backendErrors.set({});

      this.authService.login(this.loginForm.value).subscribe({
        next: () => {
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          // Set field-specific errors if available
          if (error.error?.errors) {
            this.backendErrors.set(error.error.errors);
          }
          // Set general error message
          this.errorMessage.set(error.error?.message || error.error?.error || 'Login failed');
          this.isLoading.set(false);
        }
      });
    }
  }
}