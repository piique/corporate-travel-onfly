import RequestDetails from '@/views/travel/RequestDetails.vue';
import NotFound from '@/views/NotFound.vue';
import DashboardView from '@/views/DashboardView.vue'
import DefaultLayout from '@/layout/DefaultLayout.vue'
import {
  createRouter,
  createWebHistory, type NavigationGuardNext,
  type RouteLocationNormalized,
  type RouteRecordRaw
} from 'vue-router'
import TravelRequests from '@/views/travel/TravelRequests.vue'
import CreateRequests from '@/views/travel/CreateRequests.vue'
import AuthLayout from '@/layout/AuthLayout.vue'
import Login from '@/views/auth/Login.vue'
import { useAuthStore } from '@/stores/auth';

interface RouteMeta {
  requiresAuth?: boolean;
  title?: string;
  requiresUserRole?: boolean;
}

type AppRouteRecord = RouteRecordRaw & {
  meta?: RouteMeta;
  children?: AppRouteRecord[];
};

const routes: AppRouteRecord[] = [
  {
    path: '/',
    component: DefaultLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView,
        meta: { title: 'Dashboard' }
      },
      {
        path: 'travel-requests',
        name: 'travel-requests',
        component: TravelRequests,
        meta: { title: 'Solicitações de Viagem' }
      },
      {
        path: 'travel-requests/create',
        name: 'create-request',
        component: CreateRequests,
        meta: {
          title: 'Nova Solicitação',
          requiresUserRole: true
        }
      },
      {
        path: 'travel-requests/:id',
        name: 'request-details',
        component: RequestDetails,
        meta: { title: 'Detalhes da Solicitação' },
        props: true
      }
    ]
  },
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: Login,
        meta: { title: 'Login' }
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: { title: '404 - Página não encontrada' }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Função para verificar se o usuário está autenticado
const checkAuthStatus = (): boolean => {
  const token = localStorage.getItem('token');
  return !!token;
};

// Proteção de rotas
router.beforeEach((
  to: RouteLocationNormalized,
  from: RouteLocationNormalized,
  next: NavigationGuardNext
) => {
  // Atualiza o título da página
  document.title = to.meta.title ? `${to.meta.title} - Viagens Corporativas` : 'Viagens Corporativas';

  const requiresAuth = to.matched.some(record => record.meta?.requiresAuth);
  const requiresUserRole = to.matched.some(record => record.meta?.requiresUserRole);
  const isAuthenticated = checkAuthStatus();

  console.log(`[Router] Navegando para ${to.path}, requer autenticação: ${requiresAuth}, isAuthenticated: ${isAuthenticated}`);

  if (requiresAuth && !isAuthenticated) {
    console.log('[Router] Redirecionando para login por falta de autenticação');
    next({ name: 'login', query: { redirect: to.fullPath } });
  } else if (to.path === '/auth/login' && isAuthenticated) {
    console.log('[Router] Usuário já está autenticado, redirecionando para dashboard');
    next({ name: 'dashboard' });
  } else if (requiresUserRole) {
    // Verificar se o usuário tem a role 'user'
    const authStore = useAuthStore();
    if (!authStore.isUser) {
      console.log('[Router] Acesso negado: usuário não tem a role necessária');
      next({ name: 'dashboard' });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
