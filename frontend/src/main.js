import { createApp } from 'vue'
import App from './App.vue'

import BootstrapVue3 from 'bootstrap-vue-3'
import axios from 'axios'

// Estilos
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-3/dist/bootstrap-vue-3.css'

// Establecer URL base global
axios.defaults.baseURL = 'http://localhost/activos/backend/index.php'

const app = createApp(App)
app.use(BootstrapVue3)
app.config.globalProperties.$axios
// Registrar axios como propiedad global (opcional)
app.config.globalProperties.$baseURL = 'http://localhost/activos/backend/index.php'

app.mount('#app')