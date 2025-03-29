<template>
  <nav class="bg-indigo-600">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <router-link :to="{ name: 'dashboard' }" class="text-white font-bold text-xl">
              Viagens Corporativas
            </router-link>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <router-link
                  :to="{ name: 'dashboard' }"
                  class="text-white hover:bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'bg-indigo-700': isCurrentRoute('dashboard') }"
              >
                Dashboard
              </router-link>

              <router-link
                  :to="{ name: 'travel-requests' }"
                  class="text-white hover:bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'bg-indigo-700': isCurrentRoute('travel-requests') }"
              >
                Solicitações
              </router-link>

              <router-link
                  v-if="authStore.isUser"
                  :to="{ name: 'create-request' }"
                  class="text-white hover:bg-indigo-700 px-3 py-2 rounded-md text-sm font-medium"
                  :class="{ 'bg-indigo-700': isCurrentRoute('create-request') }"
              >
                Nova Solicitação
              </router-link>
            </div>
          </div>
        </div>

        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">
            <div class="flex items-center">
              <span class="text-white mr-4">{{ authStore.user?.name }}</span>
              <button
                  @click="authStore.logout"
                  class="text-white bg-indigo-700 hover:bg-indigo-800 px-3 py-2 rounded-md text-sm font-medium"
              >
                Sair
              </button>
            </div>
          </div>
        </div>

        <div class="-mr-2 flex md:hidden">
          <!-- Mobile menu button -->
          <button
              @click="toggleMobileMenu"
              class="bg-indigo-600 inline-flex items-center justify-center p-2 rounded-md text-indigo-200 hover:text-white hover:bg-indigo-700 focus:outline-none"
          >
            <span class="sr-only">Abrir menu principal</span>
            <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
              <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state -->
    <div
        class="md:hidden"
        :class="{ 'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen }"
    >
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
        <router-link
            :to="{ name: 'dashboard' }"
            class="text-white hover:bg-indigo-700 block px-3 py-2 rounded-md text-base font-medium"
            :class="{ 'bg-indigo-700': isCurrentRoute('dashboard') }"
            @click="isMobileMenuOpen = false"
        >
          Dashboard
        </router-link>

        <router-link
            :to="{ name: 'travel-requests' }"
            class="text-white hover:bg-indigo-700 block px-3 py-2 rounded-md text-base font-medium"
            :class="{ 'bg-indigo-700': isCurrentRoute('travel-requests') }"
            @click="isMobileMenuOpen = false"
        >
          Solicitações
        </router-link>

        <router-link
            v-if="authStore.isUser"
            :to="{ name: 'create-request' }"
            class="text-white hover:bg-indigo-700 block px-3 py-2 rounded-md text-base font-medium"
            :class="{ 'bg-indigo-700': isCurrentRoute('create-request') }"
            @click="isMobileMenuOpen = false"
        >
          Nova Solicitação
        </router-link>
      </div>

      <div class="pt-4 pb-3 border-t border-indigo-700">
        <div class="flex items-center px-5">
          <div class="ml-3">
            <div class="text-base font-medium leading-none text-white">
              {{ authStore.user?.name }}
            </div>
            <div class="text-sm font-medium leading-none text-indigo-200 mt-1">
              {{ authStore.user?.email }}
            </div>
          </div>
        </div>
        <div class="mt-3 px-2 space-y-1">
          <button
              @click="authStore.logout"
              class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-white hover:bg-indigo-700"
          >
            Sair
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from "../stores/auth.ts";

const route = useRoute();
const authStore = useAuthStore();
const isMobileMenuOpen = ref<boolean>(false);
console.log(authStore.isUser);

const isCurrentRoute = (routeName: string): boolean => {
  return route.name === routeName;
};

const toggleMobileMenu = (): void => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
</script>
