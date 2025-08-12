<template>
  <div :class="modoOscuro ? 'modo-oscuro' : 'modo-claro'">
    <div v-if="loading" class="loading-container">
      <b-spinner label=""></b-spinner>
    </div>
    <div v-else>
      <div v-if="!isAuthenticated" class="login-container">
        <Login @login-success="handleLoginSuccess" />
      </div>
      <div v-else style="height: 100vh;">
        <!-- NAVBAR: solo móvil -->
        <b-navbar id="narbar-movil" v-if="isMobile" toggleable="md" class="shadow-sm" fixed="top">
          <h2>Sistema de Activos</h2>
          <b-navbar-toggle id="hamburgesa" target="nav-collapse">
            <i class="fa-solid fa-bars fa-xl"></i>
          </b-navbar-toggle>
          <b-collapse id="nav-collapse" is-nav ref="navCollapse" v-model="collapseVisible">
            <b-navbar-nav>
              <!-- Activos -->
              <b-nav-item-dropdown class="ms-3" text="Activos" drop="down">
                <b-dropdown-item @click="seleccionarVista('registro')">
                  <i class="fa-solid fa-pen-to-square"></i> <span class="small"> Registrar Activos</span>
                </b-dropdown-item>
                <b-dropdown-item @click="seleccionarVista('lista')">
                  <i class="fa-solid fa-list"></i> <span class="small"> Lista de activos</span>
                </b-dropdown-item>
              </b-nav-item-dropdown>
              <!-- Tipo -->
              <b-nav-item-dropdown text="Tipo" class="ms-3" drop="down">
                <b-dropdown-item @click="seleccionarVista('tipo')">
                  <i class="fa-solid fa-pen-to-square"></i> <span class="small"> Registro / Lista</span>
                </b-dropdown-item>
              </b-nav-item-dropdown>
              <!-- Empresa -->
              <b-nav-item-dropdown text="Empresa" class="ms-3" drop="down">
                <b-dropdown-item @click="seleccionarVista('Registro Empresa')">
                  <i class="fa-solid fa-pen-to-square"></i> <span class="small"> Registrar Empresa</span>
                </b-dropdown-item>
                <b-dropdown-item @click="seleccionarVista('Empresa')">
                  <i class="fa-solid fa-list"></i> <span class="small"> Lista de empresa</span>
                </b-dropdown-item>
              </b-nav-item-dropdown>
              <!-- Usuarios -->
              <b-nav-item-dropdown text="Usuarios" class="ms-3" drop="down">
                <b-dropdown-item @click="seleccionarVista('RegistroResponsable')">
                  <i class="fa-solid fa-pen-to-square"></i> <span class="small"> Registrar Usuarios</span>
                </b-dropdown-item>
                <b-dropdown-item @click="seleccionarVista('Responsable')">
                  <i class="fa-solid fa-list"></i> <span class="small"> Lista de Usuarios</span>
                </b-dropdown-item>
              </b-nav-item-dropdown>
            </b-navbar-nav>
            <b-navbar-nav class="ms-auto">
              <b-nav-item role="button" aria-label="Ajustes de usuario" @click="UsuariosAjustes = true" class="d-flex align-items-center ms-3">
                <i class="fa-solid fa-gear fa-lg"></i>
                <span class="small"> Ajustes</span>
              </b-nav-item>
              <b-nav-item role="button" aria-label="Cerrar sesión" @click="showConfirmLogout = true" class="d-flex align-items-center ms-3">
                <i class="fa-solid fa-right-from-bracket fa-lg text-danger me-1"></i>
                <span class="text-danger small">Cerrar sesión</span>
              </b-nav-item>
            </b-navbar-nav>
          </b-collapse>
        </b-navbar>
        <!-- SIDEBAR: solo escritorio -->
        <div v-if="!isMobile" class="d-flex flex-column flex-shrink-0 p-3 border-end"
          :class="modoOscuro ? 'bg-dark text-white' : 'bg-light'" 
          style="width: 180px; height: 100vh; position: fixed; z-index: 1;">
          <span class="fs-4 text-center mb-4">Sistema de Activos</span>
          <ul class="nav nav-pills flex-column mb-auto">
            <!-- Activos -->
            <li>
              <a class="nav-link" data-bs-toggle="collapse" href="#collapseActivos" role="button">Activos</a>
              <div class="collapse show" id="collapseActivos">
                <ul class="list-unstyled ps-3 small">
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'registro'"><i class="fa-solid fa-pen-to-square"></i> Registro</a></li>
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'lista'"><i class="fa-solid fa-list"></i> Lista</a></li>
                </ul>
              </div>
            </li>
            <!-- Empresa -->
            <li class="mt-2">
              <a class="nav-link" data-bs-toggle="collapse" href="#collapseEmpresa" role="button">Empresa</a>
              <div class="collapse" id="collapseEmpresa">
                <ul class="list-unstyled ps-3 small">
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'Registro Empresa'"><i class="fa-solid fa-pen-to-square"></i> Registro</a></li>
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'Empresa'"><i class="fa-solid fa-list"></i> Lista</a></li>
                </ul>
              </div>
            </li>
            <!-- Usuarios -->
            <li class="mt-2">
              <a class="nav-link" data-bs-toggle="collapse" href="#collapseUsuarios" role="button">Usuarios</a>
              <div class="collapse" id="collapseUsuarios">
                <ul class="list-unstyled ps-3 small">
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'RegistroResponsable'"><i class="fa-solid fa-pen-to-square"></i> Registro</a></li>
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'Responsable'"><i class="fa-solid fa-list"></i> Lista</a></li>
                </ul>
              </div>
            </li>
            <!-- Tipos -->
            <li class="mt-2">
              <a class="nav-link" data-bs-toggle="collapse" href="#collapseTipos" role="button">Tipos</a>
              <div class="collapse" id="collapseTipos">
                <ul class="list-unstyled ps-3 small">
                  <li><a class="nav-link nav-link-li" @click.prevent="currentView = 'tipo'">Lista / Registro</a></li>
                </ul>
              </div>
            </li>
          </ul>
          <hr />
          <div>
            <b-button class="w-100 mb-2" variant="outline-secondary" @click="UsuariosAjustes = true">
              <i class="fa-solid fa-gear"></i> Ajustes
            </b-button>
            <b-button class="w-100 mb-2" variant="outline-danger" @click="showConfirmLogout = true">
              <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </b-button>
          </div>
        </div>
        <!-- CONTENIDO PRINCIPAL -->
        <main :class="['main', 'pt-4', modoOscuro ? 'modo-oscuro' : 'modo-claro']" :style="{ marginTop: isMobile ? '2rem' : '0', marginLeft: !isMobile ? '200px' : '0' }">
          <ActivosRegistro v-if="currentView === 'registro'" />
          <TablaActivos v-else-if="currentView === 'lista'" />
          <Tipo v-else-if="currentView === 'tipo'" />
          <RegistroEmpresa v-else-if="currentView === 'Registro Empresa'" />
          <ListaEmpresa v-else-if="currentView === 'Empresa'" />
          <ListaRespo v-else-if="currentView === 'Responsable'" />
          <RegistroRespo v-else-if="currentView === 'RegistroResponsable'" />
        </main>
        <!-- MODAL CONFIRMACIÓN LOGOUT -->
        <b-modal v-model="showConfirmLogout" title="¿Cerrar sesión?" centered>
          ¿Estás seguro que deseas cerrar tu sesión?
          <template #footer>
            <b-button variant="outline-secondary" @click="showConfirmLogout = false">Cancelar</b-button>
            <b-button variant="outline-danger" @click="confirmLogout">Sí, cerrar sesión</b-button>
          </template>
        </b-modal>
        <!-- MODAL AJUSTES DE USUARIO -->
        <b-modal v-model="UsuariosAjustes" title="Ajustes de Usuario" centered @hide="limpiarErroresUsuario">
          <b-form>
            <b-form-group label="Nombre y Apellido">
              <b-form-input v-model="nombreCompleto" disabled />
            </b-form-group>
            <b-row>
              <b-col sm="6">
                <b-form-group label="Correo">
                  <b-form-input v-model.trim="datosUsuariosRegistado.usr_correo" :class="{ 'is-invalid': inputErrors[0].correo }" type="email" placeholder="ejemplo@correo.cl" @input="validarCampo('correo', datosUsuariosRegistado.usr_correo)" />
                </b-form-group>
              </b-col>
              <b-col sm="6">
                <b-form-group label="Teléfono">
                  <b-form-input v-model.trim="datosUsuariosRegistado.usr_telefono" :class="{ 'is-invalid': inputErrors[0].telefono }" type="tel" placeholder="+56912345678" maxlength="9" @input="validarCampo('telefono', datosUsuariosRegistado.usr_telefono)" />
                </b-form-group>
              </b-col>
            </b-row>
            <b-form-group label="Clave (Anterior)">
              <b-form-input v-model="datosUsuariosRegistado.usr_claveAntigua" type="password" placeholder="" 
                :class="{ 'is-invalid': inputErrors[0].claveAntigua }" 
                @input="validarCampo('claveAntigua', datosUsuariosRegistado.usr_claveAntigua)" />
              <b-form-text class="text-muted">solo si deseas cambiarla.</b-form-text>
              </b-form-group>
            <b-form-group label="Clave nueva">
              <b-form-input v-model="datosUsuariosRegistado.usr_claveNueva" type="password" placeholder="" 
                :class="{ 'is-invalid': inputErrors[0].clave }" 
                @input="validarCampo('clave', datosUsuariosRegistado.usr_claveNueva)" />
              <b-form-text class="text-muted">Mínimo 8 caracteres, al menos una letra y un número.</b-form-text>
            </b-form-group>
            <b-form-group label="Repetir clave nueva">
              <b-form-input v-model="datosUsuariosRegistado.usr_claveNueva2" type="password" placeholder="" 
                :class="{ 'is-invalid': inputErrors[0].clave }" 
                @input="validarCampo('clave', datosUsuariosRegistado.usr_claveNueva2)" />
            </b-form-group>
            <hr />
            <b-form-checkbox v-model="modoOscuro" switch>Usar el modo oscuro</b-form-checkbox>
          </b-form>
          <template #footer>
            <b-button variant="secondary" @click="UsuariosAjustes = false">Cancelar</b-button>
            <b-button variant="primary" @click="guardarCambiosUsuario">Guardar cambios</b-button>
          </template>
        </b-modal>
      </div>
    </div>
  </div>
