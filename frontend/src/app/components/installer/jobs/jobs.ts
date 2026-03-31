import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';

@Component({
  selector: 'app-installer-jobs',
  standalone: true,
  imports: [CommonModule, RouterModule],
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
              <a routerLink="/installer/jobs" class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded">Jobs</a>
              <a routerLink="/installer/schedule" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Schedule</a>
              <a routerLink="/installer/availability" class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded">Availability</a>
            </nav>
          </div>
          <div class="p-6 border-t border-gray-200">
            <button (click)="logout()" class="w-full px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded">Logout</button>
          </div>
        </aside>

        <main class="flex-1 p-8">
          <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Jobs</h1>
            <p class="text-sm text-gray-600 mt-1">Manage your installation jobs</p>
          </div>

          <div class="bg-white border border-gray-200 rounded">
            <div class="px-6 py-4 border-b border-gray-200 flex gap-4">
              <button (click)="filter = 'all'" [class]="filter === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">All</button>
              <button (click)="filter = 'pending'" [class]="filter === 'pending' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Pending</button>
              <button (click)="filter = 'active'" [class]="filter === 'active' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Active</button>
              <button (click)="filter = 'completed'" [class]="filter === 'completed' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600'" class="pb-2 text-sm font-medium">Completed</button>
            </div>
            <table class="w-full">
              <thead class="border-b border-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr *ngFor="let job of filteredJobs()" class="border-b border-gray-100">
                  <td class="px-6 py-4 text-sm font-medium">#{{ job.id }}</td>
                  <td class="px-6 py-4 text-sm">{{ job.client }}</td>
                  <td class="px-6 py-4 text-sm">{{ job.location }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 text-xs rounded" [ngClass]="{
                      'bg-yellow-100 text-yellow-700': job.status === 'pending',
                      'bg-blue-100 text-blue-700': job.status === 'active',
                      'bg-green-100 text-green-700': job.status === 'completed'
                    }">{{ job.status }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <button *ngIf="job.status === 'pending'" (click)="acceptJob(job.id)" class="text-blue-600 hover:text-blue-800 mr-2">Accept</button>
                    <button *ngIf="job.status === 'active'" (click)="completeJob(job.id)" class="text-green-600 hover:text-green-800">Complete</button>
                  </td>
                </tr>
                <tr *ngIf="filteredJobs().length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No jobs found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
      </div>
    </div>
  `
})
export class InstallerJobsComponent {
  filter = 'all';
  jobs = [
    { id: 2001, client: 'Alice Brown', location: 'Accra', status: 'pending' },
    { id: 2002, client: 'Charlie Davis', location: 'Kumasi', status: 'active' },
    { id: 2003, client: 'Diana Evans', location: 'Tema', status: 'completed' }
  ];

  constructor(private authService: AuthService, private router: Router) {}

  filteredJobs() {
    return this.filter === 'all' ? this.jobs : this.jobs.filter(j => j.status === this.filter);
  }

  acceptJob(id: number) {
    const job = this.jobs.find(j => j.id === id);
    if (job) job.status = 'active';
  }

  completeJob(id: number) {
    const job = this.jobs.find(j => j.id === id);
    if (job) job.status = 'completed';
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => this.router.navigate(['/'])
    });
  }
}
