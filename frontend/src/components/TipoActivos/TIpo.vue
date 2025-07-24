<template>
  <b-container fluid>
    <!-- Formulario de creación -->
    <b-col>
      <b-container fluid="sm" id="NAG" style="color: white;">
        <b-form-group label="Nombre de Tipo">
          <b-form-input v-model="Tipo.Nombre" id="Nombre" name="Nombre" placeholder="" />
          <small v-if="errors.Nombre" class="text-danger">{{ errors.Nombre }}</small>
        </b-form-group>
      </b-container>

      <div class="text-center mt-4">
        <b-button
          id="Guarda"
          @click="guardarTipo"
          style="background-color: rgb(97 221 166); color: white; border-radius: 18px; border: 2px solid var(--Primery, #42B883);"
        >
          Guardar
        </b-button>
      </div>

      <b-alert v-if="mensaje" variant="success" dismissible class="mt-3">
        {{ mensaje }}
      </b-alert>
    </b-col>

    <!-- Tabla de Tipos -->
    <b-col>
      <b-container>
        <b-table
          striped
          hover
          dark
          responsive
          :items="tipos"
          :fields="fields"
          small
          class="w-100"
          style="text-align: center;"
        >
          <template #cell(tip_descripcion)="data">
            {{ data.item.tip_descripcion }}
          </template>

          <template #cell(actions)="data">
            <b-button size="sm" variant=""  class="me-1" @click="EditarModal(data.item)">
              <i class="fa-solid fa-pen-to-square fa-lg" style="color: #258f24;"></i>
            </b-button>
            <b-button size="sm" variant="" @click="confirmacionEliminado(data.item)">
              <i class="fa-solid fa-trash fa-lg" style="color: #8e0101;"></i>
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
      <b-modal v-model="modalEditar" title="Editar Tipo" style="color: black;" size="md" hide-footer>
        <b-form @submit.prevent>
          <b-form-group label="Descripción">
            <b-form-input v-model="tipoAEditar.tip_descripcion" />
          </b-form-group>

          <div class="text-end mt-3">
            <b-button variant="success" class="me-2" @click="modalConfirmarEditar = true">Guardar</b-button>
            <b-button variant="secondary" @click="cerrarModalEditar">Cancelar</b-button>
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
        @ok="deleteTipo"
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

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const BASE_URL = 'http://localhost/activos/Backend/index.php/tiposactivos'

const Tipo = ref({ Nombre: '' })
const errors = ref({})
const mensaje = ref('')

const tipos = ref([])
const selectedTipo = ref(null)

const modalEditar = ref(false)
const modalEliminar = ref(false)
const modalConfirmarEditar = ref(false)

const tipoAEditar = ref({})
const tipoAEliminar = ref(null)

const fields = [
  { key: 'tip_id', label: 'ID', sortable: true, thClass: 'text-center', tdClass: 'text-center' },
  { key: 'tip_descripcion', label: 'Descripción', sortable: true },
  { key: 'actions', label: 'Acciones', thClass: 'text-center', tdClass: 'text-center' }
]

// Validar datos del formulario
const validadatos = () => {
  if (!Tipo.value.Nombre || Tipo.value.Nombre.trim().length < 3) {
    mensaje.value = 'El nombre del tipo debe tener al menos 3 caracteres.'
    return false
  }
  return true
}
// Guardar nuevo tipo
const guardarTipo = async () => {
  if (!validadatos()) return

  try {
    const res = await axios.post(`${BASE_URL}/CrearTipo`, {
      tip_descripcion: Tipo.value.Nombre.trim()
    })
    
    if (res.data.status === 'success') {
      mensaje.value = '✅ Tipo de Activo guardado con éxito'
      fetchTipos()           // Refrescar lista
      resetForm()            // Limpiar campos
    } else {
      mensaje.value = '⚠️ Error al guardar tipo de activo'
    }
  } catch (error) {
    console.error('Error en guardarTipo:', error)
    mensaje.value = '❌ Error del servidor al guardar tipo'
  }
}
const resetForm = () => {
  Tipo.value.Nombre = ''
  mensaje.value = ''
}
// Obtener todos los tipos
const fetchTipos = async () => {
  try {
    const res = await fetch(`${BASE_URL}`)
    tipos.value = await res.json()
  } catch (err) {
    console.error('Error al obtener tipos:', err)
  }
}
// Editar tipo
const EditarModal = (tipo) => {
  tipoAEditar.value = { ...tipo }
  modalEditar.value = true
}
const cerrarModalEditar = () => {
  modalEditar.value = false
  tipoAEditar.value = {}
}
// Guardar edición
const saveEdit = async () => {
  try {
    const res = await axios.post(`${BASE_URL}/ActualizarTipos/${tipoAEditar.value.tip_id}`, tipoAEditar.value)
    if (res.data.status === 'updated') {
      const index = tipos.value.findIndex(t => t.tip_id === tipoAEditar.value.tip_id)
      if (index !== -1) tipos.value[index] = { ...tipoAEditar.value }
      alert('Tipo actualizado correctamente')
    } else {
      alert('Error al actualizar el tipo')
    }
  } catch (err) {
    console.error('Error al actualizar:', err)
    alert('Error en la conexión al servidor')
  } finally {
    modalEditar.value = false
    modalConfirmarEditar.value = false
  }
}
// Confirmación de eliminación
const confirmacionEliminado = (tipo) => {
  tipoAEliminar.value = tipo
  modalEliminar.value = true
}
// Eliminar tipo
const deleteTipo = async () => {
  try {
    const res = await axios.delete(`${BASE_URL}/EliminarTipo/${tipoAEliminar.value.tip_id}`)
    if (res.data.status === 'deleted') {
      tipos.value = tipos.value.filter(t => t.tip_id !== tipoAEliminar.value.tip_id)
    } else {
      alert('No se pudo eliminar el tipo')
    }
  } catch (err) {
    console.error('Error al eliminar:', err)
    alert('Error en la conexión al servidor')
  } finally {
    modalEliminar.value = false
    tipoAEliminar.value = null
  }
}
// Cargar tipos al iniciar
onMounted(fetchTipos)
</script>

<style>
.h5 {
  color: #000;
}
.modal-title {
  color: #000;
}
</style>
