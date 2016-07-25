<?php
/*
    Plugin Name: OCWS Slider Plugin
    Description: This is a full featured slider plugin. It is actually a simple implementation of a nivo slideshow into WordPress. It utilizes the nivo slider jQuery code, following a tutorial by Ciprian Turcu. A couple of OCWS custom features have been added. Make sure you include the shortcode [ocwssl-shortcode] in any page where you wish the slider to appear.
    Author: Paul Taylor
<<<<<<< HEAD
    Version: 0.5
=======
    Version: 0.3.4
>>>>>>> origin/master
    Plugin URI: http://oldcastleweb.com/pws/plugins
    Author URI: http://oldcastleweb.com/pws/about
    License: GPL2
    GitHub Plugin URI: https://github.com/pftaylor61/ocws-slider
    GitHub Branch:     master
*/

/* Essential Initialization Definitions */
define('SLSLUG', 'ocwsslider');
define('SLNAME_SG', 'Slider Image');
define('SLNAME_PL', 'Slider Images');
define("OCWSSL_BASE_DIR",dirname(__FILE__));
define("OCWSSL_BASE_URL",plugins_url( '', __FILE__ ));
define("OCWSSL_IMAGES_URL",OCWSSL_BASE_URL."/images");



function ocwssl_init() {
    add_shortcode('ocwssl-shortcode', 'ocwssl_function');
    $args = array(
        'public' => true,
        'labels' => array(
					'name' => __( 'Slider Images' , SLSLUG),
					'singular_name' => __( SLNAME_SG , SLSLUG),
					'add_new' => __( 'Add New', SLSLUG ),
					'add_new_item' => __( 'Add New '.SLNAME_SG, SLSLUG ),
					'edit_item' => __( 'Edit '.SLNAME_SG, SLSLUG ),
					'new_item' => __( 'New '.SLNAME_SG, SLSLUG ),
					'view_item' => __( 'View '.SLNAME_SG, SLSLUG ),
					'search_items' => __( 'Search '.SLNAME_PL, SLSLUG ),
					'not_found' => __( 'No '.SLNAME_PL.' found', SLSLUG ),
					'not_found_in_trash' => __( 'No '.SLNAME_PL.' found in Trash', SLSLUG ),
					'parent_item_colon' => __( 'Parent '.SLNAME_SG.':', SLSLUG ),
					'menu_name' => __( SLNAME_PL, SLSLUG ),
                         ),
        'menu_icon'   => OCWSSL_IMAGES_URL.'/palmtree16x16.png',
        'show_ui' => true,
        'supports' => array(
            'title',
            'thumbnail',
            'editor',

        )
    );
    register_post_type('ocwssl_images', $args);
    add_action('add_meta_boxes','ocwssl_mbe_create');

    add_image_size('ocwssl_widget', 150, 83, true);
    add_image_size('ocwssl_function', 600, 280, true);
    add_image_size('ocwssl_thin',600, 40, true);
}
add_theme_support( 'post-thumbnails' );
add_action('init', 'ocwssl_init');


/* getting the nivo-slider code to work */
add_action('wp_print_scripts', 'ocwssl_register_scripts');
add_action('wp_print_styles', 'ocwssl_register_styles');


/* we need a meta box */
function ocwssl_mbe_create() {
    add_meta_box( 'ocwssl_meta', 'OCWS '.SLNAME_SG.' Information', 'ocwssl_mbe_function', SLSLUG, 'normal', 'default' );
}

function ocwssl_mbe_function() {
        global $post;
	echo "<p>All the extra information required for the ".SLNAME_SG." should be in this section.</p>";
}


function ocwssl_register_scripts() {
    if (!is_admin()) {
        // register
        wp_register_script('ocwssl_nivo-script', plugins_url('nivo-slider/jquery.nivo.slider.js', __FILE__), array( 'jquery' ));
        wp_register_script('ocwssl_script', plugins_url('script.js', __FILE__));

        // enqueue
        wp_enqueue_script('ocwssl_nivo-script');
        wp_enqueue_script('ocwssl_script');
    }
}

function ocwssl_register_styles() {
    // register
    wp_register_style('ocwssl_styles', plugins_url('nivo-slider/nivo-slider.css', __FILE__));
    wp_register_style('ocwssl_styles_theme', plugins_url('nivo-slider/themes/default/default.css', __FILE__));

    // enqueue
    wp_enqueue_style('ocwssl_styles');
    wp_enqueue_style('ocwssl_styles_theme');
}

/* querying the loop to get the slides */
function ocwssl_function($type='ocwssl_function') {
    $args = array(
        'post_type' => 'ocwssl_images',
        'posts_per_page' => 5
    );
    $result = '<div class="slider-wrapper theme-default">';
    $result .= '<div id="slider" class="nivoSlider">';

    //the loop
    $loop = new WP_Query($args);
    while ($loop->have_posts()) {
        $loop->the_post();

        $the_url = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $type);
        $result .='<img id="'.get_the_title().'" src="' . $the_url[0] . '" data-thumb="' . $the_url[0] . '" title="'.get_the_content().'" alt=""/>';
    }
    $result .= '</div>';
    $result .='<div id = "htmlcaption" class = "nivo-html-caption">';
    $result .='<strong>This</strong> is an example of a <em>HTML</em> caption with <a href = "#">a link</a>.';
    $result .='</div>';
    $result .='</div>';
    return $result;
}

/* material for slideshow widget */
function ocwssl_widgets_init() {
    register_widget('ocwssl_Widget');
}

add_action('widgets_init', 'ocwssl_widgets_init');

class ocwssl_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct('ocwssl_Widget', 'OCWS Slideshow', array('description' => __('A Nivo Slideshow Widget, from OCWS', 'text_domain')));
    } // end public function __construct

    public function form($instance) {
    if (isset($instance['title'])) {
        $title = $instance['title'];
    }
    else {
        $title = __('Widget Slideshow', 'text_domain');
    }
    ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
    <?php
    } // end public function form

    public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = strip_tags($new_instance['title']);

    return $instance;
    } // end public function update

    public function widget($args, $instance) {
    extract($args);
    // the title
    $title = apply_filters('widget_title', $instance['title']);
    echo $before_widget;
    if (!empty($title))
        echo $before_title . $title . $after_title;
    echo ocwssl_function('ocwssl_widget');
    echo $after_widget;
    } // end public function widget

} // end class ocwssl_Widget



?>
