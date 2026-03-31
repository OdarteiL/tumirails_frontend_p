import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-vendor-products',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
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
              <a routerLink="/vendor/products" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded">Products</a>
              <a routerLink="/vendor/orders" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Orders</a>
              <a routerLink="/vendor/inventory" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Inventory</a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <main class="flex-1 p-8">
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
              <p class="text-sm text-gray-600 mt-1">Manage your product catalog</p>
            </div>
            <button (click)="showAddForm = true" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
              Add Product
            </button>
          </div>

          <div *ngIf="showAddForm" class="bg-white border border-gray-200 rounded mb-6 p-6">
            <h3 class="text-base font-semibold mb-4">Add New Product</h3>
            <div class="space-y-4">
              <input [(ngModel)]="newProduct.name" placeholder="Product Name" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
              <input [(ngModel)]="newProduct.price" type="number" placeholder="Price" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
              <input [(ngModel)]="newProduct.stock" type="number" placeholder="Stock" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
              <div class="flex gap-2">
                <button (click)="addProduct()" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Save</button>
                <button (click)="showAddForm = false" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">Cancel</button>
              </div>
            </div>
          </div>

          <div class="bg-white border border-gray-200 rounded">
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr *ngFor="let product of products" class="border-b border-gray-100">
                  <td class="px-6 py-4 text-sm">{{ product.name }}</td>
                  <td class="px-6 py-4 text-sm">GH₵{{ product.price }}</td>
                  <td class="px-6 py-4 text-sm">{{ product.stock }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded" [class]="product.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                      {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <button (click)="deleteProduct(product.id)" class="text-red-600 hover:text-red-800">Delete</button>
                  </td>
                </tr>
                <tr *ngIf="products.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No products yet</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>
  `
})
export class VendorProductsComponent {
  showAddForm = false;
  products: { id: number; name: string; price: number; stock: number }[] = [];
  newProduct = { name: '', price: 0, stock: 0 };

  constructor(private authService: AuthService, private router: Router) {}

  addProduct() {
    if (this.newProduct.name) {
      this.products.push({ ...this.newProduct, id: Date.now() });
      this.newProduct = { name: '', price: 0, stock: 0 };
      this.showAddForm = false;
    }
  }

  deleteProduct(id: number) {
    this.products = this.products.filter(p => p.id !== id);
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => this.router.navigate(['/'])
    });
  }
}
