import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-installer-schedule',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  template: `
    <div class="min-h-screen bg-[#F4F5F7]">
      <div class="flex">
        <aside class="w-64 bg-white border-r border-gray-200 min-h-screen flex flex-col">
          <div class="p-6 border-b border-gray-200 flex justify-center">
            <a routerLink="/" class="block hover:opacity-80 transition-opacity">
              <img src="/assets/tumi_logo.png" alt="Tumi Solar" class="h-8 w-auto">
            </a>
          </div>
          <div class="p-6 flex-1">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Installer Portal</h2>
            <nav class="space-y-1">
              <a routerLink="/installer/dashboard" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Dashboard</a>
              <a routerLink="/installer/jobs" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Jobs</a>
              <a routerLink="/installer/schedule" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded">Schedule</a>
              <a routerLink="/installer/availability" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Availability</a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <main class="flex-1 p-8">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Schedule</h1>
            <p class="text-sm text-gray-600 mt-1">View your upcoming appointments</p>
          </div>

          <div class="grid grid-cols-7 gap-2 mb-6">
            <div *ngFor="let day of days" class="text-center">
              <div class="text-xs font-medium text-gray-500 uppercase mb-2">{{ day }}</div>
            </div>
          </div>

          <div class="bg-white border border-gray-200 rounded">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-base font-semibold text-gray-900">Upcoming Appointments</h2>
            </div>
            <div class="divide-y divide-gray-100">
              <div *ngFor="let appointment of appointments" class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ appointment.title }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ appointment.date }} at {{ appointment.time }}</div>
                    <div class="text-xs text-gray-500">{{ appointment.location }}</div>
                  </div>
                  <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">{{ appointment.type }}</span>
                </div>
              </div>
              <div *ngIf="appointments.length === 0" class="px-6 py-12 text-center text-sm text-gray-500">
                No upcoming appointments
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  `
})
export class InstallerScheduleComponent {
  days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  appointments = [
    { title: 'Solar Panel Installation', date: '2026-03-15', time: '09:00 AM', location: 'Accra', type: 'Installation' },
    { title: 'Site Inspection', date: '2026-03-16', time: '02:00 PM', location: 'Kumasi', type: 'Inspection' },
    { title: 'Maintenance Check', date: '2026-03-18', time: '11:00 AM', location: 'Tema', type: 'Maintenance' }
  ];

  constructor(private authService: AuthService, private router: Router) {}

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => this.router.navigate(['/'])
    });
  }
}
