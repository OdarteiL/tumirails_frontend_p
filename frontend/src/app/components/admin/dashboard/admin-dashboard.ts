import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AdminLayoutComponent } from '../admin-layout/admin-layout';
import { AdminNavComponent } from '../admin-nav/admin-nav';
import { AuthService } from '../../../services/auth.service';
import { AdminService } from '../../../services/admin.service';
import { LucideAngularModule, Users, Building2, Mail, TrendingUp } from 'lucide-angular';

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [CommonModule, RouterModule, AdminLayoutComponent, AdminNavComponent, LucideAngularModule],
  template: `
    <app-admin-layout [pageTitle]="'Dashboard'" [userName]="userName()" [userRole]="'Administrator'" [userInitials]="userInitials()">
      <ng-container sidebarMenu><app-admin-nav></app-admin-nav></ng-container>

      <div class="space-y-8">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div *ngFor="let card of kpiCards()" class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="p-3 rounded-xl" [class]="card.bg">
              <lucide-icon [img]="card.icon" [size]="22" [class]="card.color"></lucide-icon>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ card.label }}</p>
              <h3 class="text-2xl font-black text-slate-900">{{ card.value }}</h3>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Recent Users -->
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
              <h3 class="font-bold text-slate-900">Recent Users</h3>
              <a routerLink="/admin/users" class="text-xs text-primary font-semibold hover:underline">View all</a>
            </div>
            <div *ngIf="loadingUsers()" class="p-8 text-center text-slate-400 text-sm">Loading...</div>
            <table *ngIf="!loadingUsers()" class="w-full">
              <tbody>
                <tr *ngFor="let u of recentUsers()" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
                  <td class="px-6 py-3 text-sm font-medium text-slate-800">{{ u.first_name }} {{ u.last_name }}</td>
                  <td class="px-6 py-3 text-xs text-slate-500 hidden md:table-cell">{{ u.email }}</td>
                  <td class="px-6 py-3">
                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold capitalize" [class]="roleBadge(u.role)">{{ u.role }}</span>
                  </td>
                  <td class="px-6 py-3">
                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold"
                      [class]="u.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ u.status }}</span>
                  </td>
                </tr>
                <tr *ngIf="recentUsers().length === 0">
                  <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-sm">No users found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Recent Enquiries -->
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
              <h3 class="font-bold text-slate-900">Recent Enquiries</h3>
              <a routerLink="/admin/contacts" class="text-xs text-primary font-semibold hover:underline">View all</a>
            </div>
            <div *ngIf="loadingContacts()" class="p-8 text-center text-slate-400 text-sm">Loading...</div>
            <div *ngIf="!loadingContacts()">
              <div *ngFor="let c of recentContacts()" class="px-6 py-4 border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
                <div class="flex justify-between items-start">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">{{ c.name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 truncate max-w-xs">{{ c.subject }}</p>
                  </div>
                  <span class="text-xs text-slate-400 shrink-0 ml-2">{{ c.created_at | date:'MMM d' }}</span>
                </div>
              </div>
              <div *ngIf="recentContacts().length === 0" class="px-6 py-8 text-center text-slate-400 text-sm">No enquiries yet</div>
            </div>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class AdminDashboardComponent implements OnInit {
  private authService = inject(AuthService);
  private adminService = inject(AdminService);

  userName = signal('');
  userInitials = signal('');
  loadingUsers = signal(true);
  loadingContacts = signal(true);
  recentUsers = signal<any[]>([]);
  recentContacts = signal<any[]>([]);
  kpiCards = signal<any[]>([]);

  readonly Users = Users; readonly Building2 = Building2;
  readonly Mail = Mail; readonly TrendingUp = TrendingUp;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userInitials.set(`${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`);
    }

    // Init KPI cards with 0 then fill in
    this.kpiCards.set([
      { label: 'Total Users', value: 0, icon: Users, bg: 'bg-blue-50', color: 'text-blue-600' },
      { label: 'Active Users', value: 0, icon: TrendingUp, bg: 'bg-purple-50', color: 'text-purple-600' },
      { label: 'Organisations', value: 0, icon: Building2, bg: 'bg-emerald-50', color: 'text-emerald-600' },
      { label: 'Enquiries', value: 0, icon: Mail, bg: 'bg-amber-50', color: 'text-amber-600' },
    ]);

    this.adminService.getUsers({ per_page: '5' }).subscribe({
      next: (res) => {
        this.recentUsers.set(res.data ?? []);
        const total = res.meta?.total ?? 0;
        const active = (res.data ?? []).filter((u: any) => u.status === 'active').length;
        this.kpiCards.update(cards => cards.map(c =>
          c.label === 'Total Users' ? { ...c, value: total } :
          c.label === 'Active Users' ? { ...c, value: active } : c
        ));
        this.loadingUsers.set(false);
      },
      error: () => this.loadingUsers.set(false)
    });

    this.adminService.getOrganisations({ per_page: '1' }).subscribe({
      next: (res) => {
        this.kpiCards.update(cards => cards.map(c =>
          c.label === 'Organisations' ? { ...c, value: res.meta?.total ?? 0 } : c
        ));
      },
      error: () => {}
    });

    this.adminService.getContacts().subscribe({
      next: (res) => {
        const contacts = res.data ?? [];
        this.recentContacts.set(contacts.slice(0, 5));
        this.kpiCards.update(cards => cards.map(c =>
          c.label === 'Enquiries' ? { ...c, value: contacts.length } : c
        ));
        this.loadingContacts.set(false);
      },
      error: () => this.loadingContacts.set(false)
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
