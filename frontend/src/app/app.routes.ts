import { Routes } from '@angular/router';
import { LoginComponent } from './components/auth/login/login.component';
import { RegisterComponent } from './components/auth/register/register.component';
import { ForgotPasswordComponent } from './components/auth/forgot-password/forgot-password.component';
import { ResetPasswordComponent } from './components/auth/reset-password/reset-password.component';
import { DashboardComponent } from './components/dashboard.component';
import { AuthGuard } from './guards/auth.guard';
import { GuestLayoutComponent } from './components/guest/guest-layout/guest-layout';
import { ConsumerDashboardComponent } from './components/admin/consumer/dashboard/dashboard';
import { VendorDashboardComponent } from './components/vendor/dashboard/dashboard';
import { VendorProductsComponent } from './components/vendor/products/products';
import { VendorOrdersComponent } from './components/vendor/orders/orders';
import { InstallerDashboardComponent } from './components/installer/dashboard/dashboard';
import { InstallerJobsComponent } from './components/installer/jobs/jobs';
import { InstallerScheduleComponent } from './components/installer/schedule/schedule';
import { InstallerAssessmentsComponent } from './components/installer/assessments/assessments';
import { AdminOrdersComponent } from './components/admin/orders/orders';
import { AdminCustomersComponent } from './components/admin/customers/customers';
import { AdminDashboardComponent } from './components/admin/dashboard/admin-dashboard';
import { AdminUsersComponent } from './components/admin/users/admin-users';
import { AdminOrganisationsComponent } from './components/admin/organisations/admin-organisations';
import { AdminContactsComponent } from './components/admin/contacts/admin-contacts';
import { AdminAppliancesComponent } from './components/admin/appliances/admin-appliances';
import { AdminAddUserComponent } from './components/admin/users/admin-add-user';
import { AdminSettingsComponent } from './components/admin/settings/admin-settings';
import { MarketplaceComponent } from './components/admin/consumer/marketplace/marketplace';
import { ProductDetailComponent } from './components/admin/consumer/product-detail/product-detail';
import { HomeComponent } from './components/guest/pages/home/home';
import { AboutComponent } from './components/guest/pages/about/about';
import { ConsumersComponent } from './components/guest/pages/consumers/consumers';
import { InstallersComponent } from './components/guest/pages/installers/installers';
import { EstimatorComponent } from './components/guest/pages/estimator/estimator';
import { ContactComponent } from './components/guest/pages/contact/contact';
import { HowItWorksComponent } from './components/guest/pages/how-it-works/how-it-works';
import { CustomerEstimationsComponent } from './components/admin/consumer/estimations/estimations';
import { CustomerSettingsComponent } from './components/admin/consumer/settings/settings';
import { SiteAssessmentsComponent } from './components/admin/consumer/site-assessments/site-assessments';

export const routes: Routes = [
  {
    path: '',
    component: GuestLayoutComponent,
    children: [
      { path: '', component: HomeComponent },
      { path: 'about', component: AboutComponent },
      { path: 'consumers', component: ConsumersComponent },
      { path: 'installers', component: InstallersComponent },
      { path: 'estimator', component: EstimatorComponent },
      { path: 'contact', component: ContactComponent },
      { path: 'how-it-works', component: HowItWorksComponent },
    ]
  },
  {
    path: 'customer',
    canActivate: [AuthGuard],
    children: [
      { path: 'dashboard', component: ConsumerDashboardComponent },
      { path: 'marketplace', component: MarketplaceComponent },
      { path: 'product-detail/:id', component: ProductDetailComponent },
      { path: 'estimations', component: CustomerEstimationsComponent },
      { path: 'site-assessments', component: SiteAssessmentsComponent },
      { path: 'settings', component: CustomerSettingsComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  {
    path: 'vendor',
    canActivate: [AuthGuard],
    children: [
      { path: 'dashboard', component: VendorDashboardComponent },
      { path: 'products', component: VendorProductsComponent },
      { path: 'orders', component: VendorOrdersComponent },
      { path: 'inventory', component: VendorProductsComponent },
      { path: 'settings', component: VendorDashboardComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  {
    path: 'installer',
    canActivate: [AuthGuard],
    children: [
      { path: 'dashboard', component: InstallerDashboardComponent },
      { path: 'assessments', component: InstallerAssessmentsComponent },
      { path: 'jobs', component: InstallerJobsComponent },
      { path: 'schedule', component: InstallerScheduleComponent },
      { path: 'availability', component: InstallerDashboardComponent },
      { path: 'settings', component: InstallerDashboardComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  {
    path: 'admin',
    canActivate: [AuthGuard],
    children: [
      { path: 'dashboard', component: AdminDashboardComponent },
      { path: 'users', component: AdminUsersComponent },
      { path: 'users/add', component: AdminAddUserComponent },
      { path: 'organisations', component: AdminOrganisationsComponent },
      { path: 'contacts', component: AdminContactsComponent },
      { path: 'orders', component: AdminOrdersComponent },
      { path: 'customers', component: AdminCustomersComponent },
      { path: 'settings', component: AdminSettingsComponent },
      { path: 'appliances', component: AdminAppliancesComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'forgot-password', component: ForgotPasswordComponent },
  { path: 'reset-password', component: ResetPasswordComponent },
  { path: 'dashboard', redirectTo: 'customer/dashboard' },
  { path: '**', redirectTo: '' }
];



