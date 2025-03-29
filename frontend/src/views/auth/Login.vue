<template>
  <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
    <form @submit.prevent="handleLogin" class="mb-0 space-y-6">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <div class="mt-1">
          <input
            id="email"
            v-model="form.email"
            name="email"
            type="email"
            required
            autocomplete="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
        <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
        <div class="mt-1">
          <input
            id="password"
            v-model="form.password"
            name="password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
        <div v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</div>
      </div>

      <div v-if="authStore.error" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ authStore.error }}</h3>
          </div>
        </div>
      </div>

      <div>
        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <svg
            v-if="authStore.loading"
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
          {{ authStore.loading ? 'Autenticando...' : 'Entrar' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '@/stores/auth';
import { LoginCredentials } from '@/types';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const authStore = useAuthStore();

interface FormErrors {
  email: string;
  password: string;
}

const form = reactive<LoginCredentials>({
  email: '',
  password: ''
});

const errors = reactive<FormErrors>({
  email: '',
  password: ''
});

const validateForm = (): boolean => {
  let valid = true;

  errors.email = '';
  errors.password = '';

  if (!form.email) {
    errors.email = 'O email é obrigatório';
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Digite um email válido';
    valid = false;
  }

  if (!form.password) {
    errors.password = 'A senha é obrigatória';
    valid = false;
  } else if (form.password.length < 6) {
    errors.password = 'A senha deve ter pelo menos 6 caracteres';
    valid = false;
  }

  return valid;
};

const handleLogin = async (): Promise<void> => {
  console.log('Tentando fazer login...');

  if (!validateForm()) {
    toast.error('Por favor, corrija os erros no formulário');
    return;
  }

  const success = await authStore.login(form);

  if (success) {
    console.log('Login bem-sucedido, redirecionando...');
    toast.success('Login realizado com sucesso!');

    // Verificar se existe um redirecionamento na URL
    const redirectPath = route.query.redirect as string || '/';

    // Usar nextTick para garantir que a navegação ocorra após as atualizações do DOM
    await nextTick();

    // Usar window.location para forçar uma navegação completa
    if (redirectPath === '/') {
      console.log('Redirecionando para o dashboard...');
      // Usar abordagem diferente para o redirecionamento
      window.location.href = '/';
    } else {
      console.log('Redirecionando para:', redirectPath);
      router.push(redirectPath);
    }
  } else {
    console.error('Falha no login');
  }
};
</script>
