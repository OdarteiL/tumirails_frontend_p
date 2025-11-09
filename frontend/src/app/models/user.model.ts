export interface User {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  role: 'customer' | 'installer' | 'provider' | 'admin' | 'verifier';
  status: 'active' | 'inactive' | 'suspended';
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: User;
  access_token: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  first_name: string;
  last_name: string;
  email: string;
  password: string;
  phone?: string;
  role?: string;
}