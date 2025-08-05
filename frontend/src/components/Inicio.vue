<template>
  <div :class="modoOscuro ? 'modo-oscuro' : 'modo-claro'">
    <div v-if="!isAuthenticated" class="login-container">
      <Login @login-success="handleLoginSuccess" />
    </div>
    <div v-else style="height: 56rem;">
      <!-- Navbar superior -->
      <div
        class="d-flex flex-column flex-shrink-0 p-3 border-end"
        :class="modoOscuro ? 'bg-dark text-white' : 'bg-light'"
        style="width: 150px;"
      >
        <span class="fs-4 text-center mb-4"> Sistema de Activos</span>

        <ul class="nav nav-pills flex-column mb-auto">
          <li>
            <a class="nav-link " data-bs-toggle="collapse" href="#collapseActivos" role="button" >Activos</a>
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
      <!-- Contenido -->
      <main :class="['main', 'mt-5', 'pt-4', modoOscuro ? 'modo-oscuro' : 'modo-claro']">
        <h2 class="mb-2 text-center" :class="modoOscuro ? 'text-white' : 'text-black'">
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
      <!-- Modal de confirmación -->
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

// Estado general
const currentView = ref('lista')
const isAuthenticated = ref(false)
const showConfirmLogout = ref(false)
// Modo oscuro
const modoOscuro = ref(localStorage.getItem('modoOscuro') === 'true')
function toggleModoOscuro() {
  modoOscuro.value = !modoOscuro.value
  localStorage.setItem('modoOscuro', modoOscuro.value)
}
// Título dinámico
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
// Comprobación de sesión
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
// Funciones sesión
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
/* MODO CLARO / OSCURO */
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
}
.modo-oscuro .dropdown-item:hover {
  background-color: #000000 !important;
  color: white !important;
}
.modo-oscuro .form-control,
.modo-oscuro input,
.modo-oscuro textarea,
.modo-oscuro select {
  background-color: #000000 !important;
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
.modo-oscuro .vueform__input ,
.modo-oscuro .multiselect-option ,
.modo-oscuro.multiselect-dropdown  
{
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


