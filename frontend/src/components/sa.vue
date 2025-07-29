<template>
  <div>
    <b-container>
      <b-card class="mb-2 bg-card" style="color: white;">
        <h3 class="mb-3">Registro de Empresas y Sucursales</h3>
        <b-form @submit.prevent="crearEmpresa" class="">
          <b-form-group label="Nombre de la empresa">
            <b-form-input v-model="empresa.emp_nombre" placeholder="Ej: Mi Empresa Ltda" />
          </b-form-group>

          <b-form-group label="Dirección de la empresa">
            <b-form-input v-model="empresa.emp_direccion" placeholder="Ej: Av. Principal 1234" />
          </b-form-group>

          <hr>

          <h5>Sucursales</h5>
          <div v-for="(sucursal, index) in empresa.sucursales" :key="index" class="mb-4 p-3 ">
            <b-form-group label="Nombre de sucursal">
              <b-form-input v-model="sucursal.suc_nombre" placeholder="Ej: Sucursal Norte" />
            </b-form-group>

            <b-form-group label="Dirección de sucursal">
              <b-form-input v-model="sucursal.suc_direccion" placeholder="Ej: Calle 123, Ciudad" />
            </b-form-group>

            <b-form-group label="Región">
              <b-form-select
                v-model="sucursal.region"
                :options="regiones"
                @change="sucursal.comuna = ''"
              />
            </b-form-group>

            <b-form-group label="Comuna">
              <b-form-select
                v-model="sucursal.comuna"
                :options="comunasDisponibles(sucursal.region)"
                :disabled="!sucursal.region"
              />
            </b-form-group>
          </div>

          <b-button variant="secondary" @click="agregarSucursal">Agregar otra sucursal</b-button>
          <br><br>
          <b-button type="submit" variant="primary">Guardar Empresa</b-button>
        </b-form>
      </b-card>
    </b-container>
  </div>
</template>

