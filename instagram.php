<?php
// proceso para formatear los telefonos de leads y contact 
// si tienen +1, se borra el prefijo y se vuelve a colocar
// si no tiene se le agrega, tanto para leads como para contact
// social media es IN_PROCESS 
// test es UC_TX3D8B  
require_once(__DIR__ . '/crest.php');
$message = $_GET;
CRest::setLog(['message' => $_REQUEST]);
