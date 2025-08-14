<template>
  <!-- filtro -->
  <b-container fluid>
    <b-row>
      <!-- Barra de búsqueda -->
      <b-navbar md="2" toggleable="" type="" variant="" class="mb-4">
          <h3>Buscar Activos</h3>
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
          <!-- Barra lateral izquierda -->
          <transition name="fade">
            <b-col v-if="mostrarFiltros" class="col-md-3">
              <p></p>
              <b-card class="shadow-sm" >
                <h5 class="mb-3">Filtros <i class="fa-solid fa-arrow-up-wide-short"></i></h5>              
                <!-- Filtro por Tipo -->
                <ul class="nav">
                  <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#collapseFiltroTipo" role="button"><i class="fa-solid fa-angle-right"></i> Tipo</a>
                    <div class="collapse show" id="collapseFiltroTipo">
                      <ul class="list-unstyled ps-3 small">
                        <b-form-group class="checkbox-grid-2cols">
                          <b-form-checkbox-group
                            v-model="filtros.Tipos"
                            :options="TiposOpciones"
                            value-field="id"
                            text-field="nombre"
                            stacked 
                            :aria-describedby="ariaDescribedby"
                            switches
                            style="flex-wrap: wrap !important; justify-content: flex-start !important;"
                          />
                        </b-form-group>
                      </ul>
                    </div>
                  </li>
                </ul>
                <!-- Filtro por Empresa -->
                <ul class="nav">
                  <li> 
                    <a class="nav-link" data-bs-toggle="collapse" href="#collapseFiltroEmpresa" role="button"><i class="fa-solid fa-angle-right"></i> Empresa</a>
                    <div class="collapse" id="collapseFiltroEmpresa">
                      <ul class="list-unstyled ps-3 small">
                        <b-form-group class="checkbox-grid-2cols">
                          <b-form-checkbox-group
                            v-model="filtros.empresa"
                            :options="empresaOpciones"
                            value-field="id"
                            text-field="nombre"
                            stacked
                            :aria-describedby="ariaDescribedby"
                            switches
                          />
                        </b-form-group>
                      </ul>
                    </div>
                  </li>
                </ul>
                <!-- Filtro por Sucursal -->
                <ul class="nav">
                  <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#collapseFiltroSucursal" role="button"><i class="fa-solid fa-angle-right"></i> Sucursal</a>
                    <div class="collapse" id="collapseFiltroSucursal">
                      <ul class="list-unstyled ps-3 small">
                        <b-form-group label="" class="checkbox-grid-2cols">
                          <b-form-checkbox-group
                            v-model="filtros.sucursales"
                            :options="sucursalesOpciones"
                            value-field="id"
                            text-field="nombre"
                            stacked
                            :aria-describedby="ariaDescribedby"
                            switches
                          />
                        </b-form-group>
                      </ul>
                    </div>
                  </li>
                </ul>
                <!-- Filtro por Estado -->
                <ul class="nav">
                  <li>
                    <a class="nav-link" data-bs-toggle="collapse" href="#collapseFiltroEstado" role="button"><i class="fa-solid fa-angle-right"></i> Estado</a>
                    <div class="collapse show" id="collapseFiltroEstado">
                      <ul class="list-unstyled ps-3 small">
                        <b-form-group label="" class="checkbox-grid-2cols">
                          <b-form-checkbox-group
                            v-model="filtros.estados"
                            :options="estadosOpciones"
                            value-field="id"
                            text-field="nombre"
                            stacked
                            :aria-describedby="ariaDescribedby"
                            switches
                          />
                        </b-form-group>
                      </ul>
                    </div>
                  </li>
                </ul>
                
                <b-row>
                  <b-col sm>
                    <b-button size="sm" class="mb-3 w-100" variant="danger" @click="limpiarFiltros">
                      <i class="fa-solid fa-brush fa-sm"></i> Limpiar Filtros
                    </b-button>
                  </b-col>
                  <b-col sm>
                    <b-button size="sm" class="mb-3 w-100" variant="secondary" @click="mostrarFiltros = false">
                      <i class="fa-solid fa-xmark"></i> Ocultar filtros
                    </b-button>
                  </b-col>
                </b-row>
              </b-card>
              <p></p>
            </b-col>
          </transition> 
          <!-- Tabla de resultados -->
          <b-col :md="mostrarFiltros ? 9 : 12" style="padding: 20px;">
            <b-card class="shadow-sm" >
              <div v-if="cargando" class="cargando-overlay pl-1">
                <b-spinner variant="" class="mb-2" />
                <div>Cargando...</div>
              </div>
              <b-table 
                :items="activosPaginados" 
                :fields="fields" 
                hover 
              > 
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
                  <b-dropdown size="sm"
                      variant="outline-success" no-caret right
                      style="margin-top: 12px;"
                  >
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
                    <b-dropdown-item 
                      v-if="data.item.act_estado === 'activo'"
                      @click="abrirModaldarDeBaja(data.item)"
                    >
                      <i class="fa-solid fa-ban" style="color: #c02626; margin-right: 6px;"></i> Dar de baja
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
                  size="md"
                />
              </div>
            </b-card>
          </b-col>
        </b-navbar>            
      </b-row>
    </b-container>
    <!-- Modal de edición -->
    <b-modal
      v-model="modalShow"
      title="Editar Activo"
      size="xl"
      centered
      hide-footer
    >
      <b-container fluid>
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
            <label>Responsable</label>
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
              <b-form-group label="Sistema Operativo">
              <b-form-select
                v-model="selectedActivos.datos_computador.com_id_sistema_operativo"
                :options="sistemaOperativoComputadoresOpcion"
                value-field="id"
                text-field="nombre"
                placeholder="Seleccione un sistema operativo"
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
              <b-form-group label="Sistema Operativo">
                <b-form-select
                  v-model="selectedActivos.datos_servidor.ser_id_sistema_operativo"
                  :options="sistemaOperativoServidorOpcion"
                  value-field="id"
                  text-field="nombre"
                  placeholder="Seleccione un sistema operativo"
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

        <!-- Tabla de Mantenciones -->
        <b-row class="mt-4">
          <b-col>
            <h5>Mantenciones</h5>
            <b-table
              :items="mantencionesPaginados"
              :fields="tablaMantencion"
              hover
              small
            />
          </b-col>
        </b-row>

        <!-- Botones -->
        <b-row class="mt-4">
          <b-col class="text-end">
            <b-button variant="outline-success" class="me-2" @click="confirmarEdicion">
              <i class="fa-solid fa-floppy-disk fa-lg"></i> Guardar
            </b-button>
            <b-button variant="outline-secondary" @click="cancelarEditar">
              <i class="fa-solid fa-circle-xmark fa-lg"></i> Cancelar
            </b-button>
          </b-col>
        </b-row>
      </b-container>
    </b-modal>
    
    <!-- Modal de confirmación de eliminación -->
    <b-modal v-model="ConfirmaEliminadoModal" title="Confirmar eliminación" @ok="eliminarActivos" ok-title="Sí, eliminar" cancel-title="Cancelar" ok-variant="danger">
      ¿Estás seguro de que deseas eliminar el activo? 
    </b-modal>
    <!-- Modal de confirmación de edición -->
    <b-modal v-model="editorConfirmaModal" title="Confirmar modificación" @ok="editar" ok-title="Sí, guardar cambios" cancel-title="Cancelar" ok-variant="success">
      ¿Estás seguro de que deseas guardar los cambios al activo?
    </b-modal>
    <!-- Modal de Mantención -->
    <b-modal
      v-model="mantencionModal"
      title="Enviar a mantención"
      @ok="confirmarEnvioMantencion"
      ok-title="Mantención"
      cancel-title="Cancelar"
      ok-variant="primary"
    >
      <b-form @submit.stop.prevent>
        <div class="mb-3">
          ¿Estás seguro de que deseas mandar a mantención el activo?
        </div>

        <!-- Selección de usuario -->
        <b-form-group
          label="Usuario"
          :state="usuarioSeleccionadoParaMantencion !== null ? true : null"
          invalid-feedback="Por favor, seleccione un usuario."
        >
          <b-form-select
            v-model="usuarioSeleccionadoParaMantencion"
            :options="usuariosOpciones"
            value-field="id"
            text-field="nombre"
            placeholder="Seleccione un usuario"
            required
          />
        </b-form-group>

        <!-- Descripción de mantención -->
        <b-form-group
          label="Descripción"
          :state="selectedActivos.man_descripcion?.trim() ? true : null"
          invalid-feedback="Por favor, ingrese una descripción."
        >
          <b-form-textarea
            v-model="selectedActivos.man_descripcion"
            placeholder="Descripción de la mantención"
            rows="3"
            required
          />
        </b-form-group>
      </b-form>
    </b-modal>
    <!-- Modal para enviar a dar de baja -->
    <b-modal v-model="darDeBajaActivoModal" title="Dar de Baja" @ok="darDeBajaActivo" ok-title="Dar de baja" cancel-title="Cancelar" ok-variant="primary">
      <div class="mb-3">
        ¿Estás seguro de que deseas dar de baja el activo?
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
            <label>Descripción</label>
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
        </b-col>
      </div>
    </b-modal>
  </template>

