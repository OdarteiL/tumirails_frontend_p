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
  imports: [CommonModule, RouterModule, AdminLayoutComponent, LucideAngularModule],
  template: `
    <app-admin-layout 
      pageTitle="Admin Dashboard" 
      [userName]="currentUser()?.first_name + ' ' + currentUser()?.last_name"
      [userRole]="currentUser()?.role || ''"
      [userInitials]="(currentUser()?.first_name?.[0] || 'U') + (currentUser()?.last_name?.[0] || '')">
      
      <!-- Dynamic Sidebar Menu -->
      <ng-container sidebarMenu>
        <a routerLink="/admin/dashboard" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="LayoutDashboard" [size]="20" class="group-hover:scale-110 transition-transform"></lucide-icon>
          <span>Overview</span>
        </a>
        <a routerLink="/admin/orders" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="ShoppingBag" [size]="20" class="group-hover:scale-110 transition-transform"></lucide-icon>
          <span>Recent Orders</span>
        </a>
        <a routerLink="/admin/products" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="Package" [size]="20" class="group-hover:scale-110 transition-transform"></lucide-icon>
          <span>Inventory</span>
        </a>
        <a routerLink="/admin/customers" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="Users" [size]="20" class="group-hover:scale-110 transition-transform"></lucide-icon>
          <span>User Management</span>
        </a>
        <a routerLink="/admin/settings" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="Settings" [size]="20" class="group-hover:scale-110 transition-transform"></lucide-icon>
          <span>System Settings</span>
        </a>
      </ng-container>


      <!-- Main Page Content -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
           <h3 class="text-lg font-bold mb-4">Welcome Back!</h3>
           <p class="text-slate-500">You are logged in as {{ currentUser()?.email }}</p>
           <div class="mt-4 p-3 bg-primary/5 rounded-lg border border-primary/10">
              <span class="text-xs font-bold uppercase tracking-wider text-primary">Status</span>
              <p class="font-bold text-slate-700 capitalize">{{ currentUser()?.status }}</p>
           </div>
        </div>

        <!-- Placeholder for more stats -->
        <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center opacity-50 border-dashed">
            <p class="text-slate-400">Total Revenue Chart</p>
        </div>
        <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center opacity-50 border-dashed">
            <p class="text-slate-400">User Growth Chart</p>
        </div>
      </div>
    </app-admin-layout>
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