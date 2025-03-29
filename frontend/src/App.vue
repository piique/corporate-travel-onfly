<template>
  <div>
    <router-view v-slot="{ Component }">
      <transition name="fade" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Verificar token na inicialização
onMounted(() => {
  console.log('[App] Inicializando aplicação, token em localStorage:', !!localStorage.getItem('token'));

  // Configurar evento para armazenamento local
  window.addEventListener('storage', handleStorageChange);
});

onUnmounted(() => {
  window.removeEventListener('storage', handleStorageChange);
});

// Lidar com alterações no localStorage (útil para múltiplas abas)
const handleStorageChange = (event: StorageEvent) => {
  if (event.key === 'token' && !event.newValue) {
    console.log('[App] Token removido em outra aba, fazendo logout');
    authStore.logout();
  }
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
