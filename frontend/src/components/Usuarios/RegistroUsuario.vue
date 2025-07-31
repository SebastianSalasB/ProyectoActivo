<template>
  <div>
    <b-container fluid="sm" id="NAG">
      <div
        v-for="(activo, index) in usuarios.activos"
        :key="index"
        class="p-3 mb-3"
        style="color: black;"
      >
        <!-- Nombres y Apellidos -->
        <b-row>
          <b-col sm>
            <b-form-group label="Nombres">
              <b-form-input style="color: black;"
                v-model="activo.user_nombre"
                :class="{ 'is-invalid': inputErrors[index]?.nombre }"
                placeholder="Sebastian"
                @input="validarCampo(index, 'nombre', activo.user_nombre)"
              />
            </b-form-group>
          </b-col>

          <b-col sm>
            <b-form-group label="Apellidos">
              <b-form-input style="color: black;"
                v-model="activo.user_apellido"
                :class="{ 'is-invalid': inputErrors[index]?.apellido }"
                placeholder="Salas"
                @input="validarCampo(index, 'apellido', activo.user_apellido)"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <!-- RUT y Correo -->
        <b-row>
          <b-col class="sm-2">
            <div v-for="(usuario, index) in usuarios.activos" :key="index" class="mb-3">
              <b-form-group label="RUT">
              <b-form-input style="color: black;"
                :value="activo.user_rut"
                @input="formatearRut($event, index)"
                :class="{ 'is-invalid': inputErrors[index]?.rut }"
                placeholder="Ej: 20.356.341-8"
              />
              </b-form-group>
            </div>
          </b-col>
          <b-col sm-2>
            <b-form-group label="Correo">
              <b-form-input style="color: black;"
                v-model="activo.user_correo"
                :class="{ 'is-invalid': inputErrors[index]?.correo }"
                type="correo"
                placeholder="ejemplo@correo.cl"
                @input="validarCampo(index, 'correo', activo.user_correo)"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <!-- Teléfono y Clave -->
        <b-row>
          <b-col sm>
            <b-form-group label="Teléfono">
              <b-form-input style="color: black;"
                v-model="activo.user_telefono"
                :class="{ 'is-invalid': inputErrors[index]?.telefono }"
                type="tel"
                placeholder="+569 1234 5678"
                @input="validarCampo(index, 'telefono', activo.user_telefono)"
              />
            </b-form-group>
          </b-col>
          <b-row>
            <b-col sm="1">
              <b-form-group label="ADMIN">
                <b-form-checkbox style="color: black;"
                v-model="activo.tipoactivo" />
              </b-form-group>
            </b-col>
            <b-col v-if="activo.tipoactivo">
              
              <b-form-group label="Clave">
                  <b-form-input style="color: black;"
                    v-model="activo.user_clave"
                    :class="{ 'is-invalid': inputErrors[index]?.clave }"
                    type="password"
                    placeholder="Clave segura"
                    @input="validarCampo(index, 'clave', activo.user_clave)"
                  />
                </b-form-group>
            </b-col>
          </b-row>
        </b-row>
        <!-- Empresa y Sucursal -->
        <b-row>
          <b-col sm>
            <b-form-group label="Empresa" style="color: black;">
              <b-form-select style="color: black;"
                v-model="activo.user_id_empresa"
                :options="empresas.map(e => ({ value: e.emp_id, text: e.emp_nombre }))"
                :class="{ 'is-invalid': inputErrors[index]?.empresa }"
                @change="value => onEmpresaChange(index, value)"
                placeholder="Seleccione empresa"
              />
            </b-form-group>
          </b-col>

          <b-col sm>
            <b-form-group label="Sucursal" >
              <b-form-select style="color: black;"
                placeholder="Seleccione sucursal"
                v-model="activo.user_id_sucursal"
                :options="sucursalesFiltradas(activo.user_id_empresa).map(s => ({ value: s.suc_id, text: s.suc_nombre }))"
                :class="{ 'is-invalid': inputErrors[index]?.sucursal }"
                @change="val => { activo.user_id_sucursal = val; validarCampo(index, 'sucursal', val) }"
                
                :disabled="!activo.user_id_empresa"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <b-button
          v-if="usuarios.activos.length > 1"
          variant="danger"
          class="mt-2"
          @click="eliminarActivo(index)"
        >
          Eliminar
        </b-button>
      </div>

      <b-button @click="anadirActivo" variant="success" class="me-2">+ Agregar otro usuario</b-button>
      <b-button @click="GuardarUsuario" variant="primary">Guardar</b-button>
    </b-container>

    <!-- Modal de éxito -->
    <b-modal
      id="modalExito"
      title="Registro exitoso"
      ok-only
      v-model="modalShow"
      style="color: black;"
    >
      Usuarios registrados correctamente.
    </b-modal>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'RegistroUsuario',
  data() {
    return {
      usuarios: {
        activos: [
          {
            user_nombre: '',
            user_apellido: '',
            user_rut: '',
            user_correo: '',
            user_telefono: '',
            user_clave: '',
            user_id_empresa: null,
            user_id_sucursal: null,
            tipoactivo: false 
          }
        ]
      },
      empresas: [],
      sucursales: [],
      modalShow: false,
      inputErrors: [
        {
          nombre: false,
          apellido: false,
          rut: false,
          correo: false,
          telefono: false,
          clave: false,
          empresa: false,
          sucursal: false
        }
      ]
    }
  },
  mounted() {
    this.cargarEmpresasYSucursales()
  },
  methods: {
    async cargarEmpresasYSucursales() {
      try {
        const [resEmpresas, resSucursales] = await Promise.all([
          axios.get('/Usuarios/listaE'),
          axios.get('/Usuarios/listaS')
        ]);
        console.log('Empresas crudas:', resEmpresas.data);
        console.log('Sucursales crudas:', resSucursales.data);

        this.empresas = resEmpresas.data.filter(e => e.emp_estado === 'activo');
        this.sucursales = resSucursales.data.filter(s => s.suc_estados === 'activo');

        console.log('Empresas filtradas:', this.empresas);
        console.log('Sucursales filtradas:', this.sucursales);
      } catch (error) {
        console.error('Error cargando empresas o sucursales:', error);
      }
    },
    sucursalesFiltradas(empId) {
      if (!empId) return []
      return this.sucursales.filter(s => String(s.suc_id_empresa) === String(empId))
    },
    onEmpresaChange(index, value) {
      this.usuarios.activos[index].user_id_empresa = value
      // Al cambiar empresa, limpiar sucursal
      this.usuarios.activos[index].user_id_sucursal = null
      this.validarCampo(index, 'empresa', value)
      this.validarCampo(index, 'sucursal', null)
    },
    anadirActivo() {
      this.usuarios.activos.push({
        user_nombre: '',
        user_apellido: '',
        user_rut: '',
        user_correo: '',
        user_telefono: '',
        user_clave: '',
        user_id_empresa: null,
        user_id_sucursal: null,
        tipoactivo: false // 👈 Aquí también
      })
      this.inputErrors.push({
        nombre: false,
        apellido: false,
        rut: false,
        correo: false,
        telefono: false,
        clave: false,
        empresa: false,
        sucursal: false
      })
    },
    eliminarActivo(index) {
      this.usuarios.activos.splice(index, 1)
      this.inputErrors.splice(index, 1)
    },
    validarRut(rut) {
      if (!rut) return false
      const cleanRut = rut.replace(/[^\dkK]/g, '').toUpperCase()
      if (cleanRut.length < 2) return false

      const cuerpo = cleanRut.slice(0, -1)
      const dv = cleanRut.slice(-1)

      let suma = 0
      let multiplo = 2

      for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo[i]) * multiplo
        multiplo = multiplo < 7 ? multiplo + 1 : 2
      }

      const dvEsperado = 11 - (suma % 11)
      const dvFinal = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString()
      console.log(cuerpo,"-",dvFinal)
      return dv.toUpperCase() === dvFinal
    },
    validarEmail(correo) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return re.test(correo)
    },
    validarCampo(index, campo, valor) {
      if (!this.inputErrors[index]) return

      const error = {}

      switch (campo) {
        case 'nombre':
        case 'apellido':
          error[campo] = valor.trim() === ''
          break
        case 'rut':
          error.rut = !this.validarRut(valor)
          break
        case 'correo':
          error.correo = !this.validarEmail(valor)
          break
        case 'telefono':
          error.telefono = valor.trim().length < 7
          break
        case 'clave':
          error.clave = valor.length < 8 || !/\d/.test(valor) || !/[A-Za-z]/.test(valor)
          break
        case 'empresa':
          error.empresa = !valor
          break
        case 'sucursal':
          error.sucursal = !valor
          break

      }

      this.inputErrors[index] = {
        ...this.inputErrors[index],
        ...error
      }
    },
    validadatos() {
      let valido = true

      this.usuarios.activos.forEach((activo, index) => {
        const errores = {
          nombre: activo.user_nombre.trim() === '',
          apellido: activo.user_apellido.trim() === '',
          rut: !this.validarRut(activo.user_rut),
          correo: !this.validarEmail(activo.user_correo),
          telefono: activo.user_telefono.trim().length < 7,
          clave:
            activo.tipoactivo &&
            (activo.user_clave.length < 8 ||
            !/\d/.test(activo.user_clave) ||
            !/[A-Za-z]/.test(activo.user_clave)),
          empresa: !activo.user_id_empresa,
          sucursal: !activo.user_id_sucursal
        }

        this.inputErrors[index] = errores

        if (Object.values(errores).some(e => e)) {
          valido = false
        }
      })

      return valido
    },
    formatearRut(value, index) {

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
      
      this.usuarios.activos.user_rut=this.usuarios.activos[index].user_rut
      console.log(this.usuarios.activos.user_rut)
      return this.usuarios.activos.user_rut
    },
    async GuardarUsuario() {
      if (!this.validadatos()) return

      const activosConDatos = this.usuarios.activos.map(activo => ({
        user_nombre: activo.user_nombre,
        user_apellido: activo.user_apellido,
        user_rut: activo.user_rut,
        user_correo: activo.user_correo,
        user_telefono: activo.user_telefono,
        user_id_empresa: activo.user_id_empresa,
        user_id_sucursal: activo.user_id_sucursal,
        user_id_tipos: activo.tipoactivo ? 1 : 2,
        ...(activo.tipoactivo && activo.user_clave ? { user_clave: activo.user_clave } : {})
      }))
      console.log(activosConDatos)

      try {
        console.log("Payload que se envía al backend:", {
          activos: activosConDatos
        });
        const response = await axios.post(
          '/Usuarios/CrearUsuario',
          { activos: this.usuarios.activos },
          { headers: { 'Content-Type': 'application/json' } }
        )

        console.log('Respuesta del servidor:', response.data)
        this.modalShow = true

        // Reiniciar formulario
        this.usuarios.activos = [
          {
            user_nombre: '',
            user_apellido: '',
            user_rut: '',
            user_correo: '',
            user_telefono: '',
            user_clave: '',
            user_id_empresa: null,
            user_id_sucursal: null,
            tipoactivo: false
          }
        ]
        this.inputErrors = [
          {
            nombre: false,
            apellido: false,
            rut: false,
            correo: false,
            telefono: false,
            clave: false,
            empresa: false,
            sucursal: false
          }
        ]
      } catch (error) {
        console.error('Error al registrar usuarios:', error)
      }
    }
  },
  computed:{

  }
}
</script>

<style scoped>
.is-invalid {
  border-color: red;
}
</style>
