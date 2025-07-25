<template>
  <!-- Barra de búsqueda -->
  <b-navbar toggleable="lg"  style="background-color: rgb(54, 97, 141) !important;" class="mb-4 bg-primary">
    <b-container>
      <b-navbar-brand class="text-white">Buscar Usuario</b-navbar-brand>
      <b-form class="d-flex ms-auto" @submit.prevent>
        <b-form-input
          v-model="Buscador"
          class="me-2"
          type="search"
          placeholder="Buscar por nombre, apellido o RUT..."
          @input="paginaActual = 1"
        />
      </b-form>
    </b-container>
  </b-navbar>

  <!-- Tabla de responsables -->
  <b-container>
    <b-table
      id="tabla-usuarios"
      :items="filtrarUsuarios"
      :fields="fields"
      responsive
      striped
      hover
      class="text-center"
    >
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
      :total-rows="totalResponsables"
      :per-page="porPagina"
      align="center"
      class="my-3"
      aria-controls="tabla-usuarios"
      first-number
      last-number
      size="md"
    />

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

    <!-- Modal de confirmación de edición -->
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

    <!-- Modal de edición -->
    <b-modal v-model="modalShow" title="Editar Responsable" size="lg" hide-footer>
      <b-form>
        <b-row>
          <b-col md="6" class="mb-2">
            <label>Nombre</label>
            <b-form-input v-model="UsuarioSeleccionado.usr_nombre" required />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>Apellido</label>
            <b-form-input v-model="UsuarioSeleccionado.usr_apellido" required />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>Correo</label>
            <b-form-input type="email" v-model="UsuarioSeleccionado.usr_correo" required />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>Teléfono</label>
            <b-form-input v-model="UsuarioSeleccionado.usr_telefono" />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>RUT</label>
            <b-form-input v-model="UsuarioSeleccionado.usr_rut" required />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>Sucursal</label>
            <b-form-input v-model="UsuarioSeleccionado.nombre_sucursal" />
          </b-col>
          <b-col md="6" class="mb-2">
            <label>Tipo de Responsable</label>
            <b-form-input v-model="UsuarioSeleccionado.nombre_tipo" />
          </b-col>
          <b-col v-if="UsuarioSeleccionado.nombre_tipo=== 'admin'" md="6" class="mb-2">
            <label>Clave</label>
            <b-form-input v-model="UsuarioSeleccionado.usr_clave" />
          </b-col>

        </b-row>
        <div class="text-end mt-3">
          <b-button variant="success" class="me-2" @click="confirmacionEditar">Guardar</b-button>
          <b-button variant="secondary" @click="cancelarEditar">Cancelar</b-button>
        </div>
      </b-form>
    </b-modal>
  </b-container>
</template>

<script>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'

export default {
  setup() {
    const usuarios = ref([])
    const totalResponsables = ref(0)
    const paginaActual = ref(1)
    const porPagina = 8
    const Buscador = ref('')
    const UsuarioSeleccionado = ref({})
    const modalShow = ref(false)
    const ConfirmaEliminarModal = ref(false)
    const editarConfirmaModal = ref(false)

    const fields = [
      { key: 'usr_id', label: 'ID' ,class:'bg-table'},
      { key: 'usr_rut', label: 'RUT',class:'bg-table' },
      { key: 'usr_nombre', label: 'Nombre' ,class:'bg-table'},
      { key: 'usr_apellido', label: 'Apellido',class:'bg-table' },
      { key: 'nombre_sucursal', label: 'Sucursal' ,class:'bg-table'},
      { key: 'acciones', label: 'Acciones',class:'bg-table' }
    ]

    const cargarUsuario = async () => {
      try {
        const { data } = await axios.get(`/Usuarios/listarResponsable/${paginaActual.value}`)
        usuarios.value = data.Responsable
        totalResponsables.value = data.total
      } catch (error) {
        console.error('Error al cargar responsables:', error)
      }
    }
    

    const filtrarUsuarios = computed(() => {
      if (!Buscador.value) return usuarios.value
      return usuarios.value.filter(respo =>
        Object.values(respo).some(val =>
          String(val).toLowerCase().includes(Buscador.value.toLowerCase())
        )
      )
    })

    
    watch(paginaActual, cargarUsuario)
    onMounted(cargarUsuario)
    
    const editarUsuario = (respo) => {
      UsuarioSeleccionado.value = { ...respo }
      modalShow.value = true
    }

    const cancelarEditar = () => {
      modalShow.value = false
      UsuarioSeleccionado.value = {}
    }

    const confirmacionEditar = () => {
      editarConfirmaModal.value = true
    }

    const actualizarUsuario = async () => {
      try {
        const res = await axios.post(
          `/Usuarios/ActualizarUsuario/${UsuarioSeleccionado.value.usr_id}`,
          UsuarioSeleccionado.value
        )
        if (res.data.status === 'updated') {
          alert('Responsable actualizado correctamente')
          modalShow.value = false
          editarConfirmaModal.value = false
          cargarUsuario()
        } else {
          alert('Error al actualizar el responsable')
        }
      } catch (err) {
        console.error('Error al guardar:', err)
        alert('Error de conexión al servidor')
      }
    }

    const confirmDelete = (respo) => {
      UsuarioSeleccionado.value = respo
      ConfirmaEliminarModal.value = true
    }

    const EliminarUsuario = async () => {
      try {
        const res = await axios.delete(`/Usuarios/EliminarUsuario/${UsuarioSeleccionado.value.usr_id}`)
        if (res.data.status === 'deleted') {
          alert('Responsable eliminado correctamente')
          cargarUsuario()
        } else {
          alert('No se pudo eliminar el responsable')
        }
      } catch (err) {
        console.error('Error al eliminar:', err)
        alert('Error de conexión al servidor')
      } finally {
        ConfirmaEliminarModal.value = false
      }
    }

    return {
      usuarios,
      totalResponsables,
      paginaActual,
      porPagina,
      Buscador,
      fields,
      filtrarUsuarios,
      modalShow,
      ConfirmaEliminarModal,
      editarConfirmaModal,
      UsuarioSeleccionado,
      cargarUsuario,
      editarUsuario,
      cancelarEditar,
      confirmacionEditar,
      actualizarUsuario,
      confirmDelete,
      EliminarUsuario
    }
  }
}
</script>
<style>
  .custom-rounded-table {
    border-radius: 12px;
    overflow: hidden; /* importante para que las esquinas internas también se redondeen */
  }
  .bg-table{
    --bs-table-color: #fcfcfc;
    --bs-table-bg: #294969;
    --bs-table-border-color: #294969 !important;
    --bs-table-striped-bg: #294969 !important;
    --bs-table-striped-color: #fffefe !important;
    --bs-table-active-bg: #294969 !important;
    --bs-table-active-color: #ffffff;
    --bs-table-hover-bg: #294969 !;
    --bs-table-hover-color: #ffffff;
  }
</style>
