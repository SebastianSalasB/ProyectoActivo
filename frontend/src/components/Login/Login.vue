<template>
  <b-container fluid class="login-container">
    <b-row class="h-100">
      <!-- Panel login -->
      <b-col  class="login-panel d-flex flex-column justify-content-center align-items-center px-4">
        <b-card class="login-card shadow-lg rounded-lg p-4"  text-variant="white">
          <div class="text-center mb-4">
            <h2 class="fw-bold">Sistema de Activos</h2>
          </div>
          <b-form @submit.prevent="onSubmit" novalidate>
            <!-- RUT -->
            <b-form-group label="RUT"  class="fw-bold">
              <b-form-input
                id="rut"
                type="text"
                :value="rutFormateado"
                v-model="rutFormateado"
                placeholder="Ej: 12.345.678-9"
                :state="campoRutState"
              />
              <b-form-invalid-feedback>
                Ingresa un RUT válido (ej: 12.345.678-9).
              </b-form-invalid-feedback>
            </b-form-group>

            <!-- Password -->
            <b-form-group label="Contraseña" label-for="password" class="fw-bold">
              <b-form-input
                id="password"
                type="password"
                v-model="password"
                :state="campoPasswordState"
                placeholder="Mínimo 6 caracteres"
              ></b-form-input>
              <b-form-invalid-feedback>
                La contraseña debe tener al menos 6 caracteres.
              </b-form-invalid-feedback>
            </b-form-group>

            <!-- Botón -->
            <div class="d-grid">
              <b-button
                type="submit"
                variant="primary"
                class="btn-glow"
                :disabled="cargando"
              >
                {{ cargando ? 'Ingresando...' : 'Iniciar Sesión' }}
              </b-button>
            </div>

            <!-- Mensaje error -->
            <div class="text-white mt-2" v-if="loginError"> {{ loginError }}</div>
        
          </b-form> 
            
        </b-card>
      </b-col>
    </b-row>
  </b-container>
</template>
<script>
import axios from 'axios';
export default {
  data() {
    return {
      rutLimpio: '',
      password: '',
      loginError: '',
      intentadoEnviar: false,
      cargando: false
    };
  },
  computed: {
    rutFormateado: {
      get() {
        const clean = this.rutLimpio.replace(/[^\dkK]/gi, '').toUpperCase();
        if (clean.length <= 1) return clean;

        const cuerpo = clean.slice(0, -1);
        const dv = clean.slice(-1);

        let formateado = '';
        let i = cuerpo.length;

        while (i > 3) {
          formateado = '.' + cuerpo.slice(i - 3, i) + formateado;
          i -= 3;
        }
        formateado = cuerpo.slice(0, i) + formateado;

        return `${formateado}-${dv}`;
      },
      set(val) {
        this.rutLimpio = val.replace(/[^\dkK]/gi, '').toUpperCase();
      }
    },
    campoRutValido() {
      return this.validacionRut(this.rutFormateado);
    },
    campoPasswordValido() {
      return this.validacionPassword(this.password);
    },
    campoRutState() {
      if (!this.intentadoEnviar) return null;
      return this.campoRutValido ? true : false;
    },
    campoPasswordState() {
      if (!this.intentadoEnviar) return null;
      return this.campoPasswordValido ? true : false;
    }
  },
  methods: {
    validacionRut(valor) {
      if (!valor || typeof valor !== 'string') return false;

      const rutClean = valor.replace(/[^0-9kK]/g, '').toUpperCase();
      if (rutClean.length < 8 || !/^[0-9]+[kK0-9]{1}$/.test(rutClean)) return false;

      const cuerpo = rutClean.slice(0, -1);
      const dv = rutClean.slice(-1);

      let suma = 0;
      let multiplicador = 2;

      for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo.charAt(i)) * multiplicador;
        multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
      }

      const dvEsperado = 11 - (suma % 11);
      const dvFinal = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();

      return dv === dvFinal;
    },
    validacionPassword(valor) {
      return typeof valor === 'string' && valor.length >= 6;
    },
    async onSubmit() {       
      this.intentadoEnviar = true;
      this.loginError = '';
      this.cargando = true;
      if (!this.campoRutValido || !this.campoPasswordValido) { 
        this.loginError = 'Por favor, completa todos los campos correctamente.';
        this.cargando = false;
        return;
      }
      try { const response = await axios.post('/Auth/IniciarSession', {rut: this.rutFormateado, password: this.password}, 
        {
          headers: { 'Content-Type': 'application/json' },
          withCredentials: true,
          validateStatus: () => true
        })

        if (response.data.status === 'success') {
          this.loginError = '';
          this.rutFormateado = '';
          this.password = '';
          this.$emit('login-success', response.data.user);
        } else if (response.data.message === 'Credenciales inválidas.') {
            this.loginError = 'RUT o contraseña incorrectos.';
          } else {
            this.loginError = 'Error desconocido.';
        }
      } catch (error) {
        this.loginError = 'Error de red o servidor.';
        console.error(error);
      }finally {
        this.cargando = false;
      }
    }
  },
  mounted() {
    
  }
};
</script>
<style scoped>
.login-container {
  height: 100vh;
  background-size: cover;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background-image: url(../Activos/3409297.jpg);
}
.object-cover {
  object-fit: cover;
}
.login-panel {
   /* bg-dark */
  color: white;
}
.login-card{
  width: 100%;
  max-width: 400px;
  border-radius: 16px;
  border: none;
  background-color: rgb(84, 112, 186,0.4);
}
.b-form-input,
.b-button {
  font-size: 1rem;
}
.btn-glow {
  background-color: #0d6efd;
  color: white;
  border: none;
  transition: box-shadow 0.3s ease;
}
.btn-glow:hover {
  box-shadow: 0 0 10px rgb(13, 110 , 253,0.8);
}
@media (max-width: 768px) {
  .login-panel {
    padding: 2rem;
  }
  .login-panel .b-card {
    padding: 1.5rem;
  }
  .login-card{
    padding: 2rem 1.5rem;
  }
}
</style>