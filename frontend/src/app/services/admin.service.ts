import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';

export interface AdminUser {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  role: string;
  status: string;
  created_at: string;
  sites_count?: number;
  estimations_count?: number;
}

export interface AdminOrganisation {
  id: number;
  name: string;
  email: string;
  type: string;
  status: string;
  created_at: string;
  members_count?: number;
  sites_count?: number;
}

export interface AdminContact {
  id: number;
  name: string;
  email: string;
  subject: string;
  message: string;
  created_at: string;
}

export interface AdminAppliance {
  id: number;
  name: string;
  category_id: number;
  category?: { id: number; name: string };
  default_wattage: number;
  default_usage_hours: number;
  is_public: boolean;
  is_active: boolean;
}

export interface LaravelMeta {
  total: number;
  current_page: number;
  last_page: number;
  per_page: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: LaravelMeta;
}

@Injectable({ providedIn: 'root' })
export class AdminService {
  constructor(private api: ApiService) {}

  // Users
  getUsers(params: Record<string, string> = {}): Observable<PaginatedResponse<AdminUser>> {
    const q = new URLSearchParams(params).toString();
    return this.api.get<PaginatedResponse<AdminUser>>(`/admin/users${q ? '?' + q : ''}`);
  }

  createUser(data: object): Observable<any> {
    return this.api.post<any>('/auth/register', data);
  }

  updateUserStatus(userId: number, status: string, reason?: string): Observable<any> {
    return this.api.patch<any>(`/admin/users/${userId}/status`, { status, reason });
  }

  // Organisations
  getOrganisations(params: Record<string, string> = {}): Observable<PaginatedResponse<AdminOrganisation>> {
    const q = new URLSearchParams(params).toString();
    return this.api.get<PaginatedResponse<AdminOrganisation>>(`/admin/organisations${q ? '?' + q : ''}`);
  }

  createOrganisation(data: object): Observable<any> {
    return this.api.post<any>('/organisations', data);
  }

  updateOrgStatus(orgId: number, status: string, reason?: string): Observable<any> {
    return this.api.patch<any>(`/admin/organisations/${orgId}/status`, { status, reason });
  }

  // Contacts
  getContacts(): Observable<{ success: boolean; data: AdminContact[] }> {
    return this.api.get<{ success: boolean; data: AdminContact[] }>('/admin/contacts');
  }

  // Appliances
  getAppliances(params: Record<string, string> = {}): Observable<{ success: boolean; data: AdminAppliance[]; meta: LaravelMeta }> {
    const q = new URLSearchParams(params).toString();
    return this.api.get<any>(`/appliances${q ? '?' + q : ''}`);
  }

  createAppliance(data: object): Observable<any> {
    return this.api.post<any>('/admin/appliances', data);
  }

  updateAppliance(id: number, data: object): Observable<any> {
    return this.api.put<any>(`/admin/appliances/${id}`, data);
  }

  deleteAppliance(id: number): Observable<any> {
    return this.api.delete<any>(`/admin/appliances/${id}`);
  }
}
