<?php

 
/**
 * Add field to quick edit screen
 *
 * @param string $column_name Custom column name, used to check
 * @param string $post_type
 *
 * @return void
 */
add_action( 'quick_edit_custom_box', 'ecopart_quick_edit_add', 10, 2 );
function ecopart_quick_edit_add( $column_name, $post_type ) {
	if( $post_type != 'product') {
		return;
	}

	if( $column_name != 'price') {
		return;
	}

	if( !get_option( 'ecopart_display_quick_edit' )  || get_option( 'ecopart_display_quick_edit' ) == 'no' ) {
		return;
	}

	global $post;
	$ecopart = get_post_meta( $post->ID, '_ecopart', true );
	//echo "part = " ;var_dump($ecopart);

	?>
	<div class="inline-edit-group wp-clearfix">
		<label class="alignleft">
				<span class="title"><?php _e('Éco-participation', 'ecopart'); ?></span>
				<span class="input-text-wrap"><input type="text" name="_ecopart" value="<?php echo $ecopart; ?>" /></span>
		</label>
	</div>

	
	<?php
 
}
 
/**
 * Save quick edit data
 *
 * @param int $post_id
 *
 * @return void|int
 */
add_action( 'save_post', 'ecopart_save_quick_edit_data' );
function ecopart_save_quick_edit_data( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
 
    if ( ! current_user_can( 'edit_post', $post_id ) || !isset( $_POST['post_type'] ) || 'product' != $_POST['post_type'] ) {
        return $post_id;
    }
 
 	if( !get_option( 'ecopart_display_quick_edit' ) ) {
		return;
	}


	if( !isset( $_POST['_ecopart'] ) ) {
		return;
	}
 	$ecopart = $_POST['_ecopart'];
	$ecopart = str_replace( ',', '.', $ecopart );
	$ecopart = filter_var( $ecopart, FILTER_VALIDATE_FLOAT );

	//echo "part = $ecopart pour '$post_id'";	die('ok');
    update_post_meta( $post_id, '_ecopart', $ecopart );
}




/**
 * Add Variation Settings
 * Create new fields for variations
 *
*/
//
//add_action( 'woocommerce_product_after_variable_attributes', 'ecopart_variation_settings_fields', 10, 3 );
//add_action( 'woocommerce_variation_options', 'ecopart_variation_settings_fields', 10, 3 );
add_action( 'woocommerce_variation_options_pricing', 'ecopart_variation_settings_fields', 10, 3 );
function ecopart_variation_settings_fields( $loop, $variation_data, $variation ) {
	echo "<!-- is here -->";
	if( wp_doing_ajax() && !is_admin() ) return;
	echo "<!-- is here 2 -->";

	$ecopart = get_post_meta( $variation->ID, '_var_ecopart', true );
	//$ecopart = filter_var( $ecopart, FILTER_VALIDATE_FLOAT );
	//ecopart - montant
	?>

	<?php 
	//<div class="form-field eco_participation">

	woocommerce_wp_text_input( 
		array( 
			'id'		=> '_var_ecopart[' . $variation->ID . ']', 
			'wrapper_class'		=> 'variable_ecoparticipation_field form-row form-row-first',
			'data_type'	=> 'price',
			'label'		=> __( 'Éco-participation', 'ecopart' ), 
			'value'		=> $ecopart
		)
	);
	 ?>
	 <style type="text/css">
	 	.variable_ecoparticipation_field label {
	 		white-space: nowrap;
	 	}
	 </style>
	 <?php 
	//			</div>

}

/**
 * Affiche un champ sur produit variable pour mettre à jour l'éco participation en masse sur les variations
 * */
