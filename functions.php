<?php

//[Developed by Dairo Arce - https://arcedairo.github.io] . In search of excellence. 

//Enqueue style
function incluir_estilos() {

    wp_enqueue_style(
        'BRM-theme-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme() -> get('Version')
    );
}

add_action('wp_enqueue_scripts', 'incluir_estilos');


//Se crea el custom post type "Contactos"

function register_type_contact() {
    $labels = array(
        'name'               => 'Contactos',
        'singular_name'      => 'Contacto',
        'menu_name'          => 'Contactos',
        'add_new'            => 'Añadir Nuevo',
        'add_new_item'       => 'Añadir Nuevo Contacto',
        'edit_item'          => 'Editar Contacto',
        'new_item'           => 'Nuevo Contacto',
        'view_item'          => 'Ver Contacto',
        'search_items'       => 'Buscar Contactos',
        'not_found'          => 'No se encontraron contactos',
        'not_found_in_trash' => 'No se encontraron contactos en la papelera',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => 'contactos',
        'menu_icon'          => 'dashicons-businessman', 
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'categories', 'tags'),
        'taxonomies'          => array('category', 'post_tag'), 
        'rewrite'             => array('slug' => 'contactos'), 
        'register_meta_box_cb' => 'agregar_informacion_contacto', 
    );

    register_post_type('contactos', $args);
}
add_action('init', 'register_type_contact');

//Se agrega información desde la opción Añadir Nuevo
function agregar_informacion_contacto() {
    add_meta_box('informacion_contacto', 'Información de Contacto', 'mostrar_informacion_contacto', 'contactos', 'normal', 'default');
}


function mostrar_informacion_contacto($post) {
    // Se recupera los valores actuales
    $nombre = get_post_meta($post->ID, '_nombre', true);
    $apellido = get_post_meta($post->ID, '_apellido', true);
    $celular = get_post_meta($post->ID, '_celular', true);
    $correo = get_post_meta($post->ID, '_correo', true);

    // Se muestra los campos en el formulario
    ?>
    <p>
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo esc_attr($nombre); ?>">
    </p>
    <p>
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" value="<?php echo esc_attr($apellido); ?>">
    </p>
    <p>
        <label for="celular">Celular:</label>
        <input type="text" id="celular" name="celular" value="<?php echo esc_attr($celular); ?>">
    </p>
    <p>
        <label for="correo">Correo Electrónico:</label>
        <input type="text" id="correo" name="correo" value="<?php echo esc_attr($correo); ?>">
    </p>
    <?php
}

// Se guardan los valores de los campos
function guardar_informacion_contacto($post_id) {
    // Se verifica la seguridad
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Se verifica si los índices están definidos
    $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? sanitize_text_field($_POST['apellido']) : '';
    $celular = isset($_POST['celular']) ? sanitize_text_field($_POST['celular']) : '';
    $correo = isset($_POST['correo']) ? sanitize_email($_POST['correo']) : '';

    // Se guarda los valores
    update_post_meta($post_id, '_nombre', $nombre);
    update_post_meta($post_id, '_apellido', $apellido);
    update_post_meta($post_id, '_celular', $celular);
    update_post_meta($post_id, '_correo', $correo);
}
add_action('save_post', 'guardar_informacion_contacto');

//Se agrega el shortcode para el formulario
function formulario_shortcode() {
    ob_start(); // Se inicia el buffer de salida
    ?>
    <div class="container">
    <h2>Crear contactos</h2>
    <form action="<?php echo get_template_directory_uri(); ?>/procesar-formulario.php" method="post" class="forma"> 
   
        <div class="elemento">
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" required><br/>
        </div>
        <div class="elemento">
            <label for="apellido">Apellido</label><br/>
            <input type="text" id="apellido" name="apellido" required><br/>
        </div>
        <div class="elemento">
            <label for="celular">Celular</label><br/>
            <input type="number" id="celular" name="celular" required><br/>
        </div>
        
        <div class="elemento">
            <label for="correo">Correo electrónico</label><br/>
            <input type="email" id="correo" name="correo" required><br>
        </div>

        <div class="elemento">
            <input type="submit" value="Crear contacto"></input>
        </div>
    </form>
    </div>

    <?php
    return ob_get_clean(); //Se captura el contenido del búfer y se devuelve
}

