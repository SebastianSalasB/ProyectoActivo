<template>
  <div>
    <div v-if="!isAuthenticated" class="login-container">
      <Login @login-success="handleLoginSuccess" />
    </div>

    <div v-else style="height: 56rem;">
      <!-- Navbar superior -->
      <b-navbar toggleable="md" class="bg-barra shadow-sm" fixed="top">
        <b-navbar-brand class="text-white" @click="currentView = 'registro'">
          Sistema de Activos
        </b-navbar-brand>
        <b-navbar-toggle target="nav-collapse">
          <i class="fa-solid fa-bars fa-xl text-white"></i>
        </b-navbar-toggle>

        <b-collapse id="nav-collapse" is-nav>
          <b-navbar-nav>
            <!-- Menú Activos -->
            <b-nav-item-dropdown text="Activos" left class="dropdown-dark">
              <b-dropdown-item @click="currentView = 'registro'">
                <i class="fa-solid fa-file-pen fa-sm"></i> Activos
              </b-dropdown-item>
              <b-dropdown-item @click="currentView = 'lista'">
                <i class="fa-solid fa-list fa-sm"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>

            <!-- Menú Tipo -->
            <b-nav-item-dropdown text="Tipo" left class="dropdown-dark">
              <b-dropdown-item @click="currentView = 'tipo'">
                Ver Tipos
              </b-dropdown-item>
            </b-nav-item-dropdown>

            <!-- Menú Empresa -->
            <b-nav-item-dropdown text="Empresa" left class="dropdown-dark">
              <b-dropdown-item @click="currentView = 'Registro Empresa'">
                <i class="fa-solid fa-file-pen fa-sm"></i> Empresa
              </b-dropdown-item>
              <b-dropdown-item @click="currentView = 'Empresa'">
                <i class="fa-solid fa-list fa-sm"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>

            <!-- Menú Usuarios -->
            <b-nav-item-dropdown text="Usuarios" left class="dropdown-dark">
              <b-dropdown-item @click="currentView = 'RegistroResponsable'">
                <i class="fa-solid fa-file-pen fa-sm"></i> Usuarios
              </b-dropdown-item>
              <b-dropdown-item @click="currentView = 'Responsable'">
                <i class="fa-solid fa-list fa-sm"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
          </b-navbar-nav>

          <!-- Logout -->
          <b-navbar-nav class="ms-auto">
            <b-nav-item @click="showConfirmLogout = true">
              <i class="fa-solid fa-right-from-bracket fa-xl" style="color: #c20000;"></i>
            </b-nav-item>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>

      <!-- Contenido -->
      <main class="main mt-5 pt-4">
        <h2 class="mb-2 text-center text-black">
          {{ tituloActual }}
        </h2>
        <ActivosRegistro v-if="currentView === 'registro'" />
        <TablaActivos v-else-if="currentView === 'lista'" />
        <Tipo v-else-if="currentView === 'tipo'" />
        <RegistroEmpresa v-else-if="currentView === 'Registro Empresa'" />
        <ListaEmpresa v-else-if="currentView === 'Empresa'" />
        <ListaRespo v-else-if="currentView === 'Responsable'" />
        <RegistroRespo v-else-if="currentView === 'RegistroResponsable'" />
      </main>

      <!-- Modal de confirmación Cerrar Sesión -->
      <b-modal v-model="showConfirmLogout" title="¿Cerrar sesión?" centered>
        ¿Estás seguro que deseas cerrar tu sesión?
        <template #footer>
          <b-button variant="secondary" @click="showConfirmLogout = false">Cancelar</b-button>
          <b-button variant="danger" @click="confirmLogout">Sí, cerrar sesión</b-button>
        </template>
      </b-modal>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// Componentes
import Login from './components/Login/Login.vue'
import ActivosRegistro from './components/Activos/ActivosRegistro.vue'
import TablaActivos from './components/Activos/TablaActivos.vue'
import Tipo from './components/TipoActivos/TIpo.vue'
import RegistroEmpresa from './components/Empresa/RegistroEmpresa.vue'
import ListaEmpresa from './components/Empresa/ListaEmpresa.vue'
import ListaRespo from './components/Usuarios/ListaUsuarios.vue'
import RegistroRespo from './components/Usuarios/RegistroUsuario.vue'

// FontAwesome
import '@fortawesome/fontawesome-free/css/all.css'

// Estados
const currentView = ref('lista')
const isAuthenticated = ref(false)
const showConfirmLogout = ref(false)

// Computed para el título dinámico
const tituloActual = computed(() => {
  switch (currentView.value) {
    case 'lista': return 'Lista de Activos'
    case 'tipo': return 'Tipo de Activos'
    case 'Registro de Tipo': return 'Registro de Tipo'
    case 'editar Tipo': return 'Editar Tipo'
    case 'Empresa': return 'Lista De Empresas'
    case 'Responsable': return 'Lista Usuarios'
    case 'RegistroResponsable': return 'Registro Usuario'
    default: return ''
  }
})

// Verificar sesión al cargar
onMounted(() => {
  axios.get('/Auth/ConfirmacionSession', { withCredentials: true })
    .then(res => {
      const data = res.data
      if (res.status === 200 && data.status === 'success') {
        isAuthenticated.value = true
        currentView.value = 'lista'
      } else {
        isAuthenticated.value = false
        currentView.value = 'login'
      }
    })
    .catch(() => {
      isAuthenticated.value = false
      currentView.value = 'login'
    })
})

// Al iniciar sesión
function handleLoginSuccess() {
  isAuthenticated.value = true
  currentView.value = 'lista'
}

// Al cerrar sesión
function confirmLogout() {
  axios.post('/Auth/CerrarSession', {}, { withCredentials: true })
    .finally(() => {
      showConfirmLogout.value = false
      setTimeout(() => {
        isAuthenticated.value = false
        currentView.value = 'login'
      }, 300)
    })
}
</script>

<style>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.bg-barra {
  background-color: rgb(0, 0, 0) !important;
  color: white !important;
}

.navbar-nav .dropdown-dark .nav-link,
.navbar-nav .dropdown-dark .dropdown-toggle {
  color: white !important;
}

.bg-barra .dropdown-menu {
  --bs-dropdown-link-active-bg: #6c757d;
  background-color: #000 !important;
}

.bg-barra .dropdown-item {
  color: white !important;
}

.bg-barra .dropdown-item:hover {
  background-color: #343a40 !important;
  color: white !important;
}
</style>
