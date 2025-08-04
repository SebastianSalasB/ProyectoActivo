<template>
  <!-- Barra de búsqueda -->
  <b-navbar toggleable="lg" class="mb-4">
    <b-container >
      <h6 href="#" >Empresas</h6>
      <b-form class="d-flex ms-auto" @submit.prevent >
        <b-form-input 
          v-model="Buscador"
          class="me-2"
          type="search"
          placeholder="Buscar..."
        />
      </b-form>
    </b-container>
  </b-navbar>
  <!-- Tabla de empresas -->
  <b-container >
    <b-table class="custom-rounded-table" :items="filtrarComputadoras" :fields="fields"responsive hover style="text-align: center;">
      <template #cell(acciones)="data">
        <b-button  variant="" size="sm" class="me-1" @click="editarEmpresa(data.item)">
          <i class="fa-solid fa-pen-to-square fa-lg" style="color: #258f24;"></i>
        </b-button>
        <b-button  variant="" size="sm" @click="ConfirmarEliminacion(data.item)">
          <i class="fa-solid fa-trash fa-lg" style="color: #8e0101;"></i>
        </b-button>
      </template>
    </b-table>
    <!-- Modal de edición -->
    <b-modal v-model="modalShow" title="Editar Empresa" size="lg" hide-footer>
      <b-form >
        <b-row>
          <b-col md="6" class="mb-2" >
            <label>Nombre</label>
            <b-form-input v-model="seleccionarEmpresa.emp_nombre" />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>Dirección</label>
            <b-form-input v-model="seleccionarEmpresa.emp_direccion" />
          </b-col>
        </b-row>
        <div v-if="seleccionarEmpresa.sucursales && seleccionarEmpresa.sucursales.length">
          <h5 class="mt-2">Sucursales</h5>
          <b-row
            v-for="(sucursal, index) in seleccionarEmpresa.sucursales"
            :key="sucursal.suc_id || index"
            class="mb-2"
          >
            <b-col md="5">
              <label>Nombre sucursal {{ index + 1 }}</label>
              <b-form-input v-model="sucursal.suc_nombre" />
            </b-col>
            <b-col md="5">
              <label>Dirección sucursal {{ index + 1 }}</label>
              <b-form-input v-model="sucursal.suc_direccion" />
            </b-col>
            <b-col md="1">
              <label>Eliminar</label>
              <b-button  variant="" size="sm" @click="ConfirmarEliminacionSucursal(sucursal)">
                <i class="fa-solid fa-trash fa-lg" style="color: #8e0101;"></i>
              </b-button>
            </b-col>
          </b-row>
        </div>
        <!-- Botón para agregar nueva sucursal -->
        <b-col md="3" class="mb-3">
          <b-button variant="success" size="sm" @click="agregarSucursal">
            <i class="fa-solid fa-plus"></i> Agregar Sucursal
          </b-button>
        </b-col>
        <div class="text-end mt-3">
          <b-button variant="success" class="me-2" @click="confirmarEditor"><i class="fa-solid fa-circle-plus fa-lg" style="color: #ffffff;"></i>  Guardar</b-button>
          <b-button variant="secondary" @click="cancelarEditor"><i class="fa-solid fa-circle-xmark fa-lg" style="color: #ffffff;"></i>  Cancelar</b-button>
        </div>
      </b-form>
    </b-modal>
    <!-- Modal de confirmación de edición -->
    <b-modal
      v-model="editarConfirmacionModal"
      title="Confirmar modificación"
      @ok="GuardarEmpresa"
      ok-title="Sí, guardar cambios"
      cancel-title="Cancelar"
      ok-variant="success"
      >
      ¿Estás seguro de que deseas guardar los cambios a la empresa <strong>{{ seleccionarEmpresa.emp_nombre }}</strong>?
    </b-modal>
    <!-- Modal de confirmación de eliminación empresas -->
    <b-modal
      v-model="eliminarConfirmaModal"
      title="Confirmar eliminación"
      @ok="EliminarEmpresa"
      ok-title="Sí, eliminar"
      cancel-title="Cancelar"
      ok-variant="danger"
      >
      ¿Estás seguro de que deseas eliminar la empresa <strong>{{ seleccionarEmpresa.emp_nombre }}</strong>?
    </b-modal>
    <!-- Modal de confirmación de eliminación sucursal -->
    <b-modal
      v-model="eliminarSucursalConfirmarModal"
      title="Confirmar eliminación"
      @ok="eliminarSucursal"
      ok-title="Sí, eliminar sucursal"
      cancel-title="Cancelar"
      ok-variant="danger"
      >
      ¿Estás seguro de que deseas eliminar la sucursal <strong>{{ sucursalEliminar?.suc_nombre }}</strong>?
    </b-modal>
  </b-container>
