import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { AdminService, AdminUser } from '../../../services/admin.service';
import { LucideAngularModule, Search, RefreshCw, Plus } from 'lucide-angular';

@Component({
  selector: 'app-admin-users',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Users'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="space-y-6">
        <!-- Toolbar -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-3 items-center">
          <div class="relative flex-1 min-w-[200px]">
            <lucide-icon [img]="Search" [size]="16" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></lucide-icon>
            <input [(ngModel)]="searchQuery" (ngModelChange)="onSearch()" placeholder="Search name or email..."
              class="w-full pl-11 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <select [(ngModel)]="roleFilter" (ngModelChange)="load()" class="text-sm border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary">
            <option value="">All Roles</option>
            <option value="customer">Customer</option>
            <option value="installer">Installer</option>
            <option value="provider">Provider</option>
            <option value="admin">Admin</option>
          </select>
          <select [(ngModel)]="statusFilter" (ngModelChange)="load()" class="text-sm border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
          <button (click)="load()" class="p-2 text-slate-500 hover:text-primary transition-colors">
            <lucide-icon [img]="RefreshCw" [size]="16"></lucide-icon>
          </button>
          <button (click)="router.navigate(['/admin/users/add'])"
            class="ml-auto flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition-colors">
            <lucide-icon [img]="Plus" [size]="16"></lucide-icon> Add User
          </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div *ngIf="loading()" class="p-12 text-center text-slate-400 text-sm">Loading users...</div>
          <div *ngIf="error()" class="p-12 text-center text-red-500 text-sm">{{ error() }}</div>
          <table *ngIf="!loading() && !error()" class="w-full">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Joined</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr *ngFor="let u of users()" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ u.first_name }} {{ u.last_name }}</td>
                <td class="px-6 py-4 text-sm text-slate-500 hidden md:table-cell">{{ u.email }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-0.5 text-xs rounded-full font-semibold capitalize" [class]="roleBadge(u.role)">{{ u.role }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-0.5 text-xs rounded-full font-semibold capitalize"
                    [class]="u.status === 'active' ? 'bg-green-100 text-green-700' : u.status === 'suspended' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'">
                    {{ u.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-400 hidden lg:table-cell">{{ u.created_at | date:'MMM d, y' }}</td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <button *ngIf="u.status !== 'active'" (click)="setStatus(u, 'active')"
                      class="text-xs px-3 py-1 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 font-medium transition-colors">Activate</button>
                    <button *ngIf="u.status === 'active'" (click)="setStatus(u, 'suspended')"
                      class="text-xs px-3 py-1 rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 font-medium transition-colors">Suspend</button>
                    <button *ngIf="u.status !== 'inactive'" (click)="setStatus(u, 'inactive')"
                      class="text-xs px-3 py-1 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium transition-colors">Deactivate</button>
                  </div>
                </td>
              </tr>
              <tr *ngIf="users().length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">No users found</td>
              </tr>
            </tbody>
          </table>
          <div *ngIf="meta().last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-sm text-slate-500">Page {{ meta().current_page }} of {{ meta().last_page }} &mdash; {{ meta().total }} users</span>
            <div class="flex gap-2">
              <button (click)="goToPage(meta().current_page - 1)" [disabled]="meta().current_page === 1"
                class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50 transition-colors">Prev</button>
              <button (click)="goToPage(meta().current_page + 1)" [disabled]="meta().current_page === meta().last_page"
                class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50 transition-colors">Next</button>
            </div>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class AdminUsersComponent implements OnInit {
  private authService = inject(AuthService);
  private adminService = inject(AdminService);
  router = inject(Router);

  userName = signal('');
  userInitials = signal('');
  users = signal<AdminUser[]>([]);
  loading = signal(true);
  error = signal('');
  meta = signal({ total: 0, current_page: 1, last_page: 1, per_page: 15 });

  searchQuery = '';
  roleFilter = '';
  statusFilter = '';
  private searchTimer: any;

  readonly Search = Search; readonly RefreshCw = RefreshCw; readonly Plus = Plus;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userInitials.set(`${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`);
    }
    this.load();
  }

  load(page = 1): void {
    this.loading.set(true);
    this.error.set('');
    const params: Record<string, string> = { page: String(page), per_page: '15' };
    if (this.searchQuery) params['search'] = this.searchQuery;
    if (this.roleFilter) params['role'] = this.roleFilter;
    if (this.statusFilter) params['status'] = this.statusFilter;

    this.adminService.getUsers(params).subscribe({
      next: (res) => { this.users.set(res.data ?? []); if (res.meta) this.meta.set(res.meta); this.loading.set(false); },
      error: (err) => { this.error.set(err?.error?.message ?? 'Failed to load users'); this.loading.set(false); }
    });
  }

  onSearch(): void {
    clearTimeout(this.searchTimer);
    this.searchTimer = setTimeout(() => this.load(), 400);
  }

  goToPage(page: number): void {
    if (page >= 1 && page <= this.meta().last_page) this.load(page);
  }

  setStatus(user: AdminUser, status: string): void {
    this.adminService.updateUserStatus(user.id, status).subscribe({
      next: () => this.users.update(list => list.map(u => u.id === user.id ? { ...u, status } : u)),
      error: (err) => alert(err?.error?.error ?? 'Failed to update status')
    });
  }

  roleBadge(role: string): string {
    const map: Record<string, string> = {
      admin: 'bg-purple-100 text-purple-700', customer: 'bg-blue-100 text-blue-700',
      installer: 'bg-amber-100 text-amber-700', provider: 'bg-emerald-100 text-emerald-700',
    };
    return map[role] ?? 'bg-slate-100 text-slate-700';
  }
}