</template>
<script>
import axios from 'axios'
import Login from './components/Login/Login.vue'
import ActivosRegistro from './components/Activos/ActivosRegistro.vue'
import TablaActivos from './components/Activos/TablaActivos.vue'
import Tipo from './components/TipoActivos/Tipo.vue'
import RegistroEmpresa from './components/Empresa/RegistroEmpresa.vue'
import ListaEmpresa from './components/Empresa/ListaEmpresa.vue'
import ListaRespo from './components/Usuarios/ListaUsuarios.vue'
import RegistroRespo from './components/Usuarios/RegistroUsuario.vue'

export default {
  components: {
    Login, ActivosRegistro, TablaActivos, Tipo,
    RegistroEmpresa, ListaEmpresa, ListaRespo, RegistroRespo
  },
  data() {
    return {
      currentView: 'lista',
      isAuthenticated: false,
      showConfirmLogout: false,
      UsuariosAjustes: false,
      collapseVisible: false,
      isLoading: true,
      UsuariosRegistado: {},
      datosUsuariosRegistado: {},
      modoOscuro: localStorage.getItem('modoOscuro') === 'true',
      isMobile: window.innerWidth < 1025,
      inputErrors: [{ correo: false, telefono: false, clave: false }],
      usuario: null
    }
  },
  computed: {
    nombreCompleto() {
      return `${this.UsuariosRegistado.usr_nombre || ''} ${this.UsuariosRegistado.usr_apellido || ''}`
    }
  },
  watch: {
    modoOscuro(newVal) {
      localStorage.setItem('modoOscuro', newVal)
      document.body.classList.toggle('modo-oscuro', newVal)
    }
  },
  methods: {
    handleResize() {
      this.isMobile = window.innerWidth < 768
    },
    handleLoginSuccess(usuario) {
      this.usuario = usuario
      this.isAuthenticated = true
      localStorage.setItem('usuario', JSON.stringify(usuario))
      this.datosUsuarioRegistrado()
    },
    async datosUsuarioRegistrado() {
      try {
        const resUsuario = await axios.get('/Auth/ConfirmacionSession', { withCredentials: true })
        if (resUsuario.data.status === 'success') {
          this.UsuariosRegistado = resUsuario.data.user
          const res = await axios.get(`/Usuarios/DatosUsuario/${this.UsuariosRegistado.usr_id}`, { withCredentials: true })
          this.datosUsuariosRegistado = res.data[0]
        }
      } catch (error) {
        console.error('Error al obtener los datos del usuario:', error)
      }
    },
    validarEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return re.test(email)
    },
    validarCampo(campo, valor) {
      const errores = this.inputErrors[0]
      switch (campo) {
        case 'correo':
          errores.correo = !this.validarEmail(valor)
          break
        case 'telefono':
          errores.telefono = !/^\d{9}$/.test(valor)
          break
        case 'claveAntigua':
        case 'clave':
          errores.clave = valor && (valor.length < 8 || !/\d/.test(valor) || !/[A-Za-z]/.test(valor))
          break
      }
    },
    validarDatos() {
      this.validarCampo('correo', this.datosUsuariosRegistado.usr_correo)
      this.validarCampo('telefono', this.datosUsuariosRegistado.usr_telefono)
      if (this.datosUsuariosRegistado.usr_claveAntigua) {
        this.validarCampo('clave', this.datosUsuariosRegistado.usr_claveAntigua)
      }
      if (this.datosUsuariosRegistado.usr_claveNueva) {
        this.validarCampo('clave', this.datosUsuariosRegistado.usr_claveNueva)
      }
      return !Object.values(this.inputErrors[0]).some(Boolean)
    },
    async guardarCambiosUsuario() {
      if (!this.validarDatos()) return

      if (this.datosUsuariosRegistado.usr_claveNueva || this.datosUsuariosRegistado.usr_claveNueva2) {
        if (!this.datosUsuariosRegistado.usr_claveAntigua) {
          alert('Debes ingresar tu clave actual para cambiarla')
          return
        }
        if (this.datosUsuariosRegistado.usr_claveNueva !== this.datosUsuariosRegistado.usr_claveNueva2) {
          alert('Las contraseñas nuevas no coinciden')
          return
        }
      }

      const payload = {
        ...this.datosUsuariosRegistado,
        usr_claveAntigua: this.datosUsuariosRegistado.usr_claveAntigua || undefined,
        usr_claveNueva: this.datosUsuariosRegistado.usr_claveNueva || undefined
      }

      try {
        const res = await axios.post(`/Usuarios/ActualizarPerfil/${this.datosUsuariosRegistado.usr_id}`, payload, {
          headers: { 'Content-Type': 'application/json' }
        })

        if (res.data.status === 'clave_incorrecta') {
          alert('La clave actual ingresada es incorrecta')
          return
        }
        if (res.data.status === 'updated') {
          alert('Usuario actualizado correctamente')
          this.UsuariosAjustes = false
        } else {
          alert('Error al actualizar el usuario')
        }
      } catch (err) {
        console.error('Error al guardar:', err)
        alert('Error de conexión al servidor')
      }
    },
    seleccionarVista(vista) {
      this.currentView = vista
      if (this.isMobile) {
        setTimeout(() => {
          this.collapseVisible = false
          this.$refs.navCollapse?.hide?.()
        }, 150)
      }
    },
    limpiarErroresUsuario() {
      this.inputErrors = [{ correo: false, telefono: false, clave: false }]
    },
    confirmLogout() {
      axios.post('/Auth/CerrarSession', {}, { withCredentials: true })
        .finally(() => {
          this.showConfirmLogout = false
          setTimeout(() => {
            this.logout()
          }, 300)
        })
    },
    logout() {
      this.usuario = null
      this.isAuthenticated = false
      localStorage.removeItem('usuario')
    }
  },
  async mounted() {
    this.handleResize()
    window.addEventListener('resize', this.handleResize)
    // Aplicar modo oscuro al body
    document.body.classList.toggle('modo-oscuro', this.modoOscuro)
    // Restaurar usuario desde localStorage (evita parpadeo de login)
    const usuarioGuardado = localStorage.getItem('usuario')
    if (usuarioGuardado) {
      this.usuario = JSON.parse(usuarioGuardado)
      this.isAuthenticated = true
    }
    // Confirmar sesión con backend
    try {
      const res = await axios.get('/Auth/ConfirmacionSession', { withCredentials: true })
      if (res.data.status === 'success') {
        this.isAuthenticated = true
        this.UsuariosRegistado = res.data.user
        await this.datosUsuarioRegistrado()
      } else {
        this.logout()
      }
    } catch (error) {
      console.error('Error verificando la sesión:', error)
    }
  },
  unmounted() {
    window.removeEventListener('resize', this.handleResize)
  }
}
</script>
<style>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.modo-claro .vueform__input::placeholder , ::placeholder {
  color: rgb(0, 0, 0) !important;
}
.modo-claro #hamburgesa, 
#narbar-movil{
  background-color: rgb(255, 255, 255);
  color:rgb(33, 37, 41);
}
.modo-oscuro {
  background-color: rgb(33, 37, 41);
  color: white;
}
.modo-oscuro #hamburgesa {
  background-color: rgb(33, 37, 41);
  color: white;
}
.modo-oscuro .card,
.modo-oscuro .table,
.modo-oscuro .navbar,
.modo-oscuro .dropdown-menu,
.modo-oscuro .modal-content {
  background-color: rgb(33, 37, 41) !important;
  color: white !important;  
}
.modo-oscuro .nav-link{
  color:white;
}
.modo-oscuro .dropdown-item{
  color:#ffffff;
}