</template>
<script>
import axios from 'axios'
export default {
  data() {
    return {
      empresas: [],
      seleccionarEmpresa: {
        emp_nombre: '',
        emp_direccion: '',
        sucursales: [{}]
      },
      Buscador: '',
      modalShow: false,
      eliminarConfirmaModal: false,
      editarConfirmacionModal: false,
      eliminarSucursalConfirmarModal: false,
      sucursalEliminar: null,
      fields: [
        { key: 'emp_nombre', label: 'Nombre' },
        { key: 'emp_direccion', label: 'Dirección' },
        { key: 'acciones', label: 'Acciones' }
      ]
    }
  },
  computed: {
    filtrarComputadoras() {
      if (!this.Buscador) return this.empresas
      return this.empresas.filter(empresa =>
        Object.values(empresa).some(val =>
          String(val).toLowerCase().includes(this.Buscador.toLowerCase())
        )
      )
    }
  },
  methods: {
    async cargarEmpresa() {
      try {
        const res = await axios.get('/EmpresaR')
        this.empresas = res.data
      } catch (err) {
        console.error('Error al obtener empresas:', err)
      }
    },
    agregarSucursal() {
      this.seleccionarEmpresa.sucursales.push({
        suc_id: null,
        suc_nombre: '',
        suc_direccion: ''
      })
    },
    editarEmpresa(empresa) {
      this.seleccionarEmpresa = {
        ...empresa,
        sucursales: empresa.sucursales || []
      }
      this.modalShow = true
    },
    cancelarEditor() {
      this.modalShow = false
      this.seleccionarEmpresa = {
        emp_nombre: '',
        emp_direccion: '',
        sucursales: []
      }
    },
    confirmarEditor() {
      this.editarConfirmacionModal = true
    },
    async GuardarEmpresa() {
      try {
        const res = await axios.post(
          `http://localhost/activos/Backend/index.php/EmpresaR/ActializarEmpresa/${this.seleccionarEmpresa.emp_id}`,
          this.seleccionarEmpresa
        )
        if (res.data.status === 'updated') {
          const index = this.empresas.findIndex(e => e.emp_id === this.seleccionarEmpresa.emp_id)
          if (index !== -1) this.empresas.splice(index, 1, { ...this.seleccionarEmpresa })
          this.modalShow = false
          this.editarConfirmacionModal = false
          alert('Empresa actualizada correctamente')
        } else {
          alert('Error al actualizar la empresa')
        }
      } catch (err) {
        console.error('Error al guardar:', err)
        alert('Error de conexión al servidor')
      }
    },
    ConfirmarEliminacion(empresa) {
      this.seleccionarEmpresa = empresa
      this.eliminarConfirmaModal = true
    },
    async EliminarEmpresa() {
      try {
        const res = await axios.put(`/EmpresaR/Eliminar/${this.seleccionarEmpresa.emp_id}`)
        if (res.data.status === 'update') {
          alert('Empresa eliminada correctamente')
          this.cargarEmpresa() 
        } else if (res.data.status === 'delete') {
          alert('Empresa eliminada correctamente')
          this.cargarEmpresa() 
        }
        
      } catch (err) {
        console.error('Error al eliminar:', err)
        this.cargarEmpresa() 
      } finally {
        this.eliminarConfirmaModal = false
      }
    },
    ConfirmarEliminacionSucursal(sucursal) {
      this.sucursalEliminar = sucursal
      this.eliminarSucursalConfirmarModal = true
    },
    async eliminarSucursal() {
      try {
        const id = this.sucursalEliminar?.suc_id
        const res = await axios.put(`/EmpresaR/eliminarSucursal/${id}`)
        if (res.data.status === 'update') {
          this.seleccionarEmpresa.sucursales = this.seleccionarEmpresa.sucursales.filter(
            s => s.suc_id !== id
          )
          alert('Sucursal eliminada correctamente')
        } else {
          alert('No se pudo eliminar la sucursal')
        }
      } catch (err) {
        console.error('Error al eliminar sucursal:', err)
        alert('Error de conexión al servidor')
      } finally {
        this.eliminarSucursalConfirmarModal = false
        this.sucursalEliminar = null
      }
    }
  },
  mounted() {
    this.cargarEmpresa()
  }
}
</script>
<style scoped>
  .custom-rounded-table {
    border-radius: 12px;
    overflow: hidden; /* importante para que las esquinas internas también se redondeen */
  }
</style>

