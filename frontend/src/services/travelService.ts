import axios from '@/services/axios';
import type { TravelRequest, TravelRequestFilters, PaginationData, PaginatedApiResponse } from '@/types';

// Interface para a resposta padronizada da API
interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

/**
 * Interface estendida para incluir campos adicionais no formulário de criação
 */
interface TravelRequestFormData {
  requester_name: string;
  destination: string;
  departure_date: string;
  return_date: string;
  purpose?: string;
  estimated_budget?: number | null;
  observations?: string;
}

/**
 * Serviço para manipulação de solicitações de viagem
 */
export default {
  /**
   * Busca todas as solicitações de viagem com filtros opcionais (sem paginação)
   * @deprecated Use getAllRequestsWithPagination instead
   */
  async getAllRequests(filters: TravelRequestFilters = {}): Promise<TravelRequest[]> {
    // Remover parâmetros vazios para evitar envio de valores nulos
    const cleanedFilters: Record<string, string> = {};

    Object.entries(filters).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        cleanedFilters[key] = value;
      }
    });

    const response = await axios.get<ApiResponse<TravelRequest[]>>('/api/travel-requests', {
      params: cleanedFilters
    });

    return response.data.data;
  },

  /**
   * Busca todas as solicitações de viagem com paginação e filtros opcionais
   */
  async getAllRequestsWithPagination(filters: TravelRequestFilters = {}): Promise<{
    data: TravelRequest[];
    pagination: PaginationData;
  }> {
    // Remover parâmetros vazios para evitar envio de valores nulos
    const cleanedFilters: Record<string, string | number> = {};

    Object.entries(filters).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        cleanedFilters[key] = value;
      }
    });

    const response = await axios.get<PaginatedApiResponse<TravelRequest[]>>('/api/travel-requests', {
      params: cleanedFilters
    });

    return {
      data: response.data.data,
      pagination: response.data.pagination
    };
  },

  /**
   * Busca uma solicitação de viagem pelo ID
   */
  async getRequestById(id: number): Promise<TravelRequest> {
    const response = await axios.get<ApiResponse<TravelRequest>>(`/api/travel-requests/${id}`);
    return response.data.data;
  },

  /**
   * Cria uma nova solicitação de viagem
   */
  async createRequest(data: TravelRequestFormData): Promise<TravelRequest> {
    const response = await axios.post<ApiResponse<TravelRequest>>('/api/travel-requests', data);
    return response.data.data;
  },

  /**
   * Atualiza o status de uma solicitação de viagem
   */
  async updateStatus(id: number, status: 'approved' | 'canceled'): Promise<TravelRequest> {
    const response = await axios.patch<ApiResponse<TravelRequest>>(`/api/travel-requests/${id}/status`, { status });
    return response.data.data;
  },

  /**
   * Cancela uma solicitação de viagem
   */
  async cancelRequest(id: number): Promise<TravelRequest> {
    const response = await axios.delete<ApiResponse<TravelRequest>>(`/api/travel-requests/${id}`);
    return response.data.data;
  },

  /**
   * Busca as estatísticas de solicitações para o dashboard
   */
  async getStats(): Promise<{ pending: number; approved: number; canceled: number; }> {
    const response = await axios.get<ApiResponse<{ pending: number; approved: number; canceled: number; }>>('/api/travel-requests/stats');
    return response.data.data;
  }
};
