import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import {
  LucideAngularModule,
  LayoutDashboard,
  ShoppingBag,
  History,
  Settings,
  ArrowLeft,
  Star,
  CheckCircle2,
  ShieldCheck,
  MessageSquare,
  Truck,
  Plus,
  Minus,
  ShoppingCart
} from 'lucide-angular';

@Component({
  selector: 'app-consumer-product-detail',
  standalone: true,
  imports: [CommonModule, RouterModule, AdminLayoutComponent, LucideAngularModule],
  template: `
    <app-admin-layout 
      pageTitle="Product Details" 
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

      <div class="animate-in fade-in slide-in-from-left-4 duration-500">
        <!-- Back Button -->
        <button routerLink="/admin/consumer/marketplace" class="flex items-center gap-2 text-slate-500 font-bold hover:text-primary mb-8 transition-colors group">
          <lucide-icon [img]="ArrowLeft" [size]="20" class="group-hover:-translate-x-1 transition-transform"></lucide-icon>
          Back to Marketplace
        </button>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-12">
          <!-- Left: Images -->
          <div class="xl:col-span-7 space-y-6">
            <div class="bg-white rounded-[3rem] p-12 border border-slate-100 flex items-center justify-center min-h-[600px] shadow-sm">
              <img src="https://placehold.co/800x800/f8fafc/304597?text=Solar+Panel+Alpha" class="max-w-full h-auto object-contain" alt="Main Product">
            </div>
            <div class="grid grid-cols-4 gap-4">
              <div *ngFor="let i of [1,2,3,4]" class="bg-white rounded-3xl p-4 border border-slate-100 flex items-center justify-center cursor-pointer hover:border-primary transition-all">
                <img src="https://placehold.co/100x100/f8fafc/304597?text=View+{{i}}" class="max-w-full h-auto" alt="Preview">
              </div>
            </div>
          </div>

          <!-- Right: Info -->
          <div class="xl:col-span-5 space-y-8">
            <div>
              <div class="flex items-center gap-3 mb-4">
                <span class="bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest">In Stock</span>
                <div class="flex items-center gap-1 ml-auto">
                    <lucide-icon [img]="Star" [size]="16" class="text-amber-400 fill-amber-400"></lucide-icon>
                    <span class="font-black text-slate-900">4.9</span>
                    <span class="text-slate-400 text-sm font-medium">(128 reviews)</span>
                </div>
              </div>
              <h1 class="text-4xl font-black text-slate-900 leading-tight mb-2">Alpha Mono-Perc Solar Panel 550W</h1>
              <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">TUMI PREFERRED SERIES</p>
            </div>

            <div class="p-8 bg-slate-50/50 rounded-3xl border border-slate-100">
               <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Price per unit</p>
               <h2 class="text-4xl font-black text-slate-900 mb-6">GH₵ 2,450.00</h2>
               
               <div class="flex items-center gap-4">
                  <div class="flex items-center bg-white border border-slate-200 rounded-2xl p-1 shadow-sm">
                    <button (click)="qty.set(qty() > 1 ? qty() - 1 : 1)" class="w-12 h-12 flex items-center justify-center hover:bg-slate-50 rounded-xl transition-colors">
                      <lucide-icon [img]="Minus" [size]="18"></lucide-icon>
                    </button>
                    <span class="w-12 text-center font-black text-xl">{{qty()}}</span>
                    <button (click)="qty.set(qty() + 1)" class="w-12 h-12 flex items-center justify-center hover:bg-slate-50 rounded-xl transition-colors">
                      <lucide-icon [img]="Plus" [size]="18"></lucide-icon>
                    </button>
                  </div>
                  <button class="flex-1 bg-primary text-white h-14 rounded-2xl font-black flex items-center justify-center gap-3 shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    <lucide-icon [img]="ShoppingCart" [size]="20"></lucide-icon>
                    Add to System Request
                  </button>
               </div>
            </div>

            <div class="space-y-6">
              <h3 class="text-lg font-black text-slate-900 border-b border-slate-100 pb-4">Key Specifications</h3>
              <div class="grid grid-cols-2 gap-y-4 gap-x-8">
                 <div *ngFor="let spec of specs" class="flex items-start gap-3">
                    <lucide-icon [img]="CheckCircle2" [size]="18" class="text-emerald-500 mt-0.5"></lucide-icon>
                    <div>
                      <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{spec.label}}</p>
                      <p class="font-bold text-slate-900">{{spec.value}}</p>
                    </div>
                 </div>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white border border-slate-100 p-4 rounded-2xl text-center space-y-2">
                   <lucide-icon [img]="ShieldCheck" [size]="20" class="mx-auto text-primary"></lucide-icon>
                   <p class="text-[10px] font-bold text-slate-700 uppercase">12 Yr Warranty</p>
                </div>
                <div class="bg-white border border-slate-100 p-4 rounded-2xl text-center space-y-2">
                   <lucide-icon [img]="Truck" [size]="20" class="mx-auto text-primary"></lucide-icon>
                   <p class="text-[10px] font-bold text-slate-700 uppercase">Fast Delivery</p>
                </div>
                <div class="bg-white border border-slate-100 p-4 rounded-2xl text-center space-y-2">
                   <lucide-icon [img]="MessageSquare" [size]="20" class="mx-auto text-primary"></lucide-icon>
                   <p class="text-[10px] font-bold text-slate-700 uppercase">Expert Support</p>
                </div>
            </div>
          </div>
        </div>
      </div>
    </app-admin-layout>
  `
})
export class ProductDetailComponent implements OnInit {
  private authService = inject(AuthService);
  private route = inject(ActivatedRoute);

  userName = signal('');
  userRole = signal('');
  userInitials = signal('');
  qty = signal(1);

  specs = [
    { label: 'Power Output', value: '550 Watts' },
    { label: 'Efficiency', value: '21.3%' },
    { label: 'Junction Box', value: 'IP68 Rated' },
    { label: 'Output Type', value: 'Mono-Perc' },
    { label: 'Frame', value: 'Alloy Black' },
    { label: 'Glass', value: 'Anti-reflective' }
  ];

  // Icons for template
  readonly LayoutDashboard = LayoutDashboard;
  readonly ShoppingBag = ShoppingBag;
  readonly History = History;
  readonly Settings = Settings;
  readonly ArrowLeft = ArrowLeft;
  readonly Star = Star;
  readonly CheckCircle2 = CheckCircle2;
  readonly ShieldCheck = ShieldCheck;
  readonly MessageSquare = MessageSquare;
  readonly Truck = Truck;
  readonly Plus = Plus;
  readonly Minus = Minus;
  readonly ShoppingCart = ShoppingCart;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userRole.set(user.role);
      this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
    }

    // In a real app, we'd fetch product by ID from route params
    const id = this.route.snapshot.paramMap.get('id');
    console.log('Loading product:', id);
  }
}
