<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['post_controller_constructor'][] = array(
    'class'    => 'Cors',
    'function' => 'enableCors',
    'filename' => 'Cors.php',
    'filepath' => 'hooks',
    'params' =>[]
);

