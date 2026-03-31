import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import {
  SiteListResponse,
  SiteResponse,
  SiteApplianceListResponse,
  CreateSiteRequest,
  AddApplianceToSiteRequest
} from '../models/site.model';

@Injectable({
  providedIn: 'root'
})
export class SitesService {
  constructor(private apiService: ApiService) {}

  getSites(): Observable<SiteListResponse> {
    return this.apiService.get<SiteListResponse>('/sites');
  }

  getSite(id: number): Observable<SiteResponse> {
    return this.apiService.get<SiteResponse>(`/sites/${id}`);
  }

  createSite(data: CreateSiteRequest): Observable<SiteResponse> {
    return this.apiService.post<SiteResponse>('/sites', data);
  }

  getSiteAppliances(siteId: number): Observable<SiteApplianceListResponse> {
    return this.apiService.get<SiteApplianceListResponse>(`/sites/${siteId}/appliances`);
  }

  addApplianceToSite(siteId: number, data: AddApplianceToSiteRequest): Observable<{ success: boolean; message: string }> {
    return this.apiService.post<{ success: boolean; message: string }>(`/sites/${siteId}/appliances`, data);
  }

  removeApplianceFromSite(siteId: number, siteApplianceId: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/sites/${siteId}/appliances/${siteApplianceId}`);
  }
}
