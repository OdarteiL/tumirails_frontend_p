import { Routes } from '@angular/router';
import { LoginComponent } from './components/auth/login/login.component';
import { RegisterComponent } from './components/auth/register/register.component';
import { DashboardComponent } from './components/dashboard.component';
import { AuthGuard } from './guards/auth.guard';
import { GuestLayoutComponent } from './components/guest/guest-layout/guest-layout';
import { ConsumerDashboardComponent } from './components/admin/consumer/dashboard/dashboard';
import { MarketplaceComponent } from './components/admin/consumer/marketplace/marketplace';
import { ProductDetailComponent } from './components/admin/consumer/product-detail/product-detail';
import { HomeComponent } from './components/guest/pages/home/home';
import { AboutComponent } from './components/guest/pages/about/about';
import { ConsumersComponent } from './components/guest/pages/consumers/consumers';
import { InstallersComponent } from './components/guest/pages/installers/installers';
import { EstimatorComponent } from './components/guest/pages/estimator/estimator';
import { ContactComponent } from './components/guest/pages/contact/contact';
import { HowItWorksComponent } from './components/guest/pages/how-it-works/how-it-works';

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
    path: 'admin',
    canActivate: [AuthGuard],
    children: [
      {
        path: 'consumer',
        children: [
          { path: 'dashboard', component: ConsumerDashboardComponent },
          { path: 'marketplace', component: MarketplaceComponent },
          { path: 'product-detail/:id', component: ProductDetailComponent },
          { path: 'orders', component: ConsumerDashboardComponent }, // Placeholder
          { path: 'settings', component: ConsumerDashboardComponent }, // Placeholder
          { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
        ]
      },
      { path: 'dashboard', component: DashboardComponent },
      { path: 'orders', component: DashboardComponent },
      { path: 'products', component: DashboardComponent },
      { path: 'customers', component: DashboardComponent },
      { path: 'settings', component: DashboardComponent },
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' }
    ]
  },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'dashboard', redirectTo: 'admin/dashboard' },
  { path: '**', redirectTo: '' }
];



