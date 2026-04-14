import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { LucideAngularModule, User, Lock, Bell } from 'lucide-angular';

@Component({
  selector: 'app-admin-settings',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Settings'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="max-w-2xl space-y-6">

        <!-- Profile -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <lucide-icon [img]="User" [size]="20" class="text-primary"></lucide-icon>
            <h2 class="font-bold text-slate-900">Profile</h2>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">First Name</label>
                <input [(ngModel)]="profile.first_name" name="first_name"
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Last Name</label>
                <input [(ngModel)]="profile.last_name" name="last_name"
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
              <input [(ngModel)]="profile.email" name="email" type="email" disabled
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 text-slate-400 cursor-not-allowed" />
              <p class="mt-1 text-xs text-slate-400">Email cannot be changed here.</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone</label>
              <input [(ngModel)]="profile.phone" name="phone"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
            </div>
            <div *ngIf="profileMsg()" class="text-sm px-4 py-2.5 rounded-xl"
              [class]="profileMsg()!.startsWith('✓') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
              {{ profileMsg() }}
            </div>
            <button (click)="saveProfile()" [disabled]="savingProfile()"
              class="px-6 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl hover:bg-primary/90 disabled:opacity-50 transition-colors">
              {{ savingProfile() ? 'Saving...' : 'Save Profile' }}
            </button>
          </div>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <lucide-icon [img]="Lock" [size]="20" class="text-primary"></lucide-icon>
            <h2 class="font-bold text-slate-900">Change Password</h2>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
              <input [(ngModel)]="pwd.current" name="current" type="password"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                <input [(ngModel)]="pwd.new" name="new" type="password"
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                <input [(ngModel)]="pwd.confirm" name="confirm" type="password"
                  class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary/20" />
              </div>
            </div>
            <div *ngIf="pwdMsg()" class="text-sm px-4 py-2.5 rounded-xl"
              [class]="pwdMsg()!.startsWith('✓') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
              {{ pwdMsg() }}
            </div>
            <button (click)="changePassword()" [disabled]="savingPwd()"
              class="px-6 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl hover:bg-primary/90 disabled:opacity-50 transition-colors">
              {{ savingPwd() ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </div>

        <!-- Notifications (UI only — no backend endpoint yet) -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <lucide-icon [img]="Bell" [size]="20" class="text-primary"></lucide-icon>
            <h2 class="font-bold text-slate-900">Notifications</h2>
          </div>
          <div class="p-6 space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" [(ngModel)]="notif.email" class="w-4 h-4 rounded text-primary" />
              <span class="text-sm text-slate-700">Email notifications for new user registrations</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" [(ngModel)]="notif.enquiries" class="w-4 h-4 rounded text-primary" />
              <span class="text-sm text-slate-700">Email notifications for new enquiries</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" [(ngModel)]="notif.orgs" class="w-4 h-4 rounded text-primary" />
              <span class="text-sm text-slate-700">Email notifications for new organisation registrations</span>
            </label>
            <p class="text-xs text-slate-400">Notification delivery settings coming soon.</p>
          </div>
        </div>

      </div>
    </app-admin-layout>
  `
})
export class AdminSettingsComponent implements OnInit {
  private authService = inject(AuthService);

  userName = signal('');
  userInitials = signal('');
  savingProfile = signal(false);
  savingPwd = signal(false);
  profileMsg = signal<string | null>(null);
  pwdMsg = signal<string | null>(null);

  profile = { first_name: '', last_name: '', email: '', phone: '' };
  pwd = { current: '', new: '', confirm: '' };
  notif = { email: true, enquiries: true, orgs: false };

  readonly User = User; readonly Lock = Lock; readonly Bell = Bell;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userInitials.set(`${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`);
      this.profile = { first_name: user.first_name, last_name: user.last_name, email: user.email, phone: user.phone ?? '' };
    }
  }

  saveProfile(): void {
    // No profile update endpoint exists yet — show informational message
    this.profileMsg.set('Profile update endpoint not yet available on the backend.');
  }

  changePassword(): void {
    if (this.pwd.new !== this.pwd.confirm) {
      this.pwdMsg.set('New passwords do not match.');
      return;
    }
    this.savingPwd.set(true);
    this.pwdMsg.set(null);
    this.authService.changePassword({
      current_password: this.pwd.current,
      password: this.pwd.new,
      password_confirmation: this.pwd.confirm
    }).subscribe({
      next: () => {
        this.pwdMsg.set('✓ Password updated successfully.');
        this.pwd = { current: '', new: '', confirm: '' };
        this.savingPwd.set(false);
      },
      error: (err) => {
        this.pwdMsg.set(err?.error?.error ?? err?.error?.message ?? 'Failed to update password.');
        this.savingPwd.set(false);
      }
    });
  }
}