add_shortcode('mi_formulario', 'formulario_shortcode');

// Se recupera la información de cada contacto y se muestra

function show_contact_info($atts) {
    // Se recupera el ID del post actual
    $post_id = get_the_ID();

    // Se obtienen los valores de los campos
    $nombre = get_post_meta($post_id, 'nombre', true);
    $apellido = get_post_meta($post_id, 'apellido', true);
    $celular = get_post_meta($post_id, 'celular', true);
    $correo = get_post_meta($post_id, 'correo', true);

    $output = '<div class="contacto-info">';
    $output .= '<p><strong>Nombre:</strong> ' . esc_html($nombre) . '</p>';
    $output .= '<p><strong>Apellido:</strong> ' . esc_html($apellido) . '</p>';
    $output .= '<p><strong>Celular:</strong> ' . esc_html($celular) . '</p>';
    $output .= '<p><strong>Correo:</strong> ' . esc_html($correo) . '</p>';
    $output .= '</div>';

    return $output;
}

add_shortcode('mostrar_contacto', 'show_contact_info');

function asignar_plantilla_contacto($template) {
    // Se verifica si se crea en un post de tipo 'contactos'
    if (is_singular('contactos')) {
        // Se localiza la plantilla personalizada para 'contactos', es decir, single-contactos.html
        $nueva_plantilla = locate_template(array('single-contactos.html'));

        // Se usa la plantilla single-contactos para los custom post de tipo 'contactos'
        if (!empty($nueva_plantilla)) {

            return $nueva_plantilla;
        }
       
    }

    return $template;
}

add_filter('single_template', 'asignar_plantilla_contacto');

function buscar_contacto_shortcode() {
    ob_start();
    ?>
    <div class="container2">
        <form method="post" action="" class="forma2">
            <label for="numero_celular">Buscar por número de celular:</label>
            <input type="text" name="numero_celular" id="numero_celular">
            <input type="submit" value="Buscar">
        </form>
    </div>
    <?php

    // Se procesa la búsqueda cuando se envía el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero_celular'])) {
        $numero_celular = sanitize_text_field($_POST['numero_celular']);

        // Se realiza la búsqueda en la base de datos
        $contacto_encontrado = buscar_contacto_por_celular($numero_celular);
    }

    return ob_get_clean(); // Se retorna el contenido del buffer de salida
}
add_shortcode('buscar_contacto', 'buscar_contacto_shortcode');


function buscar_contacto_por_celular($numero_celular) {
    // Se reemplaza 'contactos' y 'celular' con sus valores
    $args = array(
        'post_type' => 'contactos',
        'meta_query' => array(
            array(
                'key' => 'celular',
                'value' => $numero_celular,
                'compare' => '='
            )
        )
    );

    $query = new WP_Query($args);

    // Se verifica si se encontraron contactos
    if ($query->have_posts()) {
        echo '<p class="alert"><strong>Contactos encontrados:</strong></p>';

        while ($query->have_posts()) {
            $query->the_post();

            // Se recupera la información del contacto
            $nombre = get_post_meta(get_the_ID(), 'nombre', true);
            $apellido = get_post_meta(get_the_ID(), 'apellido', true);
            $celular = get_post_meta(get_the_ID(), 'celular', true);
            $correo = get_post_meta(get_the_ID(), 'correo', true);

            echo '<div class="alert"><strong>Nombre: </strong>' . $nombre . '</div>';
            echo '<div class="alert"><strong>Apellido: </strong>' . $apellido . '</div>';
            echo '<div class="alert"><strong>Celular: </strong>' . $celular . '</div>';
            echo '<div class="alert"><strong>Correo: </strong>' . $correo . '</div>';
            echo '<div><hr></div>';
        }

        wp_reset_postdata(); // Se reestablecen los datos del post
    } else {
        // Si no se encuentra ningún contacto
        echo '<p>Contacto no encontrado.</p>';
    }
}