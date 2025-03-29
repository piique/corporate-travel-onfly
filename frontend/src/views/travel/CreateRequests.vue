<template>
  <div>
    <div class="flex items-center mb-6">
      <router-link
        :to="{ name: 'dashboard' }"
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
      </router-link>
      <h1 class="text-2xl font-semibold text-gray-900">Nova Solicitação de Viagem</h1>
    </div>

    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
          <div class="sm:col-span-6">
            <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
            <div class="relative rounded-md shadow-sm">
              <input
                type="text"
                id="destination"
                v-model="form.destination"
                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300 ring-red-300': errors.destination }"
                placeholder="Cidade, Estado, País"
              />
              <div v-if="!errors.destination" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
              </div>
              <p v-if="errors.destination" class="mt-2 text-sm text-red-600">{{ errors.destination }}</p>
            </div>
          </div>

          <div class="sm:col-span-3">
            <label for="departure_date" class="block text-sm font-medium text-gray-700 mb-1">Data de Ida</label>
            <div class="relative rounded-md shadow-sm">
              <input
                type="date"
                id="departure_date"
                v-model="form.departure_date"
                class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300 ring-red-300': errors.departure_date }"
                :min="minDate"
              />
              <p v-if="errors.departure_date" class="mt-2 text-sm text-red-600">{{ errors.departure_date }}</p>
            </div>
          </div>

          <div class="sm:col-span-3">
            <label for="return_date" class="block text-sm font-medium text-gray-700 mb-1">Data de Volta</label>
            <div class="relative rounded-md shadow-sm">
              <input
                type="date"
                id="return_date"
                v-model="form.return_date"
                class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300 ring-red-300': errors.return_date }"
                :min="form.departure_date || minDate"
              />
              <p v-if="errors.return_date" class="mt-2 text-sm text-red-600">{{ errors.return_date }}</p>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
          <router-link
            :to="{ name: 'dashboard' }"
            class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
            </svg>
            Cancelar
          </router-link>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg
              v-if="loading"
              class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
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
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            {{ loading ? 'Enviando...' : 'Enviar Solicitação' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import travelService from '@/services/travelService';

const router = useRouter();
const toast = useToast();
const loading = ref<boolean>(false);

// Data mínima (hoje)
const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Formulário de solicitação simplificado
interface RequestForm {
  destination: string;
  departure_date: string;
  return_date: string;
}

// Erros do formulário
interface FormErrors {
  destination?: string;
  departure_date?: string;
  return_date?: string;
}

const form = reactive<RequestForm>({
  destination: '',
  departure_date: '',
  return_date: ''
});

const errors = reactive<FormErrors>({});

// Validar formulário
const validateForm = (): boolean => {
  let isValid = true;

  // Reset errors
  Object.keys(errors).forEach(key => {
    delete errors[key as keyof FormErrors];
  });

  if (!form.destination.trim()) {
    errors.destination = 'O destino é obrigatório';
    isValid = false;
  }

  if (!form.departure_date) {
    errors.departure_date = 'A data de ida é obrigatória';
    isValid = false;
  }

  if (!form.return_date) {
    errors.return_date = 'A data de volta é obrigatória';
    isValid = false;
  } else if (form.departure_date && form.return_date < form.departure_date) {
    errors.return_date = 'A data de volta deve ser posterior à data de ida';
    isValid = false;
  }

  return isValid;
};

// Enviar formulário
const handleSubmit = async () => {
  if (!validateForm()) {
    toast.error('Por favor, corrija os erros no formulário antes de enviar');
    return;
  }

  loading.value = true;

  try {
    // Preparar dados para envio
    const requestData = {
      destination: form.destination,
      departure_date: form.departure_date,
      return_date: form.return_date
    };

    // Enviar solicitação
    await travelService.createRequest(requestData);

    toast.success('Solicitação de viagem criada com sucesso!');
    router.push({ name: 'dashboard' });
  } catch (error) {
    console.error('Error creating travel request:', error);
    toast.error('Erro ao criar solicitação de viagem');
  } finally {
    loading.value = false;
  }
};
</script>
