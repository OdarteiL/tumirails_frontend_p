import { Component, OnInit, signal, inject, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
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
  ChevronRight,
  ClipboardCheck,
  X,
  ChevronLeft,
  MapPin
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
  imports: [CommonModule, RouterModule, FormsModule, AdminLayoutComponent, LucideAngularModule],
  template: `
    <app-admin-layout 
      pageTitle="Solar Marketplace" 
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
        <a routerLink="/customer/sites" 
           routerLinkActive="!bg-white/10 !text-secondary shadow-inner"
           [routerLinkActiveOptions]="{exact: false}" 
           class="flex items-center gap-4 px-5 py-3.5 rounded-xl text-white/60 hover:text-white hover:bg-white/5 hover:translate-x-1 transition-all duration-300 font-medium text-[0.95rem] mb-2 group">
          <lucide-icon [img]="MapPin" [size]="20"></lucide-icon>
          <span>My Sites</span>
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

      <div class="space-y-8 animate-in fade-in zoom-in-95 duration-500">
        <!-- Filter Modal -->
        <div *ngIf="showFilterModal()" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-md p-4">
          <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md p-8 relative">
            <button (click)="showFilterModal.set(false)"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors">
              <lucide-icon [img]="X" class="w-6 h-6"></lucide-icon>
            </button>
            
            <h3 class="text-2xl font-bold text-slate-900 mb-6">Filter Products</h3>
            
            <div class="space-y-6">
              <div>
                <span class="block text-sm font-bold text-slate-700 mb-3">Price Range</span>
                <div class="flex gap-3">
                  <input type="number" [(ngModel)]="priceMin" placeholder="Min" aria-label="Minimum price"
                         class="flex-1 px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary">
                  <input type="number" [(ngModel)]="priceMax" placeholder="Max" aria-label="Maximum price"
                         class="flex-1 px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary">
                </div>
              </div>
              
              <div>
                <label for="minRating" class="block text-sm font-bold text-slate-700 mb-3">Minimum Rating</label>
                <select id="minRating" [(ngModel)]="minRating" 
                        class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary">
                  <option [value]="0">All Ratings</option>
                  <option [value]="4">4+ Stars</option>
                  <option [value]="4.5">4.5+ Stars</option>
                  <option [value]="4.8">4.8+ Stars</option>
                </select>
              </div>
              
              <div class="flex gap-3 pt-4">
                <button (click)="clearFilters()" 
                        class="flex-1 px-6 py-3 border border-slate-200 rounded-xl font-medium text-slate-600 hover:bg-slate-50">
                  Clear
                </button>
                <button (click)="applyFilters()" 
                        class="flex-1 px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-primary/90">
                  Apply
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Header Controls -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
          <div class="w-full md:w-96 relative">
            <lucide-icon [img]="Search" [size]="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></lucide-icon>
            <input type="text" [(ngModel)]="searchQuery" (ngModelChange)="onSearchChange()" 
                   placeholder="Search components..." 
                   class="w-full bg-white border border-slate-200 rounded-2xl py-3 pl-12 pr-4 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all">
          </div>
          
          <div class="flex gap-3 w-full md:w-auto">
            <button (click)="showFilterModal.set(true)" 
                    class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-white border border-slate-200 px-6 py-3 rounded-2xl font-medium text-slate-600 hover:bg-slate-50 transition-all">
              <lucide-icon [img]="Filter" [size]="18"></lucide-icon>
              Filter
            </button>
            <button class="flex-1 md:flex-none bg-primary text-white px-8 py-3 rounded-2xl font-medium shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 transition-all">
              Check Compatibility
            </button>
          </div>
        </div>

        <!-- Categories -->
        <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
          <button *ngFor="let cat of categories" 
                  (click)="selectCategory(cat.name)"
                  [class]="cat.active ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white border border-slate-100 text-slate-500 hover:border-slate-300'"
                  class="whitespace-nowrap px-6 py-2.5 rounded-full text-sm font-bold transition-all">
            {{cat.name}}
          </button>
        </div>

        <!-- Results Info -->
        <div class="flex justify-between items-center text-sm text-slate-500">
          <p>Showing <span class="font-bold text-slate-900">{{paginatedProducts().length}}</span> of <span class="font-bold text-slate-900">{{filteredProducts().length}}</span> products</p>
          <p>Page {{currentPage()}} of {{totalPages()}}</p>
        </div>

        <!-- Product Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8">
          <div *ngFor="let prod of paginatedProducts()" 
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

        <!-- Empty State -->
        <div *ngIf="filteredProducts().length === 0" class="text-center py-20">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
            <lucide-icon [img]="Search" class="w-8 h-8 text-slate-400"></lucide-icon>
          </div>
          <p class="text-slate-500 font-medium">No products found</p>
          <button (click)="clearFilters()" class="mt-4 text-primary font-bold hover:underline">Clear filters</button>
        </div>

        <!-- Pagination -->
        <div *ngIf="totalPages() > 1" class="flex justify-center items-center gap-2">
          <button (click)="goToPage(currentPage() - 1)" [disabled]="currentPage() === 1"
                  [class.opacity-50]="currentPage() === 1"
                  [class.cursor-not-allowed]="currentPage() === 1"
                  class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
            <lucide-icon [img]="ChevronLeft" [size]="20"></lucide-icon>
          </button>
          
          <button *ngFor="let page of pageNumbers()" 
                  (click)="goToPage(page)"
                  [class.bg-primary]="page === currentPage()"
                  [class.text-white]="page === currentPage()"
                  [class.border-primary]="page === currentPage()"
                  class="w-10 h-10 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors font-medium">
            {{page}}
          </button>
          
          <button (click)="goToPage(currentPage() + 1)" [disabled]="currentPage() === totalPages()"
                  [class.opacity-50]="currentPage() === totalPages()"
                  [class.cursor-not-allowed]="currentPage() === totalPages()"
                  class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
            <lucide-icon [img]="ChevronRight" [size]="20"></lucide-icon>
          </button>
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

  // Search and filter
  searchQuery = signal('');
  showFilterModal = signal(false);
  priceMin = signal<number | null>(null);
  priceMax = signal<number | null>(null);
  minRating = signal(0);
  selectedCategory = signal('All Components');

  // Pagination
  currentPage = signal(1);
  itemsPerPage = 8;

  categories = [
    { name: 'All Components', active: true },
    { name: 'Solar Panels', active: false },
    { name: 'Inverters', active: false },
    { name: 'Batteries', active: false },
    { name: 'Kits', active: false }
  ];

  allProducts: Product[] = [
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
    },
    {
      id: '5',
      name: 'EcoMax 450W Bifacial',
      category: 'Solar Panels',
      price: 2100,
      rating: 4.6,
      reviews: 95,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Solar+Panel',
      specs: ['19.8% Efficiency', '15 Year Warranty', 'Bifacial Design']
    },
    {
      id: '6',
      name: 'GridMaster 10kW Inverter',
      category: 'Inverters',
      price: 24500,
      rating: 4.9,
      reviews: 67,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Inverter',
      specs: ['Three Phase', 'Smart Grid Ready', '98.5% Efficiency']
    },
    {
      id: '7',
      name: 'VoltStack 9.6kWh Battery',
      category: 'Batteries',
      price: 32000,
      rating: 4.8,
      reviews: 54,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Battery',
      specs: ['5000 Cycles', '8 Year Warranty', 'Modular Design']
    },
    {
      id: '8',
      name: 'Premium Business Kit',
      category: 'Kits',
      price: 58900,
      rating: 4.9,
      reviews: 38,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Solar+Kit',
      specs: ['8x Panels', '10kW Inverter', 'Battery Included']
    },
    {
      id: '9',
      name: 'SolarPro 600W Panel',
      category: 'Solar Panels',
      price: 2850,
      rating: 5.0,
      reviews: 112,
      image: 'https://placehold.co/400x400/f8fafc/304597?text=Solar+Panel',
      specs: ['22.1% Efficiency', '25 Year Warranty', 'Anti-Reflective']
    }
  ];

  // Computed filtered products
  filteredProducts = computed(() => {
    let products = this.allProducts;
    const query = this.searchQuery().toLowerCase();
    const category = this.selectedCategory();
    const minPrice = this.priceMin();
    const maxPrice = this.priceMax();
    const rating = this.minRating();

    // Search filter
    if (query) {
      products = products.filter(p => 
        p.name.toLowerCase().includes(query) || 
        p.category.toLowerCase().includes(query) ||
        p.specs.some(s => s.toLowerCase().includes(query))
      );
    }

    // Category filter
    if (category !== 'All Components') {
      products = products.filter(p => p.category === category);
    }

    // Price filter
    if (minPrice !== null) {
      products = products.filter(p => p.price >= minPrice);
    }
    if (maxPrice !== null) {
      products = products.filter(p => p.price <= maxPrice);
    }

    // Rating filter
    if (rating > 0) {
      products = products.filter(p => p.rating >= rating);
    }

    return products;
  });

  // Computed paginated products
  paginatedProducts = computed(() => {
    const start = (this.currentPage() - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    return this.filteredProducts().slice(start, end);
  });

  // Computed total pages
  totalPages = computed(() => {
    return Math.ceil(this.filteredProducts().length / this.itemsPerPage);
  });

  // Computed page numbers
  pageNumbers = computed(() => {
    const total = this.totalPages();
    const current = this.currentPage();
    const pages: number[] = [];
    
    if (total <= 7) {
      for (let i = 1; i <= total; i++) pages.push(i);
    } else {
      if (current <= 4) {
        for (let i = 1; i <= 5; i++) pages.push(i);
        pages.push(-1, total);
      } else if (current >= total - 3) {
        pages.push(1, -1);
        for (let i = total - 4; i <= total; i++) pages.push(i);
      } else {
        pages.push(1, -1);
        for (let i = current - 1; i <= current + 1; i++) pages.push(i);
        pages.push(-1, total);
      }
    }
    
    return pages;
  });

  // Icons for template
  readonly LayoutDashboard = LayoutDashboard;
  readonly ShoppingBag = ShoppingBag;
  readonly History = History;
  readonly Settings = Settings;
  readonly Search = Search;
  readonly Filter = Filter;
  readonly Star = Star;
  readonly ChevronRight = ChevronRight;
  readonly ClipboardCheck = ClipboardCheck;
  readonly MapPin = MapPin;
  readonly X = X;
  readonly ChevronLeft = ChevronLeft;

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userRole.set(user.role);
      this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
    }
  }

  onSearchChange() {
    this.currentPage.set(1);
  }

  selectCategory(category: string) {
    this.selectedCategory.set(category);
    this.categories.forEach(c => c.active = c.name === category);
    this.currentPage.set(1);
  }

  applyFilters() {
    this.showFilterModal.set(false);
    this.currentPage.set(1);
  }

  clearFilters() {
    this.searchQuery.set('');
    this.priceMin.set(null);
    this.priceMax.set(null);
    this.minRating.set(0);
    this.selectedCategory.set('All Components');
    this.categories.forEach(c => c.active = c.name === 'All Components');
    this.currentPage.set(1);
    this.showFilterModal.set(false);
  }

  goToPage(page: number) {
    if (page >= 1 && page <= this.totalPages()) {
      this.currentPage.set(page);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  goToDetail(id: string) {
    this.router.navigate(['/customer/product-detail', id]);
  }
}
