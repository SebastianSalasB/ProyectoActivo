<template>
  <b-container >
  <b-row>
    <b-col md="12" id="NAG" >
      <div v-for="(activo, index) in Activo.activos":key="index" class=" rounded p-3 mb-3 bg-dark" style="color: white;">
        <b-row>
          <b-col sm>
            <b-form-group label="Empresa" >
              <b-form-select 
                required
                v-model="empresaSeleccionada"
                :options="empresasConSucursales.map(e => ({ value: e.emp_id, text: e.emp_nombre }))" 
                :class="{ 'is-invalid': inputErrors[index]?.empresa }"
                @change="value => validarCampo(index, 'empresa', value)"
              />
              <small v-if="errors.empresaSeleccionada" class="text-danger">falta empresa</small>
              
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Sucursal">
              <b-form-select
                v-model="sucursalSeleccionada"
                :options="sucursalesFiltradas.map(s => ({ value: s.suc_id, text: s.suc_nombre }))"
                :class="{ 'is-invalid': inputErrors[index]?.sucursal }"
                @change="val => validarCampo(index, 'sucursal', val)"
              />
              <small v-if="errors.sucursalSeleccionada" class="text-danger">{{ errors.sucursalSeleccionada }}</small>
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Usuario">
              <b-form-select
                v-model="activo.usuario_id"
                :options="usuariosFiltrados.map(u => ({ value: u.usr_id, text: u.usr_nombre }))"
                :class="{ 'is-invalid': inputErrors[index]?.usuario }"
                @change="val => validarCampo(index, 'usuario', val)"
              />

              <small v-if="errors.usuario_id" class="text-danger">{{ errors.usuario_id }}</small>
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Fecha">
              <b-form-input
                type="date"
                v-model="activo.fecharegistro"
                :class="{ 'is-invalid': inputErrors[index]?.fecharegistro }"
                @input="validarCampo(index, 'fecharegistro', activo.fecharegistro)"
              />


              <small v-if="errors.fecharegistro" class="text-danger">{{ errors.fecharegistro }}</small>
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Tipo">
            <b-form-select
              v-model="activo.tipoactivo"
              :options="tiposDisponibles"
              value-field="tip_id"
              text-field="tip_descripcion"
              placeholder="Seleccione un tipo"
              :class="{ 'is-invalid': inputErrors[index]?.tipoactivo }"
              @change="val => validarCampo(index, 'tipoactivo', val)"
            />
            <small v-if="errors.tipoactivo" class="text-danger">Tipo requerido</small></b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <div v-if="activo.tipoactivo === 1"> 
            <b-row>
            <b-col sm>
              <b-form-group label="RAM">
                <b-form-select
                  v-model="activo.com_ram"
                  :class="{ 'is-invalid': inputErrors[index]?.ram }"
                  @change="validarCampo(index, 'ram', activo.com_ram)"
                  :options="[
                    { value: '4GB', text: '4 GB' },
                    { value: '8GB', text: '8 GB' },
                    { value: '16GB', text: '16 GB' },
                    { value: '32GB', text: '32 GB' },
                    { value: '64GB', text: '64 GB' }
                  ]"
                />

                <small v-if="errors.com_ram" class="text-danger">{{ errors.com_ram }}</small>
              </b-form-group>
            </b-col>
            <b-col sm>
              <b-form-group label="CPU">
                <b-form-select
                  v-model="activo.com_cpu"
                  :class="{ 'is-invalid': inputErrors[index]?.cpu }"
                  @input="validarCampo(index, 'cpu', activo.com_cpu)"
                  :options="[
                    { value: 'i3', text: 'Intel i3' },
                    { value: 'i5', text: 'Intel i5' },
                    { value: 'i7', text: 'Intel i7' },
                    { value: 'Ryzen5', text: 'Ryzen 5' },
                    { value: 'Ryzen7', text: 'Ryzen 7' }
                  ]"
                />
                <small v-if="errors.com_cpu" class="text-danger">{{ errors.com_cpu }}</small>
              </b-form-group>
            </b-col>
            <b-col sm>
             <b-form-group label="Disco">
                <b-form-select
                  v-model="activo.com_disco"
                  :class="{ 'is-invalid': inputErrors[index]?.disco }"
                  @input="validarCampo(index, 'disco', activo.com_disco)"
                  :options="[
                    { value: '256GB_SSD', text: '256 GB SSD' },
                    { value: '512GB_SSD', text: '512 GB SSD' },
                    { value: '1TB_HDD', text: '1 TB HDD' },
                    { value: '1TB_SSD', text: '1 TB SSD' }
                  ]"
                />
                <small v-if="errors.com_disco" class="text-danger">{{ errors.com_disco }}</small>
              </b-form-group>
            </b-col>
            <b-col sm>
              <b-form-group label="Sistema Operativo">
                <b-form-select
                  v-model="activo.com_sistema_operativo"
                  :class="{ 'is-invalid': inputErrors[index]?.sistemaoperativo }"
                  @input="validarCampo(index, 'cpu', activo.com_cpu)"
                  :options="[
                    { value: 'Windows', text: 'Windows' },
                    { value: 'Linux', text: 'Linux' }
                  ]"
                />
                <small v-if="errors.com_sistema_operativo" class="text-danger">{{ errors.com_sistema_operativo }}</small>
              </b-form-group>
            </b-col>
          </b-row>
          </div>
          <div v-if="activo.tipoactivo === 2"> <!-- Asume que 'Computadores' tiene tip_id = 1 -->
            <b-row>
              <b-col sm >
                <b-form-group label="Nombre Servidor">
                  <b-form-input v-model="activo.ser_nombre" />
                  <small v-if="errors.ser_nombre" class="text-danger">{{ errors.ser_nombre }}</small>
                </b-form-group>
                </b-col>
                <b-col sm>
                <b-form-group label="RAM">
                  <b-form-select
                  v-model="activo.ser_ram"
                  :class="{ 'is-invalid': inputErrors[index]?.RAM }"
                  @input="validarCampo(index, 'cpu', activo.ser_ram)"
                  :options="[
                    { value: '4GB', text: '4 GB' },
                    { value: '8GB', text: '8 GB' },
                    { value: '16GB', text: '16 GB' },
                    { value: '32GB', text: '32 GB' },
                    { value: '64GB', text: '64 GB' }
                  ]"
                />
                <small v-if="errors.ser_ram" class="text-danger">{{ errors.ser_ram }}</small>
                </b-form-group>
                </b-col>
                <b-col sm>
                  <b-form-group label="CPU">
                    <b-form-select
                  v-model="activo.ser_cpu"
                  :class="{ 'is-invalid': inputErrors[index]?.cpuServidor }"
                  @input="validarCampo(index, 'cpuServidor', activo.ser_cpu)"
                  :options="[
                    { value: 'Intel Xeon', text: 'Intel Xeon' },
                    { value: 'AMD EPYC', text: 'AMD EPYC' },
                    { value: 'ARM', text: 'ARM' },
                    { value: 'PowerPC', text: 'PowerPC' }
                  ]"
                />
                <small v-if="errors.ser_cpu" class="text-danger">{{ errors.ser_cpu }}</small>
                  </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <b-col sm>
                <b-form-group label="Disco">
                  <b-form-select
                  v-model="activo.ser_disco"
                  :class="{ 'is-invalid': inputErrors[index]?.discoServidor }"
                  @input="validarCampo(index, 'discoServidor', activo.ser_disco)"
                  :options="[
                    { value: '256GB_SSD', text: '256 GB SSD' },
                    { value: '512GB_SSD', text: '512 GB SSD' },
                    { value: '1TB_HDD', text: '1 TB HDD' },
                    { value: '2TB_SSD', text: '2 TB SSD' },
                    { value: '10TB_HDD', text: '10 TB HDD' },
                    { value: '15TB_SSD', text: '15 TB SSD' },
                    { value: '20TB_HDD', text: '20 TB HDD' },
                    { value: '24TB_SSD', text: '25 TB SSD' }
                  ]"
                />
                <small v-if="errors.ser_disco" class="text-danger">{{ errors.ser_disco }}</small>
                </b-form-group>
              </b-col>
              <b-col sm>
                <b-form-group label="Sistema Operativo">
                  <b-form-select
                  v-model="activo.ser_sistema_operativo"
                  :class="{ 'is-invalid': inputErrors[index]?.sistemaOperativoServidor }"
                  @input="validarCampo(index, 'sistemaOperativoServidor', activo.ser_sistema_operativo)"
                  :options="[
                    { value: 'Windows', text: 'Windows' },
                    { value: 'linux', text: 'Ubuntu' },
                    { value: 'FreeBSD', text: 'FreeBSD' }
                  ]"
                />
                <small v-if="errors.ser_sistema_operativo" class="text-danger">{{ errors.ser_sistema_operativo }}</small>
                </b-form-group>
              </b-col>
              <b-col sm>
                <b-form-group label="Ranuras">
                 <b-form-select
                  v-model="activo.ser_ranuras_ram"
                  :class="{ 'is-invalid': inputErrors[index]?.ramServidor }"
                  @input="validarCampo(index, 'ramServidor', activo.ser_ranuras_ram)"
                  :options="[
                    { value: '8 Ranuras', text: '8 Ranuras' },
                    { value: '16 Ranuras', text: '16 Ranuras' },
                    { value: '32 Ranuras', text: '32 Ranuras' }
                  ]"
                />
                <small v-if="errors.ser_ranuras_ram" class="text-danger">{{ errors.ser_ranuras_ram }}</small>
                </b-form-group>
              </b-col>
              <b-col sm>
                <b-form-group label="Cantidad Max CPU">
                  <b-form-select
                  v-model="activo.ser_cantidad_max_cpu"
                  :class="{ 'is-invalid': inputErrors[index]?.maxCpu }"
                  @input="validarCampo(index, 'maxCpu', activo.ser_cantidad_max_cpu)"
                  :options="[
                    { value: '2 CPU', text: '2 CPU' },
                    { value: '4 CPU', text: '4 CPU' },
                    { value: '8 CPU', text: '8 CPU' },
                    { value: '16 CPU', text: '16 CPU' }
                  ]"
                />
                <small v-if="errors.ser_cantidad_max_cpu" class="text-danger">{{ errors.ser_cantidad_max_cpu }}</small>
                </b-form-group>
              </b-col>
            </b-row>
          </div>
        </b-row>
        <b-row>
          <b-col sm>
            <b-form-group label="Nº de Serie">
              <b-form-input v-model="activo.numeroserie"
                  :class="{ 'is-invalid': inputErrors[index]?.numeroSerie }"
                  @input="validarCampo(index, 'numeroSerie', activo.numeroserie)"
               />
              <small v-if="errors.numeroserie" class="text-danger">{{ errors.numeroserie }}</small>
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Factura">
              <b-form-input v-model="activo.factura"
                  :class="{ 'is-invalid': inputErrors[index]?.factura }"
                  @input="validarCampo(index, 'factura', activo.factura)" />    
              <small v-if="errors.factura" class="text-danger">{{ errors.factura }}</small>
            </b-form-group>
          </b-col>          
        </b-row>
        <b-row>
          <b-col sm>
            <b-form-group label="Modelo">
              <b-form-input v-model="activo.modelo"
                  :class="{ 'is-invalid': inputErrors[index]?.modelo }"
                  @input="validarCampo(index, 'modelo', activo.modelo)" />
              <small v-if="errors.modelo" class="text-danger">{{ errors.modelo }}</small>
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Fabricante">
              <b-form-input v-model="activo.fabricante"
                  :class="{ 'is-invalid': inputErrors[index]?.fabricante }"
                  @input="validarCampo(index, 'fabricante', activo.fabricante)" />
              <small v-if="errors.fabricante" class="text-danger">{{ errors.fabricante }}</small>
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Marca">
              <b-form-input v-model="activo.marca"
                  :class="{ 'is-invalid': inputErrors[index]?.marca }"
                  @input="validarCampo(index, 'marca', activo.marca)" />
              <small v-if="errors.marca" class="text-danger">{{ errors.marca }}</small>
            </b-form-group>
          </b-col>
        </b-row>
        <b-form-group label="Dirección">
          <b-form-input v-model="activo.direccion" 
                  :class="{ 'is-invalid': inputErrors[index]?.direccion }"
                  @input="validarCampo(index, 'direccion', activo.direccion)" />
          <small v-if="errors.direccion" class="text-danger">{{ errors.direccion }}</small>
        </b-form-group>
        <b-form-group label="Descripción">
          <b-form-textarea v-model="activo.descripcion"
                  :class="{ 'is-invalid': inputErrors[index]?.descripcion }"
                  @input="validarCampo(index, 'descripcion', activo.descripcion)" />
          <small v-if="errors.descripcion" class="text-danger">{{ errors.descripcion }}</small>
        </b-form-group>
        <div>
          <b-form-group label="" label-for="input-1">
            <b-form-checkbox
              v-model="activo.tieneIp"
              :id="'checkbox-' + index"
            >
              Tiene IP:
            </b-form-checkbox>
            <div v-if="activo.tieneIp">
              <div v-for="(ip, i) in activo.ips" :key="i" class="d-flex mb-2">
                <b-form-input
                  v-model="activo.ips[i]"
                  placeholder="Ingrese IP"
                />
                <b-button
                  size="sm"
                  variant="danger"
                  class="ms-2"
                  @click="activo.ips.splice(i, 1)"
                  v-if="activo.ips.length > 1"
                >
                  ✕
                </b-button>
              </div>
              <b-button size="sm" variant="success" @click="activo.ips.push('')">
                + Agregar otra IP
              </b-button>
            </div>
          </b-form-group>
        </div> 

        <b-button
          v-if="Activo.activos.length > 1"
          size="sm"
          variant="danger"
          @click="removeActivo(index)"
        >
          Eliminar Activo
        </b-button>
      </div>
      <b-button size="sm" variant="primary" @click="AgregarActivo">
        + Agregar otro activo
      </b-button>
      <div class="text-center mt-4">
        <b-button size="sm" @click="GuardarActivo" style="background-color:rgb(97 221 166); width: 150px; color: white; border-radius: 18px; border:2px solid var(--Primery, #42B883);">
          Guardar
        </b-button>
      </div>
      <b-modal v-model="modalShow" title="Resultado del registro" hide-footer>
        <div class="text-center">
          <p>{{ modalMessage }}</p>
          <b-button variant="primary" @click="modalShow = false">Aceptar</b-button>
        </div>
      </b-modal>
    </b-col>
  </b-row>
  </b-container>
