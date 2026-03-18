import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import { EstimationsService } from '../../../../services/estimations.service';
import {
    LucideAngularModule,
    LayoutDashboard,
    ShoppingBag,
    History,
    Settings,
    Zap,
    TrendingUp,
    Clock,
    ArrowRight,
    ClipboardCheck
} from 'lucide-angular';

@Component({
    selector: 'app-consumer-dashboard',
    standalone: true,
    imports: [CommonModule, RouterModule, AdminLayoutComponent, LucideAngularModule],
    template: `
    <app-admin-layout 
      [pageTitle]="'Welcome, ' + userName().split(' ')[0]" 
      [userName]="userName()"
      [userRole]="userRole()"
      [userInitials]="userInitials()">
      
      <!-- Consumer Sidebar Menu -->
      <ng-container sidebarMenu>
        <a routerLink="/customer/dashboard" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner"
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="LayoutDashboard" [size]="20"></lucide-icon>
          <span>Dashboard</span>
        </a>
        <a routerLink="/customer/marketplace" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner"
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="ShoppingBag" [size]="20"></lucide-icon>
          <span>Marketplace</span>
        </a>
        <a routerLink="/customer/estimations" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner"
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="History" [size]="20"></lucide-icon>
          <span>My Estimations</span>
        </a>
        <a routerLink="/customer/site-assessments" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner"
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="ClipboardCheck" [size]="20"></lucide-icon>
          <span>Site Assessments</span>
        </a>
        <a routerLink="/customer/settings" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner"
           [routerLinkActiveOptions]="{exact: false}"
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="Settings" [size]="20"></lucide-icon>
          <span>Settings</span>
        </a>
      </ng-container>

      <!-- Dashboard Content -->
      <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-primary group-hover:text-white transition-colors">
                <lucide-icon [img]="Zap" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Monthly Cost</p>
                <h3 class="text-2xl font-black text-slate-900">GH₵{{ monthlyEstimate() }}</h3>
              </div>
            </div>
            <div *ngIf="estimationToken()" class="text-xs font-bold text-slate-500">
              Token: {{ estimationToken() }}
            </div>
          </div>

          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <lucide-icon [img]="ShoppingBag" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Site Assessments</p>
                <h3 class="text-2xl font-black text-slate-900">0 <span class="text-sm font-medium opacity-50">Open</span></h3>
              </div>
            </div>
            <p class="text-xs font-bold text-slate-400">No active assessments</p>
          </div>

          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <lucide-icon [img]="Clock" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Orders</p>
                <h3 class="text-2xl font-black text-slate-900">0 <span class="text-sm font-medium opacity-50">Pending</span></h3>
              </div>
            </div>
            <p class="text-xs font-bold text-slate-400">No pending orders</p>
          </div>
        </div>

        <!-- Purchased Items -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-900">Purchased Items</h3>
            <button routerLink="/customer/marketplace" class="text-primary font-bold text-sm hover:underline flex items-center gap-1">
              Browse Marketplace <lucide-icon [img]="ArrowRight" size="14"></lucide-icon>
            </button>
          </div>
          <div class="p-8 text-center text-slate-500">
            <p>No items purchased yet</p>
          </div>
        </div>

        <!-- Site Assessments -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="p-8 border-b border-slate-50">
            <h3 class="text-xl font-black text-slate-900">Site Assessments</h3>
          </div>
          <div class="p-8 text-center text-slate-500">
            <p>No site assessments opened yet</p>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class ConsumerDashboardComponent implements OnInit {
    private authService = inject(AuthService);
    private estimationsService = inject(EstimationsService);

    userName = signal('');
    userRole = signal('');
    userInitials = signal('');
    estimationToken = signal<string | null>(null);
    monthlyEstimate = signal<string>('0.00');
    estimations = signal<any[]>([]);

    // Icons for template
    readonly LayoutDashboard = LayoutDashboard;
    readonly ShoppingBag = ShoppingBag;
    readonly History = History;
    readonly Settings = Settings;
    readonly Zap = Zap;
    readonly TrendingUp = TrendingUp;
    readonly Clock = Clock;
    readonly ArrowRight = ArrowRight;
    readonly ClipboardCheck = ClipboardCheck;

    ngOnInit(): void {
        const user = this.authService.currentUser();
        if (user) {
            this.userName.set(`${user.first_name} ${user.last_name}`);
            this.userRole.set(user.role);
            this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
        }

        // Check for estimation token from localStorage
        const token = localStorage.getItem('estimation_token');
        if (token) {
            this.estimationToken.set(token);
            // Fetch estimation details
            this.estimationsService.getGuestEstimationByCode(token).subscribe({
                next: (res) => {
                    if (res.success) {
                        this.monthlyEstimate.set(res.data.estimated_monthly_cost || '0.00');
                    }
                },
                error: () => console.error('Failed to fetch estimation')
            });
        }

        // Fetch user's estimations
        this.estimationsService.getEstimations().subscribe({
            next: (res) => {
                if (res.success) {
                    this.estimations.set(res.data);
                }
            },
            error: () => console.error('Failed to fetch estimations')
        });
    }
}
