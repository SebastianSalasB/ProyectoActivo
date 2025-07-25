<template>
  <div>
    <b-container fluid="sm" id="NAG">
      <b-col class="row row-cols-2">
        <b-form-group label="Nombre de la Empresa" style="color: white;">
          <b-form-input v-model="empresa.emp_nombre" id="emp_nombre" name="emp_nombre" />
          <small v-if="errors.emp_nombre" class="text-danger">{{ errors.emp_nombre }}</small>
        </b-form-group>

        <b-form-group label="Dirección de la Empresa" style="color: white;">
          <b-form-input 
          v-model="empresa.emp_direccion" 
          id="emp_direccion" name="emp_direccion" />
          <small v-if="errors.emp_direccion" class="text-danger">{{ errors.emp_direccion }}</small>
        </b-form-group>
      </b-col>
      
      <b-form-group label="Sucursales" style="color:white;">
        <div v-for="(sucursal, index) in empresa.sucursales" :key="index" class=" p-3 mb-3" >
          <hr />
          <b-form-group 
          label="Nombre de Sucursal" 
          style="color: white;">
            <b-form-input v-model="sucursal.suc_nombre" :name="`suc_nombre${index}`" />
          </b-form-group>

          <b-form-group 
          label="Dirección de Sucursal" 
          style="color: white;">
            <b-form-input v-model="sucursal.suc_direccion" :name="`suc_direccion${index}`" />
          </b-form-group>
          <hr />
          <b-button
            v-if="empresa.sucursales.length > 1"
            size="sm"
            variant="danger"
            @click="eliminarSucursal(index)"
          >Eliminar</b-button>
        </div>

        <b-button size="sm" variant="primary" @click="añadirSucursal">
          + Agregar otra sucursal
        </b-button>
        <small v-if="errors.sucursales" class="text-danger">{{ errors.sucursales }}</small>
      </b-form-group>

      <div class="text-center mt-4">
        <b-button
          id="Guarda"
          @click="crearEmpresa"
          style="background-color: rgb(97 221 166); color: white; border-radius: 18px; border: 2px solid var(--Primery, #42B883);"
        >Guardar</b-button>
      </div>

      <b-modal v-model="modalShow" title="Resultado del registro" ok-only ok-title="Aceptar">
        {{ mensaje }}
      </b-modal>
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