.modo-oscuro .dropdown-item:hover, 
.modo-oscuro .nav-link.show, 
.modo-oscuro .container,
.modo-oscuro .table{
  background-color: rgb(33, 37, 41) !important;
  color: rgb(255, 255, 255) !important;
}
.modo-oscuro table > :not(caption) > * > * {
  background-color: rgb(33, 37, 41) !important;
  color: rgb(255, 255, 255) !important;
}
.modo-oscuro .form-control,
.modo-oscuro .input,
.modo-oscuro textarea,
.modo-oscuro select {
  background-color: rgb(33, 37, 41) !important;
  color: white !important;
  border-color: #c9c9c9 !important;
}
.modo-oscuro .vueform__input,
.modo-oscuro .multiselect-option,
.modo-oscuro.multiselect-dropdown ,
.modo-oscuro .vueform__input::placeholder ,
.modo-oscuro ::placeholder{
  background-color: rgb(33, 37, 41) !important;
  color: rgb(255, 255, 255) !important;
}
.modo-oscuro .nav-link:hover{
  color: rgb(201, 201, 201);
  
}

.modo-oscuro .multiselect-search ,
.modo-oscuro .multiselect-no-options{
  background-color: rgb(33,37,41);
  color: #ffffff;
}
.modo-oscuro .text-muted{
  color: rgb(255 255 255 / 75%) !important;
}

.modo-oscuro .placeholder {
  color: rgb(255, 255, 255);
}

.modo-oscuro body ,
.modo-oscuro  :root, [data-bs-theme=light]{
  background-color: rgb(33,37,41);
  color: #ffffff;
}
.modo-oscuro .loading-container{
  background-color: rgb(33,37,41);
}

body.modo-claro , 
.modo-claro :root, [data-bs-theme=light]{
  background-color: #ffffff;
  color: #000000;
}
.modo-oscuro .cargando-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    font-weight: bold;
    font-size: 1.2rem;
    color: #c5c5c5; 
  }
  .modo-claro .cargando-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    font-weight: bold;
    font-size: 1.2rem;
    color: #007bff; 
  }
</style>
