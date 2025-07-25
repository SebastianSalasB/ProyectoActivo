<template> 
  <!-- Barra de búsqueda -->
    <b-navbar toggleable="lg" type="" variant="" class="mb-4 ">
      <b-container >
        <b-navbar-brand href="#" style="color: white;">Buscar Activos</b-navbar-brand>
        <b-input-group class="mt-2">
          <b-form-input 
            v-model="Buscador" 
            type="search" 
            placeholder="Buscar por palabra clave..." 
            @input="paginaActual = 1"  
          />
          <b-input-group-append v-if="!mostrarFiltros">
            <b-button variant="outline-light" @click="mostrarFiltros = true">
              <i class="fa-solid fa-filter fa-sm" style="color: rgb(255 255 255);"></i>   filtros
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
          <b-col cols="12" md="2"  v-if="mostrarFiltros">
            <b-card class="bg-card shadow-sm" style="color:white ; border: none;">
              <h5 class="mb-3" >Filtros</h5>              
              <!-- Filtro por Tipo -->
              <b-form-group label="Tipo">
                <b-form-checkbox-group
                  v-model="filtros.Tipos"
                  :options="TiposOpciones"
                  value-field="id"
                  text-field="nombre"
                  stacked
                  style="flex-wrap: wrap !important; justify-content: flex-start !important; "
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
                    <b-button size="sm" class="mb-3"  variant="danger" @click="limpiarFiltros">
                      <i class="fa-solid fa-brush fa-sm" style="color: #ffffff;"></i> Limpiar Filtros
                    </b-button>
                  </div>
                </b-col>
                <b-col>
                  <b-button  variant="outline-light" size="sm" class="mb-3 small" @click="mostrarFiltros = false">
                    <i class="fa-solid fa-xmark"></i> Ocultar filtros
                  </b-button>
                </b-col>
              </b-row>
            </b-card>
            <p></p>
          </b-col>
        </transition>
        <!-- Tabla de resultados -->
        <b-col :md="mostrarFiltros ? 10 : 12"  style="text-align: left">
          <b-spinner
            v-if="cargando"
            label="Cargando activos..."
            variant="primary"
            style="width: 3rem; height: 3rem; margin: 2rem auto; display: block;"
          ></b-spinner>
          <b-table  v-else :items="filtrarActivos" :fields="fields" :per-page="porPagina" :current-page="paginaActual" class="custom-rounded-table" > 
            <template #cell(act_id)="data" id="actID">
              <div>
                <span  style="font-size: 1rem; margin-top: 25px; text-align: start; padding: 0.2rem 0.2rem;">{{ data.value }}</span>
              </div>
            </template>
            <template #cell(nombre_usuario)="data" #cell(apellido_usuario)> 
              <div>
                <span >{{data.item.nombre_usuario}}</span>
              </div>
              <div>
                <span style="font-size: 0.80rem; text-align: start;">{{ data.item.apellido_usuario }}</span>
              </div>
            </template>              
            <template  #cell(nombre_sucursal)="data" #cell(nombre_tipo)="data">
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
            <template #cell(acciones)="data" style="width: 0;">
              <b-dropdown size="sm" style="margin-top: 12px;" variant="light" text="Acciones" toggle-class="btn-sm" no-caret>
                <template #button-content >
                  <i class="fa-solid fa-gear fa-xl" ></i>
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
            <template #cell(act_estado)="data" style="width: 0; padding: 0.2rem 0.2rem !important;" id="estados">
              <div >
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
                  style="color: #ff4747;
                  font-size: 0.870rem; padding: 0.2rem 0.2rem;"
                ></i>
                
                <i v-else-if="data.item.act_estado === 'mantenimiento'" class="fa-solid fa-circle-exclamation" title="mantenimiento" 
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
              :current-page="paginaActual"
              :total-rows="pagina"
              :per-page="porPagina"       
              align="center"
              size="md"/>
          </div>
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
        <div v-if="selectedActivos.act_id_tipo  === 2">
          <!-- Servidor -->
          <b-row>
              <b-col sm >
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
                ]"required
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
                ]"required
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
                ]"required
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
    <b-modal v-model="ConfirmaEliminadoModal" title="Confirmar eliminación" @ok="eliminarActivos" ok-title="Sí, eliminar" cancel-title="Cancelar" ok-variant="danger" >
      ¿Estás seguro de que deseas eliminar el activo? 
    </b-modal>
    <!-- Modal de confirmación de edición -->
    <b-modal v-model="editorConfirmaModal" title="Confirmar modificación" @ok="editar" ok-title="Sí, guardar cambios" cancel-title="Cancelar" ok-variant="success" >
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
          <b-col ms>
            <b-form-textarea v-model="selectedActivos.bjs_descripcion" />
          </b-col>
          <p></p>
          <b-col ms>
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
    <div>
    <p style=" color: rgb(0,0,0,0);">as</p>
  </div>
