import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { NavbarComponent } from '../../guest/navbar/navbar';
import { FooterComponent } from '../../guest/footer/footer';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule, NavbarComponent, FooterComponent],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent {
  registerForm: FormGroup;
  isLoading = signal(false);
  errorMessage = signal('');
  backendErrors = signal<Record<string, string[]>>({});

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

  getFieldError(fieldName: string): string | null {
    const errors = this.backendErrors();
    return errors[fieldName] ? errors[fieldName][0] : null;
  }

  onSubmit(): void {
    if (this.registerForm.valid) {
      this.isLoading.set(true);
      this.errorMessage.set('');
      this.backendErrors.set({});

      const payload = {
        ...this.registerForm.value,
        role: 'customer'
      };

      this.authService.register(payload).subscribe({
        next: () => {
          this.router.navigate(['/dashboard']);
        },
        error: (error) => {
          // Set field-specific errors if available
          if (error.error?.errors) {
            this.backendErrors.set(error.error.errors);
          }
          // Set general error message
          this.errorMessage.set(error.error?.message || error.error?.error || 'Registration failed');
          this.isLoading.set(false);
        }
      });
    }
  }
}