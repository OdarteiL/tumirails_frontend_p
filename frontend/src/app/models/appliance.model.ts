export interface Appliance {
  id: number;
  name: string;
  category_id: number;
  category?: Category;
  wattage: number;
  is_public: boolean;
  is_active: boolean;
  owner_type: 'App\\Models\\User' | 'App\\Models\\Organisation';
  owner_id: number;
  metadata?: {
    efficiency_rating?: string;
    brand?: string;
    model?: string;
    notes?: string;
  };
  created_at: string;
  updated_at: string;
}

export interface Category {
  id: number;
  name: string;
  power_factor: number;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface CreateApplianceRequest {
  name: string;
  category_id: number;
  wattage: number;
  is_public?: boolean;
  metadata?: {
    efficiency_rating?: string;
    brand?: string;
    model?: string;
    notes?: string;
  };
}

export interface UpdateApplianceRequest {
  name?: string;
  category_id?: number;
  wattage?: number;
  is_active?: boolean;
  metadata?: {
    efficiency_rating?: string;
    brand?: string;
    model?: string;
    notes?: string;
  };
}

export interface ApplianceListResponse {
  success: boolean;
  data: Appliance[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ApplianceResponse {
  success: boolean;
  data: Appliance;
}
