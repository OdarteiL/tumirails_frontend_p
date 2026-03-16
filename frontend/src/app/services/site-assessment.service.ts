import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

export interface SiteAssessmentRequest {
  property_type: string;
  address: string;
  preferred_date: string;
  preferred_time: string;
  notes: string;
  purchased_equipment: string;
}

export interface SiteAssessment {
  id: number;
  property_type: string;
  address: string;
  preferred_date: string;
  preferred_time: string;
  notes: string;
  purchased_equipment: string;
  status: 'pending' | 'accepted' | 'declined' | 'completed';
  installer_name?: string;
  created_at: string;
}

@Injectable({ providedIn: 'root' })
export class SiteAssessmentService {
  constructor(private api: ApiService) {}

  // Customer
  requestAssessment(data: SiteAssessmentRequest): Observable<{ data: SiteAssessment }> {
    return this.api.post<{ data: SiteAssessment }>('/site-assessments', data);
  }

  getMyAssessments(): Observable<{ data: SiteAssessment[] }> {
    return this.api.get<{ data: SiteAssessment[] }>('/site-assessments');
  }

  // Installer
  getPendingAssessments(): Observable<{ data: SiteAssessment[] }> {
    return this.api.get<{ data: SiteAssessment[] }>('/installer/site-assessments');
  }

  respondToAssessment(id: number, action: 'accepted' | 'declined'): Observable<{ data: SiteAssessment }> {
    return this.api.put<{ data: SiteAssessment }>(`/installer/site-assessments/${id}`, { status: action });
  }
}
