<template>
  <!-- Barra de búsqueda -->
  <b-navbar toggleable="lg" class="mb-4">
    <b-container >
      <b-navbar-brand href="#" style="color: white;">Empresas</b-navbar-brand>
      <b-form class="d-flex ms-auto" @submit.prevent>
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
    <b-table class="custom-rounded-table" :items="filtrarComputadoras" :fields="fields" dark responsive striped hover style="text-align: center;">
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
      <b-form>
        <b-row>
          <b-col md="6" class="mb-2">
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
          <b-button variant="success" class="me-2" @click="confirmarEditor">Guardar</b-button>
          <b-button variant="secondary" @click="cancelarEditor">Cancelar</b-button>
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

<script setup>
  import { ref, onMounted, computed } from 'vue'
  import axios from 'axios'
  // Datos reactivos
  const empresas = ref([])
  const seleccionarEmpresa = ref({
    emp_nombre: '',
    emp_direccion: '',
    sucursales: [{}]
  })
  const Buscador = ref('')
  const modalShow = ref(false)
  const eliminarConfirmaModal = ref(false)
  const editarConfirmacionModal = ref(false)
  const eliminarSucursalConfirmarModal = ref(false)
  const sucursalEliminar = ref(null)
  // Columnas de la tabla
  const fields = [
    { key: 'emp_nombre', label: 'Nombre',class:'bg-table' },
    { key: 'emp_direccion', label: 'Dirección' ,class:'bg-table'},
    { key: 'acciones', label: 'Acciones', class:'bg-table'}
  ]
  // Obtener empresas
  const cargarEmpresa = async () => {
    try {
      const res = await fetch('http://localhost/activos/Backend/index.php/EmpresaR')
      empresas.value = await res.json()
    } catch (err) {
      console.error('Error al obtener empresas:', err)
    }
  }
  // Filtro de búsqueda
  const filtrarComputadoras = computed(() => {
    if (!Buscador.value) return empresas.value
    return empresas.value.filter(empresa =>
      Object.values(empresa).some(val =>
        String(val).toLowerCase().includes(Buscador.value.toLowerCase())
      )
    )
  })
  // Agregar una nueva sucursal vacía
  const agregarSucursal = () => {
    seleccionarEmpresa.value.sucursales.push({
      suc_id: null,
      suc_nombre: '',
      suc_direccion: ''
    })
  }
  // Abrir modal de edición
  const editarEmpresa = (empresa) => {
    seleccionarEmpresa.value = {
      ...empresa,
      sucursales: empresa.sucursales || []
    }
    modalShow.value = true
  }
  // Cancelar edición
  const cancelarEditor = () => {
    modalShow.value = false
    seleccionarEmpresa.value = {
      emp_nombre: '',
      emp_direccion: '',
      sucursales: []
    }
  }
  // Confirmar guardar
  const confirmarEditor = () => {
    editarConfirmacionModal.value = true
  }
  // Guardar empresas
  const GuardarEmpresa = async () => {
    try {
      console.log(seleccionarEmpresa)
      const res = await axios.post(
        `http://localhost/activos/Backend/index.php/EmpresaR/ActializarEmpresa/${seleccionarEmpresa.value.emp_id}`,
        seleccionarEmpresa.value
      )
      if (res.data.status === 'updated') {
        const index = empresas.value.findIndex(u => u.emp_id === seleccionarEmpresa.value.emp_id)
        if (index !== -1) empresas.value[index] = { ...seleccionarEmpresa.value }
        modalShow.value = false
        editarConfirmacionModal.value = false
        alert('Empresa actualizada correctamente')
      } else {
        alert('Error al actualizar la empresa')
      }
    } catch (err) {
      console.error('Error al guardar:', err)
      alert('Error de conexión al servidor')
    }
  }
  // Confirmar eliminación de empresa
  const ConfirmarEliminacion = (empresa) => {
    seleccionarEmpresa.value = empresa
    eliminarConfirmaModal.value = true
  }
  // Eliminar empresa
  const EliminarEmpresa = async () => {
    try {
      const res = await axios.put(
        `/EmpresaR/Eliminar/${seleccionarEmpresa.value.emp_id}`
      )
      if (res.data.status === 'deleted') {
        empresas.value = empresas.value.filter(u => u.emp_id !== seleccionarEmpresa.value.emp_id)
        alert('Empresa eliminada correctamente')
      } else {
        alert('No se pudo eliminar la empresa')
      }
    } catch (err) {
      console.error('Error al eliminar:', err)
      alert('Error de conexión al servidor')
    } finally {
      eliminarConfirmaModal.value = false
    }
  }
  // Confirmar eliminación de sucursal
  const ConfirmarEliminacionSucursal = (sucursal) => {
    sucursalEliminar.value = sucursal
    eliminarSucursalConfirmarModal.value = true
  }
  // Eliminar sucursal
  const eliminarSucursal = async () => {
    try {
      const id = sucursalEliminar.value?.suc_id
      const res = await axios.put(
        `/EmpresaR/eliminarSucursal/${id}`
      )
      if (res.data.status === 'deleted') {
        seleccionarEmpresa.value.sucursales = seleccionarEmpresa.value.sucursales.filter(
          s => s.suc_id !== id
        )
        alert('Sucursal eliminada correctamente')
      } else {
        alert('No se pudo eliminar la sucursal')
      }
    } catch (err) {
      console.error('Error al eliminar:', err)
      alert('Error de conexión al servidor')
    } finally {
      eliminarSucursalConfirmarModal.value = false
      sucursalEliminar.value = null
    }
  }
  // Al montar componente
  onMounted(cargarEmpresa)
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

