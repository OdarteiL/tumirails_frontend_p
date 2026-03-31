import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import {
  ApplianceListResponse,
  ApplianceResponse,
  CreateApplianceRequest,
  UpdateApplianceRequest
} from '../models/appliance.model';

@Injectable({
  providedIn: 'root'
})
export class AppliancesService {
  constructor(private apiService: ApiService) {}

  getAppliances(params?: { search?: string; category_id?: number; page?: number }): Observable<ApplianceListResponse> {
    const queryParams = new URLSearchParams();
    if (params?.search) queryParams.append('search', params.search);
    if (params?.category_id) queryParams.append('category_id', params.category_id.toString());
    if (params?.page) queryParams.append('page', params.page.toString());
    
    const query = queryParams.toString();
    return this.apiService.get<ApplianceListResponse>(`/appliances${query ? '?' + query : ''}`);
  }

  getAppliance(id: number): Observable<ApplianceResponse> {
    return this.apiService.get<ApplianceResponse>(`/appliances/${id}`);
  }

  createAppliance(data: CreateApplianceRequest): Observable<ApplianceResponse> {
    return this.apiService.post<ApplianceResponse>('/appliances', data);
  }

  updateAppliance(id: number, data: UpdateApplianceRequest): Observable<ApplianceResponse> {
    return this.apiService.put<ApplianceResponse>(`/appliances/${id}`, data);
  }

  deleteAppliance(id: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/appliances/${id}`);
  }

  // Admin endpoints
  createPublicAppliance(data: CreateApplianceRequest): Observable<ApplianceResponse> {
    return this.apiService.post<ApplianceResponse>('/admin/appliances', data);
  }

  updateAnyAppliance(id: number, data: UpdateApplianceRequest): Observable<ApplianceResponse> {
    return this.apiService.put<ApplianceResponse>(`/admin/appliances/${id}`, data);
  }

  deleteAnyAppliance(id: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/admin/appliances/${id}`);
  }
}
