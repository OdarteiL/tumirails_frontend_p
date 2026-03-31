export interface Site {
  id: number;
  name: string;
  address: string;
  latitude: number;
  longitude: number;
  owner_type: 'App\\Models\\User' | 'App\\Models\\Organisation';
  owner_id: number;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface SiteAppliance {
  id: number;
  site_id: number;
  appliance_id: number;
  appliance?: {
    id: number;
    name: string;
    wattage: number;
    category?: {
      id: number;
      name: string;
      power_factor: number;
    };
  };
  quantity: number;
  daily_usage_hours: number;
  notes?: string;
  added_by: number;
  created_at: string;
  updated_at: string;
}

export interface CreateSiteRequest {
  name: string;
  address: string;
  latitude: number;
  longitude: number;
  notes?: string;
}

export interface AddApplianceToSiteRequest {
  appliance_id: number;
  quantity: number;
  daily_usage_hours: number;
  notes?: string;
}

export interface SiteListResponse {
  success: boolean;
  data: Site[];
}

export interface SiteResponse {
  success: boolean;
  data: Site;
}

export interface SiteApplianceListResponse {
  success: boolean;
  data: SiteAppliance[];
}
