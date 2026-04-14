import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { AdminService, AdminAppliance } from '../../../services/admin.service';
import { LucideAngularModule, Search, RefreshCw, Plus, X, Pencil, Trash2 } from 'lucide-angular';

const CATEGORIES = [
  { id: 1, name: 'Lighting' },
  { id: 2, name: 'Kitchen Appliances' },
  { id: 3, name: 'Entertainment' },
  { id: 4, name: 'Cooling & Heating' },
  { id: 5, name: 'Computing' },
];

@Component({
  selector: 'app-admin-appliances',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Appliances'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-3 items-center">
          <div class="relative flex-1 min-w-[200px]">
            <lucide-icon [img]="Search" [size]="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></lucide-icon>
            <input [(ngModel)]="searchQuery" (ngModelChange)="onSearch()" placeholder="Search appliances..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <button (click)="load()" class="p-2 text-slate-500 hover:text-primary transition-colors">
            <lucide-icon [img]="RefreshCw" [size]="16"></lucide-icon>
          </button>
          <button (click)="openCreate()"
            class="ml-auto flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition-colors">
            <lucide-icon [img]="Plus" [size]="16"></lucide-icon> Add Appliance
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div *ngIf="loading()" class="p-12 text-center text-slate-400 text-sm">Loading appliances...</div>
          <div *ngIf="error()" class="p-12 text-center text-red-500 text-sm">{{ error() }}</div>
          <table *ngIf="!loading() && !error()" class="w-full">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">Category</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Wattage</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Usage hrs/day</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Visibility</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr *ngFor="let a of appliances()" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ a.name }}</td>
                <td class="px-6 py-4 text-sm text-slate-500 hidden md:table-cell">{{ a.category?.name ?? categoryName(a.category_id) }}</td>
                <td class="px-6 py-4 text-sm text-slate-700">{{ a.default_wattage }}W</td>
                <td class="px-6 py-4 text-sm text-slate-500 hidden lg:table-cell">{{ a.default_usage_hours ?? '—' }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-0.5 text-xs rounded-full font-semibold"
                    [class]="a.is_public ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'">
                    {{ a.is_public ? 'Public' : 'Private' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <button (click)="openEdit(a)" class="p-1.5 text-slate-400 hover:text-primary transition-colors">
                      <lucide-icon [img]="Pencil" [size]="15"></lucide-icon>
                    </button>
                    <button (click)="deleteAppliance(a)" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                      <lucide-icon [img]="Trash2" [size]="15"></lucide-icon>
                    </button>
                  </div>
                </td>
              </tr>
              <tr *ngIf="appliances().length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">No appliances found</td>
              </tr>
            </tbody>
          </table>
          <div *ngIf="meta().last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-sm text-slate-500">Page {{ meta().current_page }} of {{ meta().last_page }} &mdash; {{ meta().total }} appliances</span>
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

    <!-- Add / Edit Modal -->
    <div *ngIf="showModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
          <h3 class="font-bold text-slate-900">{{ editingId() ? 'Edit Appliance' : 'Add Appliance' }}</h3>
          <button (click)="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
            <lucide-icon [img]="X" [size]="20"></lucide-icon>
          </button>
        </div>
        <form (ngSubmit)="submitAppliance()" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Name *</label>
            <input [(ngModel)]="form.name" name="name" required
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Category *</label>
            <select [(ngModel)]="form.category_id" name="category_id"
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary">
              <option *ngFor="let c of categories" [value]="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Default Wattage (W) *</label>
              <input [(ngModel)]="form.default_wattage" name="default_wattage" type="number" min="0" required
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Usage Hours/Day</label>
              <input [(ngModel)]="form.default_usage_hours" name="default_usage_hours" type="number" min="0" max="24"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
            </div>
          </div>
          <div class="flex items-center gap-3">
            <input [(ngModel)]="form.is_public" name="is_public" type="checkbox" id="is_public"
              class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary" />
            <label for="is_public" class="text-sm text-slate-700">Make publicly visible to all users</label>
          </div>
          <div *ngIf="formError()" class="text-sm text-red-600 bg-red-50 rounded-xl px-4 py-2">{{ formError() }}</div>
          <div class="flex gap-3 pt-2">
            <button type="button" (click)="closeModal()"
              class="flex-1 px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit" [disabled]="saving()"
              class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-primary rounded-xl hover:bg-primary/90 disabled:opacity-50 transition-colors">
              {{ saving() ? 'Saving...' : (editingId() ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  `
})
export class AdminAppliancesComponent implements OnInit {
  private authService = inject(AuthService);
  private adminService = inject(AdminService);

  userName = signal('');
  userInitials = signal('');
  appliances = signal<AdminAppliance[]>([]);
  loading = signal(true);
  error = signal('');
  meta = signal({ total: 0, current_page: 1, last_page: 1, per_page: 15 });
  showModal = signal(false);
  saving = signal(false);
  formError = signal('');
  editingId = signal<number | null>(null);

  searchQuery = '';
  categories = CATEGORIES;
  private searchTimer: any;

  form = { name: '', category_id: 1, default_wattage: 0, default_usage_hours: 0, is_public: true };

  readonly Search = Search; readonly RefreshCw = RefreshCw;
  readonly Plus = Plus; readonly X = X; readonly Pencil = Pencil; readonly Trash2 = Trash2;

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

    this.adminService.getAppliances(params).subscribe({
      next: (res) => {
        this.appliances.set(res.data ?? []);
        if (res.meta) this.meta.set(res.meta);
        this.loading.set(false);
      },
      error: (err) => { this.error.set(err?.error?.message ?? 'Failed to load appliances'); this.loading.set(false); }
    });
  }

  onSearch(): void {
    clearTimeout(this.searchTimer);
    this.searchTimer = setTimeout(() => this.load(), 400);
  }

  goToPage(page: number): void {
    if (page >= 1 && page <= this.meta().last_page) this.load(page);
  }

  openCreate(): void {
    this.editingId.set(null);
    this.form = { name: '', category_id: 1, default_wattage: 0, default_usage_hours: 0, is_public: true };
    this.formError.set('');
    this.showModal.set(true);
  }

  openEdit(a: AdminAppliance): void {
    this.editingId.set(a.id);
    this.form = { name: a.name, category_id: a.category_id, default_wattage: a.default_wattage, default_usage_hours: a.default_usage_hours, is_public: a.is_public };
    this.formError.set('');
    this.showModal.set(true);
  }

  closeModal(): void {
    this.showModal.set(false);
    this.editingId.set(null);
    this.formError.set('');
  }

  submitAppliance(): void {
    this.saving.set(true);
    this.formError.set('');
    const id = this.editingId();
    const req = id
      ? this.adminService.updateAppliance(id, this.form)
      : this.adminService.createAppliance(this.form);

    req.subscribe({
      next: () => { this.closeModal(); this.load(); },
      error: (err) => {
        const errors = err?.error?.errors;
        this.formError.set(errors ? Object.values(errors).flat().join(' ') : (err?.error?.message ?? 'Failed to save appliance'));
        this.saving.set(false);
      }
    });
  }

  deleteAppliance(a: AdminAppliance): void {
    if (!confirm(`Delete "${a.name}"?`)) return;
    this.adminService.deleteAppliance(a.id).subscribe({
      next: () => this.appliances.update(list => list.filter(x => x.id !== a.id)),
      error: (err) => alert(err?.error?.message ?? 'Failed to delete appliance')
    });
  }

  categoryName(id: number): string {
    return CATEGORIES.find(c => c.id === id)?.name ?? '—';
  }
}