add_action( 'woocommerce_variable_product_bulk_edit_actions', 'ecopart_bulk_edit_field' );
function ecopart_bulk_edit_field() {
	global $woocommerce, $post;
	$WC_Product = wc_get_product( $post->ID );

	if( $WC_Product->get_type() != 'variable' )  {
		return;
	}
	if( !get_option( 'ecopart_display_quick_edit' ) || get_option( 'ecopart_display_quick_edit' ) == 'no' ) {
		return;
	}


	//var_dump(get_post_meta( $post->ID, '_ecopart', true ));
	?>
			<br /><input type="text" class="short wc_input_price" style="width: 17rem;" name="" id="_ecopart_bulk" value="" placeholder="<?php _e("Forcer l'éco-participation des variables à", 'ecopart' ); ?>"> 
			<input type="button" class="button" id="bulk_apply_ecopart" value="<?php _e('Forcer', 'ecopart'); ?>">
		
		<script type="text/javascript">
			jQuery('input#bulk_apply_ecopart').on('click', function() {

				var bulk_ecopart = jQuery('input#_ecopart_bulk').val();
				console.log("bulk apply : " + bulk_ecopart );
				jQuery('input[name^="_var_ecopart"]').val( bulk_ecopart );
				return false;
				

			});
		</script>
	</div>
	<?php 
}

/**
 * Save Variation Settings
 * */
add_action( 'woocommerce_save_product_variation', 'ecopart_save_variation_settings_fields', 10, 2 );
function ecopart_save_variation_settings_fields( $post_id ) {
	// ecopart - montant

	//plouf($_POST , " pour $post_id");	die('ok');
	 $var_ecopart = $_POST['_var_ecopart'][$post_id];
	 $var_ecopart = str_replace( ',', '.', $var_ecopart );
	 $var_ecopart = filter_var( $var_ecopart, FILTER_VALIDATE_FLOAT );
	 update_post_meta( $post_id, '_var_ecopart', $var_ecopart );
}

add_action('save_post_product', 'ecopart_on_save_post_product', 10, 3);
function ecopart_on_save_post_product( $post_id, $post, $update ) {

	if( isset( $_POST['_var_ecopart'] ) ) {
		foreach( $_POST['_var_ecopart'] as $variation_id => $ecopart ) {
			update_post_meta( $variation_id, '_var_ecopart', $ecopart );
		}
	}
}


/**
 * Affiche l'écopart sur le produit simple
 * */
add_action( 'woocommerce_product_options_general_product_data', 'ecopart_woo_add_custom_general_fields' );
function ecopart_woo_add_custom_general_fields() {
	global $woocommerce, $post;
	$WC_Product = wc_get_product( $post->ID );

	if( $WC_Product->get_type() == 'variable' )  {
		return;
	}

	//var_dump(get_post_meta( $post->ID, '_ecopart', true ));
	echo '<div class="options_group">';
	//ecopart simple
	woocommerce_wp_text_input( 
		array( 
			'id'		=> '_ecopart', 
			'data_type'	=> 'price',
			'label'		=> __( 'Éco-participation', 'ecopart' ),
			'value'		=> get_post_meta( $post->ID, '_ecopart', true ),
		)
	);
	echo '</div>'; 
}


/**
 * Sauve l'écopart simple
 * */
add_action( 'woocommerce_process_product_meta', 'ecopart_woo_add_custom_general_fields_save' );
function ecopart_woo_add_custom_general_fields_save( $post_id ){
	
	//plouf($_POST);	die('ok');
	$ecopart = isset( $_POST['_ecopart'] ) ? $_POST['_ecopart'] : false;
	$ecopart = str_replace( ',', '.', $ecopart );
	$ecopart = filter_var( $ecopart, FILTER_VALIDATE_FLOAT );

//	echo "ecopart = $ecopart pour $post_id";

	update_post_meta( $post_id, '_ecopart', $ecopart );			

//	echo " base = " . get_post_meta( $post_id, '_ecopart', true);	die('ok');

}

/*
 * 


 * Ajoute l'écopart à une nouvelle variation

ATTENTION PROVOQUE UN BUG SUR LE QUICK VIEW EN FRONT AVEC LE THEME / FRAMEWORK PORTO
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields', 90, 1 );
function ecopart_load_variation_settings_fields( $variations ) {
	if( wp_doing_ajax() ) return $variations;
	// duplicate the line for each field
	$variations['var_ecopart'] = get_post_meta( $variations[ 'variation_id' ], '_var_ecopart', true );
	return $variations;
}

*/

