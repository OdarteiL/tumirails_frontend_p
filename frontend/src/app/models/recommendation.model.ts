export interface RecommendationBundle {
  id: number;
  estimation_id: number;
  total_cost: string;
  currency: string;
  components: RecommendationBundleComponent[];
  created_at: string;
  updated_at: string;
}

export interface RecommendationBundleComponent {
  id: number;
  bundle_id: number;
  hardware_id: number;
  hardware?: Hardware;
  quantity: number;
  unit_price: string;
  total_price: string;
  rationale?: string;
}

export interface Hardware {
  id: number;
  hardware_type_id: number;
  hardware_type?: HardwareType;
  name: string;
  brand?: string;
  model?: string;
  specifications: Record<string, unknown>;
  price: string;
  formatted_price?: string;
  currency: string;
  owner_type: 'App\\Models\\User' | 'App\\Models\\Organisation';
  owner_id: number;
  is_available: boolean;
  verified: boolean;
  created_at: string;
  updated_at: string;
}

export interface HardwareType {
  id: number;
  name: string;
  category: string;
  created_at: string;
  updated_at: string;
}

export interface Recommendation {
  bundle_id?: number;
  total_cost: string;
  currency: string;
  components: {
    hardware_id: number;
    hardware_type: string;
    name: string;
    brand?: string;
    model?: string;
    specifications: Record<string, unknown>;
    quantity: number;
    unit_price: string;
    total_price: string;
    rationale?: string;
    provider?: {
      id: number;
      name: string;
      rating?: number;
      verified: boolean;
    };
  }[];
}

export interface SaveRecommendationRequest {
  bundle: {
    components: {
      hardware_id: number;
      quantity: number;
      unit_price: string;
      rationale?: string;
    }[];
  };
}

export interface RecommendationsResponse {
  success: boolean;
  data: Recommendation[];
}

export interface RecommendationBundlesResponse {
  success: boolean;
  data: RecommendationBundle[];
}

export interface SaveRecommendationResponse {
  success: boolean;
  data: RecommendationBundle;
}
