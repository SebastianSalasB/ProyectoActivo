<template>
  <!-- Barra de búsqueda -->
  <b-navbar toggleable="lg" type="" variant="" class="mb-4">
    <b-container>
      <b-navbar-brand href="#" >Buscar Activos</b-navbar-brand>
      <b-input-group class="mt-2">
        <b-form-input 
          v-model="Buscador" 
          type="search" 
          placeholder="Buscar por palabra clave..." 
          @input="paginaActual = 1"  
        />
        <b-input-group-append v-if="!mostrarFiltros">
          <b-button variant="outline-secondary" @click="mostrarFiltros = true">
            <i class="fa-solid fa-filter fa-sm"></i> filtros
          </b-button>
        </b-input-group-append>
      </b-input-group>
    </b-container>
  </b-navbar>
  
  <!-- filtro -->
  <b-container fluid>
    <b-row>
      <!-- Barra lateral izquierda -->
      <transition name="fade">
        <b-col md="2" v-if="mostrarFiltros">
          <b-card class="shadow-sm" style="border: none;" >
            <h5 class="mb-3" style="">Filtros</h5>              
            <!-- Filtro por Tipo -->
            <b-form-group label="Tipo">
              <b-form-checkbox-group
                v-model="filtros.Tipos"
                :options="TiposOpciones"
                value-field="id"
                text-field="nombre"
                stacked
                style="flex-wrap: wrap !important; justify-content: flex-start !important;"
              />
            </b-form-group>
            <!-- Filtro por Empresa -->
            <b-form-group label="Empresa">
              <b-form-checkbox-group
                v-model="filtros.empresa"
                :options="empresaOpciones"
                value-field="id"
                text-field="nombre"
                stacked
              />
            </b-form-group>
            <!-- Filtro por Sucursal -->
            <b-form-group label="Sucursal">
              <b-form-checkbox-group
                v-model="filtros.sucursales"
                :options="sucursalesOpciones"
                value-field="id"
                text-field="nombre"
                stacked
              />
            </b-form-group>
            <!-- Filtro por Estado -->
            <b-form-group label="Estado">
              <b-form-checkbox-group
                v-model="filtros.estados"
                :options="estadosOpciones"
                value-field="id"
                text-field="nombre"
                stacked
              />
            </b-form-group>
            <b-row>
              <b-col>
                <div class="d-grid gap-2 small">
                  <b-button size="sm" class="mb-3" variant="danger" @click="limpiarFiltros">
                    <i class="fa-solid fa-brush fa-sm"></i> Limpiar Filtros
                  </b-button>
                </div>
              </b-col>
              <b-col>
                <b-button variant="outline-secondary" size="sm" class="mb-3 small" @click="mostrarFiltros = false">
                  <i class="fa-solid fa-xmark"></i> Ocultar filtros
                </b-button>
              </b-col>
            </b-row>
          </b-card>
          <p></p>
        </b-col>
      </transition>
      
      <!-- Tabla de resultados -->
      <b-col :md="mostrarFiltros ? 10 : 12">
        <b-overlay :show="cargando" rounded="sm" spinner-variant="secondary" opacity="0.6">
          <b-table 
          :items="activosPaginados" 
          :fields="fields" 
          responsive hover
          class="custom-rounded-table"> 
            <template #cell(act_id)="data">
              <div>
                <span style="font-size: 1rem; margin-top: 25px; text-align: start; padding: 0.2rem 0.2rem;">{{ data.value }}</span>
              </div>
            </template>
            
            <template #cell(nombre_usuario)="data"> 
              <div>
                <span>{{ data.item.nombre_usuario }}</span>
              </div>
              <div>
                <span style="font-size: 0.80rem; text-align: start;">{{ data.item.apellido_usuario }}</span>
              </div>
            </template>              
            
            <template #cell(nombre_sucursal)="data">
              <div>
                <span style="font-size: 0.80rem; text-align: start;">{{ data.item.nombre_sucursal }}</span>
              </div>
              <div>
                <span style="font-size: 0.60rem; text-align: start;">{{ data.item.nombre_tipo }}</span>
              </div>
            </template>
            
            <template #cell(act_fecha_registro)="data">
              <span class="d-none d-lg-inline">{{ data.item.act_fecha_registro }}</span>
            </template>              
            
            <template #cell(acciones)="data">
              <b-dropdown size="sm" style="margin-top: 12px;" variant="light" text="Acciones" toggle-class="btn-sm" no-caret>
                <template #button-content>
                  <i class="fa-solid fa-gear fa-xl"></i>
                </template>
                <!-- Editar: solo si NO está en baja ni eliminado -->
                <b-dropdown-item style="color: black;"
                  v-if="!['eliminado', 'baja'].includes(data.item.act_estado)"
                  @click="editarActivo(data.item)"
                >
                  <i class="fa-solid fa-pen-to-square" style="color: #258f24; margin-right: 6px;"></i> Editar
                </b-dropdown-item>

                <!-- Eliminar: solo si NO está en baja ni eliminado -->
                <b-dropdown-item style="color: black;"
                  v-if="!['eliminado', 'baja'].includes(data.item.act_estado)"
                  @click="confirmarEliminar(data.item)"
                >
                  <i class="fa-solid fa-trash" style="color: #8e0101; margin-right: 6px;"></i> Eliminar
                </b-dropdown-item>

                <!-- Mantención: solo si está activo -->
                <b-dropdown-item style="color: black;"
                  v-if="data.item.act_estado === 'activo'"
                  @click="abrirModalMantencion(data.item)"
                >
                  <i class="fa-solid fa-screwdriver-wrench" style="color: #e3d21c; margin-right: 6px;"></i> Mantención
                </b-dropdown-item>

                <!-- Activar: si está eliminado, baja o mantenimiento -->
                <b-dropdown-item 
                  v-if="['eliminado', 'baja', 'mantenimiento'].includes(data.item.act_estado)"
                  @click="activarActivo(data.item)" 
                >
                  <i class="fa-solid fa-toggle-on" style="color: #007bff; margin-right: 6px;"></i> Activar
                </b-dropdown-item>
              </b-dropdown>
            </template>
            
            <template #cell(act_estado)="data">
              <div>
                <i
                  v-if="data.item.act_estado === 'activo'"
                  class="fa-solid fa-circle-check text-success"
                  title="activo"
                  style="font-size: 0.870rem; padding: 0.2rem 0.2rem;"
                ></i>
                <i
                  v-else-if="data.item.act_estado === 'eliminado'"
                  class="fa-solid fa-circle-xmark text-danger"
                  title="Eliminado"
                  style="font-size: 0.870rem; padding: 0.2rem 0.2rem;"
                ></i>
                <i
                  v-else-if="data.item.act_estado === 'baja'"
                  class="fa-solid fa-ban" 
                  title="Baja"
                  style="color: #ff4747; font-size: 0.870rem; padding: 0.2rem 0.2rem;"
                ></i>
                <i 
                  v-else-if="data.item.act_estado === 'mantenimiento'" 
                  class="fa-solid fa-circle-exclamation" 
                  title="mantenimiento" 
                  style="color: #ffe852; font-size: 0.870rem; padding: 0.2rem 0.2rem;"
                ></i>
                <i
                  v-else
                  class="fa-solid fa-circle-question text-secondary"
                  title="Desconocido"
                  style="font-size: 0.870em; padding: 0.2rem 0.2rem;"
                ></i>
              </div> 
            </template>
          </b-table> 
          <div class="overflow-auto">
            <b-pagination
              v-model="paginaActual"
              :total-rows="filtrarActivos.length"
              :per-page="porPagina"       
              align="center"
              class="my-3"
              first-number
              last-number
              size="md"/>
          </div>
        </b-overlay>
      </b-col>
    </b-row>
  </b-container>
  
  <!-- Modal de edición -->
  <b-modal v-model="modalShow" title="Datos del Activo Editor" size="lg" hide-footer>
    <b-form class="small">
      <b-row>
        <!-- empresas -->
        <b-col md="6" class="mb-2">
          <label>Empresa</label>
          <b-form-select
            v-model="selectedActivos.act_id_empresa"
            :options="empresaOpciones"
            value-field="id"
            text-field="nombre"
            placeholder="Selecciona una empresa"
          />
        </b-col>

        <!-- Sucursal -->
        <b-col md="6" class="mb-2">
          <label>Sucursal</label>
          <b-form-select
            v-model="selectedActivos.act_id_sucursal"
            :options="sucursalesOpciones"
            value-field="id"
            text-field="nombre"
            placeholder="Seleccione una sucursal"
            required
          />
        </b-col>

        <!-- usuario -->
        <b-col md="6" class="mb-2">
          <label>Usuario</label>
          <b-form-select
            v-model="selectedActivos.nombre_usuario"
            :options="usuariosOpciones"
            value-field="id"
            text-field="nombre"
            placeholder="Seleccione un usuario"
            required
          />
        </b-col>

        <!-- Fecha Registro -->
        <b-col md="6" class="mb-2">
          <label>Fecha Registro</label>
          <b-form-input type="date" v-model="selectedActivos.act_fecha_registro" required />
        </b-col>

        <!-- Marca -->
        <b-col md="6" class="mb-2">
          <label>Marca</label>
          <b-form-input v-model="selectedActivos.act_marca" required />
        </b-col>

        <!-- Modelo -->
        <b-col md="6" class="mb-2">
          <label>Modelo</label>
          <b-form-input v-model="selectedActivos.act_modelo" required />
        </b-col>

        <!-- Fabricante -->
        <b-col md="6" class="mb-2">
          <label>Fabricante</label>
          <b-form-input v-model="selectedActivos.act_fabricante" required />
        </b-col>

        <!-- N° Serie -->
        <b-col md="6" class="mb-2">
          <label>N° de Serie</label>
          <b-form-input v-model="selectedActivos.act_numero_serie" required />
        </b-col>

        <!-- Ubicación -->
        <b-col md="6" class="mb-2">
          <label>Ubicación</label>
          <b-form-input v-model="selectedActivos.act_ubicacion" required />
        </b-col>

        <!-- Tipo -->
        <b-col md="6" class="mb-2">
          <label>Tipo</label>
          <b-form-input :value="nombreTipoDisplay" disabled />
        </b-col>
      </b-row>
      
      <!-- Datos especiales según tipo de activo -->
      <div v-if="selectedActivos.act_id_tipo == 1">
        <!-- Computador -->
        <b-row>
          <b-col sm>
            <b-form-group label="RAM">
              <b-form-select
                v-model="selectedActivos.datos_computador.com_ram"
                :options="[
                  { value: '4GB', text: '4 GB' },
                  { value: '8GB', text: '8 GB' },
                  { value: '16GB', text: '16 GB' },
                  { value: '32GB', text: '32 GB' },
                  { value: '64GB', text: '64 GB' }
                ]" required
              />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="CPU">
              <b-form-select
                v-model="selectedActivos.datos_computador.com_cpu"
                :options="[
                  { value: 'i3', text: 'Intel i3' },
                  { value: 'i5', text: 'Intel i5' },
                  { value: 'i7', text: 'Intel i7' },
                  { value: 'Ryzen5', text: 'Ryzen 5' },
                  { value: 'Ryzen7', text: 'Ryzen 7' }
                ]" required
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col sm>
            <b-form-group label="Disco">
              <b-form-select
                v-model="selectedActivos.datos_computador.com_disco"
                :options="[
                  { value: '256GB_SSD', text: '256 GB SSD' },
                  { value: '512GB_SSD', text: '512 GB SSD' },
                  { value: '1TB_HDD', text: '1 TB HDD' },
                  { value: '1TB_SSD', text: '1 TB SSD' }
                ]" required
              />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Sistema Operativo">
              <b-form-select
                v-model="selectedActivos.datos_computador.com_sistema_operativo"
                :options="[
                  { value: 'Windows', text: 'Windows' },
                  { value: 'Linux', text: 'Linux' }
                ]" required
              />
            </b-form-group>
          </b-col>
        </b-row>
      </div>
      
      <div v-if="selectedActivos.act_id_tipo === 2">
        <!-- Servidor -->
        <b-row>
          <b-col sm>
            <b-form-group label="Nombre">
              <b-form-input v-model="selectedActivos.datos_servidor.ser_nombre" required />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="RAM">
              <b-form-select
                v-model="selectedActivos.datos_servidor.ser_ram"
                :options="[
                  { value: '4GB', text: '4 GB' },
                  { value: '8GB', text: '8 GB' },
                  { value: '16GB', text: '16 GB' },
                  { value: '32GB', text: '32 GB' },
                  { value: '64GB', text: '64 GB' }
                ]" required
              />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="CPU">
              <b-form-select
                v-model="selectedActivos.datos_servidor.ser_cpu"
                :options="[
                  { value: 'Intel Xeon', text: 'Intel Xeon' },
                  { value: 'AMD EPYC', text: 'AMD EPYC' },
                  { value: 'ARM', text: 'ARM' },
                  { value: 'PowerPC', text: 'PowerPC' }
                ]" required
              />
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col sm>
            <b-form-group label="Disco">
              <b-form-select
                v-model="selectedActivos.datos_servidor.ser_disco"
                :options="[
                  { value: '256GB_SSD', text: '256 GB SSD' },
                  { value: '512GB_SSD', text: '512 GB SSD' },
                  { value: '1TB_HDD', text: '1 TB HDD' },
                  { value: '2TB_SSD', text: '2 TB SSD' },
                  { value: '10TB_HDD', text: '10 TB HDD' },
                  { value: '15TB_SSD', text: '15 TB SSD' },
                  { value: '20TB_HDD', text: '20 TB HDD' },
                  { value: '24TB_SSD', text: '25 TB SSD' }
                ]" required
              />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Sistema Operativo">
              <b-form-select
                v-model="selectedActivos.datos_servidor.ser_sistema_operativo"
                :options="[
                  { value: 'Windows', text: 'Windows' },
                  { value: 'linux', text: 'Ubuntu' },
                  { value: 'FreeBSD', text: 'FreeBSD' }
                ]" required
              />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Ranuras">
              <b-form-select
                v-model="selectedActivos.datos_servidor.ser_ranuras_ram"
                :options="[
                  { value: '8 Ranuras', text: '8 Ranuras' },
                  { value: '16 Ranuras', text: '16 Ranuras' },
                  { value: '32 Ranuras', text: '32 Ranuras' }
                ]" required
              />
            </b-form-group>
          </b-col>
          <b-col sm>
            <b-form-group label="Cantidad Max CPU">
              <b-form-select
                v-model="selectedActivos.datos_servidor.ser_cantidad_max_cpu"
                :options="[
                  { value: '2 CPU', text: '2 CPU' },
                  { value: '4 CPU', text: '4 CPU' },
                  { value: '8 CPU', text: '8 CPU' },
                  { value: '16 CPU', text: '16 CPU' }
                ]" required
              />
            </b-form-group>
          </b-col>
        </b-row>
      </div>
      
      <!-- Factura y descripción y ip -->
      <b-row class="mt-3">
        <b-col md="6" class="mb-2">
          <label>Factura</label>
          <b-form-input v-model="selectedActivos.act_factura" required />
        </b-col>
        <b-col md="6" class="mb-2">
          <label>Descripción</label>
          <b-form-textarea v-model="selectedActivos.act_descripcion" required />
          <small v-if="errors.com_ram" class="text-danger">{{ errors.com_ram }}</small>
        </b-col>
        <b-col>
          <b-form-group label="Direcciones IP">
            <div
              v-if="selectedActivos.direcciones_ip.length === 0"
              class="text-muted mb-2"
            >
              No hay direcciones IP registradas.
            </div>

            <div
              v-for="(ip, index) in selectedActivos.direcciones_ip"
              :key="index"
              class="mb-2"
            >
              <b-input-group>
                <b-form-input v-model="selectedActivos.direcciones_ip[index]" />
                <b-input-group-append>
                  <b-button
                    variant="danger"
                    @click="selectedActivos.direcciones_ip.splice(index, 1)"
                  >
                    Eliminar
                  </b-button>
                </b-input-group-append>
              </b-input-group>
            </div>

            <b-button
              variant="success"
              size="sm"
              @click="selectedActivos.direcciones_ip.push('')"
            >
              Agregar IP
            </b-button>
          </b-form-group>
        </b-col>          
      </b-row>
      
      <!-- Botones -->
      <div class="text-end mt-3">
        <b-button variant="success" class="me-2" @click="confirmarEdicion">Guardar</b-button>
        <b-button variant="secondary" @click="cancelarEditar">Cancelar</b-button>
      </div>
    </b-form>
  </b-modal>   
  
  <!-- Modal de confirmación de eliminación -->
  <b-modal v-model="ConfirmaEliminadoModal" title="Confirmar eliminación" @ok="eliminarActivos" ok-title="Sí, eliminar" cancel-title="Cancelar" ok-variant="danger">
    ¿Estás seguro de que deseas eliminar el activo? 
  </b-modal>
  
  <!-- Modal de confirmación de edición -->
  <b-modal v-model="editorConfirmaModal" title="Confirmar modificación" @ok="editar" ok-title="Sí, guardar cambios" cancel-title="Cancelar" ok-variant="success">
    ¿Estás seguro de que deseas guardar los cambios al activo?
  </b-modal>
  
  <!-- Modal para enviar a mantención o dar de baja -->
  <b-modal v-model="mantencionModal" title="Enviar a mantención o Dar de Baja" @ok="confirmarEnvioMantencion" ok-title="Mantención" cancel-title="Cancelar" ok-variant="primary">
    <div class="mb-3">
      ¿Estás seguro de que deseas mandar a mantención el activo?
    </div>
    <div class="d-flex justify-content-end">
      <b-col>
        <label>Descripción por la cual se da de baja</label>
        <b-col md="6" class="mb-2">
          <label>Usuario</label>
          <b-form-select
            v-model="selectedActivos.nombre_usuario"
            :options="usuariosOpciones"
            value-field="id"
            text-field="nombre"
            placeholder="Seleccione un usuario"
            required
          />
        </b-col>
        <b-col>
          <b-form-textarea v-model="selectedActivos.bjs_descripcion" />
        </b-col>
        <p></p>
        <b-col>
          <label for="archivo">Selecciona un archivo de baja</label>
          <input
            id="archivo"
            type="file"
            class="form-control"
            @change="cargarArchivo"
          />
        </b-col>
        <p></p>
        <b-col>
          <b-button variant="danger" @click="darDeBajaActivo">
            Dar de Baja
          </b-button>
        </b-col>
      </b-col>
    </div>
  </b-modal>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      // Reactive data properties
      contarActivo: [],
      total: 0,
      totalActivos: 0,
      empresasConSucursales: [],
      selectedActivos: {
        act_id: null,
        act_id_usuario: null,
        act_id_sucursal: null,
        act_fecha_registro: '',
        act_marca: '',
        act_modelo: '',
        act_fabricante: '',
        act_numero_serie: '',
        act_ubicacion: '',
        act_factura: '',
        act_descripcion: '',
        nombre_empresa: null,
        nombre_tipo: '',
        nombre_usuario: '',
        tipoactivo: null,
        bjs_descripcion: '',
        apellido_usuario: '',
        nombre_sucursal: '',
        datos_servidor: {},
        datos_computador: {},
        direcciones_ip: [],
        act_id_empresa: null
      },
      modalShow: false,
      ConfirmaEliminadoModal: false,
      editorConfirmaModal: false,
      mantencionModal: false,
      activoParaMantencion: null,
      filtroModal: false,
      Tipos: [],
      sucursales: [],
      estados: [],
      empresa: [],
      usuariosDisponibles: [],
      NombreApellido: [],
      mostrarFiltros: false,
      filtros: {
        Tipos: [],
        sucursales: [],
        usuarios: [],
        estados: [],
        empresa: []
      },
      activos: [],
      activoss: [],
      cargando: false,
      error: null,
      Buscador: '',
      activosFiltros: [],
      paginaActual: 1,
      porPagina: 8,
      errors: {}
    }
  },

  computed: {
    // Computed properties
    empresaOpciones() {
      return this.empresa.map(e => ({ id: e.emp_id, nombre: e.emp_nombre }))
    },
    nombreTipoDisplay() {
      return this.selectedActivos.nombre_tipo || 'No definido'
    },
    fields() {
      return [
        { key: 'act_id', label: 'ID', thClass: 'actID', tdClass: 'actID' },
        { key: 'nombre_usuario', label: 'Nombre', thClass: '', tdClass: 'usuario' },
        { key: 'nombre_empresa', label: 'Empresa', class: 'd-none d-lg-table-cell' },
        { key: 'nombre_sucursal', label: 'Sucursal/Tipo', thClass: '', tdClass: 'tipo' },
        { key: 'act_fecha_registro', label: 'Fecha', class: 'd-none d-lg-table-cell' },
        { key: 'act_descripcion', label: 'Descricion', class: 'd-none d-lg-table-cell' },
        { key: 'acciones', label: '', thClass: 'text-center', tdClass: 'acciones', style: "color: black;" },
        { key: 'act_estado', label: '', thClass: 'text-center', tdClass: 'estados' }
      ]
    },
    filtrarActivos() {
      return this.activos.filter(activo => {
        const query = this.Buscador.toLowerCase()
        const Query = !query || Object.values(activo).some(val => String(val).toLowerCase().includes(query))
        const filtroSucursales = this.filtros.sucursales.length === 0 || this.filtros.sucursales.map(String).includes(String(activo.act_id_sucursal))
        const filtroUsuarios = this.filtros.usuarios.length === 0 || this.filtros.usuarios.map(String).includes(String(activo.usr_id))
        const filtroEstados = this.filtros.estados.length === 0 || this.filtros.estados.map(String).includes(String(activo.act_estado))
        const filtroTipos = this.filtros.Tipos.length === 0 || this.filtros.Tipos.map(String).includes(String(activo.act_id_tipo))
        const filtroEmpresa = this.filtros.empresa.length === 0 || this.filtros.empresa.map(id => {
          const emp = this.empresa.find(e => e.emp_id == id)
          return emp?.emp_nombre
        }).includes(activo.nombre_empresa)

        return Query && filtroSucursales && filtroUsuarios && filtroEstados && filtroTipos && filtroEmpresa
      })
    },
    sucursalesFiltradas() {
      if (!this.selectedActivos.act_id_empresa) return []
      const empresa = this.empresasConSucursales.find(e => e.emp_id === this.selectedActivos.act_id_empresa)
      return empresa ? empresa.sucursales : []
    },
    contarActivosPorSucursal() {
      const conteo = {}
      this.activos.forEach(activo => {
        const sucId = activo.act_id_sucursal
        if (conteo[sucId]) {
          conteo[sucId]++
        } else {
          conteo[sucId] = 1
        }
      })
      return conteo
    },
    activosFiltrados() {
      if (!this.Buscador) return this.activos
      return this.activos.filter(a =>
        a.act_nombre?.toLowerCase().includes(this.Buscador.toLowerCase()) ||
        a.act_descripcion?.toLowerCase().includes(this.Buscador.toLowerCase()) ||
        a.act_estado?.toLowerCase().includes(this.Buscador.toLowerCase())
      )
    },
    activosPaginados() {
      const start = (this.paginaActual - 1) * this.porPagina
      const end = start + this.porPagina;
      
      return this.filtrarActivos.slice(start, end)
    },
    sucursalesOpciones() {
      return this.sucursales
        .filter(s => {
          const tieneActivos = (this.contarActivosPorSucursal[s.suc_id] || 0) > 0
          if (this.filtros.empresa.length === 0) return tieneActivos
          const perteneceAEmpresaSeleccionada = this.filtros.empresa.includes(s.suc_id_empresa)
          return tieneActivos && perteneceAEmpresaSeleccionada
        })
        .map(s => {
          const cantidad = this.contarActivosPorSucursal[s.suc_id]
          return {
            id: s.suc_id,
            nombre: `${s.suc_nombre} (${cantidad})`
          }
        })
    },
    estadosOpciones() {
      const unicos = new Set()
      this.activos.forEach(activo => {
        if (activo.act_estado) {
          unicos.add(activo.act_estado)
        }
      })
      return Array.from(unicos).map((estado, index) => ({
        id: estado,
        nombre: estado
      }))
    },
    usuariosOpciones() {
      return this.usuariosDisponibles.map(u => ({
        id: u.usr_id_usuario,
        nombre: u.nombre_usuario || u.usr_nombre
      }))
    },
    usuariosNombreApellido() {
      return this.usuariosDisponibles.map(u => ({
        id: u.usr_id_usuario,
        nombre: `${u.usr_nombre} ${u.usr_apellido}`
      }))
    },
    TiposOpciones() {
      const unicos = new Map()
      this.activos.forEach(activo => {
        if (activo.act_id_tipo && activo.nombre_tipo) {
          unicos.set(activo.act_id_tipo, activo.nombre_tipo)
        }
      })
      return Array.from(unicos.entries()).map(([id, nombre]) => ({
        id,
        nombre
      }))
    }
  },

  watch: {
    paginaActual() {
      this.cargarActivos()
    }
  },

  methods: {
    // Methods
    async cargarActivos() {
      this.cargando = true
      try {
        const payload = {
          buscador: this.Buscador,
          filtros: {
            sucursales: this.filtros.sucursales,
            usuarios: this.filtros.usuarios,
            estados: this.filtros.estados,
            Tipos: this.filtros.Tipos,
            empresa: this.filtros.empresa
          }
        }
        const res = await axios.post('/Activos/filtros',
          JSON.stringify(payload),
          {
            headers: {
              'Content-Type': 'application/json'
            }
          }
        )

        this.activos = res.data
      } catch (err) {
        console.error('Error cargando activos:', err.response?.data || err.message)
      } finally {
        this.cargando = false
      }
    },

    async contarActivos() {
      try {
        const res = await axios.get('/Activos/contarActivos')
        this.contarActivo = res.data.Activos
        this.totalActivo = res.data.total
        this.cargarActivos()
      } catch (error) {
        console.error('Error cargando Activo:', error)
      }
    },

    editarActivo(activo) {
      Object.assign(this.selectedActivos, activo)
      console.log(this.selectedActivos)
      this.selectedActivos.act_id_tipo = Number(activo.act_id_tipo) || null
      this.selectedActivos.act_id_empresa = activo.emp_id || null
      const sucursal = this.sucursales.find(s => s.suc_id === activo.act_id_sucursal)
      this.selectedActivos.act_id_empresa = sucursal?.suc_id_empresa || null
      this.modalShow = true
    },

    aplicarFiltros() {
      this.filtroModal = false
    },

    limpiarFiltros() {
      this.filtros.Tipos.splice(0, this.filtros.Tipos.length)
      this.filtros.sucursales.splice(0, this.filtros.sucursales.length)
      this.filtros.usuarios.splice(0, this.filtros.usuarios.length)
      this.filtros.estados.splice(0, this.filtros.estados.length)
      this.filtros.empresa.splice(0, this.filtros.empresa.length)
    },

    async cargarTipos() {
      try {
        const res = await axios.get('/Activos/listaTipo')
        this.Tipos = res.data
      } catch (error) {
        console.error('Error cargando Tipos:', error)
      }
    },

    async cargarSucursales() {
      try {
        const res = await axios.get('/Activos/listaSucursal')
        this.sucursales = res.data
      } catch (error) {
        console.error('Error cargando sucursales:', error)
      }
    },

    async cargarEstados() {
      try {
        const res = await axios.get('/Activos/listaActivos')
        this.estados = res.data
      } catch (error) {
        console.error('Error cargando estados:', error)
      }
    },

    async cargarEmpresa() {
      try {
        const res = await axios.get('/Activos/listaEmpresa')
        this.empresa = res.data.filter(e => e.emp_estado === 'activo')
      } catch (error) {
        console.error('Error cargando empresas:', error)
      }
    },

    async cargarUsuarios() {
      try {
        const res = await axios.get('/Activos/listaUsuarios')
        this.usuariosDisponibles = res.data.filter(u => u.usr_estado === 'activo')
        this.NombreApellido = res.data.map(u => ({
          id: u.usr_id,
          nombre: `${u.usr_nombre} ${u.usr_apellido}`
        }))
      } catch (error) {
        console.error('Error al cargar usuarios', error)
      }
    },

    abrirModalMantencion(activo) {
      this.activoParaMantencion = activo
      this.mantencionModal = true
    },

    async confirmarEnvioMantencion() {
      const activo = this.activoParaMantencion

      if (!activo?.act_id || !activo?.act_id_usuario) {
        alert('Faltan datos para enviar a mantención.')
        return
      }
      try {
        await axios.post('/Mantencion/Mantencion', {
          act_id: activo.act_id,
          man_id_activo: activo.act_id,
          man_id_usuario: activo.act_id_usuario,
          man_fecha: new Date().toISOString().split('T')[0]
        })
        this.cargarActivos()
      } catch (error) {
        console.error('Error al enviar a mantención:', error)
        alert('Error al comunicar con el servidor.')
      } finally {
        this.mantencionModal = false
        this.activoParaMantencion = null
      }
    },

    async activarActivo(activo) {
      if (!activo?.act_id) {
        alert('Faltan datos para activar el activo.')
        return
      }
      try {
        await axios.post('/Activos/activar', { act_id: activo.act_id })
        this.cargarActivos()
      } catch (error) {
        console.error('Error al activar el activo:', error)
        alert('No se pudo activar el activo.')
      }
    },

    async darDeBajaActivo() {
      const activo = this.activoParaMantencion
      if (!activo?.act_id || !this.selectedActivos.bjs_descripcion?.trim()) {
        alert('Faltan datos para dar de baja.')
        return
      }
      try {
        const res = await axios.post('/activos/dardebaja', {
          bjs_id_activo: activo.act_id,
          bjs_id_usuario: activo.act_id_usuario,
          bjs_descripcion: this.selectedActivos.bjs_descripcion,
        })
        this.cargarActivos()
      } catch (error) {
        console.error('Error al dar de baja:', error)
      } finally {
        this.mantencionModal = false
        this.activoParaMantencion = null
      }
    },

    cargarArchivo(event) {
      const file = event.target.files[0]
      if (file) {
        console.log('Archivo seleccionado:', file)
      }
    },

    confirmarEdicion() {
      this.editorConfirmaModal = true
    },

    async editar() {
      console.log(this.selectedActivos)
      try {
        const payload = { ...this.selectedActivos }
        await axios.put(`/activos/ActualizarActivo/${this.selectedActivos.act_id}`, payload)
        this.cargarActivos()
        this.modalShow = false
      } catch (error) {
        console.error('Error al actualizar activo:', error)
        alert('Error al guardar los cambios.')
      } finally {
        this.editorConfirmaModal = false
      }
    },

    cancelarEditar() {
      this.modalShow = false
    },

    confirmarEliminar(activo) {
      this.selectedActivos.act_id = activo.act_id
      this.ConfirmaEliminadoModal = true
    },

    async eliminarActivos() {
      try {
        const payload = { ...this.selectedActivos }
        const res = await axios.put(`/activos/Eliminar/${this.selectedActivos.act_id}`)
        this.cargarActivos()
        if (res.data.status === 'update') {
          this.activos = this.activos.filter(a => a.act_id !== this.selectedActivos.act_id)
        }
      } catch (err) {
        console.error('Error al eliminar:', err)
      } finally {
        this.ConfirmaEliminadoModal = false
      }
    },

    async EmpresasConSucursales() {
      try {
        const [empresaRes, sucursalesRes] = await Promise.all([
          axios.get('/Activos/listaEmpresa'),
          axios.get('/Activos/listaSucursal')
        ])
        const empresasActivas = empresaRes.data.filter(e => e.emp_estado === 'activo')
        this.empresasConSucursales = empresasActivas.map(empresa => {
          const sucursalesDeEmpresa = sucursalesRes.data.filter(s => s.suc_id_empresa === empresa.emp_id)
          return {
            ...empresa,
            sucursales: sucursalesDeEmpresa
          }
        })
      } catch (error) {
        console.error('Error al cargar empresas o sucursales', error)
      }
    }
  },

  mounted() {
    this.cargarActivos()
    this.cargarTipos()
    this.cargarSucursales()
    this.cargarUsuarios()
    this.cargarEstados()
    this.cargarEmpresa()
    this.contarActivos()
  }
}
</script>

<style>
  /* Puedes agregar estilos específicos aquí */
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.1s;
  }
  
  .custom-rounded-table {
    border-radius: 12px;
    overflow: hidden; /* importante para que las esquinas internas también se redondeen */
  }
  .bg-card{
    border: none;
  }
  .navbar-brand {
    text-align: center;
  }
  .estados {
    width: 0px;
    padding: 0.2rem 0.2rem;
  }
  .acciones{
    width: 0 !important;
  }
  #estados{
    width: 0 !important;
  }
  td.estados {
    width: 0px;
    padding: 0.2rem 0.2rem;
  }
  td.usuario{
    width: 120px;
  }
  td.actID{
    width: 30px;
  }
  td.tipo{
    text-align: start;
  }
</style>