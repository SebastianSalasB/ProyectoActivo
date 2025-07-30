<template>
  <div  >
    <div v-if="!isAuthenticated" class="login-container">
      <Login @login-success="handleLoginSuccess" />
    </div>

    <div v-else style="height: 56rem;">
      
      <!-- Navbar superior -->
      <b-navbar toggleable="md" class="bg-barra shadow-sm" fixed="top" style="color:white !important">
        <b-navbar-brand class="text-white" @click="currentView = 'registro'" >Sistema de Activos</b-navbar-brand>
        <b-navbar-toggle target="nav-collapse" style="color:white !important">
          <i class="fa-solid fa-bars fa-xl" style="color: #ffffff;"></i>
        </b-navbar-toggle>
        <b-collapse id="nav-collapse" is-nav style="color:white !important">
          <b-navbar-nav style="color:white !important">
            <!-- Menú Activos -->
            <b-nav-item-dropdown  text="Activos"  left class="dropdown-dark " style="color:white !important" >
              <b-dropdown-item @click="currentView = 'registro'">
                <i class="fa-solid fa-file-pen fa-sm"></i> Activos
              </b-dropdown-item>
              <b-dropdown-item @click="currentView = 'lista'">
                <i class="fa-solid fa-list fa-sm"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
            <!-- Menú Tipo -->
            <b-nav-item-dropdown text="Tipo" left class=" dropdown-dark" style="color:white !important">
              <b-dropdown-item @click="currentView = 'tipo'">Ver Tipos</b-dropdown-item>
            </b-nav-item-dropdown>
            <!-- Menú Empresa -->
            <b-nav-item-dropdown text="Empresa" left class="dropdown-dark" style="color:white !important">
              <b-dropdown-item @click="currentView = 'Registro Empresa'">
                <i class="fa-solid fa-file-pen fa-sm"></i> Empresa
              </b-dropdown-item>
              <b-dropdown-item @click="currentView = 'Empresa'">
                <i class="fa-solid fa-list fa-sm"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
            <!-- Menú Usuarios -->
            <b-nav-item-dropdown text="Usuarios" left class="dropdown-dark" style="color:white !important">
              <b-dropdown-item @click="currentView = 'RegistroResponsable'">
                <i class="fa-solid fa-file-pen fa-sm"></i> Usuarios
              </b-dropdown-item>
              <b-dropdown-item @click="currentView = 'Responsable'">
                <i class="fa-solid fa-list fa-sm"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
          </b-navbar-nav>
          <!-- Logout -->
          <b-navbar-nav class="ms-auto ">
            <b-nav-item @click="showConfirmLogout = true">
              <i class="fa-solid fa-right-from-bracket fa-xl" style="color: #c20000;"></i>
            </b-nav-item>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>
      <!-- Contenido -->
      <main class="main mt-5 pt-4 " >
        <h2 class="mb-2" style="text-align: center; color: black;" >
          {{
            currentView === 'registro' ? '' :
            currentView === 'lista' ? 'Lista de Activos' :
            currentView === 'tipo' ? 'Tipo de Activos' :
            currentView === 'Registro de Tipo' ? 'Registro de Tipo' :
            currentView === 'editar Tipo' ? 'Editar Tipo' :
            currentView === 'Empresa' ? 'Lista De Empresas' :
            currentView === 'Registro Empresa' ? '' :
            currentView === 'Responsable' ? 'Lista Usuarios' :
            currentView === 'RegistroResponsable' ? 'Registro Usuario' :
            ''
          }}
        </h2>
        <ActivosRegistro v-if="currentView === 'registro'" />
        <TablaActivos v-else-if="currentView === 'lista'" />
        <Tipo v-else-if="currentView === 'tipo'" />
        <RegistroEmpresa v-else-if="currentView === 'Registro Empresa'" />
        <ListaEmpresa v-else-if="currentView === 'Empresa'" />
        <ListaRespo v-else-if="currentView === 'Responsable'" />
        <RegistroRespo v-else-if="currentView === 'RegistroResponsable'" />
      </main>

      <!-- Modal de confirmación Cerrar Session -->
      <b-modal
        v-model="showConfirmLogout"
        title="¿Cerrar sesión?"
        centered
        >
        ¿Estás seguro que deseas cerrar tu sesión?
        <template #footer>
          <b-button 
          variant="secondary" 
          @click="showConfirmLogout = false">Cancelar</b-button>
          <b-button variant="danger" @click="confirmLogout">Sí, cerrar sesión</b-button>
        </template>
      </b-modal>
    </div>
  </div>
</template>

<script setup>
import ActivosRegistro from './components/Activos/ActivosRegistro.vue'
import TablaActivos from './components/Activos/TablaActivos.vue'
import Login from './components/Login/Login.vue'
import Tipo from './components/TipoActivos/TIpo.vue'
import RegistroEmpresa from './components/Empresa/RegistroEmpresa.vue'
import ListaEmpresa from './components/Empresa/ListaEmpresa.vue'
import ListaRespo from './components/Usuarios/ListaUsuarios.vue'
import RegistroRespo from './components/Usuarios/RegistroUsuario.vue'
import '@fortawesome/fontawesome-free/css/all.css'
import { ref, onMounted } from 'vue'

const currentView = ref('lista')
const isAuthenticated = ref(false)
const showConfirmLogout = ref(false)

onMounted(() => {
  fetch("http://localhost/activos/Backend/index.php/Auth/ConfirmacionSession", {
    credentials: "include"
  })
    .then(async res => {
      const data = await res.json()
      if (res.ok && data.status === "success") {
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

function handleLoginSuccess() {
  isAuthenticated.value = true
  currentView.value = 'lista'
}

function confirmLogout() {
  fetch("http://localhost/activos/Backend/index.php/Auth/CerrarSession", {
    method: "POST",
    credentials: "include"
  })
    .then(() => {
      isAuthenticated.value = false
      currentView.value = 'login'
      showConfirmLogout.value = false
    })
    .catch(() => {
      isAuthenticated.value = false
      currentView.value = 'login'
      showConfirmLogout.value = false
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
  color:white !important
}
.navbar-nav .dropdown-dark .nav-link,
.navbar-nav .dropdown-dark .dropdown-toggle {
  color: white !important;
}
.bg-barra .dropdown-menu {
  --bs-dropdown-link-active-bg: #6c757d; /* gris oscuro (Bootstrap gris) */
  background-color: #000 !important; /* fondo negro del dropdown */
}

.bg-barra .dropdown-item {
  color: white !important;
}

.bg-barra .dropdown-item:hover {
  background-color: #343a40 !important; /* gris más oscuro al hacer hover */
  color: white !important;
}
</style>
