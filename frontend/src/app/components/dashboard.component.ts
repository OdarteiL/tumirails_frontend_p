import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { User } from '../models/user.model';
import { AdminLayoutComponent } from './admin/admin-layout/admin-layout';
import { LucideAngularModule, LayoutDashboard, ShoppingBag, Package, Users, Settings } from 'lucide-angular';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule, LucideAngularModule],
  template: `
    <div class="min-h-screen bg-[#F4F5F7]">
      <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 min-h-screen flex flex-col">
          <div class="p-6 border-b border-gray-200 flex justify-center">
            <a routerLink="/" class="block hover:opacity-80 transition-opacity">
              <img src="/assets/tumi_logo.png" alt="Tumi Solar" class="h-8 w-auto">
            </a>
          </div>
          <div class="p-6 flex-1">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Admin Portal</h2>
            <nav class="space-y-1">
              <a routerLink="/admin/dashboard" routerLinkActive="!text-blue-600 !bg-blue-50" 
                 class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                <lucide-icon [img]="LayoutDashboard" [size]="18"></lucide-icon>
                <span>Dashboard</span>
              </a>
              <a routerLink="/admin/orders" routerLinkActive="!text-blue-600 !bg-blue-50"
                 class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                <lucide-icon [img]="ShoppingBag" [size]="18"></lucide-icon>
                <span>Orders</span>
              </a>
              <a routerLink="/admin/products" routerLinkActive="!text-blue-600 !bg-blue-50"
                 class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                <lucide-icon [img]="Package" [size]="18"></lucide-icon>
                <span>Inventory</span>
              </a>
              <a routerLink="/admin/customers" routerLinkActive="!text-blue-600 !bg-blue-50"
                 class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                <lucide-icon [img]="Users" [size]="18"></lucide-icon>
                <span>Users</span>
              </a>
              <a routerLink="/admin/settings" routerLinkActive="!text-blue-600 !bg-blue-50"
                 class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">
                <lucide-icon [img]="Settings" [size]="18"></lucide-icon>
                <span>Settings</span>
              </a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-600 mt-1">Welcome back, {{ currentUser()?.first_name }}</p>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Account Status</div>
              <div class="text-sm font-medium capitalize">{{ currentUser()?.status }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Role</div>
              <div class="text-sm font-medium capitalize">{{ currentUser()?.role }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Email</div>
              <div class="text-sm font-medium">{{ currentUser()?.email }}</div>
            </div>
          </div>

          <!-- Activity -->
          <div class="bg-white border border-gray-200 rounded">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-base font-semibold text-gray-900">Recent Activity</h2>
            </div>
            <div class="p-6">
              <div class="text-center py-12 text-gray-500 text-sm">
                No recent activity to display
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  `
})
export class DashboardComponent implements OnInit {
  currentUser = signal<User | null>(null);

  // Icons for template
  readonly LayoutDashboard = LayoutDashboard;
  readonly ShoppingBag = ShoppingBag;
  readonly Package = Package;
  readonly Users = Users;
  readonly Settings = Settings;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    this.currentUser = this.authService.currentUser;
  }

  ngOnInit(): void {
    // Optionally refresh user data
    this.authService.getCurrentUser().subscribe({
      error: (error: unknown) => {
        console.error('Failed to fetch user data:', error);
      }
    });
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/login']);
      },
      error: (error: unknown) => {
        console.error('Logout error:', error);
        this.router.navigate(['/login']);
      }
    });
  }
}