<script>
import axios from 'axios';
import { version } from 'vue';

export default {
  data() {
    return {
      contarActivo: [],
      empresasConSucursales: [],
      Tipos: [],
      sucursales: [],
      estados: [],
      empresa: [],
      usuariosDisponibles: [],
      sistemasOperativosDisponibles: [],
      sistemaOperativoServidores: [],
      NombreApellido: [],
      activos: [],
      activosFiltros: [],
      mantenciones: [],
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
        act_id_empresa: null,
        ips: [], // Ensure this is initialized
        man_descripcion:''
      },
      filtros: {
        Tipos: [],
        sucursales: [],
        usuarios: [],
        estados: [],
        empresa: [],
      },
      errors: {},
      modalShow: false,
      ConfirmaEliminadoModal: false,
      editorConfirmaModal: false,
      mantencionModal: false,
      darDeBajaActivoModal: false,
      activoParaMantencion: null,
      filtroModal: false,
      mostrarFiltros: false,
      cargando: false,
      error: null,
      Buscador: '',
      paginaActual: 1,
      porPagina: 8,
      total: 0,
      totalActivos: 0,
      mantencionesPaginados: [],
      paginaMantenciones: 1,
      itemsPorPaginaMantenciones: 5,
      usuarioSeleccionadoParaMantencion: null,
      isLoading: false,
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
        { key: 'nombre_usuario', label: 'Responsable', thClass: '', tdClass: 'usuario' },
        { key: 'nombre_empresa', label: 'Empresa', class: 'd-none d-lg-table-cell' },
        { key: 'nombre_sucursal', label: 'Sucursal/Tipo', thClass: '', tdClass: 'tipo' },
        { key: 'act_fecha_registro', label: 'Fecha', class: 'd-none d-lg-table-cell' },
        { key: 'act_marca', label: 'Marca', class: 'd-none d-lg-table-cell' },
        { key: 'acciones', label: '', thClass: 'text-center', tdClass: 'acciones', style: 'color: black; right: 0;' },
        { key: 'act_estado', label: '', thClass: 'text-center', tdClass: 'estados' },
      ];
    },
    tablaMantencion() {
      return [
        { key: 'man_id', label: 'ID' },
        { key: 'man_fecha', label: 'Fecha' },
        { key: 'nombre_usuario', label: 'Responsable' },
        { key: 'man_descripcion', label: 'Descripción' },
      ];
    },
    filtrarActivos() {
      return this.activos.filter(activo => {
        const query = this.Buscador.toLowerCase();
        const Query = !query || Object.values(activo).some(val => String(val).toLowerCase().includes(query));
        const filtroSucursales = this.filtros.sucursales.length === 0 || this.filtros.sucursales.map(String).includes(String(activo.act_id_sucursal));
        const filtroUsuarios = this.filtros.usuarios.length === 0 || this.filtros.usuarios.map(String).includes(String(activo.usr_id));
        const filtroEstados = this.filtros.estados.length === 0 || this.filtros.estados.map(String).includes(String(activo.act_estado));
        const filtroTipos = this.filtros.Tipos.length === 0 || this.filtros.Tipos.map(String).includes(String(activo.act_id_tipo));
        const filtroEmpresa = this.filtros.empresa.length === 0 || this.filtros.empresa.map(id => {
          const emp = this.empresa.find(e => e.emp_id == id);
          return emp?.emp_nombre;
        }).includes(activo.nombre_empresa);

        return Query && filtroSucursales && filtroUsuarios && filtroEstados && filtroTipos && filtroEmpresa;
      });
    },
    contarActivosPorSucursal() {
      const conteo = {};
      this.activos.forEach(activo => {
        const sucId = activo.act_id_sucursal;
        if (conteo[sucId]) {
          conteo[sucId]++;
        } else {
          conteo[sucId] = 1;
        }
      });
      return conteo;
    },
    activosFiltrados() {
      if (!this.Buscador) return this.activos;
      return this.activos.filter(a =>
        a.act_nombre?.toLowerCase().includes(this.Buscador.toLowerCase()) ||
        a.act_descripcion?.toLowerCase().includes(this.Buscador.toLowerCase()) ||
        a.act_estado?.toLowerCase().includes(this.Buscador.toLowerCase())
      );
    },
    activosPaginados() {
      const start = (this.paginaActual - 1) * this.porPagina;
      const end = start + this.porPagina;
      return this.filtrarActivos.slice(start, end);
    },
    mantencionesPaginados() {
      const start = (this.paginaMantenciones - 1) * this.itemsPorPaginaMantenciones;
      const end = start + this.itemsPorPaginaMantenciones;
      return this.mantenciones.slice(start, end);
    },
    sucursalesOpciones() {
      return this.sucursales
        .filter(s => {
          const tieneActivos = (this.contarActivosPorSucursal[s.suc_id] || 0) > 0;
          if (this.filtros.empresa.length === 0) return tieneActivos;
          const perteneceAEmpresaSeleccionada = this.filtros.empresa.includes(s.suc_id_empresa);
          return tieneActivos && perteneceAEmpresaSeleccionada;
        })
        .map(s => {
          const cantidad = this.contarActivosPorSucursal[s.suc_id];
          return {
            id: s.suc_id,
            nombre: `${s.suc_nombre}`,
          };
        });
    },
    estadosOpciones() {
      const unicos = new Set();
      this.activos.forEach(activo => {
        if (activo.act_estado) {
          unicos.add(activo.act_estado);
        }
      });
      return Array.from(unicos).map((estado, index) => ({
        id: estado,
        nombre: estado,
      }));
    },
    usuariosOpciones() {
      return this.usuariosDisponibles.map(u => ({
        id: u.usr_id,
        nombre: u.nombre_usuario || u.usr_nombre,
      }));
    },
    usuariosNombreApellido() {
      return this.usuariosDisponibles.map(u => ({
        id: u.usr_id_usuario,
        nombre: `${u.usr_nombre} ${u.usr_apellido}`,
      }));
    },
    TiposOpciones() {
      const unicos = new Map();
      this.activos.forEach(activo => {
        if (activo.act_id_tipo && activo.nombre_tipo) {
          unicos.set(activo.act_id_tipo, activo.nombre_tipo);
        }
      });
      return Array.from(unicos.entries()).map(([id, nombre]) => ({
        id,
        nombre,
      }));
    },
    sucursalesFiltradas() {
      const empresa = this.empresasConSucursales.find(e => e.emp_id === this.selectedActivos.act_id_empresa);
      return empresa ? empresa.sucursales : [];
    },
    usuariosFiltrados() {
      return this.usuariosDisponibles.filter(u => u.usr_id_sucursal === this.sucursalSeleccionada);
    },
    sistemaOperativoServidorOpcion() {
      return this.sistemaOperativoServidores.map(u => ({
        id: u.sio_id,
        nombre: `${u.sio_nombre} ${u.sio_version}`,
      }));
    },
    sistemaOperativoComputadoresOpcion() {
      return this.sistemasOperativosDisponibles.map(u => ({
        id: u.sio_id,
        nombre: `${u.sio_nombre} ${u.sio_version}`,
      }));
    },
    empresas() {
      return this.empresa.map(e => ({ id: e.emp_id, nombre: e.emp_nombre }));
    },
    usuarios() {
      return this.usuariosDisponibles.map(u => ({
        id: u.usr_id_usuario,
        nombre: `${u.usr_nombre} ${u.usr_apellido}`,
      }));
    },
    tiposActivos() {
      return this.Tipos.map(t => ({ id: t.id, nombre: t.nombre }));
    },
  },

  watch: {
    modalShow(newVal) {
      if (newVal && this.selectedActivos?.act_id) {
        this.tablaMantenciones();
      }
    },
    paginaActual() {
      this.cargarActivos();
    },
    paginaMantenciones() {
      this.actualizarMantencionesPaginadas();
    },
  },

  methods: {
    async cargarActivos() {
      this.cargando = true;
      try {
        const payload = {
          buscador: this.Buscador,
          filtros: {
            sucursales: this.filtros.sucursales,
            usuarios: this.filtros.usuarios,
            estados: this.filtros.estados,
            Tipos: this.filtros.Tipos,
            empresa: this.filtros.empresa,
          },
        };
        const res = await axios.post('/Activos/filtros', JSON.stringify(payload), {
          headers: {
            'Content-Type': 'application/json',
          },
        });
        this.activos = res.data;
      } catch (err) {
        console.error('Error cargando activos:', err.response?.data || err.message);
      } finally {
        this.cargando = false;
      }
    },
    async contarActivos() {
      try {
        const res = await axios.get('/Activos/contarActivos');
        this.contarActivo = res.data.Activos;
        this.totalActivos = res.data.total;
        this.cargarActivos();
      } catch (error) {
        console.error('Error cargando Activo:', error);
      }
    },
    async cargarEmpresasConSucursales() {
      try {
        const empresasRes = await axios.get('/Activos/listaEmpresa');
        const sucursalesRes = await axios.get('/Activos/listaSucursal');
        const empresasActivas = empresasRes.data.filter(e => e.emp_estado === 'activo');
        this.empresasConSucursales = empresasActivas.map(emp => ({
          ...emp,
          sucursales: sucursalesRes.data.filter(s => s.suc_id_empresa === emp.emp_id),
        }));
      } catch (error) {
        console.error('Error cargando empresas y sucursales:', error);
      }
    },
    editarActivo(activo) {
      this.selectedActivos = {
        ...this.selectedActivos,
        act_id: activo.act_id || null,
        act_id_usuario: activo.act_id_usuario || null,
        act_id_sucursal: activo.act_id_sucursal || null,
        act_fecha_registro: activo.act_fecha_registro || '',
        act_marca: activo.act_marca || '',
        act_modelo: activo.act_modelo || '',
        act_fabricante: activo.act_fabricante || '',
        act_numero_serie: activo.act_numero_serie || '',
        act_ubicacion: activo.act_ubicacion || '',
        act_factura: activo.act_factura || '',
        act_descripcion: activo.act_descripcion || '',
        nombre_empresa: activo.nombre_empresa || null,
        nombre_tipo: activo.nombre_tipo || '',
        nombre_usuario: activo.nombre_usuario || '',
        tipoactivo: activo.tipoactivo || null,
        bjs_descripcion: activo.bjs_descripcion || '',
        apellido_usuario: activo.apellido_usuario || '',
        nombre_sucursal: activo.nombre_sucursal || '',
        datos_servidor: activo.datos_servidor || {},
        datos_computador: activo.datos_computador || {},
        direcciones_ip: activo.direcciones_ip || [],
        act_id_empresa: activo.emp_id || null,
        act_id_tipo: Number(activo.act_id_tipo) || null,
        ips: activo.direcciones_ip || [],
        com_ram: activo.com_ram || '',
        com_procesador: activo.com_procesador || '',
        ser_nucleos: activo.ser_nucleos || '',
        ser_sistema_operativo: activo.ser_sistema_operativo || '',
      };
      const sucursal = this.sucursales.find(s => s.suc_id === activo.act_id_sucursal);
      this.selectedActivos.act_id_empresa = sucursal?.suc_id_empresa || null;
      this.modalShow = true;
    },
    aplicarFiltros() {
      this.filtroModal = false;
    },
    limpiarFiltros() {
      this.filtros.Tipos.splice(0, this.filtros.Tipos.length);
      this.filtros.sucursales.splice(0, this.filtros.sucursales.length);
      this.filtros.usuarios.splice(0, this.filtros.usuarios.length);
      this.filtros.estados.splice(0, this.filtros.estados.length);
      this.filtros.empresa.splice(0, this.filtros.empresa.length);
    },
    async cargarTipos() {
      try {
        const res = await axios.get('/Activos/listaTipo', {
          withCredentials: true,
        });
        this.Tipos = res.data;
      } catch (error) {
        console.error('Error cargando Tipos:', error);
      }
    },
    async cargarSistemasComputadoresOperativos() {
      try {
        const res = await axios.get('/Activos/listaSistemaOperativo');
        this.sistemasOperativosDisponibles = res.data;
      } catch (error) {
        console.error('Error cargando sistemas operativos:', error);
      }
    },
    async cargarsistemasServidoresOperativos() {
      try {
        const res = await axios.get('/Activos/listaSistemaOperativo');
        this.sistemaOperativoServidores = res.data;
      } catch (error) {
        console.error('Error cargando sistemas operativos:', error);
      }
    },
    async cargarSucursales() {
      try {
        const res = await axios.get('/Activos/listaSucursal');
        this.sucursales = res.data;
      } catch (error) {
        console.error('Error cargando sucursales:', error);
      }
    },
    async cargarEstados() {
      try {
        const res = await axios.get('/Activos/listaActivos');
        this.estados = res.data;
      } catch (error) {
        console.error('Error cargando estados:', error);
      }
    },
    async cargarEmpresa() {
      try {
        const res = await axios.get('/Activos/listaEmpresa', {
          withCredentials: true,
        });
        this.empresa = res.data.filter(e => e.emp_estado === 'activo');
      } catch (error) {
        console.error('Error cargando empresas:', error);
      }
    },
    async cargarUsuarios() {
      try {
        const res = await axios.get('/Activos/listaUsuarios');
        this.usuariosDisponibles = res.data.filter(u => u.usr_estado === 'activo');
        this.NombreApellido = res.data.map(u => ({
          id: u.usr_id,
          nombre: `${u.usr_nombre} ${u.usr_apellido}`,
        }));
      } catch (error) {
        console.error('Error al cargar usuarios:', error);
      }
    },
    abrirModalMantencion(activo) {
      this.activoParaMantencion = activo;
      this.mantencionModal = true;
    },
    abrirModaldarDeBaja(activo) {
      this.activoParaMantencion = activo;
      this.darDeBajaActivoModal = true;
    },
    async confirmarEnvioMantencion(bvModalEvt) {
      if (!this.activoParaMantencion?.act_id || !this.usuarioSeleccionadoParaMantencion || !this.selectedActivos.man_descripcion?.trim()) {
        alert('Por favor, complete todos los campos requeridos.');
        bvModalEvt.preventDefault();
        return;
      }
      this.isLoading = true;
      try {
        await axios.post('/Mantencion/Mantencion', {
          man_id_activo: this.activoParaMantencion.act_id,
          man_id_usuario: this.usuarioSeleccionadoParaMantencion,
          man_fecha: new Date().toISOString().split('T')[0],
          man_descripcion: this.selectedActivos.man_descripcion
        });
        this.cargarActivos();
      } catch (error) {
        console.error('Error al enviar a mantención:', error);
        alert(error.response?.data?.message || 'Error al comunicar con el servidor.');
      } finally {
        this.isLoading = false;
        this.mantencionModal = false;
        this.activoParaMantencion = null;
        this.usuarioSeleccionadoParaMantencion = null;
      }
    },
    async activarActivo(activo) {
      if (!activo?.act_id) {
        alert('Faltan datos para activar el activo.');
        return;
      }
      try {
        await axios.post('/Activos/activar', { act_id: activo.act_id });
        this.cargarActivos();
      } catch (error) {
        console.error('Error al activar el activo:', error);
        alert('No se pudo activar el activo.');
      }
    },
    async darDeBajaActivo() {
      const activo = this.activoParaMantencion;
      if (!activo?.act_id || !this.selectedActivos.bjs_descripcion?.trim()) {
        alert('Faltan datos para dar de baja.');
        return;
      }
      try {
        await axios.post('/activos/dardebaja', {
          bjs_id_activo: activo.act_id,
          bjs_id_usuario: activo.act_id_usuario,
          bjs_descripcion: this.selectedActivos.bjs_descripcion,
        });
        this.cargarActivos();
      } catch (error) {
        console.error('Error al dar de baja:', error);
        alert('Error al dar de baja el activo.');
      } finally {
        this.darDeBajaActivoModal = false;
        this.activoParaMantencion = null;
      }
    },
    async tablaMantenciones() {
      if (!this.selectedActivos || !this.selectedActivos.act_id) {
        this.mantenciones = [];
        this.actualizarMantencionesPaginadas();
        return;
      }

      try {
        // Adjust URL to match CodeIgniter's expected route
        const res = await axios.get(`/Mantencion/TABLA/${this.selectedActivos.act_id}`, {
          withCredentials: true,
        });

        // Ensure response data is an array
        this.mantenciones = Array.isArray(res.data) ? res.data : [];
        
        // Reset to page 1 and update paginated maintenance records
        this.paginaMantenciones = 1;
        this.actualizarMantencionesPaginadas();
      } catch (error) {
        console.error('Error al obtener tabla mantención:', error.response?.data || error.message);
        this.mantenciones = [];
        this.actualizarMantencionesPaginadas();
        alert('Error al cargar las mantenciones. Por favor, intenta de nuevo.');
      }
    },
    actualizarMantencionesPaginadas() {
      const start = (this.paginaMantenciones - 1) * this.itemsPorPaginaMantenciones;
      const end = start + this.itemsPorPaginaMantenciones;
      this.mantencionesPaginados = this.mantenciones.slice(start, end);
    },
    cargarArchivo(event) {
      const file = event.target.files[0];
      if (file) {
        console.log('Archivo seleccionado:', file);
      }
    },
    confirmarEdicion() {
      this.editorConfirmaModal = true;
    },
    async editar() {
      try {
        const payload = { ...this.selectedActivos };
        await axios.put(`/activos/ActualizarActivo/${this.selectedActivos.act_id}`, payload);
        this.cargarActivos();
        this.modalShow = false;
      } catch (error) {
        console.error('Error al actualizar activo:', error);
        alert('Error al guardar los cambios.');
      } finally {
        this.editorConfirmaModal = false;
      }
    },
    cancelarEditar() {
      this.modalShow = false;
    },
    confirmarEliminar(activo) {
      this.selectedActivos.act_id = activo.act_id;
      this.ConfirmaEliminadoModal = true;
    },
    async eliminarActivos() {
      try {
        await axios.put(`/activos/Eliminar/${this.selectedActivos.act_id}`);
        this.cargarActivos();
      } catch (err) {
        console.error('Error al eliminar:', err);
        alert('Error al eliminar el activo.');
      } finally {
        this.ConfirmaEliminadoModal = false;
      }
    },
    async EmpresasConSucursales() {
      try {
        const [empresaRes, sucursalesRes] = await Promise.all([
          axios.get('/Activos/listaEmpresa'),
          axios.get('/Activos/listaSucursal'),
        ]);
        const empresasActivas = empresaRes.data.filter(e => e.emp_estado === 'activo');
        this.empresasConSucursales = empresasActivas.map(empresa => {
          const sucursalesDeEmpresa = sucursalesRes.data.filter(s => s.suc_id_empresa === empresa.emp_id);
          return {
            ...empresa,
            sucursales: sucursalesDeEmpresa,
          };
        });
      } catch (error) {
        console.error('Error al cargar empresas o sucursales:', error);
      }
    },
    agregarIP() {
      this.selectedActivos.ips.push('');
    },
    eliminarIP(index) {
      this.selectedActivos.ips.splice(index, 1);
    },
    async guardarActivo() {
      this.confirmarEdicion();
    },
  },

  mounted() {
    this.cargarActivos();
    this.cargarTipos();
    this.cargarSucursales();
    this.cargarUsuarios();
    this.cargarEstados();
    this.cargarEmpresa();
    this.contarActivos();
    this.cargarSistemasComputadoresOperativos();
    this.cargarsistemasServidoresOperativos();
  },
};
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.1s;
}
.custom-rounded-table {
  border-radius: 12px;
}
.dropdown-menu {
  right: 0 !important;
}
.navbar-brand {
  text-align: center;
}
.estados {
  width: 0px;
  padding: 0.2rem 0.2rem;
}
.acciones {
  width: 0 !important;
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