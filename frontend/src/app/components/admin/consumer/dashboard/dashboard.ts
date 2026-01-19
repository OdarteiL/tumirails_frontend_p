import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import {
    LucideAngularModule,
    LayoutDashboard,
    ShoppingBag,
    History,
    Settings,
    Zap,
    TrendingUp,
    Clock,
    ArrowRight
} from 'lucide-angular';

@Component({
    selector: 'app-consumer-dashboard',
    standalone: true,
    imports: [CommonModule, RouterModule, AdminLayoutComponent, LucideAngularModule],
    template: `
    <app-admin-layout 
      pageTitle="Consumer Dashboard" 
      [userName]="userName()"
      [userRole]="userRole()"
      [userInitials]="userInitials()">
      
      <!-- Consumer Sidebar Menu -->
      <ng-container sidebarMenu>
        <a routerLink="/admin/consumer/dashboard" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="LayoutDashboard" [size]="20"></lucide-icon>
          <span>Dashboard</span>
        </a>
        <a routerLink="/admin/consumer/marketplace" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="ShoppingBag" [size]="20"></lucide-icon>
          <span>Marketplace</span>
        </a>
        <a routerLink="/admin/consumer/orders" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="History" [size]="20"></lucide-icon>
          <span>Orders</span>
        </a>
        <a routerLink="/admin/consumer/settings" routerLinkActive="!bg-white/10 !text-secondary shadow-inner" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="Settings" [size]="20"></lucide-icon>
          <span>Settings</span>
        </a>
      </ng-container>

      <!-- Dashboard Content -->
      <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-primary group-hover:text-white transition-colors">
                <lucide-icon [img]="Zap" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Energy Estimated</p>
                <h3 class="text-2xl font-black text-slate-900">450 <span class="text-sm font-medium opacity-50">kWh</span></h3>
              </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-600">
              <lucide-icon [img]="TrendingUp" size="14"></lucide-icon>
              <span>+12% from last month</span>
            </div>
          </div>

          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <lucide-icon [img]="ShoppingBag" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Active Orders</p>
                <h3 class="text-2xl font-black text-slate-900">2 <span class="text-sm font-medium opacity-50">Quotes</span></h3>
              </div>
            </div>
            <p class="text-xs font-bold text-slate-400">Next milestone: Installer visit</p>
          </div>

          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <lucide-icon [img]="Clock" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">Pending Tasks</p>
                <h3 class="text-2xl font-black text-slate-900">3 <span class="text-sm font-medium opacity-50">Items</span></h3>
              </div>
            </div>
            <p class="text-xs font-bold text-orange-500">2 Action required</p>
          </div>

          <div class="card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 group hover:border-primary/20 transition-all">
             <div class="flex items-center gap-4 mb-4">
              <div class="p-3 bg-purple-50 text-purple-600 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
                <lucide-icon [img]="Settings" size="24"></lucide-icon>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-wider">System Health</p>
                <h3 class="text-2xl font-black text-slate-900">N/A</h3>
              </div>
            </div>
            <p class="text-xs font-bold text-slate-400 italic">Install to track health</p>
          </div>
        </div>

        <!-- Main Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Recent Activity -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
              <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-black text-slate-900">Ongoing Quotations</h3>
                <button class="text-primary font-bold text-sm hover:underline flex items-center gap-1">
                  View All <lucide-icon [img]="ArrowRight" size="14"></lucide-icon>
                </button>
              </div>
              <div class="p-0">
                <div class="overflow-x-auto">
                  <table class="w-full text-left border-collapse">
                    <thead>
                      <tr class="bg-slate-50/50">
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Installer</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Requested</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                      <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                          <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-primary">SL</div>
                            <div>
                              <p class="font-bold text-slate-900">SolarLink GH</p>
                              <p class="text-xs text-slate-500">Premium Partner</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-8 py-5 text-sm font-medium text-slate-600">Jan 15, 2024</td>
                        <td class="px-8 py-5">
                          <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase rounded-full">Pending Visit</span>
                        </td>
                        <td class="px-8 py-5">
                          <button class="px-4 py-2 bg-slate-100 hover:bg-primary hover:text-white rounded-lg text-xs font-bold transition-all">Details</button>
                        </td>
                      </tr>
                      <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                          <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center font-bold text-primary">EP</div>
                            <div>
                              <p class="font-bold text-slate-900">EcoPower Systems</p>
                              <p class="text-xs text-slate-500">Certified Installer</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-8 py-5 text-sm font-medium text-slate-600">Jan 12, 2024</td>
                        <td class="px-8 py-5">
                          <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase rounded-full">Quote Ready</span>
                        </td>
                        <td class="px-8 py-5">
                          <button class="px-4 py-2 bg-primary text-white hover:bg-primary/90 rounded-lg text-xs font-bold shadow-lg shadow-primary/20 transition-all">Review</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions / Recommendations -->
          <div class="space-y-6">
            <div class="bg-gradient-to-br from-primary to-indigo-700 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl shadow-primary/20">
              <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
              <h4 class="text-xl font-black mb-2 relative z-10">Save More!</h4>
              <p class="text-white/80 text-sm mb-6 relative z-10">Get a free backup battery consultation when you order any Hybrid System this week.</p>
              <button routerLink="/admin/consumer/marketplace" class="w-full bg-secondary text-primary font-black py-4 rounded-2xl hover:bg-white transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                Browse Hybrid Units
              </button>
            </div>

            <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
              <h4 class="font-bold text-slate-900 mb-4">Complete Profile</h4>
              <p class="text-xs text-slate-500 mb-6">Complete your profile to receive more accurate installer matching.</p>
              <div class="w-full bg-slate-100 h-2 rounded-full mb-6 relative overflow-hidden">
                <div class="absolute left-0 top-0 h-full bg-primary rounded-full transition-all duration-1000" style="width: 65%"></div>
              </div>
              <button class="w-full border-2 border-slate-100 hover:border-primary hover:text-primary py-3 rounded-2xl text-sm font-bold transition-all">
                Update Info
              </button>
            </div>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class ConsumerDashboardComponent implements OnInit {
    private authService = inject(AuthService);

    userName = signal('');
    userRole = signal('');
    userInitials = signal('');

    // Icons for template
    readonly LayoutDashboard = LayoutDashboard;
    readonly ShoppingBag = ShoppingBag;
    readonly History = History;
    readonly Settings = Settings;
    readonly Zap = Zap;
    readonly TrendingUp = TrendingUp;
    readonly Clock = Clock;
    readonly ArrowRight = ArrowRight;

    ngOnInit(): void {
        const user = this.authService.currentUser();
        if (user) {
            this.userName.set(`${user.first_name} ${user.last_name}`);
            this.userRole.set(user.role);
            this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
        }
    }
}
