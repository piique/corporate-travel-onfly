<template>
  <div v-if="isReady" class="min-h-screen bg-gray-100">
    <navbar />

    <main class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>
  </div>
  <div v-else class="min-h-screen flex justify-center items-center bg-gray-100">
    <svg class="animate-spin h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import Navbar from '@/layout/Navbar.vue'

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();
const isReady = ref(false);

onMounted(async () => {
  console.log('DefaultLayout montado, verificando autenticação...');

  // Verificar se há token no localStorage
  const token = localStorage.getItem('token');

  if (!token) {
    console.log('Token não encontrado no localStorage, redirecionando para login');
    toast.error('Por favor, faça login para acessar esta página');
    router.push({ name: 'login' });
    return;
  }

  try {
    // Verifica se o token é válido
    const isValid = await authStore.checkAuth();

    if (!isValid) {
      console.log('Token inválido ou expirado, redirecionando para login');
      toast.error('Sua sessão expirou. Por favor, faça login novamente');
      router.push({ name: 'login' });
      return;
    }

    console.log('Autenticação válida, token OK');
    isReady.value = true;
  } catch (error) {
    console.error('Erro ao verificar autenticação:', error);
    toast.error('Erro ao verificar autenticação. Por favor, tente novamente.');
    router.push({ name: 'login' });
  }
});
</script>
