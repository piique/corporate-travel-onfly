import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

// Importações de estilos
import 'vue-toastification/dist/index.css';
import Toast, { useToast, type PluginOptions } from 'vue-toastification';

const toast = useToast();

// Configurações do toast
const toastOptions: PluginOptions = {
  transition: "Vue-Toastification__bounce",
  maxToasts: 3,
  newestOnTop: true,
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 60,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: "button",
  icon: true,
  rtl: false
};

// Criação da aplicação
const app = createApp(App);
const pinia = createPinia();

// Registrar plugins
app.use(pinia);
app.use(router);
app.use(Toast, toastOptions);

// Tratamento global de erros
app.config.errorHandler = (err, instance, info) => {
  console.error('Vue Error:', err);
  console.error('Error Info:', info);
  toast.error('Ocorreu um erro na aplicação. Por favor, tente novamente.');
};

// Montar a aplicação
app.mount('#app');

// Para desenvolvimento: logar ambiente
if (import.meta.env.DEV) {
  console.log('Running in development mode');
  console.log('API URL:', import.meta.env.VITE_API_URL);
}
