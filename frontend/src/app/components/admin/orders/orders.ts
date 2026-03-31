import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-admin-orders',
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
              <a routerLink="/admin/orders" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded">Orders</a>
              <a routerLink="/admin/products" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Inventory</a>
              <a routerLink="/admin/customers" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Users</a>
              <a routerLink="/admin/settings" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Settings</a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <main class="flex-1 p-8">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Orders</h1>
            <p class="text-sm text-gray-600 mt-1">Monitor all platform orders</p>
          </div>

          <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Total Orders</div>
              <div class="text-2xl font-semibold text-gray-900">{{ getTotalOrders() }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Pending</div>
              <div class="text-2xl font-semibold text-yellow-600">{{ getOrdersByStatus('pending') }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Processing</div>
              <div class="text-2xl font-semibold text-blue-600">{{ getOrdersByStatus('processing') }}</div>
            </div>
            <div class="bg-white border border-gray-200 rounded p-4">
              <div class="text-xs font-medium text-gray-500 uppercase mb-1">Completed</div>
              <div class="text-2xl font-semibold text-green-600">{{ getOrdersByStatus('completed') }}</div>
            </div>
          </div>

          <div class="bg-white border border-gray-200 rounded">
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr *ngFor="let order of orders" class="border-b border-gray-100">
                  <td class="px-6 py-4 text-sm font-medium">#{{ order.id }}</td>
                  <td class="px-6 py-4 text-sm">{{ order.customer }}</td>
                  <td class="px-6 py-4 text-sm">{{ order.vendor }}</td>
                  <td class="px-6 py-4 text-sm">GH₵{{ order.amount }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded" [ngClass]="{
                      'bg-yellow-100 text-yellow-700': order.status === 'pending',
                      'bg-blue-100 text-blue-700': order.status === 'processing',
                      'bg-green-100 text-green-700': order.status === 'completed'
                    }">{{ order.status }}</span>
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
export class AdminOrdersComponent {
  orders = [
    { id: 3001, customer: 'John Doe', vendor: 'Solar Co', amount: 5000, status: 'pending' },
    { id: 3002, customer: 'Jane Smith', vendor: 'Energy Plus', amount: 7500, status: 'processing' },
    { id: 3003, customer: 'Bob Wilson', vendor: 'Solar Co', amount: 3200, status: 'completed' },
    { id: 3004, customer: 'Alice Brown', vendor: 'Power Systems', amount: 4800, status: 'pending' }
  ];

  constructor(private authService: AuthService, private router: Router) {}

  getTotalOrders() {
    return this.orders.length;
  }

  getOrdersByStatus(status: string) {
    return this.orders.filter(o => o.status === status).length;
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => this.router.navigate(['/'])
    });
  }
}
