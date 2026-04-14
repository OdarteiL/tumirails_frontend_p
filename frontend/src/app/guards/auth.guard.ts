import { Injectable } from '@angular/core';
import { CanActivate, Router, ActivatedRouteSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({ providedIn: 'root' })
export class AuthGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot): boolean {
    if (!this.authService.isAuthenticated()) {
      this.router.navigate(['/login']);
      return false;
    }

    const user = this.authService.currentUser();
    const path = route.routeConfig?.path;

    // Prevent wrong-role access
    if (path === 'admin' && user?.role !== 'admin') {
      this.redirectByRole(user?.role);
      return false;
    }
    if (path === 'vendor' && user?.role !== 'provider') {
      this.redirectByRole(user?.role);
      return false;
    }
    if (path === 'installer' && user?.role !== 'installer') {
      this.redirectByRole(user?.role);
      return false;
    }
    if (path === 'customer' && !['customer', 'consumer'].includes(user?.role ?? '')) {
      this.redirectByRole(user?.role);
      return false;
    }

    return true;
  }

  private redirectByRole(role?: string): void {
    switch (role) {
      case 'admin': this.router.navigate(['/admin/dashboard']); break;
      case 'provider': this.router.navigate(['/vendor/dashboard']); break;
      case 'installer': this.router.navigate(['/installer/dashboard']); break;
      default: this.router.navigate(['/customer/dashboard']);
    }
  }
}
