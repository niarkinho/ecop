<?php


function ecopart_get_total_from_order( $WC_Order ) {
	$items = $WC_Order->get_items();

	//plouf($WC_Order);

	$total_ecoparticipation = 0;
	if( $items) foreach( $items as $WC_Order_Item_Product ) {
		$ecopart = 0;
		$variation_id = $WC_Order_Item_Product->get_variation_id();

		if( $variation_id ) {
			$ecopart = get_post_meta( $variation_id, '_var_ecopart', true );
			$product_id = $variation_id;
		}
		else {
			$product_id = $WC_Order_Item_Product->get_product_id();
			$ecopart = get_post_meta( $product_id, '_ecopart', true );
		}
		
		if( $ecopart ) {
			$ecopart = str_replace( ',', '.', $ecopart );
			$product = wc_get_product( $product_id );
			$args = array(
				'price'	=> $ecopart,
				'qty'	=> 1,
			);
			if( get_option('woocommerce_tax_display_cart' ) ) {
				$ecopart = wc_get_price_including_tax( $product, $args );
			}
			else {
				$ecopart = wc_get_price_to_display( $product, $args );
			}


			$total_ecoparticipation += $ecopart * $WC_Order_Item_Product->get_quantity();

		}

	}
	return apply_filters( 'ecopart_get_total_from_order', $total_ecoparticipation );

}