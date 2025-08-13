import { createApp } from 'vue'
import App from './App.vue'

import BootstrapVue3 from 'bootstrap-vue-3'
import axios from 'axios'

// Estilos
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'
import 'bootstrap-icons/font/bootstrap-icons.css';
import '@fortawesome/fontawesome-free/css/all.css'

// Establecer URL base global
axios.defaults.baseURL = 'http://localhost/Activos/Backend/index.php'
axios.defaults.withCredentials = true

const app = createApp(App)

app.use(BootstrapVue3)

// Registrar axios como propiedad global (opcional)
app.config.globalProperties.$axios= axios
app.config.globalProperties.$baseURL = 'http://localhost/Activos/Backend/index.php'

app.mount('#app')