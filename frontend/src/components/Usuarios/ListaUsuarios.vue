<template>
  <!-- Barra de búsqueda -->
  <b-navbar toggleable="lg" class="mb-4">
    <b-container>
      <b-navbar-brand>
        <h6 class="mb-0">Buscar Usuario</h6>
      </b-navbar-brand>
      <b-form class="d-flex ms-auto" @submit.prevent>
        <b-form-input 
          v-model="Buscador"
          class="me-2"
          type="search"
          placeholder="Buscar por nombre, apellido o RUT..."
        />
      </b-form>
    </b-container>
  </b-navbar>

  <!-- Tabla de responsables -->
  <b-container>
    <div v-if="cargando" class="cargando-overlay">
      <b-spinner class="mb-2" />
      <div>Cargando...</div>
    </div>
    <b-card class="shadow-sm">
      <b-table
        id="tabla-usuarios"
        :items="usuariosPaginados"
        :fields="fields"
        responsive hover 
        class="text-center custom-rounded-table"
      >
        <template #cell(usr_id)="{ value }">
          <span style="font-size: 1rem;">{{ value }}</span>
        </template>       

        <template #cell(usr_nombre)="{ item }"> 
          <div>{{ item.usr_nombre }}</div>
          <small>{{ item.usr_apellido }}</small>
        </template>

        <template #cell(acciones)="{ item }">
          <b-button size="sm" variant="outline-success" class="me-1" @click="editarUsuario(item)">
            <i class="fa-solid fa-pen-to-square fa-lg"></i>
          </b-button>
          <b-button size="sm" variant="outline-danger" @click="confirmDelete(item)">
            <i class="fa-solid fa-trash fa-lg"></i>
          </b-button>
        </template>
      </b-table>
    </b-card>
    <!-- Paginación -->
    <b-pagination
      v-model="paginaActual"
      :total-rows="filtrarUsuarios.length"
      :per-page="porPagina"
      align="center"
      class="my-3"
      aria-controls="tabla-usuarios"
      first-number
      last-number
      size="md"
    />

    <!-- Modal de edición -->
    <b-modal v-model="modalShow" title="Editar Usuario" size="lg" hide-footer>
      <b-form>
        <b-row>
          <b-col md="6" class="mb-2">            
            <b-form-group label="Nombre">
              <b-form-input 
                v-model="UsuarioSeleccionado.usr_nombre" 
                :class="{ 'is-invalid': inputErrors.nombre }" 
                @blur="validarCampo('nombre', UsuarioSeleccionado.usr_nombre)"
                required 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">            
            <b-form-group label="Apellido">
              <b-form-input 
                v-model="UsuarioSeleccionado.usr_apellido" 
                :class="{ 'is-invalid': inputErrors.apellido }" 
                @blur="validarCampo('apellido', UsuarioSeleccionado.usr_apellido)"
                required 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">            
            <b-form-group label="Correo">
              <b-form-input  
                type="email" 
                v-model="UsuarioSeleccionado.usr_correo" 
                :class="{ 'is-invalid': inputErrors.correo }" 
                @blur="validarCampo('correo', UsuarioSeleccionado.usr_correo)"
                required 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">            
            <b-form-group label="Teléfono">
              <b-form-input  
                v-model="UsuarioSeleccionado.usr_telefono" 
                :class="{ 'is-invalid': inputErrors.telefono }" 
                @blur="validarCampo('telefono', UsuarioSeleccionado.usr_telefono)"
                maxlength="9"
                required 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">            
            <b-form-group label="RUT">
              <b-form-input          
                v-model="UsuarioSeleccionado.usr_rut"
                @input="inputErrors.rut = false" 
                @blur="UsuarioSeleccionado.usr_rut = formatearRut(UsuarioSeleccionado.usr_rut)"
                placeholder="Ej: 20.356.341-8"
                :class="{ 'is-invalid': inputErrors.rut }" 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">
            <b-form-group label="Empresa">
              <b-form-select 
                v-model="UsuarioSeleccionado.usr_id_empresa"
                :options="empresas.map(e => ({ value: e.emp_id, text: e.emp_nombre }))"
                :class="{ 'is-invalid': inputErrors.empresa }" 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">
            <b-form-group label="Sucursal">
              <b-form-select
                placeholder="Seleccione sucursal"
                v-model="UsuarioSeleccionado.usr_id_sucursal"
                :options="sucursalesFiltradas(UsuarioSeleccionado.usr_id_empresa).map(s => ({ value: s.suc_id, text: s.suc_nombre }))"
                :class="{ 'is-invalid': inputErrors.sucursal }" 
              />
            </b-form-group>
          </b-col>

          <b-col md="6" class="mb-2">
            <b-form-group label="Tipo Usuario">
              <b-form-input v-model="UsuarioSeleccionado.nombre_tipo" disabled />
            </b-form-group>
          </b-col>

          <b-col v-if="UsuarioSeleccionado.nombre_tipo === 'admin'" md="6" class="mb-2">
            <b-form-group label="Clave nueva">
              <b-form-input
                v-model="UsuarioSeleccionado.usr_claveNueva"
                type="password"
                placeholder="Dejar vacío si no desea cambiar la clave"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <div class="text-end mt-3">
          <b-button variant="outline-success" class="me-2" @click="confirmacionEditar">
            <i class="fa-solid fa-floppy-disk fa-lg"></i> Guardar
          </b-button>
          <b-button variant="outline-secondary" @click="cancelarEditar">
            <i class="fa-solid fa-circle-xmark fa-lg"></i> Cancelar
          </b-button>
        </div>
      </b-form>
    </b-modal>

    <!-- Modal confirmación edición -->
    <b-modal
      v-model="editarConfirmaModal"
      title="Confirmar modificación"
      @ok="actualizarUsuario"
      ok-title="Sí, guardar cambios"
      cancel-title="Cancelar"
      ok-variant="success"
    >
      ¿Estás seguro de que deseas guardar los cambios?
    </b-modal>

    <!-- Modal confirmación eliminación -->
    <b-modal 
      v-model="ConfirmaEliminarModal"
      title="Confirmar eliminación"
      @ok="EliminarUsuario"
      ok-title="Sí, eliminar"
      cancel-title="Cancelar"
      ok-variant="danger"
    >
      ¿Estás seguro de que deseas eliminar este responsable?
    </b-modal>
  </b-container>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      usuarios: [],
      sucursales: [],
      empresas: [],
      paginaActual: 1,
      porPagina: 9,
      Buscador: '',
      UsuarioSeleccionado: {},
      modalShow: false,
      ConfirmaEliminarModal: false,
      editarConfirmaModal: false,
      fields: [
        { key: 'usr_rut', label: 'RUT' },
        { key: 'usr_nombre', label: 'Nombre' },
        { key: 'nombre_sucursal', label: 'Sucursal' },
        { key: 'acciones', label: 'Acciones' }
      ],
      cargando: false,
      inputErrors: {}
    }
  },
  computed: {
    filtrarUsuarios() {
      if (!this.Buscador) return this.usuarios
      return this.usuarios.filter(respo =>
        Object.values(respo).some(val =>
          String(val).toLowerCase().includes(this.Buscador.toLowerCase())
        )
      )
    },
    usuariosPaginados() {
      const inicio = (this.paginaActual - 1) * this.porPagina
      return this.filtrarUsuarios.slice(inicio, inicio + this.porPagina)
    }
  },
  methods: {
    async cargarUsuario() {
      this.cargando = true
      try {
        const res= await axios.get('/Usuarios/listarResponsable')
        this.usuarios = res.data
      } catch (error) {
        console.error('Error cargando sucursales:', error)
      }

      this.cargando = false
    },
    async cargarSucursales() {
      try {
        const res = await axios.get('/Activos/listaSucursal')
        this.sucursales = res.data
      } catch (error) {
        console.error('Error cargando sucursales:', error)
      }
    },
    async cargarEmpresa() {
      try {
        const res = await axios.get('/Activos/listaEmpresa', {
          withCredentials: true
        })
        this.empresas = res.data.filter(e => e.emp_estado === 'activo')
      } catch (error) {
        console.error('Error cargando empresas:', error)
      }
    },
    formatearRut(rut) {
      if (!rut) return ''
      rut = rut.replace(/[^0-9kK]/g, '').toUpperCase()
      let cuerpo = rut.slice(0, -1)
      let dv = rut.slice(-1)
      let cuerpoFormateado = ''
      while (cuerpo.length > 3) {
        cuerpoFormateado = '.' + cuerpo.slice(-3) + cuerpoFormateado
        cuerpo = cuerpo.slice(0, -3)
      }
      return `${cuerpo}${cuerpoFormateado}-${dv}`
    },
    sucursalesFiltradas(empId) {
      if (!empId) return []
      return this.sucursales.filter(s => String(s.suc_id_empresa) === String(empId))
    },
    validarRut(rut) {
      if (!rut) return false
      const cleanRut = rut.replace(/[^\dkK]/g, '').toUpperCase()
      if (cleanRut.length < 2) return false
      const cuerpo = cleanRut.slice(0, -1)
      const dv = cleanRut.slice(-1)
      let suma = 0, multiplo = 2
      for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo[i]) * multiplo
        multiplo = multiplo < 7 ? multiplo + 1 : 2
      }
      const dvEsperado = 11 - (suma % 11)
      const dvFinal = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString()
      return dv.toUpperCase() === dvFinal
    },
    validarEmail(correo) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)
    },
    validarCampo(campo, valor) {
      const errores = { ...this.inputErrors }
      switch (campo) {
        case 'nombre':
        case 'apellido':
          errores[campo] = valor.trim() === ''
          break
        case 'rut':
          errores.rut = !this.validarRut(valor)
          break
        case 'correo':
          errores.correo = !this.validarEmail(valor)
          break
        case 'telefono':
          errores.telefono = valor.trim().length < 7
          break
        case 'empresa':
          errores.empresa = !valor
          break
        case 'sucursal':
          errores.sucursal = !valor
          break
      }
      this.inputErrors = errores
    },
    validadatos() {
      const u = this.UsuarioSeleccionado
      this.inputErrors = {
        nombre: u.usr_nombre.trim() === '',
        apellido: u.usr_apellido.trim() === '',
        rut: !this.validarRut(u.usr_rut),
        correo: !this.validarEmail(u.usr_correo),
        telefono: u.usr_telefono.trim().length < 7,
        empresa: !u.usr_id_empresa,
        sucursal: !u.usr_id_sucursal
      }
      return !Object.values(this.inputErrors).some(e => e)
    },
    editarUsuario(respo) {
      this.UsuarioSeleccionado = { ...respo }
      this.inputErrors = {}
      this.modalShow = true
    },
    cancelarEditar() {
      this.modalShow = false
      this.UsuarioSeleccionado = {}
    },
    confirmacionEditar() {
      this.editarConfirmaModal = true
    },
    async actualizarUsuario() {
      if (!this.validadatos()) return
      const payload = {
        usr_nombre: this.UsuarioSeleccionado.usr_nombre,
        usr_apellido: this.UsuarioSeleccionado.usr_apellido,
        usr_rut: this.UsuarioSeleccionado.usr_rut,
        usr_correo: this.UsuarioSeleccionado.usr_correo,
        usr_telefono: this.UsuarioSeleccionado.usr_telefono,
        usr_id_tipos: this.UsuarioSeleccionado.usr_id_tipos,
        usr_id_sucursal: this.UsuarioSeleccionado.usr_id_sucursal,
        usr_estado: this.UsuarioSeleccionado.usr_estado
      }
      if (this.UsuarioSeleccionado.usr_claveNueva?.trim()) {
        payload.usr_clave = this.UsuarioSeleccionado.usr_claveNueva
      }
      try {
        const res = await axios.post(`/Usuarios/ActualizarUsuario/${this.UsuarioSeleccionado.usr_id}`, payload)
        if (res.data.status === 'updated') {
          alert('Responsable actualizado correctamente')
          this.modalShow = false
          this.editarConfirmaModal = false
          this.cargarUsuario()
        } else {
          alert('Error al actualizar el responsable')
        }
      } catch (err) {
        console.error('Error al guardar:', err)
        alert('Error de conexión al servidor')
      }
    },
    confirmDelete(respo) {
      this.UsuarioSeleccionado = respo
      this.ConfirmaEliminarModal = true
    },
    async EliminarUsuario() {
      try {
        const res = await axios.delete(`/Usuarios/EliminarUsuario/${this.UsuarioSeleccionado.usr_id}`)
        if (res.data.status === 'deleted') {
          alert('Responsable eliminado correctamente')
          this.cargarUsuario()
        } else {
          alert('No se pudo eliminar el responsable')
        }
      } catch (err) {
        console.error('Error al eliminar:', err)
        alert('Error de conexión al servidor')
      } finally {
        this.ConfirmaEliminarModal = false
      }
    }
  },
  watch: {
    Buscador() {
      this.paginaActual = 1
    }
  },
  mounted() {
    this.cargarUsuario()
    this.cargarSucursales()
    this.cargarEmpresa()
  }
}
</script>

<style scoped>
.custom-rounded-table {
  border-radius: 12px;
  overflow: hidden;
}
.cargando-overlay {
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
