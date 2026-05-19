import { Component, OnInit, signal, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import { SitesService } from '../../../../services/sites.service';
import { AppliancesService } from '../../../../services/appliances.service';
import { Site, SiteAppliance } from '../../../../models/site.model';
import {
  LucideAngularModule, LayoutDashboard, ShoppingBag, History, Settings,
  MapPin, Plus, X, Trash2, ClipboardCheck
} from 'lucide-angular';

@Component({
  selector: 'app-customer-sites',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule, AdminLayoutComponent, LucideAngularModule],
  templateUrl: './sites.html'
})
export class CustomerSitesComponent implements OnInit {
  private authService = inject(AuthService);
  private sitesService = inject(SitesService);
  private appliancesService = inject(AppliancesService);
  private fb = inject(FormBuilder);

  userName = signal('');
  userRole = signal('');
  userInitials = signal('');
  sites = signal<Site[]>([]);
  selectedSite = signal<Site | null>(null);
  siteAppliances = signal<SiteAppliance[]>([]);
  appliances = signal<any[]>([]);
  showCreateForm = signal(false);
  showAddAppliance = signal(false);
  loading = signal(false);
  error = signal('');

  siteForm: FormGroup;
  applianceForm: FormGroup;

  readonly LayoutDashboard = LayoutDashboard;
  readonly ShoppingBag = ShoppingBag;
  readonly History = History;
  readonly Settings = Settings;
  readonly MapPin = MapPin;
  readonly Plus = Plus;
  readonly X = X;
  readonly Trash2 = Trash2;
  readonly ClipboardCheck = ClipboardCheck;

  constructor() {
    this.siteForm = this.fb.group({
      name: ['', Validators.required],
      address: ['', Validators.required],
      latitude: [5.6037, [Validators.required, Validators.min(-90), Validators.max(90)]],
      longitude: [-0.1870, [Validators.required, Validators.min(-180), Validators.max(180)]],
      timezone: ['Africa/Accra', Validators.required],
      notes: ['']
    });

    this.applianceForm = this.fb.group({
      appliance_id: ['', Validators.required],
      quantity: [1, [Validators.required, Validators.min(1)]],
      daily_usage_hours: [1, [Validators.required, Validators.min(0), Validators.max(24)]],
      notes: ['']
    });
  }

  ngOnInit(): void {
    const user = this.authService.currentUser();
    if (user) {
      this.userName.set(`${user.first_name} ${user.last_name}`);
      this.userRole.set(user.role);
      this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
    }
    this.loadSites();
    this.loadAppliances();
  }

  loadSites(): void {
    this.sitesService.getSites().subscribe({
      next: (res) => this.sites.set(res.data),
      error: () => this.error.set('Failed to load sites')
    });
  }

  loadAppliances(): void {
    this.appliancesService.getAppliances().subscribe({
      next: (res) => this.appliances.set(res.data),
      error: () => {}
    });
  }

  createSite(): void {
    if (this.siteForm.valid) {
      this.loading.set(true);
      this.sitesService.createSite(this.siteForm.value).subscribe({
        next: () => {
          this.loadSites();
          this.showCreateForm.set(false);
          this.siteForm.reset({ latitude: 5.6037, longitude: -0.1870, timezone: 'Africa/Accra' });
          this.loading.set(false);
        },
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to create site');
          this.loading.set(false);
        }
      });
    }
  }

  selectSite(site: Site): void {
    this.selectedSite.set(site);
    this.sitesService.getSiteAppliances(site.id).subscribe({
      next: (res) => this.siteAppliances.set(res.data),
      error: () => this.siteAppliances.set([])
    });
  }

  addAppliance(): void {
    const site = this.selectedSite();
    if (this.applianceForm.valid && site) {
      this.loading.set(true);
      this.sitesService.addApplianceToSite(site.id, this.applianceForm.value).subscribe({
        next: () => {
          this.selectSite(site);
          this.showAddAppliance.set(false);
          this.applianceForm.reset({ quantity: 1, daily_usage_hours: 1 });
          this.loading.set(false);
        },
        error: (err) => {
          this.error.set(err.error?.message || 'Failed to add appliance');
          this.loading.set(false);
        }
      });
    }
  }

  removeAppliance(siteApplianceId: number): void {
    const site = this.selectedSite();
    if (site) {
      this.sitesService.removeApplianceFromSite(site.id, siteApplianceId).subscribe({
        next: () => this.selectSite(site),
        error: () => this.error.set('Failed to remove appliance')
      });
    }
  }
}
