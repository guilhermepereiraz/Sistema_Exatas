// Importa estilos globais da aplicação
import './assets/main.css'

// Importa dependências do Vue e plugins
import { createApp } from 'vue'
import { createPinia } from 'pinia'

// Importa componentes principais
import App from './App.vue'
import router from './router'

// Cria instância da aplicação Vue
const app = createApp(App)

// Configura plugins globais
app.use(createPinia()) // Gerenciamento de estado
app.use(router) // Roteamento

// Monta a aplicação no elemento #app do HTML
app.mount('#app')
