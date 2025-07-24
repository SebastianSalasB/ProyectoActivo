<template>
  <b-col>
    <b-container fluid="sm" id="NAG" style="color: white;">
        <b-form-group label="Nombre de Tipo ">
          <b-form-input v-model="Tipo.Nombre" id="Nombre" name="Nombre" placeholder="" />
          <small v-if="errors.Nombre" class="text-danger">{{ errors.Nombre }}</small>
        </b-form-group>
    </b-container>
    <div class="text-center mt-4">
      <b-button
        id="Guarda"
        @click="guardarTipo"
        style="background-color: rgb(97 221 166); color: white; border-radius: 18px; border: 2px solid var(--Primery, #42B883);"
      > Guardar
      </b-button>
    </div>

    <b-alert v-if="mensaje" variant="success" dismissible class="mt-3">
      {{ mensaje }}
    </b-alert>
  </b-col>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      Tipo: {
        Nombre: '',
      },
      errors: {},
      mensaje: ''
    }
  },
  methods: {
    validadatos() {
      this.errors = {}

      if (!this.Tipo.Nombre) this.errors.Nombre = 'Nombre obligatorio'
      

      return Object.keys(this.errors).length > 0
    },
    async guardarTipo() {
      if (this.validadatos()) return

      try {
        await axios.post('/tiposactivos/CrearTipo', this.Tipo)
        this.mensaje = 'Tipo de Activo guardado con éxito'
        this.resetForm()
      } catch (error) {
        console.error(error)
        this.mensaje = 'Error al guardar'
      }
    },
    resetForm() {
      this.Tipo = {
        Nombre: '',
      }
      this.errors = {}
    }
  }
}
</script>