</template>
<script>
import axios from 'axios'

export default {
  data() {
    return {
      Activo: {
        activos: [
          {
            usuario_id: '',
            sucursal_id: '',
            descripcion: '',
            fecharegistro: '',
            modelo: '',
            marca: '',
            factura: '',
            direccion: '',
            tipoactivo: '',
            numeroserie: '',
            fabricante: '',
            tieneIp: false,
            ips: ['']
          }
        ]
      },
      empresasConSucursales: [],
      usuariosDisponibles: [],
      empresaSeleccionada: '',
      sucursalSeleccionada: '',
      tiposDisponibles: [],
      modalShow: false,
      modalMessage: '',
      errors: {},
      inputErrors: [] // ← Añadido para validación campo a campo
    }
  },
  computed: {
    sucursalesFiltradas() {
      const empresa = this.empresasConSucursales.find(e => e.emp_id === this.empresaSeleccionada)
      return empresa ? empresa.sucursales : []
    },
    usuariosFiltrados() {
      return this.usuariosDisponibles.filter(u => u.usr_id_sucursal === this.sucursalSeleccionada)
    }
  },
  methods: {
    async cargarEmpresaSucursalesTipo() {
      try {
        const [empresaRes, sucursalesRes, tiposRes] = await Promise.all([
          axios.get('/Activos/listaEmpresa'),
          axios.get('/Activos/listaSucursal'),
          axios.get('/Activos/listaTipos')
        ])
        this.tiposDisponibles = tiposRes.data.filter(t => t.tip_estado === 'activo')

        const empresasActivas = empresaRes.data.filter(e => e.emp_estado === 'activo')
        this.empresasConSucursales = empresasActivas.map(empresa => {
          const sucursalesDeEmpresa = sucursalesRes.data.filter(
            s => s.suc_id_empresa === empresa.emp_id
          )
          return {
            ...empresa,
            sucursales: sucursalesDeEmpresa
          }
        })
      } catch (error) {
        console.error('Error al cargar empresas, sucursales o tipos', error)
      }
    },
    async cargarUsuarios() {
      try {
        const res = await axios.get('/Activos/listaUsuarios')
        this.usuariosDisponibles = res.data.filter(u => u.usr_estado === 'activo')
      } catch (error) {
        console.error('Error al cargar usuarios', error)
      }
    },
    validarCampo(index, campo, valor) {
      if (!this.inputErrors) this.inputErrors = []
      if (!this.inputErrors[index]) this.inputErrors[index] = {}
      this.inputErrors[index][campo] = !valor || valor === ''
    },
    async GuardarActivo() {
      if (this.validadatos()) return

      this.Activo.activos = this.Activo.activos.map(activo => ({
        ...activo,
        sucursal_id: this.sucursalSeleccionada,
        empresa_id: this.empresaSeleccionada
      }))

      try {
        await axios.post('/Activos/CrearActivo', {
          activos: this.Activo.activos
        })
        this.resetearFormulario()
        this.modalMessage = 'Activos registrados correctamente.'
      } catch (error) {
        console.error(error)
        this.modalMessage = 'Error al registrar los activos.'
      } finally {
        this.modalShow = true
      }
    },
    AgregarActivo() {
      this.Activo.activos.push({
        usuario_id: '',
        sucursal_id: '',
        descripcion: '',
        fecharegistro: '',
        modelo: '',
        marca: '',
        factura: '',
        direccion: '',
        tipoactivo: '',
        numeroserie: '',
        fabricante: '',
        tieneIp: false,
        ips: ['']
      })
    },
    validadatos() {
      this.errors = {}
      let hasError = false

      if (!this.empresaSeleccionada) {
        this.errors.empresaSeleccionada = 'Seleccione una empresa'
        hasError = true
      }

      if (!this.sucursalSeleccionada) {
        this.errors.sucursalSeleccionada = 'Seleccione una sucursal'
        hasError = true
      }

      this.Activo.activos.forEach((activo, index) => {
        if (!activo.usuario_id) {
          this.errors.usuario_id = `Debe seleccionar un usuario`
          hasError = true
        }
        if (!activo.descripcion) {
          this.errors.descripcion = `La descripción es obligatoria`
          hasError = true
        }
        if (!activo.fecharegistro) {
          this.errors.fecharegistro = `La fecha es obligatoria`
          hasError = true
        }
        if (!activo.modelo) {
          this.errors.modelo = `El modelo es obligatorio`
          hasError = true
        }
        if (!activo.marca) {
          this.errors.marca = `La marca es obligatoria`
          hasError = true
        }
        if (!activo.factura) {
          this.errors.factura = `La factura es obligatoria`
          hasError = true
        }
        if (!activo.tipoactivo) {
          this.errors.tipoactivo = `Debe seleccionar un tipo`
          hasError = true
        }
        if (!activo.direccion) {
          this.errors.direccion = `La dirección es obligatoria`
          hasError = true
        }
        if (!activo.numeroserie) {
          this.errors.numeroserie = `El número de serie es obligatorio`
          hasError = true
        }
        if (!activo.fabricante) {
          this.errors.fabricante = `El fabricante es obligatorio`
          hasError = true
        }

        if (activo.tipoactivo === 1) {
          if (!activo.com_ram) {
            this.errors.com_ram = `Debe seleccionar RAM para computador`
            hasError = true
          }
          if (!activo.com_cpu) {
            this.errors.com_cpu = `Debe seleccionar CPU para computador`
            hasError = true
          }
          if (!activo.com_disco) {
            this.errors.com_disco = `Debe seleccionar disco para computador`
            hasError = true
          }
          if (!activo.com_sistema_operativo) {
            this.errors.com_sistema_operativo = `Debe seleccionar sistema operativo`
            hasError = true
          }
        } else if (activo.tipoactivo === 2) {
          if (!activo.ser_nombre) {
            this.errors.ser_nombre = `Debe indicar nombre del servidor`
            hasError = true
          }
          if (!activo.ser_ram) {
            this.errors.ser_ram = `Debe seleccionar RAM para servidor`
            hasError = true
          }
          if (!activo.ser_cpu) {
            this.errors.ser_cpu = `Debe seleccionar CPU para servidor`
            hasError = true
          }
          if (!activo.ser_disco) {
            this.errors.ser_disco = `Debe seleccionar disco para servidor`
            hasError = true
          }
          if (!activo.ser_sistema_operativo) {
            this.errors.ser_sistema_operativo = `Debe seleccionar sistema operativo`
            hasError = true
          }
          if (!activo.ser_ranuras_ram) {
            this.errors.ser_ranuras_ram = `Debe seleccionar cantidad de ranuras RAM`
            hasError = true
          }
          if (!activo.ser_cantidad_max_cpu) {
            this.errors.ser_cantidad_max_cpu = `Debe seleccionar cantidad máxima de CPU`
            hasError = true
          }
        }

        if (activo.tieneIp && (!activo.ips || activo.ips.some(ip => ip.trim() === ''))) {
          this.errors.ips = `Hay direcciones IP vacías`
          hasError = true
        }
      })

      return hasError
    },
    removeActivo(index) {
      this.Activo.activos.splice(index, 1)
      this.inputErrors.splice(index, 1)
    },
    resetearFormulario() {
      this.Activo = {
        activos: [
          {
            usuario_id: '',
            sucursal_id: '',
            descripcion: '',
            fecharegistro: '',
            modelo: '',
            marca: '',
            factura: '',
            direccion: '',
            tipoactivo: '',
            numeroserie: '',
            fabricante: '',
            tieneIp: false,
            ips: ['']
          }
        ]
      }
      this.errors = {}
      this.inputErrors = []
    }
  },
  mounted() {
    this.cargarEmpresaSucursalesTipo()
    this.cargarUsuarios()
  }
}


</script>