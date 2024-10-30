<?php 
/**
 * Plugin Name: LH Jetpack Related Posts
 * Plugin URI: https://lhero.org/portfolio/lh-jetpack-related-posts/
 * Description: Allow you to configure related posts all over your WordPress installation
 * Author: Peter Shaw
 * Version: 1.07
 * Author URI: https://shawfactor.com
 * Text Domain: lh_login_page
 * Domain Path: /languages
*/


if (!class_exists('LH_jetpack_related_posts_plugin')) {

class LH_jetpack_related_posts_plugin {

var $filename;
var $options;
var $displayed_types_field_name = 'lh_jetpack_related_posts-displayed_types_field_name';
var $results_types_field_name = 'lh_jetpack_related_posts-results_types_field_name';
var $fallback_image_field_name = 'lh_jetpack_related_posts-fallback_image_field_name';
var $plugin_version = '1.07';
var $namespace = 'lh_jetpack_related_posts';
var $opt_name = 'lh_jetpack_related_posts-options';

private static $instance;

private function get_tos_by_from_id($id){

global $wpdb;

$sql = "SELECT p2p_to FROM ".$wpdb->prefix."p2p WHERE p2p_from = '" .$id. "' and p2p_type = 'lh_jetpack_related_posts-related_posts'";

$results = $wpdb->get_results($sql);

foreach ($results as $result) {

$tos[]['id'] = $result->p2p_to;

}
  
  if (isset($tos)){

return $tos;
	
  } else {
	
	
	
	return false;
  }

}



public function add_meta_boxes($post_type, $post) {

if (in_array($post_type, $this->options[$this->displayed_types_field_name])) {


add_meta_box($this->namespace."-related_posts_options-div", "Related Post Options", array($this,"related_posts_options_render"), $post_type, "side");

}

}





public function related_posts_options_render(){

$disable = get_post_meta( get_the_ID(), "_".$this->namespace."-disable_related_posts", true );



?>
<table>
<tbody>
<tr>
<td>
<?php wp_nonce_field( $this->namespace.'-disable-nonce', $this->namespace.'-disable-nonce' ); ?>
<input type="checkbox" value="1" name="<?php  echo $this->namespace."-disable_related_posts";  ?>" id="<?php  echo $this->namespace."-disable_related_posts";  ?>" <?php if ($disable){ echo 'checked="checked"'; } ?> /> 
</td>
<td>
<label id="<?php  echo $this->namespace."-disable_related_posts";  ?>" for="<?php  echo $this->namespace."-disable_related_posts";  ?>">Disable related posts</label></td>
</tr>






</tbody>
</table>





<?php


}


public function update_post_meta( $post_id, $post, $update ) {

if (isset($_POST[$this->namespace.'-disable-nonce']) and wp_verify_nonce( $_POST[$this->namespace.'-disable-nonce'], $this->namespace.'-disable-nonce')){

if (($_POST[$this->namespace."-disable_related_posts"] == 1) || ($_POST[$this->namespace."-disable_related_posts"] == 0)){

$content = $_POST[$this->namespace."-disable_related_posts"];

update_post_meta($post_id, "_".$this->namespace."-disable_related_posts", $content);


}

}

}


public function no_related_posts( $options ) {

global $post;

if (is_singular() and isset($post->ID)){


$disable = get_post_meta($post->ID, "_".$this->namespace."-disable_related_posts", true );

if ($disable == 1){

$options['enabled'] = false;


}


}

    return $options;
}



public function plugin_menu() {
add_options_page('LH Jetpack Related Posts', 'Related Posts', 'manage_options', $this->filename, array($this,"plugin_options"));

}


public function plugin_options() {

if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}


 // See if the user has posted us some information
    // If they did, the nonce will be set

	if( isset($_POST[ $this->namespace."-backend_nonce" ]) && wp_verify_nonce($_POST[ $this->namespace."-backend_nonce" ], $this->namespace."-backend_nonce" )) {


if (isset($_POST[ $this->displayed_types_field_name ]) && ($_POST[ $this->displayed_types_field_name ] != "")){

$options[ $this->displayed_types_field_name  ] = $_POST[ $this->displayed_types_field_name ];

}

if (isset($_POST[ $this->results_types_field_name ]) && ($_POST[ $this->results_types_field_name ] != "")){

$options[ $this->results_types_field_name  ] = $_POST[ $this->results_types_field_name ];

}

if (!isset($_POST[ $this->fallback_image_field_name.'-url' ]) or empty($_POST[ $this->fallback_image_field_name.'-url' ])){
    
    $options[ $this->fallback_image_field_name  ] = "";
    
    
} elseif (isset($_POST[ $this->fallback_image_field_name ]) && (is_numeric($_POST[ $this->fallback_image_field_name ]))){
    
$options[ $this->fallback_image_field_name  ] = $_POST[ $this->fallback_image_field_name ];

} 



if (update_option( $this->opt_name, $options )){

$this->options = get_option($this->opt_name);

?>
<div class="updated"><p><strong><?php _e('LH Jetpack Related Posts settings saved', $this->namespace ); ?></strong></p></div>
<?php


}

}

    // Now display the settings editing screen

include ('partials/option-settings.php');


}

// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}

function allowed_types_for_display( $enabled ){


	if ( is_singular() ) {

global $post;

if (isset($this->options[$this->displayed_types_field_name]) and isset($post->post_type) and in_array($post->post_type, $this->options[$this->displayed_types_field_name])){


$enabled = true;

} else {


$enabled = false;


}


}

return $enabled;
}

public function allowed_types_for_results( $post_type, $post_id ) {
    if ( is_array( $post_type ) ) {
        $search_types = $post_type;
    } else {
        $search_types = array( $post_type );
    }

if (is_array($this->options[$this->results_types_field_name])){

$search_types = $this->options[$this->results_types_field_name];


}


    return $search_types;
}


public function append_related_post( $hits, $post_id ) {
    // $post_id is the post we are currently getting related posts for




$tos = $this->get_tos_by_from_id($post_id);

if ($tos){

$length = count($tos);
for ($i = 0; $i < $length; $i++) {
$hits[$i] = $tos[$i];
}

}



 
    return $hits;
}


public function register_p2p_connection_types() {



 
  p2p_register_connection_type( array(
	'title' => 'Related Posts',
	'cardinality' => 'many-to-many',
        'name' => $this->namespace.'-related_posts',
        'from' => $this->options[$this->displayed_types_field_name],
        'to' => $this->options[$this->results_types_field_name],
  'admin_box' => array(
    'show' => 'from',
    'context' => 'side'
  )
    ) );


}

public function restrict_p2p_box_display( $show, $ctype, $post ) {



if ( 'lh_jetpack_related_posts-related_posts' == $ctype->name ) {

if (get_post_meta( $post->ID, "_".$this->namespace."-disable_related_posts", true )){

return false;

} else {

return $show;

}

} else {

return $show;


}



}

public function lh_instant_articles_related_articles_filter($related, $post_id){

$tos = $this->get_tos_by_from_id($post_id);
if ($tos){

$related = '<ul class="op-related-articles">';

foreach ($tos as $to) {

$related .= '<li><a href="'.get_permalink($to['id']).'"></a></li>';

}


$related .= '</ul>';



}

return $related;


}


// Prepare the media uploader
public function add_admin_scripts(){

if (isset($_GET['page']) && $_GET['page'] == $this->filename) {
	// must be running 3.5+ to use color pickers and image upload
	wp_enqueue_media();


wp_register_script($this->namespace.'-dashboard-admin', plugins_url( '/scripts/uploader.js', __FILE__ ), array('jquery','media-upload','thickbox'),$this->namespace);
wp_enqueue_script($this->namespace.'-dashboard-admin');

}
}



public function plugins_loaded(){


load_plugin_textdomain( $this->namespace, false, basename( dirname( __FILE__ ) ) . '/languages' ); 

}

public function custom_fallback_image( $media, $post_id, $args ) {
    if ( $media ) {
        return $media;
    } else {
        
        $url = wp_get_attachment_url($this->options[$this->fallback_image_field_name]);
        
        if ($url){

        $permalink = get_permalink( $post_id );
        
        
        
        $url = apply_filters( 'jetpack_photon_url', $url );
     
        return array( array(
            'type'  => 'image',
            'from'  => 'custom_fallback',
            'src'   => esc_url( $url ),
            'href'  => $permalink,
        ) );
        
        } else {
            
        return $media;   
            
        }
    }
}

    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }


