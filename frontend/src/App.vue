<template>
  <div :class="modoOscuro ? 'modo-oscuro' : 'modo-claro'">
    <div v-if="!isAuthenticated" class="login-container">
      <Login @login-success="handleLoginSuccess" />
    </div>
    <div v-else style="height: 100vh;"> 
      <!-- NAVBAR: visible solo en móvil -->
      <b-navbar v-if="isMobile" toggleable="md" class="bg-barra shadow-sm" fixed="top">
        <b-navbar-brand class="text-white">
          Sistema de Activos
        </b-navbar-brand>
        <b-navbar-toggle target="nav-collapse">
          <i class="fa-solid fa-bars fa-xl text-white"></i>
        </b-navbar-toggle>
        <b-collapse id="nav-collapse" is-nav ref="navCollapse"  v-model="collapseVisible">
          <b-navbar-nav>
            <b-nav-item-dropdown text="Activos" left class="dropdown-dark">
              <b-dropdown-item @click="seleccionarVista('registro')">
                <i class="fa-solid fa-pen-to-square"></i> Activos
              </b-dropdown-item>
              <b-dropdown-item @click="seleccionarVista('lista')">
                <i class="fa-solid fa-list"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
            <b-nav-item-dropdown text="Tipo" left class="dropdown-dark">
              <b-dropdown-item @click="seleccionarVista('tipo')">
                <i class="fa-solid fa-pen-to-square"></i> Ver Tipos
              </b-dropdown-item>
            </b-nav-item-dropdown>
            <b-nav-item-dropdown text="Empresa" left class="dropdown-dark">
              <b-dropdown-item @click="seleccionarVista('Registro Empresa')">
                <i class="fa-solid fa-pen-to-square"></i> Empresa
              </b-dropdown-item>
              <b-dropdown-item @click="seleccionarVista('Empresa')">
                <i class="fa-solid fa-list"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
            <b-nav-item-dropdown text="Usuarios" left class="dropdown-dark">
              <b-dropdown-item @click="seleccionarVista('RegistroResponsable')">
                <i class="fa-solid fa-pen-to-square"></i> Usuarios
              </b-dropdown-item>
              <b-dropdown-item @click="seleccionarVista('Responsable')">
                <i class="fa-solid fa-list"></i> Lista
              </b-dropdown-item>
            </b-nav-item-dropdown>
          </b-navbar-nav>
          <b-navbar-nav class="ms-auto">
            <b-nav-item variant="outline-white" class=" mb-2" @click="UsuariosAjustes = true">
              <i class="fa-solid fa-gear fa-lg text-white"></i> Ajustes
            </b-nav-item>
            <b-nav-item @click="showConfirmLogout = true">
              <i class="fa-solid fa-right-from-bracket fa-xl text-danger"></i>
            </b-nav-item>
          </b-navbar-nav>
        </b-collapse>
      </b-navbar>
      <!-- SIDEBAR: visible solo en escritorio -->
      <div 
        v-if="!isMobile"
        class="d-flex flex-column flex-shrink-0 p-3 border-end"
        :class="modoOscuro ? 'bg-dark text-white' : 'bg-light'"
        style="width: 180px; height: 100vh; position: fixed; z-index: 1;"
      >
        <span class="fs-4 text-center mb-4">Sistema de Activos</span>
        <ul class="nav nav-pills flex-column mb-auto">
          <li>
            <a class="nav-link" data-bs-toggle="collapse" href="#collapseActivos" role="button">Activos</a>
            <div class="collapse show" id="collapseActivos">
              <ul class="list-unstyled ps-3 small">
                <li>
                  <a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'registro'">
                    <i class="fa-solid fa-pen-to-square"></i> Registro
                  </a>
                </li>
                <li>
                  <a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'lista'">
                    <i class="fa-solid fa-list"></i> Lista
                  </a>
                  </li>
              </ul>
            </div>
          </li>
          <li class="mt-2">
            <a class="nav-link" data-bs-toggle="collapse" href="#collapseEmpresa" role="button">Empresa</a>
            <div class="collapse show" id="collapseEmpresa">
              <ul class="list-unstyled ps-3 small">
                <li>
                  <a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'Registro Empresa'">
                    <i class="fa-solid fa-pen-to-square"></i> Registro
                  </a></li>
                <li>
                  <a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'Empresa'">
                    <i class="fa-solid fa-list"></i> Lista
                  </a>
                </li>
              </ul>
            </div>
          </li>

          <li class="mt-2">
            <a class="nav-link" data-bs-toggle="collapse" href="#collapseUsuarios" role="button">Usuarios</a>
            <div class="collapse show" id="collapseUsuarios">
              <ul class="list-unstyled ps-3 small">
                <li>
                  <a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'RegistroResponsable'">
                    <i class="fa-solid fa-pen-to-square"></i> Registro
                  </a>
                </li>
                <li>
                  <a href="#" class="nav-link nav-link-li" @click.prevent="currentView = 'Responsable'">
                    <i class="fa-solid fa-list"></i> Lista
                  </a>
                </li>
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
          <b-button variant="outline-secondary" class="w-100 mb-2" @click="UsuariosAjustes = true">
            <i class="fa-solid fa-gear"></i> Ajustes
          </b-button>
          <b-button variant="outline-danger" class="w-100 mb-2" @click="showConfirmLogout = true">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
          </b-button>
        </div>
      </div>
      <!-- CONTENIDO PRINCIPAL -->
      <main
        :class="['main', 'pt-4', modoOscuro ? 'modo-oscuro' : 'modo-claro']"
        :style="{ marginTop: isMobile ? '2rem' : '0', marginLeft: !isMobile ? '200px' : '0' }"
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
      <!-- Modal Editar Usuario -->
      <b-modal v-model="UsuariosAjustes" title="Ajustes de Usuario" centered>
        <b-form>
          <b-form-group label="Nombre y Apellido">
            <b-row>
              <b-col>
                <b-form-input v-model="datosUsuariosRegistado.usr_nombre" disabled placeholder="Nombre" />
              </b-col>
              <b-col>
                <b-form-input v-model="datosUsuariosRegistado.usr_apellido" disabled placeholder="Apellido" />
              </b-col>
            </b-row>
          </b-form-group>
          <b-row>
            <b-col sm="6">
              <b-form-group label="Correo">
                <b-form-input v-model="datosUsuariosRegistado.usr_correo" type="email" />
              </b-form-group>
            </b-col>
            <b-col sm="6">
              <b-form-group label="Teléfono">
                <b-form-input v-model="datosUsuariosRegistado.usr_telefono" type="tel" />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col>
              <b-form-group label="Contraseña (opcional)">
                <b-form-input v-model="datosUsuariosRegistado.usr_claveNueva" type="password" />
              </b-form-group>
            </b-col>
          </b-row>
          <b-form-group label="Tema">
            <b-form-checkbox v-model="modoOscuro" switches>Usar modo oscuro</b-form-checkbox>
          </b-form-group>
        </b-form>
        <template #footer>
          <b-button variant="secondary" @click="UsuariosAjustes = false">Cancelar</b-button>
          <b-button variant="primary" @click="guardarCambiosUsuario">Guardar cambios</b-button>
        </template>
      </b-modal>
    </div>
  </div>
