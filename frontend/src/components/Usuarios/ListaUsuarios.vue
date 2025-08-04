<template>
  <!-- Barra de búsqueda -->
  <b-navbar toggleable="lg" class="mb-4">
    <b-container>
      <b-navbar-brand style="text-align: left;" >Buscar Usuario</b-navbar-brand>
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
      <b-spinner variant="primary" class="mb-2" />
      <div>Cargando...</div>
    </div>
    <b-table
      id="tabla-usuarios"
      :items="usuariosPaginados"
      :fields="fields"
      responsive hover
      class="text-center custom-rounded-table"
    >
      <template #cell(usr_id)="data">
        <div>
          <span style="font-size: 1rem; margin-top: 25px; text-align: start; padding: 0.2rem 0.2rem;">{{ data.value }}</span>
        </div>
      </template>       
      <template #cell(usr_nombre)="data"> 
        <div>
          <span>{{ data.item.usr_nombre }}</span>
        </div>
        <div>
          <span style="font-size: 0.80rem; text-align: start;">{{ data.item.usr_apellido }}</span>
        </div>
      </template>
      <template #cell(acciones)="data">
        <b-button size="sm" variant="success" class="me-1" @click="editarUsuario(data.item)">
          <i class="fa-solid fa-pen-to-square fa-lg"></i>
        </b-button>
        <b-button size="sm" variant="danger" @click="confirmDelete(data.item)">
          <i class="fa-solid fa-trash fa-lg"></i>
        </b-button>
      </template>
    </b-table>

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
    <b-modal 
      v-model="modalShow" title="Editar Usuario" size="lg" hide-footer>
      <b-form >
        <b-row>
          <b-col md="6" class="mb-2">            
            <b-form-group label="Nombre">
              <b-form-input  v-model="UsuarioSeleccionado.usr_nombre" required />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">            
            <b-form-group label="Apellido">
              <b-form-input  v-model="UsuarioSeleccionado.usr_apellido" required />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">            
            <b-form-group label="Correo">
              <b-form-input  type="email" v-model="UsuarioSeleccionado.usr_correo" required />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">            
            <b-form-group label="Teléfono">
              <b-form-input  v-model="UsuarioSeleccionado.usr_telefono" required />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">            
            <b-form-group label="RUT">
              <b-form-input
                
                v-model="UsuarioSeleccionado.usr_rut"
                @blur="UsuarioSeleccionado.usr_rut = formatearRut(UsuarioSeleccionado.usr_rut)"
                placeholder="Ej: 20.356.341-8"
              />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">
            <b-form-group label="Empresa" >
              <b-form-select 
                v-model="UsuarioSeleccionado.usr_id_empresa"
                placeholder="selecciona empresa"
                :options="empresas.map(e => ({ value: e.emp_id, text: e.emp_nombre }))"
              />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">
            <b-form-group label="Sucursal" >
              <b-form-select style="color: black;"
                placeholder="Seleccione sucursal"
                v-model="UsuarioSeleccionado.usr_id_sucursal"
                :options="sucursalesFiltradas(UsuarioSeleccionado.usr_id_empresa).map(s => ({ value: s.suc_id, text: s.suc_nombre }))"
               
              />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">
            <b-form-group label="Tipo Usuario">
              <b-form-input  v-model="UsuarioSeleccionado.nombre_tipo" disabled />
            </b-form-group>
          </b-col>
          <b-col v-if="UsuarioSeleccionado.nombre_tipo=== 'admin'" md="6" class="mb-2">
            <b-form-group label="Clave nueva">
              <b-form-input
                v-if="UsuarioSeleccionado.nombre_tipo === 'admin'"
                v-model="UsuarioSeleccionado.usr_clave"
                type="password"
                placeholder="Dejar vacío si no desea cambiar la clave"
              />
            </b-form-group>
          </b-col>
        </b-row>
        <div class="text-end mt-3">
          <b-button variant="success" class="me-2" @click="confirmacionEditar"><i class="fa-solid fa-floppy-disk fa-lg" style="color: #ffffff;"></i>   Guardar</b-button>
          <b-button variant="secondary" @click="cancelarEditar"><i class="fa-solid fa-circle-xmark fa-lg" style="color: #ffffff;"></i>    Cancelar</b-button>
        </div>
      </b-form>
    </b-modal>
    <!-- Modal de confirmación de edición -->
    <b-modal style="color: black;"
      v-model="editarConfirmaModal"
      title="Confirmar modificación"
      @ok="actualizarUsuario"
      ok-title="Sí, guardar cambios"
      cancel-title="Cancelar"
      ok-variant="success" 
    >
      ¿Estás seguro de que deseas guardar los cambios?
    </b-modal>
    <!-- Modal de confirmación de eliminación -->
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
      sucursales:[],
      empresas:[],
      totalResponsables: 0,
      paginaActual: 1,
      porPagina: 8,
      Buscador: '',
      UsuarioSeleccionado: {},
      modalShow: false,
      ConfirmaEliminarModal: false,
      editarConfirmaModal: false,
      fields: [
        { key: 'usr_id', label: 'ID' },
        { key: 'usr_rut', label: 'RUT' },
        { key: 'usr_nombre', label: 'Nombre' },
        { key: 'nombre_sucursal', label: 'Sucursal' },
        { key: 'acciones', label: 'Acciones' }
      ],
      cargando:false
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
      const fin = inicio + this.porPagina
      return this.filtrarUsuarios.slice(inicio, fin)
    }
  },
  methods: {
    async cargarUsuario() {
      this.cargando = true
      try {
        const { data } = await axios.get(`/Usuarios/listarResponsable`)
        this.usuarios = data.Responsable       
        this.totalResponsables = data.Responsable.length

      } catch (error) {
        console.error('Error al cargar responsables:', error)
      }
      this.cargando= false
    },
    async cargarEmpresasYSucursales() {
      try {
        const [resEmpresas, resSucursales,resUsuarios] = await Promise.all([
          axios.get('/Usuarios/listaE'),
          axios.get('/Usuarios/listaS'),
          axios.get('/Usuarios/listarResponsable')
        ]);
        this.empresas = resEmpresas.data.filter(e => e.emp_estado === 'activo');
        this.sucursales = resSucursales.data.filter(s => s.suc_estados === 'activo');
        this.sucursalesIDempresa = this.sucursales.map(s=> s.suc_id_empresa)
   

      } catch (error) {
        console.error('Error cargando empresas o sucursales:', error);
      }
    },
    formatearRut(rut) {
      if (!rut) return ''
      rut = rut.replace(/[^0-9kK]/g, '').toUpperCase()

      let cuerpo = rut.slice(0, -1)
      let dv = rut.slice(-1)

      if (cuerpo.length < 1) return rut

      // Formatear cuerpo con puntos
      let cuerpoFormateado = ''
      while (cuerpo.length > 3) {
        cuerpoFormateado = '.' + cuerpo.slice(-3) + cuerpoFormateado
        cuerpo = cuerpo.slice(0, -3)
      }
      cuerpoFormateado = cuerpo + cuerpoFormateado
      console.log(`${cuerpoFormateado}-${dv}`)
      return `${cuerpoFormateado}-${dv}`
    },
    sucursalesFiltradas(empId) {
      if (!empId) return []
      return this.sucursales.filter(s => String(s.suc_id_empresa) === String(empId))
    },
    EmpresasFiltradas(){
      if(!this.UsuarioSeleccionado.usr_id_sucursal) return []
    },
    editarUsuario(respo) {
      this.UsuarioSeleccionado = { ...respo }
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
      // Solo si la clave no está vacía, la incluimos
      if (
        this.UsuarioSeleccionado.usr_clave &&
        this.UsuarioSeleccionado.usr_clave.trim() !== ''
      ) {
        payload.usr_clave = this.UsuarioSeleccionado.usr_clave
      }

      try {
        const res = await axios.post(
          `/Usuarios/ActualizarUsuario/${this.UsuarioSeleccionado.usr_id}`,
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
    },
    paginaActual() {
      this.cargarUsuario()
    }
  },
  mounted() {
    this.cargarUsuario()
    this.cargarEmpresasYSucursales()
  }
}
</script>

<style scoped>
  .custom-rounded-table {
    border-radius: 12px;
    overflow: hidden; /* importante para que las esquinas internas también se redondeen */
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
