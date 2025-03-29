<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Solicitações de Viagem</h1>
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

    <!-- Filtros e busca -->
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
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label for="date-range" class="block text-sm font-medium text-gray-700 mb-1">Período</label>
          <div class="mt-1 flex space-x-2">
            <div class="relative flex-1 rounded-md shadow-sm">
              <input
                type="date"
                id="start-date"
                v-model="filters.start_date"
                class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
            </div>
            <div class="relative flex-1 rounded-md shadow-sm">
              <input
                type="date"
                id="end-date"
                v-model="filters.end_date"
                class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
            </div>
          </div>
        </div>

        <div class="sm:col-span-6 flex justify-end">
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
        <p v-if="authStore.isUser"  class="mt-1 text-sm text-gray-500">Comece criando uma nova solicitação de viagem.</p>
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
        <!-- Cabeçalho da tabela -->
        <div class="hidden sm:block">
          <div
            class="bg-gray-50 px-6 py-3 grid grid-cols-12 gap-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            <div class="col-span-2">ID</div>
            <div class="col-span-2">Solicitante</div>
            <div class="col-span-3">Destino</div>
            <div class="col-span-2">Período</div>
            <div class="col-span-2">Status</div>
            <div class="col-span-1">Ações</div>
          </div>
        </div>

        <!-- Linhas da tabela -->
        <ul class="divide-y divide-gray-200">
          <li v-for="request in requests" :key="request.id" class="hover:bg-gray-50">
            <!-- Versão mobile -->
            <div class="sm:hidden p-4 space-y-2">
              <div class="flex justify-between">
                <span class="font-medium text-indigo-600">#{{ request.id }}</span>
                <span
                  :class="[
                    'inline-flex px-2 text-xs font-semibold rounded-full',
                    request.status === 'requested' ? 'text-yellow-800 bg-yellow-100' : '',
                    request.status === 'approved' ? 'text-green-800 bg-green-100' : '',
                    request.status === 'canceled' ? 'text-red-800 bg-red-100' : '',
                  ]"
                >
                  {{ getStatusLabel(request.status) }}
                </span>
              </div>
              <div>
                <span class="text-sm font-medium text-gray-900">{{ request.destination }}</span>
              </div>
              <div class="text-sm text-gray-500">
                {{ request.user_name }} • {{ formatDate(request.departure_date) }} -
                {{ formatDate(request.return_date) }}
              </div>
              <div class="flex justify-end space-x-3">
                <router-link
                  :to="{ name: 'request-details', params: { id: request.id } }"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Detalhes
                </router-link>

                <!-- Ações de status para approvers (mobile) -->
                <div v-if="authStore.isApprover" class="flex space-x-2">
                  <button
                    @click="updateRequestStatus(request.id, 'approved')"
                    class="text-green-600 hover:text-green-900"
                  >
                    Aprovar
                  </button>
                  <button
                    @click="updateRequestStatus(request.id, 'canceled')"
                    class="text-red-600 hover:text-red-900"
                  >
                    Cancelar
                  </button>
                </div>
              </div>
            </div>

            <!-- Versão desktop -->
            <div class="hidden sm:grid sm:grid-cols-12 sm:gap-3 sm:px-6 sm:py-4 sm:items-center">
              <div class="col-span-2 text-sm font-medium text-gray-900">#{{ request.id }}</div>
              <div class="col-span-2 text-sm text-gray-500">{{ request.user_name }}</div>
              <div class="col-span-3 text-sm text-gray-900">{{ request.destination }}</div>
              <div class="col-span-2 text-sm text-gray-500">
                {{ formatDate(request.departure_date) }} - {{ formatDate(request.return_date) }}
              </div>
              <div class="col-span-2">
                <span
                  :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    request.status === 'requested' ? 'text-yellow-800 bg-yellow-100' : '',
                    request.status === 'approved' ? 'text-green-800 bg-green-100' : '',
                    request.status === 'canceled' ? 'text-red-800 bg-red-100' : '',
                  ]"
                >
                  {{ getStatusLabel(request.status) }}
                </span>
              </div>
              <div class="col-span-1 text-right text-sm font-medium">
                <div class="flex justify-end space-x-3">
                  <router-link
                    :to="{ name: 'request-details', params: { id: request.id } }"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Detalhes
                  </router-link>

                  <!-- Menu de ações para approvers (desktop) -->
                  <div v-if="authStore.isApprover" class="relative inline-block text-left ml-2">
                    <div>
                      <button
                        @click="toggleActionMenu(request.id)"
                        type="button"
                        class="inline-flex justify-center w-full rounded-md text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        id="action-menu-button"
                        aria-expanded="true"
                        aria-haspopup="true"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                      </button>
                    </div>

                    <div
                      v-if="actionMenuOpen === request.id"
                      class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                      role="menu"
                      aria-orientation="vertical"
                      aria-labelledby="action-menu-button"
                      tabindex="-1"
                    >
                      <div class="py-1">
                        <button
                          v-if="request.status !== 'approved'"
                          @click="updateRequestStatus(request.id, 'approved')"
                          class="text-green-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                          role="menuitem"
                          tabindex="-1"
                        >
                          Aprovar
                        </button>
                        <button
                          v-if="request.status !== 'canceled'"
                          @click="updateRequestStatus(request.id, 'canceled')"
                          class="text-red-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                          role="menuitem"
                          tabindex="-1"
                        >
                          Cancelar
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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

    <!-- Modal de confirmação -->
    <div
      v-if="confirmModal.show"
      class="fixed z-10 inset-0 overflow-y-auto"
      aria-labelledby="modal-title"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay com opacidade muito baixa -->
        <div class="fixed inset-0 bg-black opacity-20 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Conteúdo do modal (totalmente opaco) -->
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" :class="confirmModal.status === 'approved' ? 'bg-green-100' : 'bg-red-100'">
              <svg v-if="confirmModal.status === 'approved'" class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                {{ confirmModal.status === 'approved' ? 'Aprovar solicitação' : 'Cancelar solicitação' }}
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Tem certeza que deseja {{ confirmModal.status === 'approved' ? 'aprovar' : 'cancelar' }} esta solicitação de viagem? Esta ação não pode ser desfeita.
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="confirmStatusUpdate"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm"
              :class="confirmModal.status === 'approved' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'"
            >
              {{ confirmModal.status === 'approved' ? 'Aprovar' : 'Cancelar' }}
            </button>
            <button
              type="button"
              @click="confirmModal.show = false"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
            >
              Voltar
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
<script setup lang="ts">
import { onMounted, onUnmounted, reactive, ref, computed } from 'vue'
import { useToast } from 'vue-toastification'
import travelService from '@/services/travelService'
import type { TravelRequest, TravelRequestFilters, PaginationData } from '@/types'
import { getStatusLabel } from '@/utils/utility_functions.ts'
import { useAuthStore } from '@/stores/auth'

