<?php
/*
    Plugin Name: OCWS Slider Plugin
    Description: This is a full featured slider plugin. It is actually a simple implementation of a nivo slideshow into WordPress. It utilizes the nivo slider jQuery code, following a tutorial by Ciprian Turcu. A couple of OCWS custom features have been added. Make sure you include the shortcode [ocwssl-shortcode] in any page where you wish the slider to appear.
    Author: Paul Taylor
    Version: 1.2.1
    Plugin URI: http://oldcastleweb.com/pws/plugins
    Author URI: http://oldcastleweb.com/pws/about
    License: GPL2
    GitHub Plugin URI: https://github.com/pftaylor61/ocws-slider
    GitHub Branch:     master
*/

/* Essential Initialization Definitions */
// define('SLSLUG', 'ocwsslider');
define('SLSLUG', 'ocwssl_images');
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
        'capability_type' => SLSLUG,
        'capabilities' => array(
					'publish_posts' => 'publish_'.SLSLUG.'s',
					'edit_posts' => 'edit_'.SLSLUG.'s',
					'edit_others_posts' => 'edit_others_'.SLSLUG.'s',
					'delete_posts' => 'delete_'.SLSLUG.'s',
					'delete_others_posts' => 'delete_others_'.SLSLUG.'s',
					'read_private_posts' => 'read_private_'.SLSLUG.'s',
					'edit_post' => 'edit_'.SLSLUG,
					'delete_post' => 'delete_'.SLSLUG,
					'read_post' => 'read_'.SLSLUG,
				),
        'map_meta_cap' => true,
        'supports' => array(
            'title',
            'thumbnail',
            'menu_order',
            'page-attributes',
             

        )
    );
    register_post_type(SLSLUG, $args);
    add_action( 'init', 'slider_type_taxonomy');
    
        /*
		function ocwssl_add_theme_caps() {
        // gets the administrator role
        $admins = get_role( 'administrator' );

        $admins->add_cap( 'publish_'.SLSLUG.'s' ); 
        $admins->add_cap( 'edit_'.SLSLUG.'s' ); 
        $admins->add_cap( 'edit_others_'.SLSLUG.'s' ); 
        $admins->add_cap( 'delete_'.SLSLUG.'s' ); 
        $admins->add_cap( 'delete_others_'.SLSLUG.'s' ); 
        $admins->add_cap( 'read_private_'.SLSLUG.'s' ); 
        $admins->add_cap( 'edit_'.SLSLUG ); 
        $admins->add_cap( 'delete_'.SLSLUG, ); 
        $admins->add_cap( 'read_'.SLSLUG ); 
        }
        add_action( 'admin_init', 'ocwssl_add_theme_caps');
		*/
    
/**
* add order column to admin listing screen for header text
*/
function add_new_ocwssl_images_column($ocwssl_images_columns) {
  $ocwssl_images_columns['menu_order'] = "Slide Order";
  return $ocwssl_images_columns;
}
add_action('manage_edit-ocwssl_images_columns', 'add_new_ocwssl_images_column');

/**
* show custom order column values
*/
function show_order_column($name){
  global $post;

  switch ($name) {
    case 'menu_order':
      $order = $post->menu_order;
      echo $order;
      break;
   default:
      break;
   }
}
add_action('manage_ocwssl_images_posts_custom_column','show_order_column');

/**
* make column sortable
*/
function order_column_register_sortable($columns){
  $columns['menu_order'] = 'menu_order';
  return $columns;
}
add_filter('manage_edit-ocwssl_images_sortable_columns','order_column_register_sortable');
    
function slider_type_taxonomy() {
    register_taxonomy(
        'slidertype',
        SLSLUG,
        array(
            'hierarchical' => false,
            'label' => 'Slider Type',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'slidertype',
                'with_front' => false
				)
			)
    );
}
    

    add_image_size('ocwssl_widget', 150, 83, true);
    add_image_size('ocwssl_function', 1400, 400, true);
    add_image_size('ocwssl_thin',600, 40, true);
}
add_theme_support( 'post-thumbnails' );
add_action('init', 'ocwssl_init');
add_action('add_meta_boxes','ocwssl_mbe_create');
add_action('save_post', 'ocwssl_mbe_function_save');


/* getting the nivo-slider code to work */
add_action('wp_print_scripts', 'ocwssl_register_scripts');
add_action('wp_print_styles', 'ocwssl_register_styles');

add_action('do_meta_boxes', 'ocwssl_image_box');

function ocwssl_image_box() {

	remove_meta_box( 'postimagediv', SLSLUG, 'side' );

	add_meta_box('postimagediv', __('OCWS '.SLNAME_SG.' Featured Image'), 'ocwssl_post_thumbnail_meta_box', SLSLUG, 'normal', 'high');

}

function ocwssl_post_thumbnail_meta_box($post) {
    
    $thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
    echo ocwssl_wp_post_thumbnail_html( $thumbnail_id, $post->ID );
    
}

