<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
      <router-link
        v-if="authStore.isUser"
        :to="{ name: 'create-request' }"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <svg
          class="-ml-1 mr-2 h-5 w-5"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
            clip-rule="evenodd"
          />
        </svg>
        Nova Solicitação
      </router-link>
    </div>

    <!-- Resumo em cards -->
    <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Card - Solicitações Pendentes -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
              <svg
                class="h-6 w-6 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                <dd>
                  <div class="text-lg font-medium text-gray-900">{{ stats.pending }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Card - Solicitações Aprovadas -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
              <svg
                class="h-6 w-6 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Aprovadas</dt>
                <dd>
                  <div class="text-lg font-medium text-gray-900">{{ stats.approved }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Card - Solicitações Canceladas -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
              <svg
                class="h-6 w-6 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Canceladas</dt>
                <dd>
                  <div class="text-lg font-medium text-gray-900">{{ stats.canceled }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros simples para a lista -->
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
      <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
        <div class="sm:col-span-2">
          <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <div class="relative rounded-md shadow-sm border border-gray-300">
            <select
              id="status-filter"
              v-model="filters.status"
              class="appearance-none block w-full pl-3 pr-10 py-2 text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            >
              <option value="">Todos</option>
              <option value="requested">Pendentes</option>
              <option value="approved">Aprovados</option>
              <option value="canceled">Cancelados</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label for="destination-filter" class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
          <div class="relative rounded-md shadow-sm">
            <input
              type="text"
              id="destination-filter"
              v-model="filters.destination"
              class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Filtrar por destino"
            />
          </div>
        </div>

        <div class="sm:col-span-2">
          <div class="flex justify-end h-full items-end">
            <button
              type="button"
              @click="applyFilters"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
              </svg>
              Filtrar
            </button>
            <button
              type="button"
              @click="resetFilters"
              class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
              </svg>
              Limpar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela de solicitações -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="loading" class="flex justify-center items-center py-12">
        <svg
          class="animate-spin h-10 w-10 text-indigo-500"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
      </div>

      <div v-else-if="!requests.length" class="text-center py-12 px-4">
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma solicitação encontrada</h3>
        <p v-if="authStore.isUser" class="mt-1 text-sm text-gray-500">Comece criando uma nova solicitação de viagem.</p>
        <div v-if="authStore.isUser" class="mt-6">
          <router-link
            :to="{ name: 'create-request' }"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg
              class="-ml-1 mr-2 h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                clip-rule="evenodd"
              />
            </svg>
            Nova Solicitação
          </router-link>
        </div>
      </div>

      <div v-else>
        <ul class="divide-y divide-gray-200">
          <li v-for="request in requests" :key="request.id">
            <router-link
              :to="{ name: 'request-details', params: { id: request.id } }"
              class="block hover:bg-gray-50"
            >
              <div class="px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-indigo-600 truncate">
                      {{ request.destination }}
                    </p>
                    <div
                      :class="[
                        'ml-2 flex-shrink-0 flex',
                        request.status === 'requested' ? 'text-yellow-700 bg-yellow-100' : '',
                        request.status === 'approved' ? 'text-green-700 bg-green-100' : '',
                        request.status === 'canceled' ? 'text-red-700 bg-red-100' : '',
                      ]"
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    >
                      {{ getStatusLabel(request.status) }}
                    </div>
                  </div>
                  <div class="ml-2 flex-shrink-0 flex">
                    <svg
                      class="mr-1.5 h-5 w-5 text-gray-400"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                </div>
                <div class="mt-2 sm:flex sm:justify-between">
                  <div class="sm:flex">
                    <p class="flex items-center text-sm text-gray-500">
                      <svg
                        class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                          clip-rule="evenodd"
                        />
                      </svg>
                      {{ formatDate(request.departure_date) }} -
                      {{ formatDate(request.return_date) }}
                    </p>
                  </div>
                  <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                    <svg
                      class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400"
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Solicitante: {{ request.user_name }}
                  </div>
                </div>
              </div>
            </router-link>
          </li>
        </ul>

        <!-- Paginação -->
        <div v-if="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando <span class="font-medium">{{ pagination.from }}</span> a
                <span class="font-medium">{{ pagination.to }}</span> de
                <span class="font-medium">{{ pagination.total }}</span> resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Anterior -->
                <button
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="!pagination.prev_page_url"
                  :class="[
                    'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium',
                    pagination.prev_page_url
                      ? 'text-gray-500 hover:bg-gray-50 cursor-pointer'
                      : 'text-gray-300 cursor-not-allowed'
                  ]"
                >
                  <span class="sr-only">Anterior</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>

                <!-- Páginas -->
                <template v-for="page in pagesArray" :key="page">
                  <button
                    v-if="page !== '...'"
                    @click="goToPage(page)"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      page === pagination.current_page
                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <span
                    v-else
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                  >
                    ...
                  </span>
                </template>

                <!-- Próxima -->
                <button
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="!pagination.next_page_url"
                  :class="[
                    'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium',
                    pagination.next_page_url
                      ? 'text-gray-500 hover:bg-gray-50 cursor-pointer'
                      : 'text-gray-300 cursor-not-allowed'
                  ]"
                >
                  <span class="sr-only">Próxima</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>

          <!-- Versão mobile da paginação -->
          <div class="flex justify-between items-center w-full sm:hidden">
            <button
              @click="goToPage(pagination.current_page - 1)"
              :disabled="!pagination.prev_page_url"
              :class="[
                'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md',
                pagination.prev_page_url
                  ? 'bg-white text-gray-700 hover:bg-gray-50'
                  : 'bg-gray-100 text-gray-400 cursor-not-allowed'
              ]"
            >
              Anterior
            </button>
            <div class="text-sm text-gray-700">
              <span>{{ pagination.current_page }}</span> de <span>{{ pagination.last_page }}</span>
            </div>
            <button
              @click="goToPage(pagination.current_page + 1)"
              :disabled="!pagination.next_page_url"
              :class="[
                'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md',
                pagination.next_page_url
                  ? 'bg-white text-gray-700 hover:bg-gray-50'
                  : 'bg-gray-100 text-gray-400 cursor-not-allowed'
              ]"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { useToast } from 'vue-toastification'
