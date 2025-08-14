<template>
  <div>
    <b-container fluid="sm" id="NAG">
      <h2>Registrar Empresa</h2>
      <b-card class="shadow-sm">
        <b-col class="row row-cols-2">
          <b-form-group label="Nombre de la Empresa" >
            <b-form-input v-model="empresa.emp_nombre" id="emp_nombre" name="emp_nombre" />
            <small v-if="errors.emp_nombre" class="text-danger">{{ errors.emp_nombre }}</small>
          </b-form-group>
          <b-form-group label="Dirección de la Empresa" >
            <b-form-input 
            v-model="empresa.emp_direccion" 
            id="emp_direccion" name="emp_direccion" />
            <small v-if="errors.emp_direccion" class="text-danger">{{ errors.emp_direccion }}</small>
          </b-form-group>
        </b-col>
          <b-form-group >
            <h5> Sucursal</h5>
            <div v-for="(sucursal, index) in empresa.sucursales" :key="index" class=" p-3 mb-2" >
              <hr />
              <b-form-group label="Nombre de Sucursal" >
              <b-form-input v-model="sucursal.suc_nombre" :name="`suc_nombre${index}`" />
              </b-form-group>
              <b-form-group label="Dirección de Sucursal">
                <b-form-input v-model="sucursal.suc_direccion" :name="`suc_direccion${index}`" />
              </b-form-group>
              <hr />
              <b-button
                v-if="empresa.sucursales.length > 1"
                size="sm"
                variant="danger"
                @click="eliminarSucursal(index)"
              ><i class="fa-solid fa-circle-xmark fa-lg"></i>   Eliminar</b-button>
            </div>
            <small v-if="errors.sucursales" class="text-danger">{{ errors.sucursales }}</small>
          </b-form-group>
          <b-button  variant="outline-primary" @click="añadirSucursal" class="me-2">
            <i class="fa-solid fa-circle-plus fa-lg"></i>Agregar otra sucursal  </b-button>
          <b-button id="Guarda" @click="crearEmpresa" variant="outline-success">
            <i class="fa-solid fa-floppy-disk fa-lg" ></i>Guardar</b-button>
        <b-modal v-model="modalShow" title="Resultado del registro" ok-only ok-title="Aceptar">
          {{ mensaje }}
        </b-modal>
      </b-card>
    </b-container>
  </div>
</template>
<script>
import axios from 'axios'

export default {
  data() {
    return {
      empresa: {
        emp_nombre: '',
        emp_direccion: '',
        sucursales: [
          { suc_nombre: '', suc_direccion: '' }
        ]
      },
      errors: {},
      mensaje: '',
      modalShow: false
    }
  },
  methods: {
    añadirSucursal() {
      this.empresa.sucursales.push({ suc_nombre: '', suc_direccion: '' })
    },
    eliminarSucursal(index) {
      this.empresa.sucursales.splice(index, 1)
    },
    validadatos() {
      this.errors = {}

      if (!this.empresa.emp_nombre) this.errors.emp_nombre = 'El nombre es obligatorio'
      if (!this.empresa.emp_direccion) this.errors.emp_direccion = 'La dirección es obligatoria'

      const sucursalesInvalidas = this.empresa.sucursales.some(
        s => !s.suc_nombre || !s.suc_direccion
      )
      if (sucursalesInvalidas) this.errors.sucursales = 'Todas las sucursales deben tener nombre y dirección'

      return Object.keys(this.errors).length > 0
    },
    async crearEmpresa() {
      if (this.validadatos()) return

      try {
        const res = await axios.post('/EmpresaR/CrearEmpresa', this.empresa)
        this.resetForm()
        this.mensaje = 'Guardado con exito'
      } catch (error) {
        this.mensaje = 'Error al guardar la empresa'
        console.error(error)
      } finally {
        this.modalShow = true
      }
    },
    resetForm() {
      this.empresa = {
        emp_nombre: '',
        emp_direccion: '',
        sucursales: [
          { suc_direccionnombre: '', suc_direccion: '' }
        ]
      }
      this.errors = {}
    }
  }
}
</script>
<style>
.bg-light {
  --bs-bg-opacity: 1;
  background-color: rgb(255 255 255 / 50%) !important;
}

</style>