public function __construct() {

$this->filename = plugin_basename( __FILE__ );
$this->options = get_option($this->opt_name);

add_action('add_meta_boxes', array($this,"add_meta_boxes"),10,2);
add_action( 'save_post', array($this,"update_post_meta"),10,3);
add_action( 'p2p_init', array($this,"register_p2p_connection_types"));

add_filter( 'p2p_admin_box_show', array($this,"restrict_p2p_box_display"), 10, 3 );

add_filter( 'jetpack_relatedposts_filter_options', array($this,"no_related_posts"), 10, 1);

add_filter( 'jetpack_relatedposts_filter_enabled_for_request', array($this,"allowed_types_for_display"), 10, 1);
add_filter( 'jetpack_relatedposts_filter_post_type', array($this,"allowed_types_for_results"), 10, 2 );
add_filter( 'jetpack_relatedposts_filter_hits', array($this,"append_related_post"), 20, 2 );

add_filter( 'lh_instant_articles_related_articles_filter', array($this,"lh_instant_articles_related_articles_filter"), 20, 2 );


/* Add menu */
add_action('admin_menu', array($this, 'plugin_menu'));
add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);

/* Add the upload scripts */
add_action('admin_enqueue_scripts', array($this,"add_admin_scripts"));

//run whatever on plugins loaded (currently just translations)
add_action( 'plugins_loaded', array($this,"plugins_loaded"));

//add a custom fallback image
add_filter( 'jetpack_images_get_images', array($this,"custom_fallback_image"), 10, 3 );

}


}


$lh_jetpack_related_posts_instance = LH_jetpack_related_posts_plugin::get_instance();


}


?>