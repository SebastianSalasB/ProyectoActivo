<template>
  <b-container fluid>
    <b-row>
      <!-- Barra de búsqueda -->
      <b-col md="12" class="mb-4">
        <h3>Buscar Activos</h3>
        <b-input-group class="mt-2">
          <b-form-input 
            v-model="Buscador" 
            type="search" 
            placeholder="Buscar por palabra clave..." 
            @input="paginaActual = 1; cargarActivosDebounced()"
          />
          <b-input-group-append v-if="!mostrarFiltros">
            <b-button variant="outline-secondary" @click="mostrarFiltros = true">
              <i class="fa-solid fa-filter fa-sm"></i> Filtros
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </b-col>

      <!-- Barra lateral de filtros -->
      <transition name="fade">
        <b-col md="2" v-if="mostrarFiltros">
          <b-card class="shadow-sm" style="border: none;">
            <h5 class="mb-3">Filtros</h5>              
            <!-- Filtro por Tipo -->
            <b-form-group label="Tipo" class="checkbox-grid-2cols">
              <b-form-checkbox-group
                v-model="filtros.Tipos"
                :options="TiposOpciones"
                value-field="id"
                text-field="nombre"
                stacked 
                switches
                style="flex-wrap: wrap; justify-content: flex-start;"
              />
            </b-form-group>
            <hr />
            <!-- Filtro por Empresa -->
            <b-form-group label="Empresa" class="checkbox-grid-2cols">
              <b-form-checkbox-group
                v-model="filtros.empresa"
                :options="empresaOpciones"
                value-field="id"
                text-field="nombre"
                stacked
                switches
              />
            </b-form-group>
            <hr />
            <!-- Filtro por Sucursal -->
            <b-form-group label="Sucursal" class="checkbox-grid-2cols">
              <b-form-checkbox-group
                v-model="filtros.sucursales"
                :options="sucursalesOpciones"
                value-field="id"
                text-field="nombre"
                stacked
                switches
              />
            </b-form-group>
            <hr />
            <!-- Filtro por Estado -->
            <b-form-group label="Estado" class="checkbox-grid-2cols">
              <b-form-checkbox-group
                v-model="filtros.estados"
                :options="estadosOpciones"
                value-field="id"
                text-field="nombre"
                stacked
                switches
              />
            </b-form-group>
            <b-row>
              <b-col sm>
                <b-button size="sm" class="mb-3" variant="danger" @click="limpiarFiltros">
                  <i class="fa-solid fa-brush fa-sm"></i> Limpiar Filtros
                </b-button>
              </b-col>
              <b-col sm>
                <b-button size="sm" class="mb-3" variant="secondary" @click="mostrarFiltros = false">
                  <i class="fa-solid fa-xmark"></i> Ocultar filtros
                </b-button>
              </b-col>
            </b-row>
          </b-card>
        </b-col>
      </transition> 

      <!-- Tabla de resultados -->
      <b-col :md="mostrarFiltros ? 10 : 12" style="padding: 20px;">
        <div v-if="cargando" class="cargando-overlay">
          <b-spinner variant="primary" class="mb-2" />
          <div>Cargando...</div>
        </div>
        <b-table 
          :items="activosPaginados" 
          :fields="fields" 
          hover 
          class="custom-rounded-table"
        > 
          <template #cell(act_id)="data">
            <div>
              <span style="font-size: 1rem; text-align: start; padding: 0.2rem;">{{ data.value }}</span>
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
            <b-dropdown size="sm" variant="outline-success" no-caret right style="margin-top: 12px;">
              <template #button-content>
                <i class="fa-solid fa-gear fa-xl"></i>
              </template>
              <!-- Editar: solo si NO está en baja ni eliminado -->
              <b-dropdown-item 
                v-if="!['eliminado', 'baja'].includes(data.item.act_estado)"
                @click="editarActivo(data.item)"
              >
                <i class="fa-solid fa-pen-to-square" style="color: #258f24; margin-right: 6px;"></i> Editar
              </b-dropdown-item>
              <!-- Eliminar: solo si NO está en baja ni eliminado -->
              <b-dropdown-item 
                v-if="!['eliminado', 'baja'].includes(data.item.act_estado)"
                @click="confirmarEliminar(data.item)"
              >
                <i class="fa-solid fa-trash" style="color: #8e0101; margin-right: 6px;"></i> Eliminar
              </b-dropdown-item>
              <!-- Mantención: solo si está activo -->
              <b-dropdown-item 
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
                title="Activo"
                style="font-size: 0.870rem; padding: 0.2rem;"
              ></i>
              <i
                v-else-if="data.item.act_estado === 'eliminado'"
                class="fa-solid fa-circle-xmark text-danger"
                title="Eliminado"
                style="font-size: 0.870rem; padding: 0.2rem;"
              ></i>
              <i
                v-else-if="data.item.act_estado === 'baja'"
                class="fa-solid fa-ban" 
                title="Baja"
                style="color: #ff4747; font-size: 0.870rem; padding: 0.2rem;"
              ></i>
              <i 
                v-else-if="data.item.act_estado === 'mantenimiento'" 
                class="fa-solid fa-circle-exclamation" 
                title="Mantenimiento" 
                style="color: #ffe852; font-size: 0.870rem; padding: 0.2rem;"
              ></i>
              <i
                v-else
                class="fa-solid fa-circle-question text-secondary"
                title="Desconocido"
                style="font-size: 0.870rem; padding: 0.2rem;"
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
            size="md"
          />
        </div>
      </b-col>
    </b-row>

    <!-- Modal de edición -->
    <b-modal v-model="modalShow" title="Datos del Activo" size="lg" hide-footer>
      <b-form class="small">
        <b-row>
          <!-- Empresas -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Empresa" :invalid-feedback="errors.act_id_empresa">
              <b-form-select
                v-model="selectedActivos.act_id_empresa"
                :options="empresaOpciones"
                value-field="id"
                text-field="nombre"
                placeholder="Selecciona una empresa"
                :state="errors.act_id_empresa ? false : null"
                required
              />
            </b-form-group>
          </b-col>
          <!-- Sucursal -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Sucursal" :invalid-feedback="errors.act_id_sucursal">
              <b-form-select
                v-model="selectedActivos.act_id_sucursal"
                :options="sucursalesOpciones"
                value-field="id"
                text-field="nombre"
                placeholder="Seleccione una sucursal"
                :state="errors.act_id_sucursal ? false : null"
                required
              />
            </b-form-group>
          </b-col>
          <!-- Usuario -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Usuario" :invalid-feedback="errors.act_id_usuario">
              <b-form-select
                v-model="selectedActivos.act_id_usuario"
                :options="usuariosOpciones"
                value-field="id"
                text-field="nombre"
                placeholder="Seleccione un usuario"
                :state="errors.act_id_usuario ? false : null"
                required
              />
            </b-form-group>
          </b-col>
          <!-- Fecha Registro -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Fecha Registro" :invalid-feedback="errors.act_fecha_registro">
              <b-form-input 
                type="date" 
                v-model="selectedActivos.act_fecha_registro" 
                :state="errors.act_fecha_registro ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <!-- Marca -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Marca" :invalid-feedback="errors.act_marca">
              <b-form-input 
                v-model="selectedActivos.act_marca" 
                :state="errors.act_marca ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <!-- Modelo -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Modelo" :invalid-feedback="errors.act_modelo">
              <b-form-input 
                v-model="selectedActivos.act_modelo" 
                :state="errors.act_modelo ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <!-- Fabricante -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Fabricante" :invalid-feedback="errors.act_fabricante">
              <b-form-input 
                v-model="selectedActivos.act_fabricante" 
                :state="errors.act_fabricante ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <!-- N° de Serie -->
          <b-col md="6" class="mb-2">
            <b-form-group label="N° de Serie" :invalid-feedback="errors.act_numero_serie">
              <b-form-input 
                v-model="selectedActivos.act_numero_serie" 
                :state="errors.act_numero_serie ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <!-- Ubicación -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Ubicación" :invalid-feedback="errors.act_ubicacion">
              <b-form-input 
                v-model="selectedActivos.act_ubicacion" 
                :state="errors.act_ubicacion ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <!-- Tipo -->
          <b-col md="6" class="mb-2">
            <b-form-group label="Tipo">
              <b-form-input :value="nombreTipoDisplay" disabled />
            </b-form-group>
          </b-col>
        </b-row>
        <!-- Datos especiales según tipo de activo -->
        <div v-if="selectedActivos.act_id_tipo === 1">
          <!-- Computador -->
          <b-row>
            <b-col md="6" class="mb-2">
              <b-form-group label="RAM" :invalid-feedback="errors.com_ram">
                <b-form-input
                  v-model="selectedActivos.datos_computador.com_ram"
                  :state="errors.com_ram ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
            <b-col md="6" class="mb-2">
              <b-form-group label="CPU" :invalid-feedback="errors.com_cpu">
                <b-form-input
                  v-model="selectedActivos.datos_computador.com_cpu"
                  :state="errors.com_cpu ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="6" class="mb-2">
              <b-form-group label="Disco" :invalid-feedback="errors.com_disco">
                <b-form-input
                  v-model="selectedActivos.datos_computador.com_disco"
                  :state="errors.com_disco ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
            <b-col md="6" class="mb-2">
              <b-form-group label="Sistema Operativo" :invalid-feedback="errors.com_id_sistema_operativo">
                <b-form-select
                  v-model="selectedActivos.datos_computador.com_id_sistema_operativo"
                  :options="sistemasOperativosOpciones"
                  value-field="id"
                  text-field="nombre"
                  placeholder="Seleccione un sistema operativo"
                  :state="errors.com_id_sistema_operativo ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
          </b-row>
        </div>
        <div v-if="selectedActivos.act_id_tipo === 2">
          <!-- Servidor -->
          <b-row>
            <b-col md="6" class="mb-2">
              <b-form-group label="Nombre" :invalid-feedback="errors.ser_nombre">
                <b-form-input 
                  v-model="selectedActivos.datos_servidor.ser_nombre" 
                  :state="errors.ser_nombre ? false : null"
                  required 
                />
              </b-form-group>
            </b-col>
            <b-col md="6" class="mb-2">
              <b-form-group label="RAM" :invalid-feedback="errors.ser_ram">
                <b-form-input
                  v-model="selectedActivos.datos_servidor.ser_ram"
                  :state="errors.ser_ram ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="6" class="mb-2">
              <b-form-group label="CPU" :invalid-feedback="errors.ser_cpu">
                <b-form-input
                  v-model="selectedActivos.datos_servidor.ser_cpu"
                  :state="errors.ser_cpu ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
            <b-col md="6" class="mb-2">
              <b-form-group label="Disco" :invalid-feedback="errors.ser_disco">
                <b-form-input
                  v-model="selectedActivos.datos_servidor.ser_disco"
                  :state="errors.ser_disco ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="6" class="mb-2">
              <b-form-group label="Sistema Operativo" :invalid-feedback="errors.ser_id_sistema_operativo">
                <b-form-select
                  v-model="selectedActivos.datos_servidor.ser_id_sistema_operativo"
                  :options="sistemasOperativosOpciones"
                  value-field="id"
                  text-field="nombre"
                  placeholder="Seleccione un sistema operativo"
                  :state="errors.ser_id_sistema_operativo ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
            <b-col md="6" class="mb-2">
              <b-form-group label="Ranuras" :invalid-feedback="errors.ser_ranuras_ram">
                <b-form-input
                  v-model="selectedActivos.datos_servidor.ser_ranuras_ram"
                  :state="errors.ser_ranuras_ram ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="6" class="mb-2">
              <b-form-group label="Cantidad Max CPU" :invalid-feedback="errors.ser_cantidad_max_cpu">
                <b-form-input
                  v-model="selectedActivos.datos_servidor.ser_cantidad_max_cpu"
                  :state="errors.ser_cantidad_max_cpu ? false : null"
                  required
                />
              </b-form-group>
            </b-col>
          </b-row>
        </div>
        <!-- Factura, descripción y IPs -->
        <b-row class="mt-3">
          <b-col md="6" class="mb-2">
            <b-form-group label="Factura" :invalid-feedback="errors.act_factura">
              <b-form-input 
                v-model="selectedActivos.act_factura" 
                :state="errors.act_factura ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <b-col md="6" class="mb-2">
            <b-form-group label="Descripción" :invalid-feedback="errors.act_descripcion">
              <b-form-textarea 
                v-model="selectedActivos.act_descripcion" 
                :state="errors.act_descripcion ? false : null"
                required 
              />
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Direcciones IP">
              <div v-if="selectedActivos.direcciones_ip.length === 0" class="text-muted mb-2">
                No hay direcciones IP registradas.
              </div>
              <div v-for="(ip, index) in selectedActivos.direcciones_ip" :key="index" class="mb-2">
                <b-input-group>
                  <b-form-input v-model="selectedActivos.direcciones_ip[index]" />
                  <b-input-group-append>
                    <b-button variant="outline-danger" @click="selectedActivos.direcciones_ip.splice(index, 1)">
                      <i class="fa-solid fa-circle-xmark fa-lg"></i> Eliminar
                    </b-button>
                  </b-input-group-append>
                </b-input-group>
              </div>
              <b-button variant="outline-primary" size="sm" @click="agregarIP">
                <i class="fa-solid fa-circle-plus fa-lg"></i> Agregar IP
              </b-button>
            </b-form-group>
          </b-col>          
        </b-row>
        <!-- Botones -->
        <div class="text-end mt-3">
          <b-button variant="outline-success" class="me-2" @click="confirmarEdicion">
            <i class="fa-solid fa-floppy-disk fa-lg"></i> Guardar
          </b-button>
          <b-button variant="outline-secondary" @click="cancelarEditar">
            <i class="fa-solid fa-circle-xmark fa-lg"></i> Cancelar
          </b-button>
        </div>
      </b-form>
    </b-modal>   
    <!-- Modal de confirmación de eliminación -->
    <b-modal 
      v-model="ConfirmaEliminadoModal" 
      title="Confirmar eliminación" 
      @ok="eliminarActivos" 
      ok-title="Sí, eliminar" 
      cancel-title="Cancelar" 
      ok-variant="danger"
    >
      ¿Estás seguro de que deseas eliminar el activo? 
    </b-modal>
    <!-- Modal de confirmación de edición -->
    <b-modal 
      v-model="editorConfirmaModal" 
      title="Confirmar modificación" 
      @ok="editar" 
      ok-title="Sí, guardar cambios" 
      cancel-title="Cancelar" 
      ok-variant="success"
    >
      ¿Estás seguro de que deseas guardar los cambios al activo?
    </b-modal>
    <!-- Modal para enviar a mantención o dar de baja -->
    <b-modal 
      v-model="mantencionModal" 
      title="Enviar a mantención o Dar de Baja" 
      @ok="confirmarEnvioMantencion" 
      ok-title="Mantención" 
      cancel-title="Cancelar" 
      ok-variant="primary"
    >
      <div class="mb-3">
        ¿Estás seguro de que deseas mandar a mantención el activo?
      </div>
      <b-row>
        <b-col md="6" class="mb-2">
          <b-form-group label="Usuario" :invalid-feedback="errors.act_id_usuario">
            <b-form-select
              v-model="selectedActivos.act_id_usuario"
              :options="usuariosOpciones"
              value-field="id"
              text-field="nombre"
              placeholder="Seleccione un usuario"
              :state="errors.act_id_usuario ? false : null"
              required
            />
          </b-form-group>
        </b-col>
        <b-col md="12" class="mb-2">
          <b-form-group label="Descripción por la cual se da de baja" :invalid-feedback="errors.bjs_descripcion">
            <b-form-textarea 
              v-model="selectedActivos.bjs_descripcion" 
              :state="errors.bjs_descripcion ? false : null"
            />
          </b-form-group>
        </b-col>
        <b-col md="12" class="mb-2">
          <b-form-group label="Selecciona un archivo de baja">
            <input
              id="archivo"
              type="file"
              class="form-control"
              @change="cargarArchivo"
            />
          </b-form-group>
        </b-col>
        <b-col md="12">
          <b-button variant="danger" @click="darDeBajaActivo">
            Dar de Baja
          </b-button>
        </b-col>
      </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import axios from 'axios';

