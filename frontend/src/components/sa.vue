<template>
  <div>
    <b-container fluid="sm" id="NAG">
      <div
        v-for="(activo, index) in usuarios.activos"
        :key="index"
        class="border rounded p-3 mb-3 bg-dark"
        style="color: white;"
      >
        <!-- Nombres y Apellidos -->
        <b-row>
          <b-col sm>
            <b-form-group label="Nombres">
              <b-form-input
                v-model="activo.user_nombre"
                :class="{ 'is-invalid': inputErrors[index]?.nombre }"
                placeholder="Sebastian"
                @input="validarCampo(index, 'nombre', activo.user_nombre)"
              />
            </b-form-group>
          </b-col>

          <b-col sm>
            <b-form-group label="Apellidos">
              <b-form-input
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
          <b-col sm>
            <b-form-group label="RUT">
              <b-form-input
                v-model="activo.user_rut"
                :class="{ 'is-invalid': inputErrors[index]?.rut }"
                placeholder="12345678-9"
                @input="validarCampo(index, 'rut', activo.user_rut)"
              />
            </b-form-group>
          </b-col>

          <b-col sm>
            <b-form-group label="Correo">
              <b-form-input
                v-model="activo.user_email"
                :class="{ 'is-invalid': inputErrors[index]?.email }"
                type="email"
                placeholder="ejemplo@correo.cl"
                @input="validarCampo(index, 'email', activo.user_email)"
              />
            </b-form-group>
          </b-col>
        </b-row>

        <!-- Teléfono y Clave -->
        <b-row>
          <b-col sm>
            <b-form-group label="Teléfono">
              <b-form-input
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
                <b-form-checkbox v-model="activo.tipoactivo" />
              </b-form-group>
            </b-col>
            <b-col v-if="activo.tipoactivo">
              <b-form-group label="Clave">
                  <b-form-input
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
            <b-form-group label="Empresa">
              <b-form-select
                v-model="activo.user_id_empresa"
                :options="empresas.map(e => ({ value: e.emp_id, text: e.emp_nombre }))"
                :class="{ 'is-invalid': inputErrors[index]?.empresa }"
                @change="value => onEmpresaChange(index, value)"
                placeholder="Seleccione empresa"
              />
            </b-form-group>
          </b-col>

          <b-col sm>
            <b-form-group label="Sucursal">
              <b-form-select
                v-model="activo.user_id_sucursal"
                :options="sucursalesFiltradas(activo.user_id_empresa).map(s => ({ value: s.suc_id, text: s.suc_nombre }))"
                :class="{ 'is-invalid': inputErrors[index]?.sucursal }"
                @change="val => { activo.user_id_sucursal = val; validarCampo(index, 'sucursal', val) }"
                placeholder="Seleccione sucursal"
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

      <b-button @click="anadirActivo" variant="success" class="me-2">+ Agregar otro activo</b-button>
      <b-button @click="GuardarUsuario" variant="primary">Guardar</b-button>
    </b-container>

    <!-- Modal de éxito -->
    <b-modal
      id="modalExito"
      title="Registro exitoso"
      ok-only
      v-model="modalShow"
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
            user_email: '',
            user_telefono: '',
            user_clave: '',
            user_id_empresa: null,
            user_id_sucursal: null,
            tipoactivo: false
          }
        ]
      },
      empresas: [], // Listado de empresas
      sucursales: [], // Listado de sucursales
      modalShow: false,
      inputErrors: [
        {
          nombre: false,
          apellido: false,
          rut: false,
          email: false,
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
      // OJO: el campo debe coincidir con tu backend (aquí 'suc_id_empresa')
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
        user_email: '',
        user_telefono: '',
        user_clave: '',
        user_id_empresa: null,
        user_id_sucursal: null,
        user_id_tipos:false
      })
      this.inputErrors.push({
        nombre: false,
        apellido: false,
        rut: false,
        email: false,
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
    formatearRut(rut) {
      return rut.replace(/[.-]/g, '').toUpperCase()
    },
    validarRut(rut) {
      if (!rut) return false
      const cleanRut = this.formatearRut(rut)
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

      return dv.toUpperCase() === dvFinal
    },
    validarEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return re.test(email)
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
        case 'email':
          error.email = !this.validarEmail(valor)
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
          email: !this.validarEmail(activo.user_email),
          telefono: activo.user_telefono.trim().length < 7,
          clave:
            activo.user_clave.length < 8 ||
            !/\d/.test(activo.user_clave) ||
            !/[A-Za-z]/.test(activo.user_clave),
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
    async GuardarUsuario() {
      if (!this.validadatos()) {
        return
      }
      const activosConDatos = this.usuarios.activos.map(activo => ({
        user_nombre: activo.user_nombre,
        user_apellido: activo.user_apellido,
        user_rut: this.formatearRut(activo.user_rut),
        user_email: activo.user_email,
        user_telefono: activo.user_telefono,
        user_clave: activo.user_clave,
        user_id_tipos: activo.tipoactivo ? 1 : 2,
        user_id_empresa: activo.user_id_empresa,
        user_id_sucursal: activo.user_id_sucursal
      }))
      try {
        const response = await axios.post('/Usuarios/CrearUsuario', {
          activos: activosConDatos
        })
        console.log('Respuesta del servidor:', response.data)
        this.modalShow = true
        // Reiniciar formulario
        this.usuarios.activos = [
          {
            user_nombre: '',
            user_apellido: '',
            user_rut: '',
            user_email: '',
            user_telefono: '',
            user_clave: '',
            user_id_empresa: null,
            user_id_sucursal: null
          }
        ]
        this.inputErrors = [
          {
            nombre: false,
            apellido: false,
            rut: false,
            email: false,
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
  }
}
</script>

<style scoped>
.is-invalid {
  border-color: red;
}
</style>
