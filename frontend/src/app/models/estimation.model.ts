export interface GuestApplianceRequest {
    name: string;
    wattage: number;
    quantity: number;
    daily_usage_hours: number;
}

export interface GuestEstimationRequest {
    appliances: GuestApplianceRequest[];
}

export interface GuestEstimation {
    total_watts: string;
    daily_kwh: string;
    monthly_kwh: string;
    estimated_monthly_cost: string;
    estimated_daily_cost: string;
    ref_code?: string;
    power_factor_applied: string | null;
    seasonal_multiplier: string | null;
    appliances_breakdown: Array<{
        id?: number;
        name: string;
        category?: string;
        watts: number;
        quantity: number;
        daily_usage_hours: number;
        power_factor?: number;
        daily_kwh: number;
        monthly_cost: number;
    }>;
    calculation_metadata?: {
        tariff_structure_id: number;
        tariff_structure_name: string;
        tariff_type: string;
        seasonal_adjustment_id: number | null;
        seasonal_adjustment_name: string | null;
        location_multiplier_id: number | null;
        location_region: string | null;
        location_city: string | null;
        calculated_at: string;
        appliance_count: number;
    };
}

export interface GuestEstimationResponse {
    success: boolean;
    message: string;
    data: GuestEstimation;
}
