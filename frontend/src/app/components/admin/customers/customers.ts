import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-admin-customers',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <div class="min-h-screen bg-[#F4F5F7]">
      <div class="flex">
        <aside class="w-64 bg-white border-r border-gray-200 min-h-screen flex flex-col">
          <div class="p-6 border-b border-gray-200 flex justify-center">
            <a routerLink="/" class="block hover:opacity-80 transition-opacity">
              <img src="/assets/tumi_logo.png" alt="Tumi Solar" class="h-8 w-auto">
            </a>
          </div>
          <div class="p-6 flex-1">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Admin Portal</h2>
            <nav class="space-y-1">
              <a routerLink="/admin/dashboard" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Dashboard</a>
              <a routerLink="/admin/orders" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Orders</a>
              <a routerLink="/admin/products" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Inventory</a>
              <a routerLink="/admin/customers" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded">Users</a>
              <a routerLink="/admin/settings" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Settings</a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <main class="flex-1 p-8">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Users</h1>
            <p class="text-sm text-gray-600 mt-1">Manage platform users</p>
          </div>

          <div class="bg-white border border-gray-200 rounded mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex gap-4">
              <button (click)="filter = 'all'" [class]="filter === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">All Users</button>
              <button (click)="filter = 'customer'" [class]="filter === 'customer' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Customers</button>
              <button (click)="filter = 'vendor'" [class]="filter === 'vendor' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Vendors</button>
              <button (click)="filter = 'installer'" [class]="filter === 'installer' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Installers</button>
            </div>
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr *ngFor="let user of filteredUsers()" class="border-b border-gray-100">
                  <td class="px-6 py-4 text-sm">{{ user.name }}</td>
                  <td class="px-6 py-4 text-sm">{{ user.email }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700 capitalize">{{ user.role }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded" [class]="user.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                      {{ user.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <button (click)="toggleStatus(user.id)" class="text-blue-600 hover:text-blue-800">
                      {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>
  `
})
export class AdminCustomersComponent {
  filter = 'all';
  users = [
    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'customer', status: 'active' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'vendor', status: 'active' },
    { id: 3, name: 'Bob Wilson', email: 'bob@example.com', role: 'installer', status: 'active' },
    { id: 4, name: 'Alice Brown', email: 'alice@example.com', role: 'customer', status: 'inactive' }
  ];

  constructor(private authService: AuthService, private router: Router) {}

  filteredUsers() {
    return this.filter === 'all' ? this.users : this.users.filter(u => u.role === this.filter);
  }

  toggleStatus(id: number) {
    const user = this.users.find(u => u.id === id);
    if (user) user.status = user.status === 'active' ? 'inactive' : 'active';
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => this.router.navigate(['/'])
    });
  }
}
