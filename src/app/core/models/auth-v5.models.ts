// Authentication V5 Models
// Complete TypeScript interfaces for V5 API authentication

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
}

export interface ResetPasswordRequest {
  email: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export interface School {
  id: number;
  name: string;
  slug: string;
  description?: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
  seasons: Season[];
}

export interface Season {
  id: number;
  school_id: number;
  name: string;
  slug: string;
  start_date: string;
  end_date: string;
  status: 'active' | 'inactive' | 'upcoming' | 'completed';
  is_current: boolean;
  created_at: string;
  updated_at: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data?: {
    token: string;
    user: User;
    schools: School[];
  };
}

export interface RegisterResponse {
  success: boolean;
  message: string;
  data?: {
    user: User;
    token?: string; // Optional, some APIs auto-login after register
  };
}

export interface MeResponse {
  success: boolean;
  message: string;
  data?: {
    user: User;
    schools: School[];
    permissions: string[];
  };
}

export interface AuthContext {
  school_id: number;
  season_id: number;
  permissions: string[];
}

export interface ApiResponse<T = unknown> {
  success: boolean;
  message: string;
  data?: T;
  meta?: {
    pagination?: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
    [key: string]: unknown;
  };
}

// RFC 7807 Problem Details for HTTP APIs
export interface ProblemDetails {
  type?: string;
  title: string;
  status: number;
  detail?: string;
  instance?: string;
  code?: string;
  errors?: { [key: string]: string[] };
  userMessage?: string;
  timestamp?: string;
  request_id?: string;
}