import travelService from '@/services/travelService'
import type { TravelRequest, TravelRequestFilters, PaginationData } from '@/types'
import { getStatusLabel } from '../utils/utility_functions.ts'
import { useAuthStore } from '@/stores/auth.ts'

const authStore = useAuthStore();
const toast = useToast()
const requests = ref<TravelRequest[]>([])
const loading = ref<boolean>(true)
const pagination = ref<PaginationData | null>(null)

// Filtros
const filters = reactive<TravelRequestFilters>({
  status: '',
  destination: '',
  page: 1
})

// Estatísticas
const stats = reactive({
  pending: 0,
  approved: 0,
  canceled: 0,
})

// Buscar estatísticas gerais
const fetchStats = async () => {
  try {
    const statsData = await travelService.getStats()
    stats.pending = statsData.pending
    stats.approved = statsData.approved
    stats.canceled = statsData.canceled
  } catch (error) {
    console.error('Error fetching stats:', error)
    toast.error('Erro ao buscar estatísticas')
  }
}

// Buscar solicitações de viagem com paginação
const fetchRequests = async () => {
  loading.value = true
  try {
    // Buscar dados paginados
    const response = await travelService.getAllRequestsWithPagination(filters)
    requests.value = response.data
    pagination.value = response.pagination

    // Buscar estatísticas (pode ser executado em paralelo)
    await fetchStats()
  } catch (error) {
    console.error('Error fetching travel requests:', error)
    toast.error('Erro ao buscar solicitações de viagem')
  } finally {
    loading.value = false
  }
}

// Array de páginas para exibição na paginação
const pagesArray = computed(() => {
  if (!pagination.value) return []

  const currentPage = pagination.value.current_page
  const lastPage = pagination.value.last_page

  // Se houver menos de 8 páginas, mostrar todas
  if (lastPage <= 7) {
    return Array.from({ length: lastPage }, (_, i) => i + 1)
  }

  // Caso contrário, mostrar a primeira, a última e algumas do meio
  const pages = []

  // Sempre mostrar a primeira página
  pages.push(1)

  // Se a página atual estiver próxima do início
  if (currentPage <= 3) {
    pages.push(2, 3, 4, '...', lastPage - 1, lastPage)
  }
  // Se a página atual estiver próxima do final
  else if (currentPage >= lastPage - 2) {
    pages.push('...', lastPage - 3, lastPage - 2, lastPage - 1, lastPage)
  }
  // Se a página atual estiver no meio
  else {
    pages.push('...', currentPage - 1, currentPage, currentPage + 1, '...', lastPage)
  }

  return pages
})

// Navegar para uma página específica
const goToPage = (page: number) => {
  if (page < 1 || (pagination.value && page > pagination.value.last_page)) return

  filters.page = page
  fetchRequests()
}

// Formatar data para exibição
const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR')
}

// Aplicar filtros
const applyFilters = () => {
  filters.page = 1 // Resetar para a primeira página ao aplicar filtros
  fetchRequests()
}

// Resetar filtros
const resetFilters = () => {
  filters.status = ''
  filters.destination = ''
  filters.page = 1
  fetchRequests()
}

onMounted(() => {
  fetchRequests()
})
</script>
