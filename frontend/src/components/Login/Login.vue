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
            <b-form-group label="RUT" label-for="rut" class="fw-bold">
              <b-form-input
                id="rut"
                v-model="rut"
                :state="campoRutState"
                placeholder="Ej: 12.345.678-9"
              ></b-form-input>
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
              <b-button type="submit" variant="primary" class="btn-glow">Iniciar Sesión</b-button>
            </div>

            <!-- Mensaje error -->
            <b-alert v-if="loginError" variant="danger" class="mt-3" dismissible>
              {{ loginError }}
            </b-alert>
            <b-alert v-if="loginError" variant="danger" show class="mt-3 text-center">
              {{ loginError }}
            </b-alert>
          </b-form>
        </b-card>
      </b-col>
    </b-row>
  </b-container>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const rut = ref('');
const password = ref('');
const loginError = ref('');
const intentadoEnviar = ref(false);

const emit = defineEmits(['login-success']);

const validacionRut = (valor) => {
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
};

const validacionPassword = (valor) => {
  return typeof valor === 'string' && valor.length >= 6;
};

// Computed para estados visuales
const campoRutValido = computed(() => validacionRut(rut.value));
const campoPasswordValido = computed(() => validacionPassword(password.value));

const campoRutState = computed(() => {
  if (!intentadoEnviar.value) return null;
  return campoRutValido.value ? true : false;
});

const campoPasswordState = computed(() => {
  if (!intentadoEnviar.value) return null;
  return campoPasswordValido.value ? true : false;
});

const onSubmit = async () => {
  intentadoEnviar.value = true;
  loginError.value = '';

  if (!campoRutValido.value || !campoPasswordValido.value) {
    loginError.value = 'Por favor, completa todos los campos correctamente.';
    return;
  }

  try {
    const baseUrl = 'http://localhost/activos/Backend/index.php/Auth/';
    const dataToSend = {
      rut: rut.value,
      password: password.value
    };
    const response = await axios.post(baseUrl + 'IniciarSession', dataToSend, {
      headers: { 'Content-Type': 'application/json' },
      withCredentials: true
    });

    if (response.data && response.data.status === 'success') {
      loginError.value = '';
      emit('login-success', response.data.user);
    } else if (response.data && response.data.message) {
      loginError.value = response.data.message;
    } else {
      loginError.value = 'Error desconocido.';
    }

  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
      loginError.value = error.response.data.message;
    } else if (error.request) {
      loginError.value = 'Error de conexión: No se pudo contactar al servidor.';
    } else {
      loginError.value = 'Error: ' + error.message;
    }
  }
};
</script>

<style scoped>
.login-container {
  height: 100vh;
  background-color:rgb(59, 81, 130);
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
