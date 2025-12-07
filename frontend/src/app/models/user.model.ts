export interface User {
  id: number;
  first_name: string;
  last_name: string;
  other_names?: string;
  email: string;
  phone?: string;
  address?: string;
  role: 'customer' | 'installer' | 'provider' | 'admin' | 'verifier';
  status: 'active' | 'inactive' | 'suspended';
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    access_token: string;
  };
}

export interface ErrorResponse {
  success: boolean;
  message: string;
  errors?: Record<string, string[]>;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  first_name: string;
  last_name: string;
  other_names?: string;
  email: string;
  password: string;
  password_confirmation: string;
  phone?: string;
  address?: string;
  role?: string;
}