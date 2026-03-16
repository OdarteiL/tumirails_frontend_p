import { Component, OnInit, signal, inject, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AdminLayoutComponent } from '../../admin-layout/admin-layout';
import { AuthService } from '../../../../services/auth.service';
import { SiteAssessmentService, SiteAssessment } from '../../../../services/site-assessment.service';
import {
    LucideAngularModule, LayoutDashboard, History, Settings,
    ClipboardCheck, Calendar, MapPin, Clock, ShoppingBag, Plus, X, CheckCircle, AlertCircle
} from 'lucide-angular';

@Component({
    selector: 'app-site-assessments',
    standalone: true,
    imports: [CommonModule, RouterModule, ReactiveFormsModule, AdminLayoutComponent, LucideAngularModule],
    templateUrl: './site-assessments.html',
    styleUrl: './site-assessments.css'
})
export class SiteAssessmentsComponent implements OnInit {
    private authService = inject(AuthService);
    private assessmentService = inject(SiteAssessmentService);
    private fb = inject(FormBuilder);

    userName = signal('');
    userRole = signal('');
    userInitials = signal('');
    assessments = signal<SiteAssessment[]>([]);
    showForm = signal(false);
    isSubmitting = signal(false);
    successMessage = signal('');
    errorMessage = signal('');

    readonly LayoutDashboard = LayoutDashboard;
    readonly History = History;
    readonly Settings = Settings;
    readonly ClipboardCheck = ClipboardCheck;
    readonly Calendar = Calendar;
    readonly MapPin = MapPin;
    readonly Clock = Clock;
    readonly ShoppingBag = ShoppingBag;
    readonly Plus = Plus;
    readonly X = X;
    readonly CheckCircle = CheckCircle;
    readonly AlertCircle = AlertCircle;

    pendingCount = computed(() => this.assessments().filter(a => a.status === 'pending').length);
    acceptedCount = computed(() => this.assessments().filter(a => a.status === 'accepted').length);
    completedCount = computed(() => this.assessments().filter(a => a.status === 'completed').length);

    form: FormGroup = this.fb.group({
        property_type: ['', Validators.required],
        address: ['', Validators.required],
        preferred_date: ['', Validators.required],
        preferred_time: ['', Validators.required],
        purchased_equipment: [''],
        notes: ['']
    });

    ngOnInit(): void {
        const user = this.authService.currentUser();
        if (user) {
            this.userName.set(`${user.first_name} ${user.last_name}`);
            this.userRole.set(user.role);
            this.userInitials.set(`${user.first_name?.[0] || 'U'}${user.last_name?.[0] || ''}`);
        }
        this.loadAssessments();
    }

    loadAssessments(): void {
        this.assessmentService.getMyAssessments().subscribe({
            next: (res) => this.assessments.set(res.data),
            error: () => this.assessments.set([])
        });
    }

    submitRequest(): void {
        if (this.form.invalid) { this.form.markAllAsTouched(); return; }
        this.isSubmitting.set(true);
        this.errorMessage.set('');
        this.assessmentService.requestAssessment(this.form.value).subscribe({
            next: (res) => {
                this.assessments.update(list => [res.data, ...list]);
                this.successMessage.set('Assessment request submitted! An installer will respond shortly.');
                this.showForm.set(false);
                this.form.reset();
                this.isSubmitting.set(false);
            },
            error: () => {
                this.errorMessage.set('Failed to submit request. Please try again.');
                this.isSubmitting.set(false);
            }
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
}
