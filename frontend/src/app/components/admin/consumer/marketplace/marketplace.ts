import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import {
  LucideAngularModule,
  LayoutDashboard,
  ShoppingBag,
  History,
  Settings,
  Search,
  Filter,
  Star,
  ChevronRight
} from 'lucide-angular';

interface Product {
  id: string;
  name: string;
  category: string;
  price: number;
  rating: number;
  reviews: number;
  image: string;
  specs: string[];
  tag?: string;
}

@Component({
  selector: 'app-consumer-marketplace',
  standalone: true,
  imports: [CommonModule, RouterModule, AdminLayoutComponent, LucideAngularModule],
  template: `
    <app-admin-layout 
      pageTitle="Solar Marketplace" 
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

      <div class="space-y-8 animate-in fade-in zoom-in-95 duration-500">
        <!-- Header Controls -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
          <div class="search-bar w-full md:w-96 relative">
            <lucide-icon [img]="Search" [size]="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></lucide-icon>
            <input type="text" placeholder="Search components..." class="w-full bg-white border border-slate-200 rounded-2xl py-3 pl-12 pr-4 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
          </div>
          
          <div class="flex gap-3 w-full md:w-auto">
            <button class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-white border border-slate-200 px-6 py-3 rounded-2xl font-bold text-slate-600 hover:bg-slate-50 transition-all">
              <lucide-icon [img]="Filter" [size]="18"></lucide-icon>
              Filter
            </button>
            <button class="flex-1 md:flex-none bg-primary text-white px-8 py-3 rounded-2xl font-black shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
              Check Compatibility
            </button>
          </div>
        </div>

        <!-- Categories -->
        <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
          <button *ngFor="let cat of categories" 
                  [class]="cat.active ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white border border-slate-100 text-slate-500 hover:border-slate-300'"
                  class="whitespace-nowrap px-6 py-2.5 rounded-full text-sm font-bold transition-all">
            {{cat.name}}
          </button>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8">
          <div *ngFor="let prod of products" 
               (click)="goToDetail(prod.id)"
               (keydown.enter)="goToDetail(prod.id)"
               (keydown.space)="goToDetail(prod.id)"
               tabindex="0"
               role="button"
               [attr.aria-label]="'View details for ' + prod.name"
               class="group bg-white rounded-[2.5rem] border border-slate-100 overflow-hidden cursor-pointer hover:shadow-2xl hover:shadow-primary/10 hover:-translate-y-2 transition-all duration-500">
            <!-- Product Image -->
            <div class="relative h-64 bg-slate-50 overflow-hidden">
              <span *ngIf="prod.tag" class="absolute top-6 left-6 z-10 bg-secondary text-primary px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">{{prod.tag}}</span>
              <img [src]="prod.image" class="w-full h-full object-contain p-8 group-hover:scale-110 transition-transform duration-700" alt="Solar Product">
              <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white/80 to-transparent"></div>
            </div>

            <!-- Product Body -->
            <div class="p-8">
              <div class="flex justify-between items-start mb-2">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{prod.category}}</p>
                <div class="flex items-center gap-1">
                  <lucide-icon [img]="Star" [size]="12" class="text-amber-400 fill-amber-400"></lucide-icon>
                  <span class="text-xs font-bold text-slate-700">{{prod.rating}}</span>
                </div>
              </div>
              <h4 class="text-xl font-black text-slate-900 mb-4 group-hover:text-primary transition-colors leading-tight">{{prod.name}}</h4>
              
              <div class="space-y-3 mb-8">
                <div *ngFor="let spec of prod.specs" class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                  <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                  {{spec}}
                </div>
              </div>

              <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                <div>
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Price From</p>
                  <p class="text-2xl font-black text-slate-900">GH₵ {{prod.price | number}}</p>
                </div>
                <button class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 group-hover:bg-primary group-hover:text-white group-hover:rotate-45 transition-all duration-500 flex items-center justify-center">
                  <lucide-icon [img]="ChevronRight" [size]="20"></lucide-icon>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class MarketplaceComponent implements OnInit {
  private authService = inject(AuthService);
  private router = inject(Router);

  userName = signal('');
  userRole = signal('');
  userInitials = signal('');

  categories = [
    { name: 'All Components', active: true },
    { name: 'Solar Panels', active: false },
    { name: 'Inverters', active: false },
    { name: 'Batteries', active: false },
    { name: 'Kits', active: false }
  ];

  products: Product[] = [
    {
      id: '1',
      name: 'Alpha Mono-Perc 550W',
      category: 'Solar Panels',
      price: 2450,
      rating: 4.9,
      reviews: 128,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Solar+Panel',
      specs: ['21.3% Efficiency', '12 Year Warranty', 'IP68 Junction Box'],
      tag: 'Bestseller'
    },
    {
      id: '2',
      name: 'SunSync 5kW Hybrid Inverter',
      category: 'Inverters',
      price: 15800,
      rating: 4.8,
      reviews: 85,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Inverter',
      specs: ['Single Phase', 'WiFi Monitoring', 'Parallel Ready'],
      tag: 'New'
    },
    {
      id: '3',
      name: 'PowerStore 14.3kWh LiFePO4',
      category: 'Batteries',
      price: 42500,
      rating: 5.0,
      reviews: 42,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Battery',
      specs: ['6000 Cycles', '10 Year Warranty', 'Built-in BMS']
    },
    {
      id: '4',
      name: 'TUMI Starter Home Kit',
      category: 'Kits',
      price: 28900,
      rating: 4.7,
      reviews: 210,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Solar+Kit',
      specs: ['4x Panels Included', '3kW Inverter', 'Essential Wiring'],
      tag: 'Recommended'
    }
  ];

  // Icons for template
  readonly LayoutDashboard = LayoutDashboard;
  readonly ShoppingBag = ShoppingBag;
  readonly History = History;
  readonly Settings = Settings;
  readonly Search = Search;
  readonly Filter = Filter;
  readonly Star = Star;
  readonly ChevronRight = ChevronRight;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userRole.set(user.role);
      this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
    }
  }

  goToDetail(id: string) {
    this.router.navigate(['/admin/consumer/product-detail', id]);
  }
}
