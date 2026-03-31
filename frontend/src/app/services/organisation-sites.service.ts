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
export class OrganisationSitesService {
  constructor(private apiService: ApiService) {}

  getOrganisationSites(organisationId: number): Observable<SiteListResponse> {
    return this.apiService.get<SiteListResponse>(`/organisations/${organisationId}/sites`);
  }

  getOrganisationSite(organisationId: number, siteId: number): Observable<SiteResponse> {
    return this.apiService.get<SiteResponse>(`/organisations/${organisationId}/sites/${siteId}`);
  }

  createOrganisationSite(organisationId: number, data: CreateSiteRequest): Observable<SiteResponse> {
    return this.apiService.post<SiteResponse>(`/organisations/${organisationId}/sites`, data);
  }

  getOrganisationSiteAppliances(organisationId: number, siteId: number): Observable<SiteApplianceListResponse> {
    return this.apiService.get<SiteApplianceListResponse>(`/organisations/${organisationId}/sites/${siteId}/appliances`);
  }

  addApplianceToOrganisationSite(organisationId: number, siteId: number, data: AddApplianceToSiteRequest): Observable<{ success: boolean; message: string }> {
    return this.apiService.post<{ success: boolean; message: string }>(`/organisations/${organisationId}/sites/${siteId}/appliances`, data);
  }

  removeApplianceFromOrganisationSite(organisationId: number, siteId: number, siteApplianceId: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/organisations/${organisationId}/sites/${siteId}/appliances/${siteApplianceId}`);
  }
}