// Función antirrebote personalizada para evitar la dependencia de lodash
function debounce(func, wait) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(this, args), wait);
  };
}

export default {
  data() {
    return {
      contarActivo: [],
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
        apellido_usuario: '',
        nombre_sucursal: '',
        datos_servidor: {},
        datos_computador: {},
        direcciones_ip: [],
        act_id_empresa: null,
        act_id_tipo: null,
        bjs_descripcion: '',
      },
      modalShow: false,
      ConfirmaEliminadoModal: false,
      editorConfirmaModal: false,
      mantencionModal: false,
      activoParaMantencion: null,
      sucursales: [],
      estados: [],
      empresa: [],
      usuariosDisponibles: [],
      sistemasOperativosDisponibles: [],
      mostrarFiltros: false,
      filtros: {
        Tipos: [],
        sucursales: [],
        usuarios: [],
        estados: [],
        empresa: [],
      },
      activos: [],
      cargando: false,
      error: null,
      Buscador: '',
      paginaActual: 1,
      porPagina: 8,
      errors: {},
    };
  },

  computed: {
    empresaOpciones() {
      return this.empresa.map(e => ({ id: e.emp_id, nombre: e.emp_nombre }));
    },
    nombreTipoDisplay() {
      return this.selectedActivos.nombre_tipo || 'No definido';
    },
    fields() {
      return [
        { key: 'act_id', label: 'ID', thClass: 'actID', tdClass: 'actID' },
        { key: 'nombre_usuario', label: 'Nombre', thClass: '', tdClass: 'usuario' },
        { key: 'nombre_empresa', label: 'Empresa', class: 'd-none d-lg-table-cell' },
        { key: 'nombre_sucursal', label: 'Sucursal/Tipo', thClass: '', tdClass: 'tipo' },
        { key: 'act_fecha_registro', label: 'Fecha', class: 'd-none d-lg-table-cell' },
        { key: 'act_descripcion', label: 'Descripción', class: 'd-none d-lg-table-cell' },
        { key: 'acciones', label: '', thClass: 'text-center', tdClass: 'acciones' },
        { key: 'act_estado', label: '', thClass: 'text-center', tdClass: 'estados' },
      ];
    },
    filtrarActivos() {
      return this.activos.filter(activo => {
        const query = this.Buscador.toLowerCase();
        const Query = !query || Object.values(activo).some(val => String(val || '').toLowerCase().includes(query));
        const filtroSucursales = this.filtros.sucursales.length === 0 || this.filtros.sucursales.map(String).includes(String(activo.act_id_sucursal));
        const filtroUsuarios = this.filtros.usuarios.length === 0 || this.filtros.usuarios.map(String).includes(String(activo.usr_id));
        const filtroEstados = this.filtros.estados.length === 0 || this.filtros.estados.map(String).includes(String(activo.act_estado));
        const filtroTipos = this.filtros.Tipos.length === 0 || this.filtros.Tipos.map(String).includes(String(activo.act_id_tipo));
        const filtroEmpresa = this.filtros.empresa.length === 0 || this.filtros.empresa.map(String).includes(String(activo.act_id_empresa));
        return Query && filtroSucursales && filtroUsuarios && filtroEstados && filtroTipos && filtroEmpresa;
      });
    },
    contarActivosPorSucursal() {
      const conteo = {};
      this.activos.forEach(activo => {
        const sucId = activo.act_id_sucursal;
        conteo[sucId] = (conteo[sucId] || 0) + 1;
      });
      return conteo;
    },
    activosPaginados() {
      const start = (this.paginaActual - 1) * this.porPagina;
      const end = start + this.porPagina;
      return this.filtrarActivos.slice(start, end);
    },
    sucursalesOpciones() {
      return this.sucursales
        .filter(s => {
          const tieneActivos = (this.contarActivosPorSucursal[s.suc_id] || 0) > 0;
          if (this.filtros.empresa.length === 0) return tieneActivos;
          return tieneActivos && this.filtros.empresa.includes(s.suc_id_empresa);
        })
        .map(s => ({
          id: s.suc_id,
          nombre: s.suc_nombre,
        }));
    },
    estadosOpciones() {
      const unicos = new Set();
      this.activos.forEach(activo => {
        if (activo.act_estado) unicos.add(activo.act_estado);
      });
      return Array.from(unicos).map(estado => ({
        id: estado,
        nombre: estado.charAt(0).toUpperCase() + estado.slice(1),
      }));
    },
    sistemasOperativosOpciones() {
      return this.sistemasOperativosDisponibles.map(s => ({
        id: s.sio_id,
        nombre: `${s.sio_nombre} ${s.sio_version}`,
      }));
    },
    usuariosOpciones() {
      return this.usuariosDisponibles.map(u => ({
        id: u.usr_id_usuario,
        nombre: `${u.usr_nombre} ${u.usr_apellido}`,
      }));
    },
    TiposOpciones() {
      return this.Tipos.map(t => ({
        id: t.tip_id,
        nombre: t.tip_nombre,
      }));
    },
  },

  watch: {
    paginaActual() {
      this.cargarActivosDebounced();
    },
  },

  methods: {
    cargarActivosDebounced: debounce(async function () {
      await this.cargarActivos();
    }, 300),
    async cargarActivos() {
      this.cargando = true;
      try {
        const payload = {
          buscador: this.Buscador,
          filtros: this.filtros,
        };
        const res = await axios.post('/Activos/filtros', payload, {
          headers: { 'Content-Type': 'application/json' },
        });
        this.activos = res.data || [];
      } catch (err) {
        console.error('Error cargando activos:', err.response?.data || err.message);
      } finally {
        this.cargando = false;
      }
    },
    async contarActivos() {
      try {
        const res = await axios.get('/Activos/contarActivos');
        this.contarActivo = res.data.Activos || [];
        this.totalActivos = res.data.total || 0;
      } catch (error) {
        console.error('Error cargando activo:', error);
      }
    },
    async cargarTipos() {
      try {
        const res = await axios.get('/Activos/listaTipo');
        this.Tipos = res.data || [];
      } catch (error) {
        console.error('Error cargando tipos:', error);
      }
    },
    async cargarSucursales() {
      try {
        const res = await axios.get('/Activos/listaSucursal');
        this.sucursales = res.data || [];
      } catch (error) {
        console.error('Error cargando sucursales:', error);
      }
    },
    async cargarEstados() {
      try {
        const res = await axios.get('/Activos/listaEstados');
        this.estados = res.data || [];
      } catch (error) {
        console.error('Error cargando estados:', error);
      }
    },
    async cargarEmpresa() {
      try {
        const res = await axios.get('/Activos/listaEmpresa');
        this.empresa = res.data.filter(e => e.emp_estado === 'activo') || [];
      } catch (error) {
        console.error('Error cargando empresas:', error);
      }
    },
    async cargarUsuarios() {
      try {
        const res = await axios.get('/Activos/listaUsuarios');
        this.usuariosDisponibles = res.data.filter(u => u.usr_estado === 'activo') || [];
      } catch (error) {
        console.error('Error al cargar usuarios:', error);
      }
    },
    async cargarSistemasOperativos() {
      try {
        const res = await axios.get('/Activos/listaSistemaOperativo');
        this.sistemasOperativosDisponibles = res.data || [];
      } catch (error) {
        console.error('Error cargando sistemas operativos:', error);
      }
    },
    editarActivo(activo) {
      this.selectedActivos = {
        ...activo,
        act_id_tipo: Number(activo.act_id_tipo) || null,
        datos_computador: activo.datos_computador || {},
        datos_servidor: activo.datos_servidor || {},
        direcciones_ip: activo.direcciones_ip || [],
        act_id_empresa: this.sucursales.find(s => s.suc_id === activo.act_id_sucursal)?.suc_id_empresa || null,
      };
      this.errors = {};
      this.modalShow = true;
    },
    limpiarFiltros() {
      this.filtros = { Tipos: [], sucursales: [], usuarios: [], estados: [], empresa: [] };
      this.cargarActivosDebounced();
    },
    agregarIP() {
      const lastIP = this.selectedActivos.direcciones_ip[this.selectedActivos.direcciones_ip.length - 1];
      if (lastIP && lastIP.trim() === '') {
        console.error('Complete la última dirección IP antes de agregar una nueva.');
        return;
      }
      this.selectedActivos.direcciones_ip.push('');
    },
    async cargarArchivo(event) {
      const file = event.target.files[0];
      if (!file) {
        console.error('No se seleccionó ningún archivo.');
        return;
      }
      const formData = new FormData();
      formData.append('file', file);
      try {
        await axios.post('/Activos/uploadFile', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        console.log('Archivo subido correctamente');
      } catch (error) {
        console.error('Error al subir archivo:', error.response?.data || error.message);
      }
    },
    confirmarEdicion() {
      this.errors = {};
      const requiredFields = [
        'act_id_empresa',
        'act_id_sucursal',
        'act_id_usuario',
        'act_fecha_registro',
        'act_marca',
        'act_modelo',
        'act_fabricante',
        'act_numero_serie',
        'act_ubicacion',
        'act_factura',
        'act_descripcion',
      ];
      let valid = true;
      requiredFields.forEach(field => {
        if (!this.selectedActivos[field]?.trim()) {
          this.errors[field] = 'Este campo es obligatorio';
          valid = false;
        }
      });
      if (this.selectedActivos.act_id_tipo === 1) {
        ['com_ram', 'com_cpu', 'com_disco', 'com_id_sistema_operativo'].forEach(field => {
          if (!this.selectedActivos.datos_computador[field]?.toString().trim()) {
            this.errors[field] = 'Este campo es obligatorio';
            valid = false;
          }
        });
      } else if (this.selectedActivos.act_id_tipo === 2) {
        [
          'ser_nombre',
          'ser_ram',
          'ser_cpu',
          'ser_disco',
          'ser_id_sistema_operativo',
          'ser_ranuras_ram',
          'ser_cantidad_max_cpu',
        ].forEach(field => {
          if (!this.selectedActivos.datos_servidor[field]?.toString().trim()) {
            this.errors[field] = 'Este campo es obligatorio';
            valid = false;
          }
        });
      }
      if (valid) this.editorConfirmaModal = true;
      else console.error('Por favor, completa todos los campos obligatorios');
    },
    async editar() {
      try {
        const payload = { ...this.selectedActivos };
        await axios.put(`/activos/ActualizarActivo/${this.selectedActivos.act_id}`, payload);
        console.log('Activo actualizado correctamente');
        await this.cargarActivos();
        this.modalShow = false;
      } catch (error) {
        console.error('Error al actualizar activo:', error.response?.data || error.message);
      } finally {
        this.editorConfirmaModal = false;
      }
    },
    cancelarEditar() {
      this.modalShow = false;
      this.errors = {};
      this.selectedActivos = {
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
        apellido_usuario: '',
        nombre_sucursal: '',
        datos_servidor: {},
        datos_computador: {},
        direcciones_ip: [],
        act_id_empresa: null,
        act_id_tipo: null,
        bjs_descripcion: '',
      };
    },
    confirmarEliminar(activo) {
      this.selectedActivos = { act_id: activo.act_id };
      this.ConfirmaEliminadoModal = true;
    },
    async eliminarActivos() {
      try {
        await axios.put(`/activos/Eliminar/${this.selectedActivos.act_id}`);
        console.log('Activo eliminado correctamente');
        await this.cargarActivos();
      } catch (err) {
        console.error('Error al eliminar:', err.response?.data || err.message);
      } finally {
        this.ConfirmaEliminadoModal = false;
      }
    },
    abrirModalMantencion(activo) {
      this.activoParaMantencion = { ...activo };
      this.selectedActivos = {
        act_id: activo.act_id,
        act_id_usuario: activo.act_id_usuario,
        bjs_descripcion: '',
      };
      this.mantencionModal = true;
    },
    async confirmarEnvioMantencion() {
      const activo = this.activoParaMantencion;
      if (!activo?.act_id || !this.selectedActivos.act_id_usuario) {
        console.error('Faltan datos para enviar a mantención.');
        return;
      }
      try {
        await axios.post('/Mantencion/Mantencion', {
          act_id: activo.act_id,
          man_id_activo: activo.act_id,
          man_id_usuario: this.selectedActivos.act_id_usuario,
          man_fecha: new Date().toISOString().split('T')[0],
        });
        console.log('Activo enviado a mantención');
        await this.cargarActivos();
      } catch (error) {
        console.error('Error al enviar a mantención:', error.response?.data || error.message);
      } finally {
        this.mantencionModal = false;
        this.activoParaMantencion = null;
      }
    },
    async activarActivo(activo) {
      if (!activo?.act_id) {
        console.error('Faltan datos para activar el activo.');
        return;
      }
      try {
        await axios.post('/Activos/activar', { act_id: activo.act_id });
        console.log('Activo activado correctamente');
        await this.cargarActivos();
      } catch (error) {
        console.error('Error al activar el activo:', error.response?.data || error.message);
      }
    },
    async darDeBajaActivo() {
      const activo = this.activoParaMantencion;
      if (!activo?.act_id || !this.selectedActivos.bjs_descripcion?.trim()) {
        console.error('Faltan datos para dar de baja.');
        return;
      }
      try {
        await axios.post('/activos/dardebaja', {
          bjs_id_activo: activo.act_id,
          bjs_id_usuario: this.selectedActivos.act_id_usuario,
          bjs_descripcion: this.selectedActivos.bjs_descripcion,
        });
        console.log('Activo dado de baja correctamente');
        await this.cargarActivos();
      } catch (error) {
        console.error('Error al dar de baja:', error.response?.data || error.message);
      } finally {
        this.mantencionModal = false;
        this.activoParaMantencion = null;
      }
    },
  },

  async mounted() {
    this.cargando = true;
    try {
      await Promise.all([
        this.cargarActivos(),
        this.cargarTipos(),
        this.cargarSucursales(),
        this.cargarUsuarios(),
        this.cargarEstados(),
        this.cargarEmpresa(),
        this.cargarSistemasOperativos(),
        this.contarActivos(),
      ].map(p => p.catch(e => {
        console.error('Error en carga inicial:', e.response?.data || e.message);
        return null;
      })));
    } finally {
      this.cargando = false;
    }
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.1s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
.custom-rounded-table {
  border-radius: 12px; 
}
.dropdown-menu {
  right: 0 !important;
}
.estados {
  width: 50px;
  padding: 0.2rem;
}
.acciones {
  width: 50px !important;
}
td.usuario {
  width: 120px;
}
td.actID {
  width: 30px;
}
td.tipo {
  text-align: start;
}
.cargando-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  font-weight: bold;
  font-size: 1.2rem;
  color: #007bff; 
}
.checkbox-grid-2cols {
  display: grid;
  grid-template-columns: repeat(2, 6rem);
}
.pagination .page-link {
  background-color: #838383;
  color: white;
}
.pagination .page-link:hover {
  background-color: #505050;
  color: white;
}
.pagination {
  border-color: rgb(22, 22, 22);
}
.pagination .page-item.active .page-link {
  border-color: rgb(34, 34, 34);
  background-color: #505050;
  color: white;
}
</style>