import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { User } from '../models/user.model';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule],
  template: `
    <div class="min-h-screen bg-gray-50">
      <nav style="background: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); padding: 1rem 0;">
        <div class="container flex" style="justify-content: space-between; align-items: center;">
          <h1 style="font-size: 1.25rem; font-weight: 600; color: #333;">Tumi Configurator</h1>
          <div class="flex" style="align-items: center; gap: 1rem;">
            <span style="color: #6b7280;">Welcome, {{ currentUser()?.first_name }}!</span>
            <button
              (click)="logout()"
              class="btn"
              style="background-color: #dc2626; color: white;"
            >
              Logout
            </button>
          </div>
        </div>
      </nav>

      <main class="container" style="padding: 2rem 1rem;">
        <div class="text-center">
          <h2 style="font-size: 2rem; font-weight: bold; margin-bottom: 1rem;">Dashboard</h2>
          <p style="color: #6b7280; margin-bottom: 2rem;">Welcome to your Tumi Solar Configurator dashboard!</p>
          
          <div class="card" style="max-width: 400px; margin: 0 auto;">
            <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 1rem;">Your Profile</h3>
            <div style="text-align: left; display: flex; flex-direction: column; gap: 0.5rem;">
              <p><span style="font-weight: 500;">Name:</span> {{ currentUser()?.first_name }} {{ currentUser()?.last_name }}</p>
              <p><span style="font-weight: 500;">Email:</span> {{ currentUser()?.email }}</p>
              <p><span style="font-weight: 500;">Role:</span> {{ currentUser()?.role }}</p>
              <p><span style="font-weight: 500;">Status:</span> 
                <span style="display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px;"
                      [style.background-color]="getStatusColor(currentUser()?.status)"
                      [style.color]="getStatusTextColor(currentUser()?.status)">
                  {{ currentUser()?.status }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  `
})
export class DashboardComponent implements OnInit {
  currentUser = signal<User | null>(null);

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    this.currentUser = this.authService.currentUser;
  }

  ngOnInit(): void {
    // Optionally refresh user data
    this.authService.getCurrentUser().subscribe({
      error: (error) => {
        console.error('Failed to fetch user data:', error);
      }
    });
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        this.router.navigate(['/login']);
      },
      error: (error) => {
        console.error('Logout error:', error);
        this.router.navigate(['/login']);
      }
    });
  }

  getStatusColor(status?: string): string {
    switch (status) {
      case 'active':
        return '#dcfce7';
      case 'inactive':
        return '#f3f4f6';
      case 'suspended':
        return '#fee2e2';
      default:
        return '#f3f4f6';
    }
  }

  getStatusTextColor(status?: string): string {
    switch (status) {
      case 'active':
        return '#166534';
      case 'inactive':
        return '#374151';
      case 'suspended':
        return '#991b1b';
      default:
        return '#374151';
    }
  }
}