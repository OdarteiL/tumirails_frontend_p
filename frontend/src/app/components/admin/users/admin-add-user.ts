import { Component, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { AdminService } from '../../../services/admin.service';
import { LucideAngularModule, ArrowLeft } from 'lucide-angular';

@Component({
  selector: 'app-admin-add-user',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Add User'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="max-w-2xl">
        <a routerLink="/admin/users" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary mb-6 transition-colors">
          <lucide-icon [img]="ArrowLeft" [size]="16"></lucide-icon> Back to Users
        </a>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8">
          <h2 class="text-lg font-bold text-slate-900 mb-6">New User Details</h2>

          <form (ngSubmit)="submit()" class="space-y-6">
            <!-- Name -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                <input [(ngModel)]="form.first_name" name="first_name" required
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
                <p *ngIf="fieldError('first_name')" class="mt-1 text-xs text-red-500">{{ fieldError('first_name') }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                <input [(ngModel)]="form.last_name" name="last_name" required
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
                <p *ngIf="fieldError('last_name')" class="mt-1 text-xs text-red-500">{{ fieldError('last_name') }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Other Names</label>
              <input [(ngModel)]="form.other_names" name="other_names"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
            </div>

            <!-- Contact -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
              <input [(ngModel)]="form.email" name="email" type="email" required
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              <p *ngIf="fieldError('email')" class="mt-1 text-xs text-red-500">{{ fieldError('email') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone</label>
                <input [(ngModel)]="form.phone" name="phone"
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                <select [(ngModel)]="form.role" name="role"
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20">
                  <option value="customer">Customer</option>
                  <option value="installer">Installer</option>
                  <option value="provider">Provider</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
              <input [(ngModel)]="form.address" name="address"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input [(ngModel)]="form.password" name="password" type="password" required
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
                <p *ngIf="fieldError('password')" class="mt-1 text-xs text-red-500">{{ fieldError('password') }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                <input [(ngModel)]="form.password_confirmation" name="password_confirmation" type="password" required
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              </div>
            </div>

            <div *ngIf="generalError()" class="text-sm text-red-600 bg-red-50 rounded-xl px-4 py-3">{{ generalError() }}</div>
            <div *ngIf="success()" class="text-sm text-green-700 bg-green-50 rounded-xl px-4 py-3">User created successfully! Redirecting...</div>

            <div class="flex gap-3 pt-2">
              <a routerLink="/admin/users"
                class="px-6 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Cancel
              </a>
              <button type="submit" [disabled]="saving()"
                class="px-8 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl hover:bg-primary/90 disabled:opacity-50 transition-colors">
                {{ saving() ? 'Creating...' : 'Create User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class AdminAddUserComponent {
  private authService = inject(AuthService);
  private adminService = inject(AdminService);
  private router = inject(Router);

  userName = signal('');
  userInitials = signal('');
  saving = signal(false);
  success = signal(false);
  generalError = signal('');
  fieldErrors = signal<Record<string, string[]>>({});

  form = { first_name: '', last_name: '', other_names: '', email: '', phone: '', address: '', role: 'customer', password: '', password_confirmation: '' };

  readonly ArrowLeft = ArrowLeft;

  constructor() {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userInitials.set(`${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`);
    }
  }

  fieldError(field: string): string | null {
    return this.fieldErrors()[field]?.[0] ?? null;
  }

  submit(): void {
    this.saving.set(true);
    this.generalError.set('');
    this.fieldErrors.set({});

    this.adminService.createUser(this.form).subscribe({
      next: () => {
        this.success.set(true);
        setTimeout(() => this.router.navigate(['/admin/users']), 1200);
      },
      error: (err) => {
        if (err?.error?.errors) this.fieldErrors.set(err.error.errors);
        else this.generalError.set(err?.error?.message ?? 'Failed to create user');
        this.saving.set(false);
      }
    });
  }
}
