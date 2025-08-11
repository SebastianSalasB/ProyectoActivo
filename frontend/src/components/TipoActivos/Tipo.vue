<template>
  <b-container fluid>
    <!-- Formulario de creación -->
    <b-col>
      <b-container fluid="sm" id="NAG" >
        <b-form-group label="Nombre de Tipo">
          <b-form-input v-model="Tipo.Nombre" id="Nombre"  name="Nombre" placeholder="" />
          <small v-if="errors.Nombre" class="text-danger">{{ errors.Nombre }}</small>
        </b-form-group>
      </b-container>
      <div class="text-center mt-4">
        <b-button
          id="Guarda"
          @click="guardarTipo" variant="outline-success"
          style=" border-radius: 18px; "
        ><i class="fa-solid fa-floppy-disk fa-lg"></i>
          Guardar
        </b-button>
      </div>
      <b-alert v-if="mensaje" variant="success" dismissible class="mt-3">
        {{ mensaje }}
      </b-alert>
    </b-col>
    <p></p>
    <!-- Tabla de Tipos -->
    <b-col>
      <b-container>
        <b-table hover small responsive
          :items="tipos"
          :fields="fields"
          class="w-100 custom-rounded-table"
          style="text-align: center;"
          >
          <template #cell(tip_descripcion)="data">
            {{ data.item.tip_descripcion }}
          </template>
          <template #cell(actions)="data">
            <b-button size="sm" variant="outline-success"  class="me-1" @click="EditarModal(data.item)">
              <i class="fa-solid fa-pen-to-square fa-lg "></i>
            </b-button>
            <b-button size="sm" variant="outline-danger" @click="confirmacionEliminado(data.item)">
              <i class="fa-solid fa-trash fa-lg"></i>
            </b-button>
          </template>
        </b-table>
      </b-container>
      <!-- Detalles -->
      <b-card v-if="selectedTipo" class="mt-4 w-100">
        <h5>Detalles del tipo seleccionado:</h5>
        <b-table
          small
          bordered
          :items="[
            { label: 'ID', value: selectedTipo.tip_id },
            { label: 'Descripción', value: selectedTipo.tip_descripcion }
          ]"
          :fields="['label', 'value']"
          responsive
          striped
          >
          <template #cell(label)="data">
            <strong>{{ data.value }}</strong>
          </template>
        </b-table>
        <b-button variant="secondary" size="sm" @click="selectedTipo = null">
          Cerrar detalles
        </b-button>
      </b-card>
      <!-- Modal de Edición -->
      <b-modal v-model="modalEditar" title="Editar Tipo"  size="md" hide-footer>
        <b-form @submit.prevent>
          <b-form-group label="Descripción">
            <b-form-input v-model="tipoAEditar.tip_descripcion" />
          </b-form-group>

          <div class="text-end mt-3">
            <b-button variant="success" class="me-2" @click="modalConfirmarEditar = true"><i class="fa-solid fa-circle-plus fa-lg" style="color: #ffffff;"></i> Guardar</b-button>
            <b-button variant="secondary" @click="cerrarModalEditar"><i class="fa-solid fa-circle-xmark fa-lg" style="color: #ffffff;"></i>  Cancelar</b-button>
          </div>
        </b-form>
      </b-modal>

      <!-- Modal Confirmación Edición -->
      <b-modal
        v-model="modalConfirmarEditar"
        title="Confirmar modificación"
        @ok="saveEdit"
        ok-title="Sí, guardar cambios"
        cancel-title="Cancelar"
        ok-variant="success"
      >
        ¿Está seguro de que desea guardar los cambios del tipo
        <strong>{{ tipoAEditar?.tip_descripcion }}</strong>?
      </b-modal>

      <!-- Modal Confirmación Eliminar -->
      <b-modal
        v-model="modalEliminar"
        title="Confirmar eliminación"
        @ok="EliminarTipo"
        ok-title="Eliminar"
        ok-variant="danger"
        cancel-title="Cancelar"
        size="md"
      >
        <p>
          ¿Está seguro que desea eliminar el tipo
          <strong>{{ tipoAEliminar?.tip_descripcion }}</strong>?
        </p>
      </b-modal>
    </b-col>
  </b-container>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      Tipo: { Nombre: '' },
      errors: {},
      mensaje: '',

      tipos: [],
      selectedTipo: null,

      modalEditar: false,
      modalEliminar: false,
      modalConfirmarEditar: false,

      tipoAEditar: {},
      tipoAEliminar: null,

      fields: [
        { key: 'tip_id', label: 'ID', sortable: true, thClass: 'text-center', tdClass: 'text-center' },
        { key: 'tip_descripcion', label: 'Descripción', sortable: true },
        { key: 'actions', label: 'Acciones', thClass: 'text-center', tdClass: 'text-center' }
      ]
    }
  },
  computed: {
    // Aquí puedes agregar propiedades computadas si necesitas
  },
  methods: {
    validadatos() {
      if (!this.Tipo.Nombre || this.Tipo.Nombre.trim().length < 3) {
        this.mensaje = 'El nombre del tipo debe tener al menos 3 caracteres.'
        return false
      }
      this.mensaje = ''
      return true
    },
    async guardarTipo() {
      if (!this.validadatos()) return

      try {
        const res = await axios.post(`/TiposActivos/CrearTipo`, {
          tip_descripcion: this.Tipo.Nombre.trim()
        })

        if (res.data.status === 'success') {
          this.mensaje = '✅ Tipo de Activo guardado con éxito'
          this.fetchTipos()   // Refrescar lista
          this.resetForm()    // Limpiar campos
        } else {
          this.mensaje = '⚠️ Error al guardar tipo de activo'
        }
      } catch (error) {
        console.error('Error en guardarTipo:', error)
        this.mensaje = '❌ Error del servidor al guardar tipo'
      }
    },
    resetForm() {
      this.Tipo.Nombre = ''
      this.mensaje = ''
    },
    async fetchTipos() {
      try {
        const res = await axios.get(`/TiposActivos`)
        this.tipos = res.data
      } catch (err) {
        console.error('Error al obtener tipos:', err)
      }
    },
    EditarModal(tipo) {
      this.tipoAEditar = { ...tipo }
      this.modalEditar = true
    },
    cerrarModalEditar() {
      this.modalEditar = false
      this.tipoAEditar = {}
    },
    async saveEdit() {
      try {
        const res = await axios.post(`/TiposActivos/ActualizarTipos/${this.tipoAEditar.tip_id}`, this.tipoAEditar)
        if (res.data.status === 'updated') {
          const index = this.tipos.findIndex(t => t.tip_id === this.tipoAEditar.tip_id)
          if (index !== -1) this.tipos.splice(index, 1, { ...this.tipoAEditar })
          alert('Tipo actualizado correctamente')
        } else {
          alert('Error al actualizar el tipo')
        }
      } catch (err) {
        console.error('Error al actualizar:', err)
        alert('Error en la conexión al servidor')
      } finally {
        this.modalEditar = false
        this.modalConfirmarEditar = false
      }
    },
    confirmacionEliminado(tipo) {
      this.tipoAEliminar = tipo
      this.modalEliminar = true
    },
    async EliminarTipo() {
      try {
        const res = await axios.delete(`/TiposActivos/EliminarTipo/${this.tipoAEliminar.tip_id}`)
        if (res.data.status === 'deleted') {
          this.tipos = this.tipos.filter(t => t.tip_id !== this.tipoAEliminar.tip_id)
        } else {
          alert('No se pudo eliminar el tipo')
        }
      } catch (err) {
        console.error('Error al eliminar:', err)
        alert('Error en la conexión al servidor')
      } finally {
        this.modalEliminar = false
        this.tipoAEliminar = null
      }
    }
  },
  mounted() {
    this.fetchTipos()
  }
}
</script>


<style>
.h5 {
  color: #000;
}
.modal-title {
  color: #000;
}

</style>
