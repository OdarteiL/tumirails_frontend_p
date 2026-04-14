import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { AdminService, AdminContact } from '../../../services/admin.service';
import { LucideAngularModule, RefreshCw, ChevronDown } from 'lucide-angular';

@Component({
  selector: 'app-admin-contacts',
  standalone: true,
  imports: [CommonModule, RouterModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Enquiries'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="space-y-6">
        <div class="flex justify-end">
          <button (click)="load()" class="flex items-center gap-2 text-sm text-slate-500 hover:text-primary transition-colors">
            <lucide-icon [img]="RefreshCw" [size]="14"></lucide-icon> Refresh
          </button>
        </div>

        <div *ngIf="loading()" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center text-slate-400 text-sm">Loading...</div>
        <div *ngIf="error()" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center text-red-500 text-sm">{{ error() }}</div>

        <div *ngIf="!loading() && !error()" class="space-y-3">
          <div *ngFor="let c of contacts()"
            class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <!-- Header row (always visible) -->
            <button (click)="toggle(c.id)"
              class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors text-left">
              <div class="flex items-center gap-4">
                <div class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                  {{ c.name[0].toUpperCase() }}
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-800">{{ c.name }}
                    <span class="text-slate-400 font-normal ml-2 text-xs">{{ c.email }}</span>
                  </p>
                  <p class="text-xs text-slate-500 mt-0.5">{{ c.subject }}</p>
                </div>
              </div>
              <div class="flex items-center gap-3 shrink-0 ml-4">
                <span class="text-xs text-slate-400">{{ c.created_at | date:'MMM d, y' }}</span>
                <lucide-icon [img]="ChevronDown" [size]="16" class="text-slate-400 transition-transform"
                  [class.rotate-180]="expanded() === c.id"></lucide-icon>
              </div>
            </button>
            <!-- Expanded message -->
            <div *ngIf="expanded() === c.id" class="px-6 pb-5 border-t border-slate-50">
              <p class="text-sm text-slate-600 mt-4 leading-relaxed whitespace-pre-wrap">{{ c.message }}</p>
              <a [href]="'mailto:' + c.email" class="inline-block mt-4 text-xs font-semibold text-primary hover:underline">
                Reply to {{ c.email }}
              </a>
            </div>
          </div>

          <div *ngIf="contacts().length === 0" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center text-slate-400 text-sm">
            No enquiries yet
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class AdminContactsComponent implements OnInit {
  private authService = inject(AuthService);
  private adminService = inject(AdminService);

  userName = signal('');
  userInitials = signal('');
  contacts = signal<AdminContact[]>([]);
  loading = signal(true);
  error = signal('');
  expanded = signal<number | null>(null);

  readonly RefreshCw = RefreshCw;
  readonly ChevronDown = ChevronDown;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userInitials.set(`${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`);
    }
    this.load();
  }

  load(): void {
    this.loading.set(true);
    this.error.set('');
    this.adminService.getContacts().subscribe({
      next: (res) => { this.contacts.set(res.data ?? []); this.loading.set(false); },
      error: (err) => { this.error.set(err?.error?.message ?? 'Failed to load enquiries'); this.loading.set(false); }
    });
  }

  toggle(id: number): void {
    this.expanded.update(v => v === id ? null : id);
  }
}
