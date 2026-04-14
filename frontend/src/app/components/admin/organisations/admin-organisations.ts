import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { AdminService, AdminOrganisation } from '../../../services/admin.service';
import { LucideAngularModule, Search, RefreshCw, Plus, X } from 'lucide-angular';

@Component({
  selector: 'app-admin-organisations',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Organisations'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-3 items-center">
          <div class="relative flex-1 min-w-[200px]">
            <lucide-icon [img]="Search" [size]="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></lucide-icon>
            <input [(ngModel)]="searchQuery" (ngModelChange)="onSearch()" placeholder="Search name or email..."
              class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <select [(ngModel)]="typeFilter" (ngModelChange)="load()" class="text-sm border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary">
            <option value="">All Types</option>
            <option value="installer">Installer</option>
            <option value="provider">Provider</option>
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
          <button (click)="showModal.set(true)"
            class="ml-auto flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition-colors">
            <lucide-icon [img]="Plus" [size]="16"></lucide-icon> Add Organisation
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <div *ngIf="loading()" class="p-12 text-center text-slate-400 text-sm">Loading organisations...</div>
          <div *ngIf="error()" class="p-12 text-center text-red-500 text-sm">{{ error() }}</div>
          <table *ngIf="!loading() && !error()" class="w-full">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Members</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr *ngFor="let o of orgs()" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ o.name }}</td>
                <td class="px-6 py-4 text-sm text-slate-500 hidden md:table-cell">{{ o.email }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-0.5 text-xs rounded-full font-semibold capitalize"
                    [class]="o.type === 'installer' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">{{ o.type }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-0.5 text-xs rounded-full font-semibold capitalize"
                    [class]="o.status === 'active' ? 'bg-green-100 text-green-700' : o.status === 'suspended' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'">
                    {{ o.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500 hidden lg:table-cell">{{ o.members_count ?? 0 }}</td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <button *ngIf="o.status !== 'active'" (click)="setStatus(o, 'active')"
                      class="text-xs px-3 py-1 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 font-medium transition-colors">Activate</button>
                    <button *ngIf="o.status === 'active'" (click)="setStatus(o, 'suspended')"
                      class="text-xs px-3 py-1 rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 font-medium transition-colors">Suspend</button>
                    <button *ngIf="o.status !== 'inactive'" (click)="setStatus(o, 'inactive')"
                      class="text-xs px-3 py-1 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium transition-colors">Deactivate</button>
                  </div>
                </td>
              </tr>
              <tr *ngIf="orgs().length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">No organisations found</td>
              </tr>
            </tbody>
          </table>
          <div *ngIf="meta().last_page > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-sm text-slate-500">Page {{ meta().current_page }} of {{ meta().last_page }} &mdash; {{ meta().total }} organisations</span>
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

    <!-- Add Organisation Modal -->
    <div *ngIf="showModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white">
          <h3 class="font-bold text-slate-900">Add New Organisation</h3>
          <button (click)="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
            <lucide-icon [img]="X" [size]="20"></lucide-icon>
          </button>
        </div>
        <form (ngSubmit)="submitOrg()" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Organisation Name *</label>
            <input [(ngModel)]="form.name" name="name" required
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Type *</label>
            <select [(ngModel)]="form.type" name="type" (ngModelChange)="onTypeChange()"
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary">
              <option value="installer">Installer</option>
              <option value="provider">Provider</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Email *</label>
            <input [(ngModel)]="form.email" name="email" type="email" required
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Phone</label>
            <input [(ngModel)]="form.phone" name="phone"
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Address</label>
            <input [(ngModel)]="form.address" name="address"
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Service Areas * <span class="font-normal text-slate-400">(comma-separated)</span></label>
            <input [(ngModel)]="serviceAreasInput" name="service_areas" required
              placeholder="e.g. Accra, Kumasi, Tema"
              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
          </div>

          <!-- Installer-specific -->
          <ng-container *ngIf="form.type === 'installer'">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">License Number *</label>
              <input [(ngModel)]="form.license_number" name="license_number"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Years of Experience *</label>
              <input [(ngModel)]="form.years_experience" name="years_experience" type="number" min="0"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
            </div>
          </ng-container>

          <!-- Provider-specific -->
          <ng-container *ngIf="form.type === 'provider'">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Business Registration *</label>
              <input [(ngModel)]="form.business_registration" name="business_registration"
                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-primary" />
            </div>
          </ng-container>

          <div *ngIf="formError()" class="text-sm text-red-600 bg-red-50 rounded-xl px-4 py-2">{{ formError() }}</div>
          <div class="flex gap-3 pt-2">
            <button type="button" (click)="closeModal()"
              class="flex-1 px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit" [disabled]="saving()"
              class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-primary rounded-xl hover:bg-primary/90 disabled:opacity-50 transition-colors">
              {{ saving() ? 'Creating...' : 'Create Organisation' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  `
})
export class AdminOrganisationsComponent implements OnInit {
  private authService = inject(AuthService);
  private adminService = inject(AdminService);

  userName = signal('');
  userInitials = signal('');
  orgs = signal<AdminOrganisation[]>([]);
  loading = signal(true);
  error = signal('');
  meta = signal({ total: 0, current_page: 1, last_page: 1, per_page: 15 });
  showModal = signal(false);
  saving = signal(false);
  formError = signal('');

  searchQuery = '';
  typeFilter = '';
  statusFilter = '';
  serviceAreasInput = '';
  private searchTimer: any;

  form: any = { name: '', type: 'installer', email: '', phone: '', address: '', license_number: '', years_experience: 0, business_registration: '' };

  readonly Search = Search; readonly RefreshCw = RefreshCw;
  readonly Plus = Plus; readonly X = X;

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
    if (this.typeFilter) params['type'] = this.typeFilter;
    if (this.statusFilter) params['status'] = this.statusFilter;

    this.adminService.getOrganisations(params).subscribe({
      next: (res) => { this.orgs.set(res.data ?? []); if (res.meta) this.meta.set(res.meta); this.loading.set(false); },
      error: (err) => { this.error.set(err?.error?.message ?? 'Failed to load organisations'); this.loading.set(false); }
    });
  }

  onSearch(): void {
    clearTimeout(this.searchTimer);
    this.searchTimer = setTimeout(() => this.load(), 400);
  }

  goToPage(page: number): void {
    if (page >= 1 && page <= this.meta().last_page) this.load(page);
  }

  setStatus(org: AdminOrganisation, status: string): void {
    this.adminService.updateOrgStatus(org.id, status).subscribe({
      next: () => this.orgs.update(list => list.map(o => o.id === org.id ? { ...o, status } : o)),
      error: (err) => alert(err?.error?.error ?? 'Failed to update status')
    });
  }

  onTypeChange(): void {
    this.form.license_number = '';
    this.form.years_experience = 0;
    this.form.business_registration = '';
  }

  closeModal(): void {
    this.showModal.set(false);
    this.formError.set('');
    this.serviceAreasInput = '';
    this.form = { name: '', type: 'installer', email: '', phone: '', address: '', license_number: '', years_experience: 0, business_registration: '' };
  }

  submitOrg(): void {
    this.saving.set(true);
    this.formError.set('');
    const payload = {
      ...this.form,
      service_areas: this.serviceAreasInput.split(',').map((s: string) => s.trim()).filter(Boolean),
    };
    this.adminService.createOrganisation(payload).subscribe({
      next: () => { this.closeModal(); this.load(); },
      error: (err) => {
        const errors = err?.error?.errors;
        this.formError.set(errors ? Object.values(errors).flat().join(' ') : (err?.error?.message ?? 'Failed to create organisation'));
        this.saving.set(false);
      }
    });
  }
}
