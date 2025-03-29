<template>
  <div>
    <div class="flex items-center mb-6">
      <a
        v-on:click="$router.go(-1)"
        class="mr-4 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500"
      >
        <svg
          class="-ml-1 mr-1 h-5 w-5 text-indigo-500"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
            clip-rule="evenodd"
          />
        </svg>
        Voltar
      </a>
      <h1 class="text-2xl font-semibold text-gray-900">Detalhes da Solicitação</h1>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-12">
      <svg class="animate-spin h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>

    <div v-else-if="!request" class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Solicitação não encontrada</h3>
      <p class="mt-1 text-sm text-gray-500">
        A solicitação que você está procurando não existe ou você não tem permissão para acessá-la.
      </p>
      <div class="mt-6">
        <router-link
          :to="{ name: 'dashboard' }"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Voltar para o Dashboard
        </router-link>
      </div>
    </div>

    <div v-else>
      <!-- Status Badge -->
      <div class="mb-6 flex justify-between items-center">
        <div
          :class="[
            'px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full',
            request.status === 'solicitado' ? 'text-yellow-800 bg-yellow-100' : '',
            request.status === 'aprovado' ? 'text-green-800 bg-green-100' : '',
            request.status === 'cancelado' ? 'text-red-800 bg-red-100' : ''
          ]"
        >
          {{ getStatusLabel(request.status) }}
        </div>

        <!-- Action Buttons -->
        <div v-if="showActionButtons">
          <button
            v-if="canCancel"
            @click="confirmCancelRequest"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            :disabled="actionLoading"
          >
            <svg v-if="actionLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Cancelar Solicitação
          </button>

          <div v-if="isApprover && request.status === 'solicitado'" class="inline-flex rounded-md shadow-sm">
            <button
              @click="updateRequestStatus('aprovado')"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-l-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              :disabled="actionLoading"
            >
              <svg v-if="actionLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Aprovar
            </button>
            <button
              @click="updateRequestStatus('cancelado')"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              :disabled="actionLoading"
            >
              <svg v-if="actionLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Rejeitar
            </button>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            Informações da Solicitação
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Detalhes completos da solicitação de viagem.
          </p>
        </div>
        <div class="border-t border-gray-200">
          <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                ID da Solicitação
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ request.id }}
              </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Solicitante
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ request.user_name }}
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Destino
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ request.destination }}
              </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Período
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ formatDate(request.departure_date) }} até {{ formatDate(request.return_date) }}
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Propósito
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ request.purpose || 'Não informado' }}
              </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Orçamento Estimado
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ request.estimated_budget ? formatCurrency(request.estimated_budget) : 'Não informado' }}
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Observações
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ request.observations || 'Nenhuma observação adicional' }}
              </dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Data de Criação
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ formatDateTime(request.created_at) }}
              </dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">
                Última Atualização
              </dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                {{ formatDateTime(request.updated_at) }}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Confirmation Dialog -->
      <div v-if="showConfirmDialog" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
              <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-5">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Cancelar Solicitação
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Tem certeza que deseja cancelar esta solicitação de viagem? Esta ação não pode ser desfeita.
                  </p>
                </div>
              </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
              <button
                type="button"
                @click="cancelRequest"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm"
                :disabled="actionLoading"
              >
                <svg v-if="actionLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Confirmar
              </button>
              <button
                type="button"
                @click="showConfirmDialog = false"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '@/stores/auth';
import type { TravelRequest } from '@/types';
import travelService from '@/services/travelService';
import { getStatusLabel } from '../../utils/utility_functions.ts'

const route = useRoute();
// const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();

const loading = ref<boolean>(true);
const actionLoading = ref<boolean>(false);
const showConfirmDialog = ref<boolean>(false);
const request = ref<TravelRequest | null>(null);

// Verificar se o usuário é aprovador
const isApprover = computed<boolean>(() => authStore.isApprover);

// Verificar se podem ser mostrados botões de ação
const showActionButtons = computed<boolean>(() => {
  return (canCancel.value || isApprover.value);
});

// Verificar se o usuário pode cancelar a solicitação
const canCancel = computed<boolean>(() => {
  if (!request.value) return false;

  // Somente o próprio usuário pode cancelar sua solicitação no status "solicitado"
  if (request.value.user_id === authStore.user?.id && request.value.status === 'solicitado') {
    return true;
  }

  return false;
});

// Buscar detalhes da solicitação
const fetchRequestDetails = async () => {
  loading.value = true;

  try {
    const id = Number(route.params.id);
    if (isNaN(id)) {
      throw new Error('ID inválido');
    }

    const data = await travelService.getRequestById(id);
    request.value = data;
  } catch (error) {
    console.error('Error fetching request details:', error);
    toast.error('Erro ao buscar detalhes da solicitação');
    request.value = null;
  } finally {
    loading.value = false;
  }
};

// Formatar data para exibição
const formatDate = (dateString?: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR');
};

// Formatar data e hora para exibição
const formatDateTime = (dateString?: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('pt-BR');
};

// Formatar valor monetário
const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
};

// Mostrar diálogo de confirmação para cancelar solicitação
const confirmCancelRequest = () => {
  showConfirmDialog.value = true;
};

// Cancelar solicitação
const cancelRequest = async () => {
  if (!request.value) return;

  actionLoading.value = true;

  try {
    await travelService.cancelRequest(request.value.id);
    toast.success('Solicitação cancelada com sucesso');

    // Atualizar o status da solicitação
    request.value.status = 'cancelado';
    showConfirmDialog.value = false;
  } catch (error) {
    console.error('Error canceling request:', error);
    toast.error('Erro ao cancelar solicitação');
  } finally {
    actionLoading.value = false;
  }
};

// Atualizar status da solicitação (para aprovadores)
const updateRequestStatus = async (status: 'aprovado' | 'cancelado') => {
  if (!request.value || !isApprover.value) return;

  actionLoading.value = true;

  try {
    await travelService.updateStatus(request.value.id, status);

    const statusText = status === 'aprovado' ? 'aprovada' : 'rejeitada';
    toast.success(`Solicitação ${statusText} com sucesso`);

    // Atualizar o status da solicitação
    request.value.status = status;
  } catch (error) {
    console.error(`Error updating request status to ${status}:`, error);
    toast.error(`Erro ao ${status === 'aprovado' ? 'aprovar' : 'rejeitar'} solicitação`);
  } finally {
    actionLoading.value = false;
  }
};

onMounted(() => {
  fetchRequestDetails();
});
</script>
