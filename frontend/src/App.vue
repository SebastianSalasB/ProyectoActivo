<template>
  <div :class="modoOscuro ? 'modo-oscuro' : 'modo-claro'">
    <div v-if="!isAuthenticated" class="login-container">
      <Login @login-success="handleLoginSuccess" />
    </div>

    <div v-else style="height: 100vh;">
        
          <!-- NAVBAR: visible solo en móvil -->
          <b-navbar v-if="isMobile" toggleable="md" class="bg-barra shadow-sm" fixed="top">
            <b-navbar-brand class="text-white" @click="currentView = 'registro'">
              Sistema de Activos
            </b-navbar-brand>
            <b-navbar-toggle target="nav-collapse">
              <i class="fa-solid fa-bars fa-xl text-white"></i>
            </b-navbar-toggle>
            <b-collapse id="nav-collapse" is-nav>
              <b-navbar-nav>
                <b-nav-item-dropdown text="Activos" left class="dropdown-dark">
                  <b-dropdown-item @click="currentView = 'registro'">
                    <i class="fa-solid fa-file-pen fa-sm"></i> Activos
                  </b-dropdown-item>
                  <b-dropdown-item @click="currentView = 'lista'">
                    <i class="fa-solid fa-list fa-sm"></i> Lista
                  </b-dropdown-item>
                </b-nav-item-dropdown>
                <b-nav-item-dropdown text="Tipo" left class="dropdown-dark">
                  <b-dropdown-item @click="currentView = 'tipo'">Ver Tipos</b-dropdown-item>
                </b-nav-item-dropdown>
                <b-nav-item-dropdown text="Empresa" left class="dropdown-dark">
                  <b-dropdown-item @click="currentView = 'Registro Empresa'">
                    <i class="fa-solid fa-file-pen fa-sm"></i> Empresa
                  </b-dropdown-item>
                  <b-dropdown-item @click="currentView = 'Empresa'">
                    <i class="fa-solid fa-list fa-sm"></i> Lista
                  </b-dropdown-item>
                </b-nav-item-dropdown>
                <b-nav-item-dropdown text="Usuarios" left class="dropdown-dark">
                  <b-dropdown-item @click="currentView = 'RegistroResponsable'">
                    <i class="fa-solid fa-file-pen fa-sm"></i> Usuarios
                  </b-dropdown-item>
                  <b-dropdown-item @click="currentView = 'Responsable'">
                    <i class="fa-solid fa-list fa-sm"></i> Lista
                  </b-dropdown-item>
                </b-nav-item-dropdown>
              </b-navbar-nav>
              <b-navbar-nav class="ms-auto">
                <b-nav-item @click="toggleModoOscuro" title="Cambiar modo">
                  <i class="fa-solid fa-xl" :class="modoOscuro ? 'fa-sun text-warning' : 'fa-moon text-white'"></i>
                </b-nav-item>
                <b-nav-item @click="showConfirmLogout = true">
                  <i class="fa-solid fa-right-from-bracket fa-xl" style="color: #c20000;"></i>
                </b-nav-item>
              </b-navbar-nav>
            </b-collapse>
          </b-navbar>

          <!-- SIDEBAR: visible solo en escritorio -->
          <div 
            v-if="!isMobile"
            class="d-flex flex-column flex-shrink-0 p-3 border-end"
            :class="modoOscuro ? 'bg-dark text-white' : 'bg-light'"
            style="width: 150px; height: 100vh; position: fixed; z-index: 1;"
          >
            <span class="fs-4 text-center mb-4">Sistema de Activos</span>

            <ul class="nav nav-pills flex-column mb-auto">
              <li>
                <a class="nav-link" data-bs-toggle="collapse" href="#collapseActivos" role="button">Activos</a>
                <div class="collapse show" id="collapseActivos">
                  <ul class="list-unstyled ps-3 small">
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'registro'"><i class="fa-solid fa-pen-to-square"></i> Registro</a></li>
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'lista'"><i class="fa-solid fa-list"></i> Lista</a></li>
                  </ul>
                </div>
              </li>

              <li class="mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#collapseEmpresa" role="button">Empresa</a>
                <div class="collapse show" id="collapseEmpresa">
                  <ul class="list-unstyled ps-3 small">
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'Registro Empresa'"><i class="fa-solid fa-pen-to-square"></i> Registro</a></li>
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'Empresa'"><i class="fa-solid fa-list"></i> Lista</a></li>
                  </ul>
                </div>
              </li>

              <li class="mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#collapseUsuarios" role="button">Usuarios</a>
                <div class="collapse show" id="collapseUsuarios">
                  <ul class="list-unstyled ps-3 small">
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'RegistroResponsable'"><i class="fa-solid fa-pen-to-square"></i> Registro</a></li>
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'Responsable'"><i class="fa-solid fa-list"></i> Lista</a></li>
                  </ul>
                </div>
              </li>

              <li class="mt-2">
                <a class="nav-link" data-bs-toggle="collapse" href="#collapseTipos" role="button">Tipos</a>
                <div class="collapse" id="collapseTipos">
                  <ul class="list-unstyled ps-3 small">
                    <li><a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'tipo'">Lista / Registro</a></li>
                  </ul>
                </div>
              </li>
            </ul>

            <hr />
            <div>
              <b-button variant="outline-secondary" class="w-100 mb-2" @click="toggleModoOscuro">
                <i :class="modoOscuro ? 'fa fa-sun' : 'fa fa-moon'"></i>
              </b-button>
              <b-button variant="outline-danger" class="w-100" @click="showConfirmLogout = true">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
              </b-button>
            </div>
          </div>

          <!-- CONTENIDO PRINCIPAL -->
          <main
            :class="['main', 'pt-4', modoOscuro ? 'modo-oscuro' : 'modo-claro']"
            :style="{ marginTop: isMobile ? '2rem' : '0', marginLeft: !isMobile ? '150px' : '0' }"
          >
            <ActivosRegistro v-if="currentView === 'registro'" />
            <TablaActivos v-else-if="currentView === 'lista'" />
            <Tipo v-else-if="currentView === 'tipo'" />
            <RegistroEmpresa v-else-if="currentView === 'Registro Empresa'" />
            <ListaEmpresa v-else-if="currentView === 'Empresa'" />
            <ListaRespo v-else-if="currentView === 'Responsable'" />
            <RegistroRespo v-else-if="currentView === 'RegistroResponsable'" />
          </main>

          <!-- MODAL CONFIRMACIÓN -->
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
import { ref, onMounted, onUnmounted, computed } from 'vue'
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

