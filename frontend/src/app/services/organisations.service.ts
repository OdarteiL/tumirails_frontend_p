import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import {
  OrganisationListResponse,
  OrganisationResponse,
  OrganisationMembersResponse,
  CreateOrganisationRequest,
  UpdateOrganisationRequest,
  InviteMemberRequest,
  UpdateMemberRequest,
  AcceptInvitationRequest,
  RejectInvitationRequest
} from '../models/organisation.model';

@Injectable({
  providedIn: 'root'
})
export class OrganisationsService {
  constructor(private apiService: ApiService) {}

  getOrganisations(): Observable<OrganisationListResponse> {
    return this.apiService.get<OrganisationListResponse>('/organisations');
  }

  getOrganisation(id: number): Observable<OrganisationResponse> {
    return this.apiService.get<OrganisationResponse>(`/organisations/${id}`);
  }

  createOrganisation(data: CreateOrganisationRequest): Observable<OrganisationResponse> {
    return this.apiService.post<OrganisationResponse>('/organisations', data);
  }

  updateOrganisation(id: number, data: UpdateOrganisationRequest): Observable<OrganisationResponse> {
    return this.apiService.put<OrganisationResponse>(`/organisations/${id}`, data);
  }

  deleteOrganisation(id: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/organisations/${id}`);
  }

  getMembers(organisationId: number): Observable<OrganisationMembersResponse> {
    return this.apiService.get<OrganisationMembersResponse>(`/organisations/${organisationId}/members`);
  }

  inviteMember(organisationId: number, data: InviteMemberRequest): Observable<{ success: boolean; message: string }> {
    return this.apiService.post<{ success: boolean; message: string }>(`/organisations/${organisationId}/invite`, data);
  }

  updateMember(organisationId: number, memberId: number, data: UpdateMemberRequest): Observable<{ success: boolean; message: string }> {
    return this.apiService.put<{ success: boolean; message: string }>(`/organisations/${organisationId}/members/${memberId}`, data);
  }

  removeMember(organisationId: number, memberId: number): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ success: boolean; message: string }>(`/organisations/${organisationId}/members/${memberId}`);
  }

  acceptInvitation(data: AcceptInvitationRequest): Observable<{ success: boolean; message: string }> {
    return this.apiService.post<{ success: boolean; message: string }>('/invitations/accept', data);
  }

  rejectInvitation(data: RejectInvitationRequest): Observable<{ success: boolean; message: string }> {
    return this.apiService.post<{ success: boolean; message: string }>('/invitations/reject', data);
  }
}
