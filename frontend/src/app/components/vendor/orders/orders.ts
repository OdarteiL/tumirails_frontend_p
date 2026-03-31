import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-vendor-orders',
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
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Vendor Portal</h2>
            <nav class="space-y-1">
              <a routerLink="/vendor/dashboard" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Dashboard</a>
              <a routerLink="/vendor/products" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Products</a>
              <a routerLink="/vendor/orders" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded">Orders</a>
              <a routerLink="/vendor/inventory" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Inventory</a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <main class="flex-1 p-8">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Orders</h1>
            <p class="text-sm text-gray-600 mt-1">Manage customer orders</p>
          </div>

          <div class="bg-white border border-gray-200 rounded">
            <div class="px-6 py-4 border-b border-gray-200 flex gap-4">
              <button (click)="filter = 'all'" [class]="filter === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">All</button>
              <button (click)="filter = 'pending'" [class]="filter === 'pending' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Pending</button>
              <button (click)="filter = 'completed'" [class]="filter === 'completed' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Completed</button>
            </div>
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr *ngFor="let order of filteredOrders()" class="border-b border-gray-100">
                  <td class="px-6 py-4 text-sm font-medium">#{{ order.id }}</td>
                  <td class="px-6 py-4 text-sm">{{ order.customer }}</td>
                  <td class="px-6 py-4 text-sm">GH₵{{ order.amount }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded" [ngClass]="{
                      'bg-yellow-100 text-yellow-700': order.status === 'pending',
                      'bg-green-100 text-green-700': order.status === 'completed'
                    }">{{ order.status }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <button *ngIf="order.status === 'pending'" (click)="updateStatus(order.id, 'completed')" class="text-blue-600 hover:text-blue-800">Complete</button>
                  </td>
                </tr>
                <tr *ngIf="filteredOrders().length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No orders found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>
  `
})
export class VendorOrdersComponent {
  filter = 'all';
  orders = [
    { id: 1001, customer: 'John Doe', amount: 1500, status: 'pending' },
    { id: 1002, customer: 'Jane Smith', amount: 2300, status: 'completed' },
    { id: 1003, customer: 'Bob Wilson', amount: 890, status: 'pending' }
  ];

  constructor(private authService: AuthService, private router: Router) {}

  filteredOrders() {
    return this.filter === 'all' ? this.orders : this.orders.filter(o => o.status === this.filter);
  }

  updateStatus(id: number, status: string) {
    const order = this.orders.find(o => o.id === id);
    if (order) order.status = status;
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => this.router.navigate(['/'])
    });
  }
}