</template>

<script>
import { ref, onMounted, computed, reactive,watch } from 'vue'
import axios from 'axios'

export default 
{
  setup() {
    const contarActivo =  ([]);
    const total = ref(0)
    const totalActivo= ref(0)
    const empresasConSucursales = ref([])
    // Usamos reactive para selectedActivos y definimos todas las propiedades
    const selectedActivos = reactive({   
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
      act_id: null,

      nombre_empresa: null ,
      nombre_tipo: '',
      nombre_usuario:'' ,
      tipoactivo: null,

      bjs_descripcion: '',
      apellido_usuario:'',
      nombre_sucursal:'' ,
      datos_servidor:{},
      datos_computador:{},
      direcciones_ip: [{}],
      act_id_empresa: null

    })
    const modalShow = ref(false)
    const ConfirmaEliminadoModal = ref(false)
    const editorConfirmaModal = ref(false)
    const mantencionModal = ref(false)
    const activoParaMantencion = ref(null)
    const filtroModal = ref(false)
    const Tipos = ref([])
    const sucursales = ref([])
    const estados = ref([])
    const empresa = ref([])
    const usuariosDisponibles = ref([])
    const NombreApellido = ref([])
    const mostrarFiltros = ref(false)
    const filtros = reactive({
      Tipos: [],
      sucursales: [],
      usuarios: [],
      estados: [],
      empresa:[]
    })
    const activos = ref([])
    const cargando = ref(false)
    const error = ref(null)
    const Buscador = ref('')
    const activosFiltros = ref ([])
    const paginaActual = ref(1)
    const porPagina = 8
    // Opciones para selects
    const empresaOpciones = computed(() => empresa.value.map(e => ({ id: e.emp_id, nombre: e.emp_nombre })))
    const nombreTipoDisplay = computed(() => selectedActivos.nombre_tipo || 'No definido') 
    const fields = [
      { key: 'act_id', label: 'ID' , thClass: 'actID ', tdClass: 'actID', class:" bg-table"},   
      { key: 'nombre_usuario', label: 'Nombre' , thClass: '', tdClass: 'usuario' , class:"bg-table"},  
      { key: 'nombre_empresa', label: 'Empresa' , class: 'd-none bg-table d-lg-table-cell' },           
      { key: 'nombre_sucursal', label: 'Sucursal/Tipo' , thClass: '' , tdClass: 'tipo', class:"bg-table"},
      { key: 'act_fecha_registro', label: 'Fecha' , class: 'd-none d-lg-table-cell bg-table' },         
      { key: 'act_descripcion', label: 'Descricion' , class: 'd-none d-lg-table-cell bg-table' },
      { key: 'acciones', label: '' , thClass: 'text-center', tdClass: 'acciones', class:"bg-table"},
      { key: 'act_estado', label: '' , thClass: 'text-center' , tdClass: 'estados', class:"bg-table" },
    ]
    const cargarActivoss = async () => {
      try {
        const res = await fetch('http://localhost/activos/Backend/index.php/Activos');
        const text = await res.text(); // primero lee como texto
        const data = JSON.parse(text); // parsea el JSON explícitamente
        activos.value = data;
      } catch (err) {
        console.error('Error cargando activos:', err);
      }
    } 
    const cargarActivos = async () => {
      cargando.value = true;
      try {
        const payload = {
          buscador: Buscador.value,
          filtros: {
            sucursales: filtros.sucursales,
            usuarios: filtros.usuarios,
            estados: filtros.estados,
            Tipos: filtros.Tipos,
            empresa: filtros.empresa
          }
        };
        const res = await axios.post(
          'http://localhost/activos/Backend/index.php/Activos/filtros',
          JSON.stringify(payload),
          {
            headers: {
              'Content-Type': 'application/json'
            }
          }
        );

        activos.value = res.data;
      } catch (err) {
        console.error('Error cargando activos:', err.response?.data || err.message);
      } finally {
        cargando.value = false;  // <--- Esto es clave para ocultar el spinner al terminar
      }
    }
    const filtrarActivos = computed(() => {
      return activos.value.filter(activo => {
        const query = Buscador.value.toLowerCase()
        const Query =
          !query || Object.values(activo).some(val => String(val).toLowerCase().includes(query))
        const filtroSucursales =
          filtros.sucursales.length === 0 ||
          filtros.sucursales.map(String).includes(String(activo.act_id_sucursal))
        const filtroUsuarios =
          filtros.usuarios.length === 0 ||
          filtros.usuarios.map(String).includes(String( activo.usr_id ))       
        const filtroEstados =
          filtros.estados.length === 0 ||
          filtros.estados.map(String).includes(String(activo.act_estado))
        const filtroTipos =
          filtros.Tipos.length === 0 ||
          filtros.Tipos.map(String).includes(String(activo.act_id_tipo))
        const filtroEmpresa =
          filtros.empresa.length === 0 ||
          filtros.empresa.map(id => { const emp = empresa.value.find(e => e.emp_id == id)
            return emp?.emp_nombre }).includes(activo.nombre_empresa)
        
        return Query &&  filtroSucursales && filtroUsuarios && filtroEstados && filtroTipos && filtroEmpresa 
      }) 
    })
    const contarActivos = async () => {
      try {
        const res = await axios.get('/Activos/contarActivos')
        contarActivo.value = res.data.Activos
        totalActivo.value = res.data.total
        cargarActivos()
      } catch (error) {
        console.error('Error cargando Activo:', error)
      }
    }
    const editarActivo = (activo) => { 
     // Reemplazamos selectedActivos con la copia del objeto para mantener reactividad
      Object.assign(selectedActivos, activo)
      // Aseguramos que sea número (en caso que venga string)
      selectedActivos.act_id_tipo = Number(activo.act_id_tipo) || null 
      selectedActivos.act_id_empresa = activo.emp_id || null
      const sucursal = sucursales.value.find(s => s.suc_id === activo.act_id_sucursal)
      selectedActivos.act_id_empresa = sucursal?.suc_id_empresa || null
      modalShow.value = true
    }
    const aplicarFiltros = () => {
      filtroModal.value = false
    }
    const limpiarFiltros = () => {
      filtros.Tipos.splice(0, filtros.Tipos.length)
      filtros.sucursales.splice(0, filtros.sucursales.length)
      filtros.usuarios.splice(0, filtros.usuarios.length)
      filtros.estados.splice(0, filtros.estados.length)
      filtros.empresa.splice(0, filtros.empresa.length)
    }
    const cargarTipos = async () => {
      try {
        const res = await axios.get('/Activos/listaTipo')
        Tipos.value = res.data
      } catch (error) {
        console.error('Error cargando Tipos:', error)
      }
    }
    const cargarSucursales = async () => {
      try {
        const res = await axios.get('/Activos/listaSucursal')
        sucursales.value = res.data
      } catch (error) {
        console.error('Error cargando sucursales:', error)
      }
    }
    const cargarEstados = async () => {
      try {
        const res = await axios.get('/Activos/listaActivos')
        estados.value = res.data
      } catch (error) {
        console.error('Error cargando estados:', error)
      }
    }
    const cargarEmpresa = async () => {
      try {
        const res = await axios.get('/Activos/listaEmpresa')
        empresa.value = res.data.filter(e => e.emp_estado === 'activo')
      } catch (error) {
        console.error('Error cargando empresas:', error)
      }
    }
    const cargarUsuarios = async () => {
      try {
        const res = await axios.get('/Activos/listaUsuarios')
        usuariosDisponibles.value = res.data.filter(u => u.usr_estado === 'activo')
        NombreApellido.value = res.data.map(u => ({
          id: u.usr_id,
          nombre: `${u.usr_nombre} ${u.usr_apellido}`
        }));
        
      } catch (error) {
        console.error('Error al cargar usuarios', error)
      }
    } 
    // Métodos para modales, edición, baja, etc.
    const abrirModalMantencion = (activo) => {
      activoParaMantencion.value = activo
      mantencionModal.value = true
    }
    const confirmarEnvioMantencion = async () => {
      const activo = activoParaMantencion.value
      
      if (!activo?.act_id || !activo?.act_id_usuario) {
        alert('Faltan datos para enviar a mantención.')
        return
      }
      try {
        await axios.post('/Mantencion/Mantencion', {
          act_id:activo.act_id,
          man_id_activo: activo.act_id,
          man_id_usuario: activo.act_id_usuario,
          man_fecha: new Date().toISOString().split('T')[0]
        })
        cargarActivos()
        
      } catch (error) {
        console.error('Error al enviar a mantención:', error)
        alert('Error al comunicar con el servidor.')
      } finally {
        mantencionModal.value = false
        activoParaMantencion.value = null
      }
    }
    const activarActivo = async (activo) => {
      if (!activo?.act_id) {
        alert('Faltan datos para activar el activo.')
        return
      }
      try {
        await axios.post('/Activos/activar', { act_id: activo.act_id })
        
        cargarActivos()
      } catch (error) {
        console.error('Error al activar el activo:', error)
        alert('No se pudo activar el activo.')
      }
    }
    const darDeBajaActivo = async () => {
      const activo = activoParaMantencion.value
      if (!activo?.act_id || !selectedActivos.bjs_descripcion?.trim()) {
        alert('Faltan datos para dar de baja.')
        return
      }
      try {
        const res = await axios.post('/activos/dardebaja', {
          bjs_id_activo: activo.act_id,
          bjs_id_usuario: activo.act_id_usuario,
          bjs_descripcion: selectedActivos.bjs_descripcion,
          // Aquí iría manejo de archivo si es necesario
        })
        cargarActivos()
      } catch (error) {
        console.error('Error al dar de baja:', error)
      } finally {
        mantencionModal.value = false
        activoParaMantencion.value = null
      }
    }
    const cargarArchivo = (event) => {
      const file = event.target.files[0]
      if (file) {
        // Aquí puedes procesar el archivo para enviarlo al backend si lo deseas
        console.log('Archivo seleccionado:', file)
      }
    }
    // Confirmar edición y guardar cambios
    const confirmarEdicion = () => {
      editorConfirmaModal.value = true
    }
    const editar = async () => {
      
      try {
        const payload = { ...selectedActivos }
        // POST para actualizar el activo
        
        await axios.put(`/activos/ActualizarActivo/${selectedActivos.act_id}`, payload)
        cargarActivos()
           // recargar lista para actualizar tabla
        modalShow.value = false
      } catch (error) {   
        console.error('Error al actualizar activo:', error)
        alert('Error al guardar los cambios.')
      } finally {
        editorConfirmaModal.value = false
      }
    }
    const cancelarEditar = () => {
      modalShow.value = false
    }    
    // Eliminar
    const confirmarEliminar = (activo) => {
      selectedActivos.act_id = activo.act_id
      ConfirmaEliminadoModal.value = true
    }
    const eliminarActivos = async () => {
      try {
        const payload = { ...selectedActivos }
        // POST para actualizar el activo
        const res = await axios.put(`http://localhost/activos/Backend/index.php/activos/Eliminar/${selectedActivos.act_id}`)
        cargarActivos()
        if (res.data.status === 'update') {
          activos.value = activos.value.filter(a => a.act_id !== selectedActivos.value.act_id)}
      } catch (err) {
        console.error('Error al eliminar:', err)
      } finally {
        ConfirmaEliminadoModal.value = false
      }
    }
    const sucursalesFiltradas = computed(() => {
      if (!selectedActivos.act_id_empresa) return []
      const empresa = empresasConSucursales.value.find(
        e => e.emp_id === selectedActivos.act_id_empresa
      )
      return empresa ? empresa.sucursales : []
    })
    const EmpresasConSucursales = async () => {
      try {
        const [empresaRes, sucursalesRes] = await Promise.all([
          axios.get('http://localhost/activos/Backend/index.php/Activos/listaEmpresa'),
          axios.get('http://localhost/activos/Backend/index.php/Activos/listaSucursal')
        ])
        const empresasActivas = empresaRes.data.filter(e => e.emp_estado === 'activo')
        empresasConSucursales.value = empresasActivas.map(empresa => {
          const sucursalesDeEmpresa = sucursalesRes.data.filter(
            s => s.suc_id_empresa === empresa.emp_id
          )
          return {
            ...empresa,
            sucursales: sucursalesDeEmpresa
          }
        })
      } catch (error) {
        console.error('Error al cargar empresas o sucursales', error)
      }
    } 
    const contarActivosPorSucursal = computed(() => {
      const conteo = {}
      activos.value.forEach(activo => {
        const sucId = activo.act_id_sucursal
        if (conteo[sucId]) {
          conteo[sucId]++
        } else {
          conteo[sucId] = 1
        }
      })
      return conteo
    })
    // Cargar activos desde el backend
    const activosFiltrados = computed(() => {
      if (!Buscador.value) return activos.value
      return activos.value.filter(a =>
        a.act_nombre?.toLowerCase().includes(Buscador.value.toLowerCase()) ||
        a.act_descripcion?.toLowerCase().includes(Buscador.value.toLowerCase()) ||
        a.act_estado?.toLowerCase().includes(Buscador.value.toLowerCase())
      )
    })
    // Paginación
    const activosPaginados = computed(() => {
      const start = (paginaActual.value - 1) * porPagina
      const end = start + porPagina
      return activosFiltrados.value.slice(start, end)
    })
    watch(paginaActual,cargarActivos)
    onMounted(() => {
      cargarActivos()
      cargarTipos()
      cargarSucursales()
      cargarUsuarios()
      cargarEstados()
      cargarEmpresa()
      contarActivos()
    })
    return {
      errors: {},
      activos,
      Buscador,
      activosPaginados,
      selectedActivos,
      modalShow,
      ConfirmaEliminadoModal,
      editorConfirmaModal,
      mantencionModal,
      activoParaMantencion,
      filtroModal,
      filtros,
      Tipos,
      sucursales,
      estados,
      empresa,
      usuariosDisponibles:[],
      mostrarFiltros,
      fields,
      activosFiltrados,
      nombreTipoDisplay,
      filtrarActivos,
      sucursalesFiltradas,
      empresasConSucursales,
      empresaOpciones,
      paginaActual,
      total,
      contarActivo,
      totalActivo,
      porPagina,
      editarActivo,
      aplicarFiltros,
      limpiarFiltros,
      cargarActivos,
      abrirModalMantencion,
      activarActivo,
      confirmarEnvioMantencion,
      darDeBajaActivo,
      cargarArchivo,
      confirmarEdicion,
      editar,
      cancelarEditar,
      confirmarEliminar,
      eliminarActivos,
      EmpresasConSucursales,
      sucursalesOpciones: computed(() => {
        return sucursales.value
          .filter(s => {
            const tieneActivos = (contarActivosPorSucursal.value[s.suc_id] || 0) > 0
            if (filtros.empresa.length === 0) return tieneActivos
            const perteneceAEmpresaSeleccionada = filtros.empresa.includes(s.suc_id_empresa)
            return tieneActivos && perteneceAEmpresaSeleccionada
          })
          .map(s => {
            const cantidad = contarActivosPorSucursal.value[s.suc_id]
            return {
              id: s.suc_id,
              nombre: `${s.suc_nombre} (${cantidad})`
            }
          })
      }),
      empresaOpciones: computed(() =>
        empresa.value.map(e => ({ id: e.emp_id, nombre: e.emp_nombre }))
      ),
      estadosOpciones: computed(() => {
        const unicos = new Set()
        activos.value.forEach(activo => {
          if (activo.act_estado) {
            unicos.add(activo.act_estado)
          }
        })
        return Array.from(unicos).map((estado, index) => ({
          id: estado,
          nombre: estado
        }))
      }),
      usuariosOpciones: computed(() =>
        usuariosDisponibles.value.map(u => ({ id:u.usr_id_usuario,
          nombre: u.nombre_usuario || u.usr_nombre 
        }))
      ),
      usuariosNombreApellido: computed(() =>
        usuariosDisponibles.value.map(u => ({
          id: u.usr_id_usuario,
          nombre: `${u.usr_nombre} ${u.usr_apellido}`
        }))
      ),
      TiposOpciones: computed(() => {
        const unicos = new Map()
        activos.value.forEach(activo => {
          if (activo.act_id_tipo && activo.nombre_tipo) {
            unicos.set(activo.act_id_tipo, activo.nombre_tipo)
          }
        })
        return Array.from(unicos.entries()).map(([id, nombre]) => ({
          id,
          nombre
        }))
      })
    } 
  },
  computed: {
    pagina() {
      this.cargarActivos()
      return this.activos.length
    }
  }
}

</script>
<style >
  /* Puedes agregar estilos específicos aquí */
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.1s;
  }
  .custom-rounded-table {
    border-radius: 12px;
    overflow: hidden; /* importante para que las esquinas internas también se redondeen */
  }
  .bg-table{
    --bs-table-color: #fcfcfc;
    --bs-table-bg: #294969;
    --bs-table-border-color: #294969 !important;
    --bs-table-striped-bg: #294969 !important;
    --bs-table-striped-color: #fffefe !important;
    --bs-table-active-bg: #294969 !important;
    --bs-table-active-color: #ffffff;
    --bs-table-hover-bg: #294969 !;
    --bs-table-hover-color: #ffffff;
    --bs-table-border-color: rgb(54, 97, 139) !important;
  }
  .bg-card{
    background-color: rgba(41 ,73 ,105, 0.836) !important;
    border: none;
    color: #ffffff;
  }
  .navbar-brand {
    text-align: center;
    color: rgb(255 255 255);
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
