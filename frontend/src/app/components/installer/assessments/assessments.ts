import { Component, OnInit, signal, inject, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { SiteAssessmentService, SiteAssessment } from '../../../services/site-assessment.service';

@Component({
    selector: 'app-installer-assessments',
    standalone: true,
    imports: [CommonModule, RouterModule],
    templateUrl: './assessments.html',
    styleUrl: './assessments.css'
})
export class InstallerAssessmentsComponent implements OnInit {
    private authService = inject(AuthService);
    private assessmentService = inject(SiteAssessmentService);
    private router = inject(Router);

    assessments = signal<SiteAssessment[]>([]);
    filter = signal<'all' | 'pending' | 'accepted' | 'declined'>('pending');
    respondingId = signal<number | null>(null);

    filtered = computed(() => {
        const f = this.filter();
        return f === 'all' ? this.assessments() : this.assessments().filter(a => a.status === f);
    });

    pendingCount = computed(() => this.assessments().filter(a => a.status === 'pending').length);

    ngOnInit(): void {
        this.loadAssessments();
    }

    loadAssessments(): void {
        this.assessmentService.getPendingAssessments().subscribe({
            next: (res) => this.assessments.set(res.data),
            error: () => this.assessments.set([])
        });
    }

    respond(id: number, action: 'accepted' | 'declined'): void {
        this.respondingId.set(id);
        this.assessmentService.respondToAssessment(id, action).subscribe({
            next: (res) => {
                this.assessments.update(list => list.map(a => a.id === id ? res.data : a));
                this.respondingId.set(null);
            },
            error: () => this.respondingId.set(null)
        });
    }

    statusClass(status: string): string {
        const map: Record<string, string> = {
            pending: 'bg-amber-100 text-amber-700',
            accepted: 'bg-blue-100 text-blue-700',
            declined: 'bg-red-100 text-red-700',
            completed: 'bg-emerald-100 text-emerald-700'
        };
        return map[status] ?? 'bg-gray-100 text-gray-700';
    }

    logout(): void {
        this.authService.logout().subscribe({
            next: () => this.router.navigate(['/']),
            error: () => this.router.navigate(['/'])
        });
    }
}
