import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import { LucideAngularModule, LayoutDashboard, ShoppingBag, History, Settings, User, Lock, Bell, ClipboardCheck } from 'lucide-angular';

@Component({
  selector: 'app-customer-settings',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, LucideAngularModule],
  template: `
    <app-admin-layout 
      pageTitle="Settings" 
      [userName]="userName()"
      [userRole]="userRole()"
      [userInitials]="userInitials()">
      
      <ng-container sidebarMenu>
        <a routerLink="/customer/dashboard" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="LayoutDashboard" [size]="20"></lucide-icon>
          <span>Dashboard</span>
        </a>
        <a routerLink="/customer/marketplace" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="ShoppingBag" [size]="20"></lucide-icon>
          <span>Marketplace</span>
        </a>
        <a routerLink="/customer/estimations" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="History" [size]="20"></lucide-icon>
          <span>My Estimations</span>
        </a>
        <a routerLink="/customer/site-assessments" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="ClipboardCheck" [size]="20"></lucide-icon>
          <span>Site Assessments</span>
        </a>
        <a routerLink="/customer/settings" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="Settings" [size]="20"></lucide-icon>
          <span>Settings</span>
        </a>
      </ng-container>

      <div class="space-y-6">
        <!-- Profile Settings -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <lucide-icon [img]="User" [size]="24" class="text-primary"></lucide-icon>
            <h2 class="text-2xl font-bold text-slate-900">Profile Settings</h2>
          </div>
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">First Name</label>
                <input type="text" [(ngModel)]="profile.firstName" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Last Name</label>
                <input type="text" [(ngModel)]="profile.lastName" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                <input type="email" [(ngModel)]="profile.email" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Phone</label>
                <input type="tel" [(ngModel)]="profile.phone" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                <textarea [(ngModel)]="profile.address" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
              </div>
            </div>
            <div class="mt-6">
              <button (click)="saveProfile()" class="bg-primary text-white px-6 py-3 rounded-xl font-medium hover:bg-primary/90 transition-all">
                Save Changes
              </button>
            </div>
          </div>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <lucide-icon [img]="Lock" [size]="24" class="text-primary"></lucide-icon>
            <h2 class="text-2xl font-bold text-slate-900">Change Password</h2>
          </div>
          <div class="p-8">
            <div class="space-y-4 max-w-md">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Current Password</label>
                <input type="password" [(ngModel)]="password.current" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                <input type="password" [(ngModel)]="password.new" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Confirm New Password</label>
                <input type="password" [(ngModel)]="password.confirm" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent">
              </div>
              <button (click)="changePassword()" class="bg-primary text-white px-6 py-3 rounded-xl font-medium hover:bg-primary/90 transition-all">
                Update Password
              </button>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <lucide-icon [img]="Bell" [size]="24" class="text-primary"></lucide-icon>
            <h2 class="text-2xl font-bold text-slate-900">Notifications</h2>
          </div>
          <div class="p-8">
            <div class="space-y-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" [(ngModel)]="notifications.email" class="w-5 h-5 text-primary rounded focus:ring-primary">
                <span class="text-slate-700">Email notifications</span>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" [(ngModel)]="notifications.sms" class="w-5 h-5 text-primary rounded focus:ring-primary">
                <span class="text-slate-700">SMS notifications</span>
              </label>
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" [(ngModel)]="notifications.marketing" class="w-5 h-5 text-primary rounded focus:ring-primary">
                <span class="text-slate-700">Marketing updates</span>
              </label>
            </div>
            <div class="mt-6">
              <button (click)="saveNotifications()" class="bg-primary text-white px-6 py-3 rounded-xl font-medium hover:bg-primary/90 transition-all">
                Save Preferences
              </button>
            </div>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class CustomerSettingsComponent implements OnInit {
  private authService = inject(AuthService);

  userName = signal('');
  userRole = signal('');
  userInitials = signal('');

  profile = {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    address: ''
  };

  password = {
    current: '',
    new: '',
    confirm: ''
  };

  notifications = {
    email: true,
    sms: false,
    marketing: false
  };

  readonly LayoutDashboard = LayoutDashboard;
  readonly ShoppingBag = ShoppingBag;
  readonly History = History;
  readonly Settings = Settings;
  readonly User = User;
  readonly Lock = Lock;
  readonly Bell = Bell;
  readonly ClipboardCheck = ClipboardCheck;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userRole.set(user.role);
      this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
      
      this.profile.firstName = user.first_name;
      this.profile.lastName = user.last_name;
      this.profile.email = user.email;
    }
  }

  saveProfile() {
    console.log('Saving profile:', this.profile);
    alert('Profile updated successfully!');
  }

  changePassword() {
    if (this.password.new !== this.password.confirm) {
      alert('Passwords do not match!');
      return;
    }
    this.authService.changePassword({
      current_password: this.password.current,
      password: this.password.new,
      password_confirmation: this.password.confirm
    }).subscribe({
      next: () => {
        alert('Password updated successfully!');
        this.password = { current: '', new: '', confirm: '' };
      },
      error: (err) => alert(err.error?.message || 'Failed to update password.')
    });
  }

  saveNotifications() {
    console.log('Saving notifications:', this.notifications);
    alert('Notification preferences saved!');
  }
}
