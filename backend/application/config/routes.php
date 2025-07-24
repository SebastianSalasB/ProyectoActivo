<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['Activos'] = 'Activos/index';
$route['Activos/show/(:num)'] = 'Activos/show/$1';
$route['Activos/create'] = 'Activos/create';
$route['Activos/update/(:num)'] = 'Activos/update/$1';
$route['Activos/delete/(:num)'] = 'Activos/delete/$1';
$route['Activos/dardebaja'] = 'Activos/dardebaja';

$route['Auth/login']['POST'] = 'Auth/login';
$route['Auth/register']['POST'] = 'Auth/register';
$route['tiposactivos'] = 'tiposactivos/index';
$route['tiposactivos/(:num)'] = 'tiposactivos/show/$1';
$route['tiposactivos/create'] = 'tiposactivos/create';
$route['tiposactivos/update/(:num)'] = 'tiposactivos/update/$1';
$route['tiposactivos/delete/(:num)'] = 'tipoactivo/delete/$1';
$route['Compu'] = 'Compu/index';
$route['Compu/(:num)'] = 'Compu/show/$1';
$route['Compu/create'] = 'Compu/create';
$route['Compu/update/(:num)'] = 'Compu/update/$1';
$route['Compu/delete/(:num)'] = 'Compu/delete/$1';
$route['Respo'] = 'Respo/index';
$route['Respo/(:num)'] = 'Respo/show/$1';
$route['Respo/create'] = 'Respo/create';
$route['Respo/update/(:num)'] = 'Respo/update/$1';
$route['Respo/delete/(:num)'] = 'Respo/delete/$1';
