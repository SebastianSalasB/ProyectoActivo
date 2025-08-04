<template>
  <div>
    <div v-for="(usuario, index) in usuarios.activos" :key="index" class="mb-3">
      <label>RUT del Usuario:</label>
      <b-form-input
        :value="usuario.user_rut"
        @input="formatearRut($event, index)"
        placeholder="Ej: 20.356.341-8"
        />
      <p class="text-white">RUT guardado: {{ usuario.user_rut }}</p>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      usuarios: {
        activos: [
          { user_rut: '' },
          { user_rut: '' },
        ]
      }
    }
  },
  methods: {
    formatearRut(value, index) {
      // Limpia lo que no sea número o k/K
      let rut = value.replace(/[^\dkK]/g, '').toUpperCase()
      if (!rut) {
        this.usuarios.activos[index].user_rut = ''
        return
      }
      let cuerpo = rut.slice(0, -1)
      let dv = rut.slice(-1)
      // Formatear con puntos cada 3 cifras desde el final
      let formateado = ''
      let i = cuerpo.length
      while (i > 3) {
        formateado = '.' + cuerpo.slice(i - 3, i) + formateado
        i -= 3
      }
      formateado = cuerpo.slice(0, i) + formateado
      this.usuarios.activos[index].user_rut = `${formateado}-${dv}`
    }
  }
}
</script>
