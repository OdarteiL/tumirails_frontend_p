export interface Organisation {
  id: number;
  name: string;
  type: 'customer' | 'installer' | 'provider';
  owner_id: number;
  created_at: string;
  updated_at: string;
  installer_detail?: OrganisationInstallerDetail;
  provider_detail?: OrganisationProviderDetail;
}

export interface OrganisationInstallerDetail {
  id: number;
  organisation_id: number;
  certifications?: string[];
  service_areas?: string[];
  years_experience?: number;
  rating?: number;
}

export interface OrganisationProviderDetail {
  id: number;
  organisation_id: number;
  certifications?: string[];
  service_areas?: string[];
  rating?: number;
  verified: boolean;
}

export interface OrganisationMember {
  id: number;
  organisation_id: number;
  user_id: number;
  role: 'owner' | 'admin' | 'member';
  user?: {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
  };
  created_at: string;
  updated_at: string;
}

export interface OrganisationInvitation {
  id: number;
  organisation_id: number;
  email: string;
  role: 'admin' | 'member';
  token: string;
  invited_by: number;
  accepted_at?: string;
  rejected_at?: string;
  expires_at: string;
  created_at: string;
  updated_at: string;
}

export interface CreateOrganisationRequest {
  name: string;
  type: 'customer' | 'installer' | 'provider';
  transfer_sites?: boolean;
  installer_detail?: {
    certifications?: string[];
    service_areas?: string[];
    years_experience?: number;
  };
  provider_detail?: {
    certifications?: string[];
    service_areas?: string[];
  };
}

export interface UpdateOrganisationRequest {
  name?: string;
}

export interface InviteMemberRequest {
  email: string;
  role: 'admin' | 'member';
}

export interface UpdateMemberRequest {
  role: 'admin' | 'member';
}

export interface AcceptInvitationRequest {
  token: string;
  email: string;
}

export interface RejectInvitationRequest {
  token: string;
  email: string;
}

export interface OrganisationListResponse {
  success: boolean;
  data: Organisation[];
}

export interface OrganisationResponse {
  success: boolean;
  data: Organisation;
}

export interface OrganisationMembersResponse {
  success: boolean;
  data: OrganisationMember[];
}
