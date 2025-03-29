import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useToast } from 'vue-toastification';
import axios from '@/services/axios';
import type { User, LoginCredentials, AuthResponse } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  const toast = useToast();
  const access_token = ref<string | null>(localStorage.getItem('token') || null);
  const user = ref<User | null>(JSON.parse(localStorage.getItem('user') || 'null'));
  const loading = ref<boolean>(false);
  const error = ref<string | null>(null);

  const isAuthenticated = computed<boolean>(() => !!access_token.value);
  const isApprover = computed<boolean>(() => user.value?.role === 'approver');
  const isUser = computed<boolean>(() => user.value?.role === 'user');

  async function login(credentials: LoginCredentials): Promise<boolean> {
    try {
      loading.value = true;
      error.value = null;

      console.log('Tentando login com:', credentials.email);
      const response = await axios.post<AuthResponse>('/api/login', credentials);

      const { token, user: userData } = response.data;
      console.log('Login bem-sucedido, token recebido:', token);

      // Salvar token e usuário
      access_token.value = token;
      user.value = userData;

      // Armazenar no localStorage
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(userData));

      // Configurar axios para incluir o token em todas as requisições
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      return true;
    } catch (err: unknown) {
      console.error('Erro no login:', err);
      error.value = 'Falha na autenticação. Verifique suas credenciais.';
      toast.error("Falha na autenticação. Verifique suas credenciais.");
      return false;
    } finally {
      loading.value = false;
    }
  }

  function logout(): void {
    console.log('Realizando logout');

    // Remover token do estado e do localStorage
    access_token.value = null;
    user.value = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');

    // Remover header de autorização
    delete axios.defaults.headers.common['Authorization'];

    toast.info('Você foi desconectado');

    // Usar window.location para garantir um redirecionamento completo
    window.location.href = '/auth/login';
  }

  // Verificar se o token ainda é válido ao inicializar
  function checkAuth(): Promise<boolean> {
    return new Promise((resolve) => {
      if (!access_token.value) {
        resolve(false);
        return;
      }

      // Garantir que o token esteja definido no cabeçalho antes da requisição
      const currentToken = access_token.value;
      axios.defaults.headers.common['Authorization'] = `Bearer ${currentToken}`;

      console.log('Verificando autenticação com token:', currentToken);

      // Tente obter o perfil do usuário para verificar se o token é válido
      axios.get('/api/user', {
        headers: {
          'Authorization': `Bearer ${currentToken}`
        }
      })
        .then((response) => {
          // Atualizar dados do usuário se necessário
          if (response.data && response.data.id) {
            user.value = response.data;
            localStorage.setItem('user', JSON.stringify(response.data));
            resolve(true);
          } else {
            // Se não retornou dados do usuário, considere inválido
            logout();
            resolve(false);
          }
        })
        .catch((error) => {
          // Token inválido ou expirado
          console.error('Erro ao verificar token:', error);
          logout();
          resolve(false);
        });
    });
  }

  return {
    token: access_token,
    user,
    loading,
    error,
    isAuthenticated,
    isApprover,
    isUser,
    login,
    logout,
    checkAuth
  };
});
