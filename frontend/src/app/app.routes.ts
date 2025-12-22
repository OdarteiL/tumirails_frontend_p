import { Routes } from '@angular/router';
import { LoginComponent } from './components/auth/login/login.component';
import { RegisterComponent } from './components/auth/register/register.component';
import { DashboardComponent } from './components/dashboard.component';
import { AuthGuard } from './guards/auth.guard';
import { GuestLayoutComponent } from './components/guest/guest-layout/guest-layout';
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
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'dashboard', component: DashboardComponent, canActivate: [AuthGuard] },
  { path: '**', redirectTo: '' }
];