<script>
const RegionesYcomunas = [
  {
    "NombreRegion": "Arica y Parinacota",
    "comunas": ["Arica", "Camarones", "Putre", "General Lagos"]
  },
  {
    "NombreRegion": "Tarapacá",
    "comunas": ["Iquique", "Alto Hospicio", "Pozo Almonte", "Camiña", "Colchane", "Huara", "Pica"]
  },
  {
    "NombreRegion": "Antofagasta",
    "comunas": ["Antofagasta", "Mejillones", "Sierra Gorda", "Taltal", "Calama", "Ollagüe", "San Pedro de Atacama", "Tocopilla", "María Elena"]
  },
  {
    "NombreRegion": "Atacama",
    "comunas": ["Copiapó", "Caldera", "Tierra Amarilla", "Chañaral", "Diego de Almagro", "Vallenar", "Alto del Carmen", "Freirina", "Huasco"]
  },
  {
    "NombreRegion": "Coquimbo",
    "comunas": ["La Serena", "Coquimbo", "Andacollo", "La Higuera", "Paiguano", "Vicuña", "Illapel", "Canela", "Los Vilos", "Salamanca", "Ovalle", "Combarbalá", "Monte Patria", "Punitaqui", "Río Hurtado"]
  },
  {
    "NombreRegion": "Valparaíso",
    "comunas": ["Valparaíso", "Casablanca", "Concón", "Juan Fernández", "Puchuncaví", "Quintero", "Viña del Mar", "Isla de Pascua", "Los Andes", "Calle Larga", "Rinconada", "San Esteban", "La Ligua", "Cabildo", "Papudo", "Petorca", "Zapallar", "Quillota", "Calera", "Hijuelas", "La Cruz", "Nogales", "San Antonio", "Algarrobo", "Cartagena", "El Quisco", "El Tabo", "Santo Domingo", "San Felipe", "Catemu", "Llaillay", "Panquehue", "Putaendo", "Santa María", "Quilpué", "Limache", "Olmué", "Villa Alemana"]
  },
  {
    "NombreRegion": "Libertador General Bernardo O'Higgins",
    "comunas": ["Rancagua", "Codegua", "Coinco", "Coltauco", "Doñihue", "Graneros", "Las Cabras", "Machalí", "Malloa", "Mostazal", "Olivar", "Peumo", "Pichidegua", "Quinta de Tilcoco", "Rengo", "Requínoa", "San Vicente", "Pichilemu", "La Estrella", "Litueche", "Marchihue", "Navidad", "Paredones", "San Fernando", "Chépica", "Chimbarongo", "Lolol", "Nancagua", "Palmilla", "Peralillo", "Placilla", "Pumanque", "Santa Cruz"]
  },
  {
    "NombreRegion": "Maule",
    "comunas": ["Talca", "Constitución", "Curepto", "Empedrado", "Maule", "Pelarco", "Pencahue", "Río Claro", "San Clemente", "San Rafael", "Cauquenes", "Chanco", "Pelluhue", "Curicó", "Hualañé", "Licantén", "Molina", "Rauco", "Romeral", "Sagrada Familia", "Teno", "Vichuquén", "Linares", "Colbún", "Longaví", "Parral", "Retiro", "San Javier", "Villa Alegre", "Yerbas Buenas"]
  },
  {
    "NombreRegion": "Ñuble",
    "comunas": ["Chillán", "Bulnes", "Cobquecura", "Coelemu", "Coihueco", "Chillán Viejo", "El Carmen", "Ninhue", "Ñiquén", "Pemuco", "Pinto", "Portezuelo", "Quillón", "Quirihue", "Ránquil", "San Carlos", "San Fabián", "San Ignacio", "San Nicolás", "Treguaco", "Yungay"]
  },
  {
    "NombreRegion": "Biobío",
    "comunas": ["Concepción", "Coronel", "Chiguayante", "Florida", "Hualqui", "Lota", "Penco", "San Pedro de la Paz", "Santa Juana", "Talcahuano", "Tomé", "Hualpén", "Lebu", "Arauco", "Cañete", "Contulmo", "Curanilahue", "Los Álamos", "Tirúa", "Los Ángeles", "Antuco", "Cabrero", "Laja", "Mulchén", "Nacimiento", "Negrete", "Quilaco", "Quilleco", "San Rosendo", "Santa Bárbara", "Tucapel", "Yumbel", "Alto Biobío"]
  },
  {
    "NombreRegion": "La Araucanía",
    "comunas": ["Temuco", "Carahue", "Cunco", "Curarrehue", "Freire", "Galvarino", "Gorbea", "Lautaro", "Loncoche", "Melipeuco", "Nueva Imperial", "Padre las Casas", "Perquenco", "Pitrufquén", "Pucón", "Saavedra", "Teodoro Schmidt", "Toltén", "Vilcún", "Villarrica", "Cholchol", "Angol", "Collipulli", "Curacautín", "Ercilla", "Lonquimay", "Los Sauces", "Lumaco", "Purén", "Renaico", "Traiguén", "Victoria"]
  },
  {
    "NombreRegion": "Los Ríos",
    "comunas": ["Valdivia", "Corral", "Lanco", "Los Lagos", "Máfil", "Mariquina", "Paillaco", "Panguipulli", "La Unión", "Futrono", "Lago Ranco", "Río Bueno"]
  },
  {
    "NombreRegion": "Los Lagos",
    "comunas": ["Puerto Montt", "Calbuco", "Cochamó", "Fresia", "Frutillar", "Los Muermos", "Llanquihue", "Maullín", "Puerto Varas", "Castro", "Ancud", "Chonchi", "Curaco de Vélez", "Dalcahue", "Puqueldón", "Queilén", "Quellón", "Quemchi", "Quinchao", "Osorno", "Puerto Octay", "Purranque", "Puyehue", "Río Negro", "San Juan de la Costa", "San Pablo", "Chaitén", "Futaleufú", "Hualaihué", "Palena"]
  },
  {
    "NombreRegion": "Aysén del General Carlos Ibáñez del Campo",
    "comunas": ["Coihaique", "Lago Verde", "Aysén", "Cisnes", "Guaitecas", "Cochrane", "O'Higgins", "Tortel", "Chile Chico", "Río Ibáñez"]
  },
  {
    "NombreRegion": "Magallanes y de la Antártica Chilena",
    "comunas": ["Punta Arenas", "Laguna Blanca", "Río Verde", "San Gregorio", "Cabo de Hornos", "Antártica", "Porvenir", "Primavera", "Timaukel", "Natales", "Torres del Paine"]
  },
  {
    "NombreRegion": "Región Metropolitana de Santiago",
    "comunas": ["Cerrillos", "Cerro Navia", "Conchalí", "El Bosque", "Estación Central", "Huechuraba", "Independencia", "La Cisterna", "La Florida", "La Granja", "La Pintana", "La Reina", "Las Condes", "Lo Barnechea", "Lo Espejo", "Lo Prado", "Macul", "Maipú", "Ñuñoa", "Pedro Aguirre Cerda", "Peñalolén", "Providencia", "Pudahuel", "Quilicura", "Quinta Normal", "Recoleta", "Renca", "San Joaquín", "San Miguel", "San Ramón", "Vitacura", "Puente Alto", "Pirque", "San José de Maipo", "Colina", "Lampa", "Tiltil", "San Bernardo", "Buin", "Calera de Tango", "Paine", "Melipilla", "Alhué", "Curacaví", "María Pinto", "San Pedro", "Talagante", "El Monte", "Isla de Maipo", "Padre Hurtado", "Peñaflor"]
  }
]
export default {
  data() {
    return {
      empresa: {
        emp_nombre: '',
        emp_direccion: '',
        sucursales: [
          { suc_nombre: '', suc_direccion: '', region: '', comuna: '' }
        ]
      },
      errors: {}
    }
  },
  computed: {
    regiones() {
      return [
        { value: '', text: 'Seleccione Región' },
        ...RegionesYcomunas.map(region => ({
          value: region.NombreRegion,
          text: region.NombreRegion
        }))
      ];
    }
  },
  methods: {
    comunasDisponibles(region) {
      const regionData = RegionesYcomunas.find(r => r.NombreRegion === region);
      if (!regionData) return [];
      return [
        { value: '', text: 'Seleccione Comuna' },
        ...regionData.comunas.map(c => ({ value: c, text: c }))
      ];
    },
    agregarSucursal() {
      this.empresa.sucursales.push({ suc_nombre: '', suc_direccion: '', region: '', comuna: '' });
    },
    validadatos() {
      this.errors = {};
      if (!this.empresa.emp_nombre) this.errors.emp_nombre = 'Nombre requerido';
      if (!this.empresa.emp_direccion) this.errors.emp_direccion = 'Dirección requerida';

      this.empresa.sucursales.forEach((sucursal, index) => {
        if (!sucursal.suc_nombre) this.errors[`sucursal_nombre_${index}`] = 'Nombre requerido';
        if (!sucursal.suc_direccion) this.errors[`sucursal_direccion_${index}`] = 'Dirección requerida';
        if (!sucursal.region) this.errors[`region_${index}`] = 'Región requerida';
        if (!sucursal.comuna) this.errors[`comuna_${index}`] = 'Comuna requerida';
      });

      return Object.keys(this.errors).length === 0;
    },
    async crearEmpresa() {
      if (!this.validadatos()) {
        console.warn("Errores en formulario", this.errors);
        return;
      }
      // Aquí puedes usar axios.post() para enviar al backend
      if (this.validadatos()) return

      try {
        const res = await axios.post('/EmpresaR/CrearEmpresa', this.empresa)
        this.resetForm()
      } catch (error) {
        this.mensaje = 'Error al guardar la empresa'
        console.error(error)
      } finally {
        this.modalShow = true
      }
      // Reset
      this.empresa = {
        emp_nombre: '',
        emp_direccion: '',
        sucursales: [
          { suc_nombre: '', suc_direccion: '', region: '', comuna: '' }
        ]
      };
    },
    eliminarSucursal(index) {
      this.empresa.sucursales.splice(index, 1)
    }
  }
}
</script>
<style>
 .bg-form{
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
</style>