import '@fortawesome/fontawesome-free/css/all.css'

const currentView = ref('lista')
const isAuthenticated = ref(false)
const showConfirmLogout = ref(false)
const modoOscuro = ref(localStorage.getItem('modoOscuro') === 'true')

// DETECTAR PANTALLA PEQUEÑA
const isMobile = ref(window.innerWidth < 768)
function handleResize() {
  isMobile.value = window.innerWidth < 768
}
onMounted(() => {
  window.addEventListener('resize', handleResize)
  handleResize()

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
onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

function toggleModoOscuro() {
  modoOscuro.value = !modoOscuro.value
  localStorage.setItem('modoOscuro', modoOscuro.value)
}

function handleLoginSuccess() {
  isAuthenticated.value = true
  currentView.value = 'lista'
}

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
/* MISMO CSS QUE TENÍAS */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}
.bg-barra {
  background-color: #000 !important;
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
  background-color: #000000 !important;
  color: white !important;
}
.modo-claro {
  background-color: #ffffff;
  color: black;
}
.modo-oscuro {
  background-color: #121212;
  color: white;
}
.modo-oscuro .card,
.modo-oscuro .table,
.modo-oscuro .dropdown-menu,
.modo-oscuro .modal-content {
  background-color: #000000 !important;
  color: white !important;
  right: 0 !important;
}
.modo-oscuro .dropdown-item:hover {
  background-color: #000000 !important;
  color: white !important;
}
.modo-oscuro .form-control,
.modo-oscuro input,
.modo-oscuro textarea,
.modo-oscuro select {
  background-color: #2c2c2c !important;
  color: white !important;
  border-color: #c9c9c9 !important;
}
.modo-oscuro .btn {
  color: white;
  background-color: #000000;
  border-color: #000000;
}
.modo-oscuro .btn:hover {
  background-color: #000000;
}
.modo-oscuro .vueform__input,
.modo-oscuro .multiselect-option,
.modo-oscuro.multiselect-dropdown {
  background-color: #424242 !important;
  color: rgb(255, 255, 255) !important;
}
.modo-oscuro .is-selected {
  background-color: #b6b6b6 !important;
  color: rgb(0, 0, 0) !important;
}
.modo-oscuro .vueform__input::placeholder {
  color: rgb(255, 255, 255) !important;
}

</style>
