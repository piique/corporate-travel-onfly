import { useToast } from 'vue-toastification';
import type { ApiError } from '@/types';
import axios, {
  type AxiosError,
  type AxiosInstance,
  type AxiosResponse,
  type InternalAxiosRequestConfig
} from 'axios'

// Criar instância do axios com configuração base
const instance: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});


// Interceptor para requisições
instance.interceptors.request.use(
  (config: InternalAxiosRequestConfig): InternalAxiosRequestConfig => {
    // Obter token do localStorage em cada requisição
    const token = localStorage.getItem('token');

    // Adicionar token ao header Authorization se existir
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      console.log('Token adicionado ao header:', `Bearer ${token}`);
    } else {
      console.warn('Token não encontrado no localStorage');
    }

    return config;
  },
  (error: AxiosError): Promise<AxiosError> => {
    console.error('Erro na requisição:', error);
    return Promise.reject(error);
  }
);

// Interceptors de resposta para tratamento global de erros
instance.interceptors.response.use(
  (response: AxiosResponse): AxiosResponse => {
    return response;
  },
  (error: AxiosError<ApiError>): Promise<never> => {
    const toast = useToast();
    const statusCode = error.response ? error.response.status : null;

    console.error('Erro na resposta:', error);
    console.error('Status code:', statusCode);
    console.error('Dados de erro:', error.response?.data);

    if (statusCode === 401) {
      // Token inválido ou expirado
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/auth/login';
      toast.error('Sessão expirada. Por favor, faça login novamente.');
    } else if (statusCode === 403) {
      toast.error('Você não tem permissão para acessar este recurso.');
    } else if (statusCode === 404) {
      toast.error('Recurso não encontrado.');
    } else if (statusCode === 422) {
      // // Erros de validação
      // const errors = error.response?.data?.errors;
      // if (errors) {
      //   Object.values(errors).forEach((messages: string[]) => {
      //     messages.forEach((message: string) => toast.error(message));
      //   });
      // } else {
      //   toast.error('Dados inválidos. Verifique os campos e tente novamente.');
      // }
    } else if (statusCode && statusCode >= 500) {
      toast.error('Erro no servidor. Por favor, tente novamente mais tarde.');
    } else {
      toast.error('Ocorreu um erro. Por favor, tente novamente.');
    }

    return Promise.reject(error);
  }
);

export default instance;
