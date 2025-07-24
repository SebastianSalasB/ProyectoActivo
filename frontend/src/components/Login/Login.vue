<template>
  <b-container >
    <b-row>
      <b-col md="9"></b-col>
      <b-col md="3" >
        <b-form novalidate>
          <h1 style="text-align: center;">Activos</h1>
          <b-form-group label="RUT" class="fw-bold" label-for="rut">
            <b-form-input
              id="rut"
              v-model="rut"
              :state="validacionRut(rut)"
              placeholder="Ej: 12.345.678-9"
              required
            ></b-form-input>
            <b-form-invalid-feedback class="fw-light" style="font-size: 0.8rem !important; color: brown;">
              Ingresa un RUT válido (ej: 12.345.678-9).
            </b-form-invalid-feedback>
          </b-form-group>
          <b-form-group label="Contraseña" class="fw-bold" label-for="password">
            <b-form-input
              id="password"
              type="password"
              v-model="password"
              :state="validacionPassword(password)"
              placeholder="Mínimo 6 caracteres"
              required
            ></b-form-input>
            <b-form-invalid-feedback class="fw-light" style="font-size: 0.8rem !important; color: brown;">
              La contraseña debe tener al menos 6 caracteres.
            </b-form-invalid-feedback>
          </b-form-group>
          <b-button @click="onSubmit()" variant="primary">Iniciar Sesión</b-button>
        </b-form>
      </b-col>
    </b-row>
  </b-container>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const rut = ref('');
const password = ref('');
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
  if (typeof valor !== 'string') return false;
  return valor.length >= 6;
};
const onSubmit = async () => {
  const rutValido = validacionRut(rut.value);
  const passValido = validacionPassword(password.value);
  if (!rutValido || !passValido) {
    alert('Por favor, completa todos los campos correctamente.');
    return;
  }
  try {
    const baseUrl = 'http://localhost/activos/Backend/index.php/Auth/';
    const dataToSend = {
      rut: rut.value,
      password: password.value
    };
    const response = await axios.post(baseUrl + 'IniciarSession', dataToSend, {
      headers: {
        'Content-Type': 'application/json'
      },
      withCredentials: true
    });
    if (response.data.status === 'success') {
      emit('login-success', response.data.user);
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    if (error.response) {
      alert('Error: ' + (error.response.data.message || 'Ha ocurrido un error en el servidor.'));
    } else if (error.request) {
      alert('Error de conexión: No se pudo conectar al servidor.');
    } else {
      alert('Error desconocido: ' + error.message);
    }
  }
};
</script>
<style scoped>
</style>