</template>
<script>
import axios from 'axios'
import Login from './components/Login/Login.vue'
import ActivosRegistro from './components/Activos/ActivosRegistro.vue'
import TablaActivos from './components/Activos/TablaActivos.vue'
import Tipo from './components/TipoActivos/TIpo.vue'
import RegistroEmpresa from './components/Empresa/RegistroEmpresa.vue'
import ListaEmpresa from './components/Empresa/ListaEmpresa.vue'
import ListaRespo from './components/Usuarios/ListaUsuarios.vue'
import RegistroRespo from './components/Usuarios/RegistroUsuario.vue'
import '@fortawesome/fontawesome-free/css/all.css'

export default {
  components: {
    Login,
    ActivosRegistro,
    TablaActivos,
    Tipo,
    RegistroEmpresa,
    ListaEmpresa,
    ListaRespo,
    RegistroRespo
  },

  data() {
    return {
      currentView: 'lista',
      isAuthenticated: false,
      showConfirmLogout: false,
      UsuariosAjustes: false,
      collapseVisible: false,
      UsuariosRegistado: {},
      datosUsuariosRegistado: {}, // este objeto debe existir
      modoOscuro: localStorage.getItem('modoOscuro') === 'true',
      isMobile: window.innerWidth < 768
    }
  },

  computed: {
    // Aquí puedes agregar computadas si son necesarias
  },

  methods: {
    handleResize() {
      this.isMobile = window.innerWidth < 768
    },
    toggleModoOscuro() {
      this.modoOscuro = !this.modoOscuro
      localStorage.setItem('modoOscuro', this.modoOscuro)
    },
    handleLoginSuccess() {
      this.isAuthenticated = true
      this.currentView = 'lista'
    },
    confirmLogout() {
      axios.post('/Auth/CerrarSession', {}, { withCredentials: true })
        .finally(() => {
          this.showConfirmLogout = false
          setTimeout(() => {
            this.isAuthenticated = false
            this.currentView = 'login'
          }, 300)
        })
    },
    async datosUsuarioRegistrado() {
      try {
        const resUsuario = await axios.get('/Auth/ConfirmacionSession', { withCredentials: true });
        this.UsuariosRegistado = resUsuario.data.user;

        if (!this.UsuariosRegistado?.usr_id) {
          console.error("No se obtuvo el ID del usuario");
          return;
        }

        const res = await axios.get(`/Usuarios/DatosUsuario/${this.UsuariosRegistado.usr_id}`, {
          withCredentials: true
        });

        this.datosUsuariosRegistado = res.data[0];
        console.log('Datos usuario cargado:', res.data);

      } catch (error) {
        console.error('Error al obtener los datos del usuario:', error);
      }
    },
    async guardarCambiosUsuario() {
      const payload = {
        usr_nombre: this.datosUsuariosRegistado.usr_nombre,
        usr_apellido: this.datosUsuariosRegistado.usr_apellido,
        usr_rut: this.datosUsuariosRegistado.usr_rut,
        usr_correo: this.datosUsuariosRegistado.usr_correo,
        usr_telefono: this.datosUsuariosRegistado.usr_telefono,
        usr_id_tipos: this.datosUsuariosRegistado.usr_id_tipos,
        usr_id_sucursal: this.datosUsuariosRegistado.usr_id_sucursal,
        usr_estado: this.datosUsuariosRegistado.usr_estado
      }
      // Solo si la clave no está vacía, la incluimos
      if (
        this.datosUsuariosRegistado.usr_claveNueva &&
        this.datosUsuariosRegistado.usr_claveNueva.trim() !== ''
      ) {
        payload.usr_clave = this.datosUsuariosRegistado.usr_claveNueva
      }

      try {
        const res = await axios.post(
          `/Usuarios/ActualizarUsuario/${this.datosUsuariosRegistado.usr_id}`,
          payload,
          {
            headers: {
              'Content-Type': 'application/json'  
            }
          }
        )
        console.log("Payload enviado:", JSON.stringify(payload))
        if (res.data.status === 'updated') {
          alert('Responsable actualizado correctamente')
          console.log(res.data.message, res.data.usr_clave_hash)
          this.modalShow = false
          this.editarConfirmaModal = false
        } else {
          alert('Error al actualizar el responsable')
        }
      } catch (err) {
        console.error('Error al guardar:', err)
        alert('Error de conexión al servidor')
      }
    },
    seleccionarVista(vista) {
      this.currentView = vista;
      if (this.isMobile) {
        this.collapseVisible = false; // Oculta el menú visualmente

        // Cierra el collapse si está abierto (por si Bootstrap-Vue no lo hace bien solo con v-model)
        if (this.$refs.navCollapse && this.$refs.navCollapse.hide) {
          this.$refs.navCollapse.hide();
        }
        setTimeout(() => {
          this.collapseVisible = false;
        }, 100);
      }
    },
  },
  mounted() {

    this.datosUsuarioRegistrado()
    window.addEventListener('resize', this.handleResize)
    this.handleResize()
    axios.get('/Auth/ConfirmacionSession', { withCredentials: true })
      .then(res => {
        const data = res.data
        
        if (res.status === 200 && data.status === 'success') {
          this.isAuthenticated = true
          this.currentView = 'lista'
        } else {
          this.isAuthenticated = false
          this.currentView = 'login'
        }
      })
  },

  unmounted() {
    window.removeEventListener('resize', this.handleResize)
  }
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
}
.modo-oscuro .nav-link{
  color:white;
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
.modo-oscuro .nav-link:hover{
  color: gray;
}
.modo-oscuro .vueform__input::placeholder {
  color: rgb(255, 255, 255) !important;
}

</style>
