<?php

//[Developed by Dairo Arce - https://arcedairo.github.io] . In search of excellence. 

// Se carga el entorno de WordPress
include_once($_SERVER['DOCUMENT_ROOT'].'/prueba_BRM/wp-load.php');

// Se verifica si el formulario se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se recogen los datos
    $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? sanitize_text_field($_POST['apellido']) : '';
    $celular = isset($_POST['celular']) ? sanitize_text_field($_POST['celular']) : '';
    $correo = isset($_POST['correo']) ? sanitize_email($_POST['correo']) : '';

    // Se crea un nuevo post de tipo 'contactos'
    $nueva_entrada = wp_insert_post(array(
        'post_type' => 'contactos',
        'post_title' => $nombre . ' ' . $apellido,
        'post_status' => 'publish',
    ));

    update_post_meta($nueva_entrada, 'nombre', $nombre);
    update_post_meta($nueva_entrada, 'apellido', $apellido);
    update_post_meta($nueva_entrada, 'celular', $celular);
    update_post_meta($nueva_entrada, 'correo', $correo);

    // Se redirige a la página de confirmación
    wp_redirect(home_url('/registro-exitoso/'));
    exit;
}