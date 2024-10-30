<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
<form name="form" method="post" action="">
<?php wp_nonce_field( $this->namespace."-backend_nonce", $this->namespace."-backend_nonce", false ); ?>
<table class="form-table">
<tr valign="top">
<th scope="row"><label>Display Post Types</label></th>
<td>
<fieldset>
<legend>What post types can related posts be displayed on?</legend>
<?php

foreach ( get_post_types( array('public'   => true ), 'names' ) as $posttype ) {

echo '<input type="checkbox" name="'.$this->displayed_types_field_name.'[]" value="'.$posttype.'" ';

		if (in_array($posttype, $this->options[$this->displayed_types_field_name])) {
						echo "checked=\"checked\"";
					}


echo '/>'.$posttype.'<br />';



}


?>

</fieldset>
</td>
</tr>
<tr valign="top">
<th scope="row">
<label>Results Post Types</label></th>
<td>
<fieldset>
<legend>What post types can be included in related posts results?</legend>
<?php

foreach ( get_post_types( array('public'   => true ), 'names' ) as $posttype ) {

echo '<input type="checkbox" name="'.$this->results_types_field_name.'[]" value="'.$posttype.'" ';

		if (in_array($posttype, $this->options[$this->results_types_field_name])) {
						echo "checked=\"checked\"";
					}


echo '/>'.$posttype.'<br />';



}


?>

</fieldset>
</td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo $this->fallback_image_field_name; ?>"><?php _e("Fallback Image:", $this->namespace); ?></label></th>
<td>
<input type="hidden" name="<?php echo $this->fallback_image_field_name; ?>"  id="<?php echo $this->fallback_image_field_name; ?>" value="<?php echo $this->options[$this->fallback_image_field_name]; ?>" size="10" />
<input type="url" name="<?php echo $this->fallback_image_field_name; ?>-url" id="<?php echo $this->fallback_image_field_name; ?>-url" value="<?php echo wp_get_attachment_url($this->options[$this->fallback_image_field_name]); ?>" size="50" />
<input type="button" class="button" name="<?php echo $this->fallback_image_field_name; ?>-upload_button" id="<?php echo $this->fallback_image_field_name; ?>-upload_button" value="Upload/Select Image" />
</td>
</tr>
</table>
<?php submit_button( 'Save Changes' ); ?>
</form>