function ocwssl_wp_post_thumbnail_html( $thumbnail_id = null, $post = null ) {
    global $content_width, $_wp_additional_image_sizes;

    $post               = get_post( $post );
    $post_type_object   = get_post_type_object( $post->post_type );
    $set_thumbnail_link = '<p class="hide-if-no-js"><a title="%s" href="%s" id="set-post-thumbnail" class="thickbox">%s</a></p>';
    $upload_iframe_src  = get_upload_iframe_src( 'image', $post->ID );
    
    

    $content = sprintf( $set_thumbnail_link,
        esc_attr( $post_type_object->labels->set_featured_image ),
        esc_url( $upload_iframe_src ),
        esc_html( $post_type_object->labels->set_featured_image )
    );

    if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
        $old_content_width = $content_width;
        $content_width = 1000;
        if ( !isset( $_wp_additional_image_sizes['full'] ) )    // use 'full' for system defined fullsize image OR use our custom image size instead of 'post-thumbnail'
            $thumbnail_html = wp_get_attachment_image( $thumbnail_id, array( $content_width, $content_width ) );
        else
            $thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'full' ); // use 'full' for system defined fullsize image OR use our custom image size instead of 'post-thumbnail'
        if ( !empty( $thumbnail_html ) ) {
            $ajax_nonce = wp_create_nonce( 'set_post_thumbnail-' . $post->ID );
            $content = sprintf( $set_thumbnail_link,
                esc_attr( $post_type_object->labels->set_featured_image ),
                esc_url( $upload_iframe_src ),
                $thumbnail_html
            );
            $content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail" onclick="WPRemoveThumbnail(\'' . $ajax_nonce . '\');return false;">' . esc_html( $post_type_object->labels->remove_featured_image ) . '</a></p>';
        }
        $content_width = $old_content_width;
    }
    $content .= "<p>It is recommended that the slider image should have the following sizes:</p>\n";
    $content .= "<ul><li>Large Slider: 1400 x 400</li><li>Widget: 150 x 83</li><li>Thin Slider: 1200 x 80</li></ul>\n";

    /**
     * Filter the admin post thumbnail HTML markup to return.
     *
     * @since 2.9.0
     *
     * @param string $content Admin post thumbnail HTML markup.
     * @param int    $post_id Post ID.
     */
    return apply_filters( 'admin_post_thumbnail_html', $content, $post->ID );
}

/* we need a meta box */



function ocwssl_mbe_create() {
    add_meta_box( 'ocwssl_meta', 'OCWS '.SLNAME_SG.' Information', 'ocwssl_mbe_function', SLSLUG, 'normal', 'default' );
}

function ocwssl_mbe_function() {
        global $post;
	echo '<p>Special information required for the '.SLNAME_SG.'.</p>';
        
	// let's see if any metadata values exist
	$ocwssl_info = get_post_meta( $post->ID, '_ocwssl_info', true);
        $ocwssl_url = get_post_meta( $post->ID, '_ocwssl_url', true);
        ?>
        <label for="ocwssl_info"><strong>Type one line of brief info here (no HTML code):</strong></label>
        <input style="width:75%" type="text" id="ocwssl_info" name="ocwssl_info" value="<?php echo esc_attr($ocwssl_info); ?>"  />
        <p>If you want your slider to link to another page or post, then put the whole URL for that page in the box below.</p>
        <label for="ocwssl_url"><strong>Type URL here:</strong> (don't forget http:// or https://)</label>
        <input style="width:75%" type="text" id="ocwssl_url" name="ocwssl_url" value="<?php echo esc_attr($ocwssl_url); ?>"  />
        
        <?php
        
}

function ocwssl_mbe_function_save($post_id) {
	// this function will save the data used by ocwscc_mbe_function and ocwscc_mbe_function_sd
	
	// first check to ssee if the metadata has been set
	if ( isset( $_POST['ocwssl_url'])) {
		
		// now save the data
		
		update_post_meta( $post_id, '_ocwssl_info', strip_tags( $_POST['ocwssl_info']));
                update_post_meta( $post_id, '_ocwssl_url', strip_tags( $_POST['ocwssl_url']));
        }
} // end function ocwssl_mbe_function_save


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

add_filter( 'posts_where', function( $where, $q )
{
    global $wpdb;

    if( (bool) $q->get( '_ignore_default_menu_order' ) ) {
        $where .= "AND {$wpdb->posts}.menu_order <> 0";
    }
    return $where;

}, 10, 2 );

/* querying the loop to get the slides */
function ocwssl_function($type='ocwssl_function') {
    $args = array(
        'post_type' => 'ocwssl_images',
        'posts_per_page' => 10,
        '_ignore_default_menu_order' => true,
        'order' => 'ASC',
        'orderby' => 'menu_order',
    );
    $result = '<div class="slider-wrapper theme-default">';
    $result .= '<div id="slider" class="nivoSlider">';

    //the loop
    $loop = new WP_Query($args);
    while ($loop->have_posts()) {
        $loop->the_post();

        // $ocwssl_id = $post->ID;
        $ocwssl_info2 = get_post_meta(get_the_ID(), '_ocwssl_info');
        $ocwssl_url2 = get_post_meta(get_the_ID(), '_ocwssl_url');
        $the_url = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $type);
        
        // echo $ocwssl_id;
        $result .='<img id="'.get_the_title().'" src="' . $the_url[0] . '" data-thumb="' . $the_url[0] . '" title="'.$ocwssl_info2[0].'" alt="" data-url="'.$ocwssl_url2[0].'" />';
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
