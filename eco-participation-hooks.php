<?php

add_filter('woocommerce_grouped_product_list_column_label', 'ecopart_grouped_product_list_label', 10, 2);
function ecopart_grouped_product_list_label( $label, $child_product ) {
	$ecopart_display_product = get_option( 'ecopart_display_product' );
	if( $ecopart_display_product !== 0 )  {

		$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );
		$hide_empty = get_option('ecopart_hide_empty_ecopart' );
		$ecopart = get_post_meta( $child_product->get_id(), '_ecopart', true );
		if( $ecopart > 0 || !$hide_empty )  {
			$label .= '<span class="ecopart ecopart_child_product">' . sprintf( 
					$ecopart_format,
					wc_price( $ecopart )
				) . '</span>';
		}
	}

	return $label;
}

function ecopart_grouped_product_list( $grouped_product ) {
//	plouf($grouped_product);
	return;
}

/**
 * Sauvegarde l'écopartipcation à la création de commande
 * */
add_action( 'woocommerce_checkout_update_order_meta', 'ecopart_save_ecoparticipation', 10 , 2 );
function ecopart_save_ecoparticipation( $order_id, $data ) {
	if( isset( $_POST['_ecopart'] ) ) {
		$ecoparticipation = filter_var( $_POST['_ecopart'], FILTER_VALIDATE_FLOAT );
		update_post_meta( $order_id, '_ecopart', $ecoparticipation );
	}
}


/**
 * Affiche l'ecopart sur la page produit
 * */
$ecopart_display_product = get_option( 'ecopart_display_product' );
if( $ecopart_display_product !== 0 )  {
	$ecopart_display_product = filter_var( $ecopart_display_product, FILTER_SANITIZE_STRING );
	if( !has_action( $ecopart_display_product, 'ecopart_display_product_page' ) ) {
		add_action( $ecopart_display_product, 'ecopart_display_product_page', 15 );

	}
}



/**
 * Ajoute l'éco participation au nom du produit dans le panier
 * Attention : penser à masquer l'écopart dans le mini panier
 * .woocommerce-mini-cart .ecopart { display: none; }
 * */
$ecopart_display_cart_item = get_option( 'ecopart_display_cart_item' );
if( $ecopart_display_cart_item !== 0) {
	$ecopart_display_cart_item = filter_var( $ecopart_display_cart_item, FILTER_SANITIZE_STRING );
	if(! has_action( $ecopart_display_cart_item, 'ecopart_cart_item_name') )  {
		add_action( $ecopart_display_cart_item, 'ecopart_cart_item_name', 10, 3 );
	}
		
}




/**
 * Ajoute l'affichage du total eco participation sur les pages panier et commande
 */
$ecopart_display_cart = get_option( 'ecopart_display_cart' );
//echo "\n<br />in cart = "; var_dump($ecopart_display_cart);

if( $ecopart_display_cart !== 0 ) {
	$ecopart_display_cart = filter_var( $ecopart_display_cart, FILTER_SANITIZE_STRING );
	if( !has_action( $ecopart_display_cart, 'ecopart_display_total_ecopart' ) ) {
		add_action( $ecopart_display_cart, 'ecopart_display_total_ecopart' );
		//echo "\n<br /> adding action $ecopart_display_cart";
	}
}


$ecopart_display_checkout = get_option( 'ecopart_display_checkout' );
//echo "\n<br /> in checkout ";var_dump($ecopart_display_checkout);
if( $ecopart_display_checkout !== 0) {
	$ecopart_display_checkout = filter_var( $ecopart_display_checkout, FILTER_SANITIZE_STRING );
	if( !has_action( $ecopart_display_checkout, 'ecopart_display_total_ecopart' ) ) {
		add_action( $ecopart_display_checkout, 'ecopart_display_total_ecopart' );
		//echo "\n<br />adding checkout $ecopart_display_checkout";
	}
	else {
	}
}

$ecopart_display_confirmation = get_option( 'ecopart_display_confirmation' );
//echo "\n<br /> in checkout ";var_dump($ecopart_display_checkout);
if( $ecopart_display_confirmation !== 0) {
	$ecopart_display_confirmation = filter_var( $ecopart_display_confirmation, FILTER_SANITIZE_STRING );
	if( !has_action( $ecopart_display_confirmation, 'ecopart_display_total_ecopart' ) ) {
		add_action( $ecopart_display_confirmation, 'ecopart_display_total_ecopart' );
		//echo "\n<br />adding checkout $ecopart_display_checkout";
	}
	else {
	}
}




/**
 * Affiche dans l'email
 * */
$ecopart_display_email = get_option( 'ecopart_display_email' );
if( $ecopart_display_email !== 0 ) {
	$ecopart_display_email = filter_var( $ecopart_display_email, FILTER_SANITIZE_STRING );
	if( !has_action( $ecopart_display_email, 'ecopart_display_total_ecopart_mail' ) ) {
		add_action( $ecopart_display_email, 'ecopart_display_total_ecopart_mail', 10, 4 );

	}
}


/**
 * Affichage dnas la facture
 * */

$show_in_wpo_invoices = get_option ('ecopart_show_in_woocommerce-pdf-invoices-packing-slips' );
if( $show_in_wpo_invoices ) 
	add_action('wpo_wcpdf_after_customer_notes', 'ecopart_show_in_woocommerce_pdf_invoices_packing_slips', 10, 2);


/**
 * Affiche dans les totaux
 * */
$ecopart_in_totals = get_option('ecopart_in_totals');
if( $ecopart_in_totals ) {
	//echo "\n<br />adding in totals";
	add_filter( 'woocommerce_get_order_item_totals', 'ecopart_add_ecopart_row_in_totals', 10, 3 );
}