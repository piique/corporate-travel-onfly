// User types
export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  created_at?: string;
  updated_at?: string;
}

// Auth types
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthResponse {
  token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

// Travel Request types
export interface TravelRequest {
  id: number;
  user_id: number;
  requester_name: string;
  user_name: string;
  destination: string;
  departure_date: string;
  return_date: string;
  status: 'requested' | 'approved' | 'canceled';
  purpose?: string;
  estimated_budget?: number;
  observations?: string;
  created_at?: string;
  updated_at?: string;
  user?: User;
}

// Dashboard stats
export interface DashboardStats {
  pending: number;
  approved: number;
  canceled: number;
  total: number;
}

// API error response
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
  error?: string;
}

/**
 * Interface para uma solicitação de viagem
 */
export interface TravelRequest {
  id: number;
  user_id: number;
  user_name: string;
  destination: string;
  departure_date: string;
  return_date: string;
  status: 'requested' | 'approved' | 'canceled';
  created_at: string;
  updated_at: string;
  purpose?: string;
  estimated_budget?: number | null;
  observations?: string;
}

/**
 * Interface para os filtros de solicitação de viagem
 */
export interface TravelRequestFilters {
  status?: string;
  destination?: string;
  start_date?: string;
  end_date?: string;
  page?: number;
}

/**
 * Interface para a paginação
 */
export interface PaginationData {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from: number;
  to: number;
  path: string;
  first_page_url: string;
  last_page_url: string;
  next_page_url: string | null;
  prev_page_url: string | null;
}

/**
 * Interface para a resposta da API com paginação
 */
export interface PaginatedApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  pagination: PaginationData;
}