// Interfaces para paginação
interface PaginationData {
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

// Interface para o modal de confirmação
interface ConfirmModal {
  show: boolean;
  requestId: number | null;
  status: 'approved' | 'canceled';
}

const authStore = useAuthStore()
const toast = useToast()
const requests = ref<TravelRequest[]>([])
const loading = ref<boolean>(true)
const pagination = ref<PaginationData | null>(null)
const actionMenuOpen = ref<number | null>(null)
const statusUpdateLoading = ref<boolean>(false)

console.log("isApprover", authStore.isApprover)

// Estado do modal de confirmação
const confirmModal = reactive<ConfirmModal>({
  show: false,
  requestId: null,
  status: 'approved'
})

// Filtros
const filters = reactive<TravelRequestFilters>({
  status: '',
  destination: '',
  start_date: '',
  end_date: '',
  page: 1
})

// Buscar solicitações de viagem
const fetchRequests = async () => {
  loading.value = true
  try {
    // Atualizar o método para receber a resposta completa da API
    const response = await travelService.getAllRequestsWithPagination(filters)
    requests.value = response.data
    pagination.value = response.pagination
  } catch (error) {
    console.error('Error fetching travel requests:', error)
    toast.error('Erro ao buscar solicitações de viagem')
  } finally {
    loading.value = false
  }
}

// Alternar menu de ações
const toggleActionMenu = (requestId: number) => {
  if (actionMenuOpen.value === requestId) {
    actionMenuOpen.value = null
  } else {
    actionMenuOpen.value = requestId
  }
}

// Mostrar modal de confirmação
const updateRequestStatus = (requestId: number, status: 'approved' | 'canceled') => {
  confirmModal.requestId = requestId
  confirmModal.status = status
  confirmModal.show = true
  actionMenuOpen.value = null
}

// Confirmar a atualização de status
const confirmStatusUpdate = async () => {
  if (!confirmModal.requestId) return

  statusUpdateLoading.value = true

  try {
    await travelService.updateStatus(confirmModal.requestId, confirmModal.status)

    // Atualizar a lista
    const requestIndex = requests.value.findIndex(r => r.id === confirmModal.requestId)
    if (requestIndex !== -1) {
      requests.value[requestIndex].status = confirmModal.status
    }

    toast.success(`Solicitação ${confirmModal.status === 'approved' ? 'aprovada' : 'cancelada'} com sucesso!`)
  } catch (error) {
    console.error(`Error updating request status:`, error)
    if (error.response && error.response.status === 422) {
      Object.values(error.response?.data?.errors).forEach((err: string) => {
        toast.error(err)
      })
      return;
    }

    toast.error(`Erro ao ${confirmModal.status === 'approved' ? 'aprovar' : 'cancelar'} a solicitação.`)
  } finally {
    statusUpdateLoading.value = false
    confirmModal.show = false
    confirmModal.requestId = null
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
  filters.page = 1;
  fetchRequests()
}

// Resetar filtros
const resetFilters = () => {
  filters.status = ''
  filters.destination = ''
  filters.start_date = ''
  filters.end_date = ''
  filters.page = 1
  fetchRequests()
}

// Fechar o menu de ações ao clicar fora dele
const closeActionMenu = (event: MouseEvent) => {
  if (actionMenuOpen.value !== null) {
    const target = event.target as HTMLElement
    if (!target.closest('[id^="action-menu"]')) {
      actionMenuOpen.value = null
    }
  }
}

onMounted(() => {
  fetchRequests()
  document.addEventListener('click', closeActionMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', closeActionMenu)
})
</script>
