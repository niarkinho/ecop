<?php

add_shortcode( 'ecopartproduit', 'shortcode_ecopart_produit' );
function shortcode_ecopart_produit() {
	ob_start();
	ecopart_display_product_page();
	return ob_get_clean();
}


function _ecopart_get_country_from_order( $WC_Order ) {
	$country = false;
	if( method_exists($WC_Order, 'get_billing_country' ) ) {
		$country = $WC_Order->get_billing_country();
	}
	else {
		if( $WC_Order->get_parent_id() ) {
			$parent_order = wc_get_order( $WC_Order->get_parent_id() );
			if( method_exists( $parent_order, 'get_billing_country' ) ) {
				$country = $parent_order->get_billing_country();
			}
		}
		else {
		}

	}
	return apply_filters( '_ecopart_get_country_from_order', $country );

}



function ecopart_show_in_woocommerce_pdf_invoices_packing_slips( $type, $WC_Order ) {
	

	$show = apply_filters('ecopart_afficher_dans_cette_commande', true, $type, $WC_Order );

	if( !$show ) {
		return;
	}

	// ajout 220825 reco WP Overnight
	if ( $type == 'credit-note' ) {
        $WC_Order = wc_get_order( $WC_Order->get_parent_id() );
	}

	$allowed_countries = get_option('ecopart_restrict_countries');

	$country = _ecopart_get_country_from_order( $WC_Order );

	if( count( $allowed_countries ) && !in_array( $country, $allowed_countries ) ) {
		return;
	}

	$ecoparticipation = $WC_Order->get_meta( '_ecopart' );

	$ecoparticipation = floatval( $ecoparticipation );
	if( !$ecoparticipation ) {
		return;
	}
	$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );
	$ecoparticipation = apply_filters( __FUNCTION__, $ecoparticipation );

	printf( 
		$ecopart_format,
		wc_price( $ecoparticipation ) 
	);




}



function ecopart_display_product_page() {
	global $product;

	$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );
	$hide_empty = get_option('ecopart_hide_empty_ecopart' );
	
	if( !$product || !method_exists($product, 'get_type' ) ) {
		return;
	}
	if( $product->get_type() == 'variable' )  { 

		$variations_id = $product->get_children();


		$sum_ecopart = 0;
		$vars_ecopart = array();
		foreach($variations_id as $variation_id) {
			$value = get_post_meta( $variation_id, '_var_ecopart' , true );
			$value = str_replace( ',', '.', $value );
			$vars_ecopart[$variation_id] = $value;
			if( $value && filter_var( $value, FILTER_VALIDATE_FLOAT ) ) {
				$sum_ecopart += $value;
			}
		}

//
		if( !$hide_empty || $sum_ecopart > 0 ) {
			echo '<div class="ecopart var_ecopart ecopart_product ecopart_product_variable">';
			printf( 
				$ecopart_format,
				'<span class="montant"></span>'
			);
			echo '</div>';

			

			$displays_ecopart = array();
			foreach($variations_id as $variation_id) {
				$ecopart = $vars_ecopart[$variation_id];
				$args = array(
					'price'	=> $ecopart,
					'qty'	=> 1,
				);
				if( $ecopart && $ecopart > 0 ) {
					//$ecopart = wc_get_price_including_tax( $product, $args );
					$ecopart = wc_get_price_to_display( $product, $args );
					$montant = $ecopart;
					$display = addslashes( wc_price( $ecopart ) );
					$displays_ecopart[$variation_id] = compact( 'montant', 'display' );

				}
				else {
					$var_ecoparts[$variation_id] = null;
				}


			}

			$var_ecoparts = apply_filters( 'ecopart_display_product_page_var_ecopart', $var_ecoparts, $product );
			$displays_ecopart = apply_filters( 'ecopart_display_product_page_displays_ecopart', $displays_ecopart, $product );

			?>
			<script type="text/javascript">
			jQuery(document).ready(function($) {


				var hide_empty = '<?php echo $hide_empty; ?>';

				var var_ecoparts = JSON.parse('<?php echo json_encode($displays_ecopart); ?>');
				//console.dir(var_ecoparts);

	            function ecopart_update_montant() {
	            	var variation_id = jQuery('input.variation_id').val();

		            //console.log(" id var = " + variation_id);
		            if( variation_id && variation_id != 0) {
		            	var var_ecopart = false;
		            	var var_ecopart = var_ecoparts[variation_id];

		            	//console.log(" var = " + variation_id + " ecopart = ");			            	console.dir( var_ecopart);			            	console.log(var_ecopart.montant );				            console.log( var_ecopart.montant > 0 );

		            	if( variation_id && var_ecopart != undefined && var_ecopart.montant > 0 ) {
		                    jQuery('.var_ecopart .montant').html(var_ecopart.montant);
		                    if( !hide_empty || var_ecopart.montant > 0 ) {
		                    	jQuery('.var_ecopart .montant').html( var_ecopart.display);
		                    	jQuery('.var_ecopart').show();

		                    }
		                }
		                else {
		                	jQuery('.var_ecopart').hide();
		                }
		            }
		            else {
		            	jQuery('.var_ecopart').hide();
		            }
	            }
	            ecopart_update_montant();
	            jQuery('input.variation_id').change( function(){
	                ecopart_update_montant();
	            });
	             
	        });
			
        </script>
        <?php 
		}

	}
	else {
		$ecopart = get_post_meta( $product->get_id(), '_ecopart', true );

		if( !empty( $ecopart ) ) {

			$args = array(
				'price'	=> $ecopart,
				'qty'	=> 1,
			);

			// $ecopart = wc_get_price_including_tax( $product, $args );
			$ecopart = wc_get_price_to_display( $product, $args );
			$ecopart = apply_filters( 'ecopart_display_product_page_single_ecopart', $ecopart );

			if( floatval($ecopart) > 0 || $hide_empty == 'no' ) {
				echo '<div class="ecopart ecopart_product">';
				printf( 
					$ecopart_format,
					wc_price( $ecopart )
				);
				echo '</div>';

			}
		}


	}
}


// 20210912 compatbilité yith-woocommerce-advanced-product-options-premium
function ecopart_yith_advanced_product_options_get_ecopart_from_addons( $cart_item ) {
	$extra_ecopart = 0;
	$debug = false;
	//$debug = true;

	if( isset( $cart_item['yith_wapo_options'] ) && !empty( $cart_item['yith_wapo_options'] ) ) {
		// doesnt' work$addons_data = YITH_WAPO_Cart::get_item_data( array(), $cart_item );

		foreach ( $cart_item['yith_wapo_options'] as $index => $option ) {
			foreach ( $option as $key => $value ) {
				if ( $key && $value != '' ) {
					$explode = explode( '-', $key );
					if ( isset( $explode[1] ) ) {
						$addon_id = $explode[0];
						$option_id = $explode[1];
					} else {
						$addon_id = $key;
						$option_id = $value;
					}
				}
			}
			$info = yith_wapo_get_option_info( $addon_id, $option_id );

			 if ( in_array( $info['addon_type'], array( 'product' ) ) ) {
				$option_product_info = explode( '-', $value );
				$option_product_id = $option_product_info[1];
				$option_product_qty = $option_product_info[2];
				$addon_ecopart = get_post_meta( $option_product_id, '_ecopart', true );
				if( !$addon_ecopart ) {
					$addon_ecopart = get_post_meta( $option_product_id, '_var_ecopart', true );

				}
				$addon_ecopart = str_replace(',', '.', $addon_ecopart );
				$addon_ecopart = floatval( $addon_ecopart );

				$extra_ecopart += $option_product_qty * $addon_ecopart;

			}
		}
	}
	if( $debug) echo "\n EXTRA ECOPART : $extra_ecopart ";
	return $extra_ecopart;
}

function ecopart_display_total_ecopart() {


	//die(__FUNCTION__);
	$current_action = current_action();
	$cart_items =  WC()->cart->get_cart();
	$total_ecoparticipation = 0;

	
	

	$allowed_countries = get_option('ecopart_restrict_countries');
	if( is_array( $allowed_countries ) ) {
		$customer_country = WC()->customer->get_shipping_country();
		if( $customer_country && count( $allowed_countries ) && !in_array( $customer_country , $allowed_countries ) ) {
			return;
		}

	}

	$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );

	$debug = false;
	//$debug = true;

	foreach( $cart_items as $cart_item ) {



		$ecopart = 0;
		// 20210912 compatbilité yith-woocommerce-advanced-product-options-premium
		$extra_ecopart = ecopart_yith_advanced_product_options_get_ecopart_from_addons( $cart_item );
		if( $debug) echo " adding $extra_ecopart";
		$extra_ecopart = floatval( str_replace( ',', '.', $extra_ecopart ) );
		$ecopart = $ecopart + $extra_ecopart;


		if( isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] ) {
			$var_ecopart = get_post_meta( $cart_item['variation_id'], '_var_ecopart', true );
			if( $var_ecopart ) {
				$var_ecopart = floatval( str_replace( ',', '.', $var_ecopart ) );
				$ecopart += $var_ecopart;
			}
			if( $debug ) echo "\n adding $var_ecopart VAR ";
		    $product_id = $cart_item['variation_id'];

		}
		else {
			$product_ecopart = get_post_meta( $cart_item['product_id'], '_ecopart', true );
			if( $product_ecopart ) {
				$product_ecopart = floatval( str_replace( ',', '.', $product_ecopart ) );
				$ecopart += $product_ecopart;
			}
			if( $debug ) echo "\n adding $product_ecopart VAR ";
			$product_id = $cart_item['product_id'];
		}

		if ($debug) echo "\n DONC $ecopart";


		if( $ecopart ) {
        	$ecopart = str_replace( ',', '.', $ecopart );
			$product = wc_get_product( $product_id );
			$args = array(
				'price'	=> $ecopart,
				'qty'	=> $cart_item['quantity'],
			);

			if( get_option('woocommerce_tax_display_cart' ) != 'excl' ) {
				if( $debug) echo "<br />on affiche avec taxe";
				$ecopart = wc_get_price_including_tax( $product, $args );
			}
			else {
				if( $debug) echo "<br />on affiche sans taxe";
				$ecopart = wc_get_price_excluding_tax( $product, $args );
			}

			if( $debug) echo "<br />\n AFFICHAGE taxe ? ";
			if( $debug) var_dump(get_option('woocommerce_tax_display_cart' )  );
			if( $debug ) {
				if(  get_option('woocommerce_tax_display_cart' ) == 'excl') echo "\n ECOPART HT  $ecopart";
				else echo "\n ECOPART TTC $ecopart";
		}

		$ecopart = apply_filters( 'ecopart_display_total_ecopart_get_ecoparticipation_for_cart_item', $ecopart, $cart_item );
		
		$total_ecoparticipation += $ecopart; // * $cart_item['quantity'];

		}

	}
	if( $debug) echo "\n TOTAL ECOPART  $ecopart";

	if( in_array( $current_action, array('woocommerce_review_order_before_shipping', 'woocommerce_review_order_after_shipping', 'woocommerce_cart_totals_before_shipping', 'woocommerce_cart_totals_after_shipping') ) ) {
		$in_table = true;
	}
	else {
		$in_table = false;
	}

	$total_ecoparticipation = apply_filters( 'ecopart_display_total_ecopart_total_ecoparticipation', $total_ecoparticipation );

	//echo "\n table ? $in_table / ecopart = $total_ecoparticipation";
	if( floatval($total_ecoparticipation) > 0 ) {
		if( $in_table ) {
			$label = str_replace('%s', '', $ecopart_format );
			echo '
			<tr class="ecopart ecopart_total ' . $current_action .'">
				<th>' . $label . '</th> 
				<td>';
		}
		else {
			echo '<div class="ecopart ecopart_total ' . $current_action .'">';		
		}

		printf( '<input type="hidden" name="_ecopart" value="%f" />', $total_ecoparticipation );
		if( $in_table ) {
			echo wc_price( $total_ecoparticipation );
		}	
		else {
			printf( 
			$ecopart_format,
			wc_price( $total_ecoparticipation )
		);
		}

		if( $in_table ) {
			echo '
				</td>
			</tr>';
		}
		else {
			echo '</div>';		
		}

	}
}


function ecopart_cart_item_name( $name, $cart_item, $cart_item_key ) {
//	plouf($cart_item);
	$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );

	$allowed_countries = get_option('ecopart_restrict_countries');
	if( is_array( $allowed_countries ) ) {
		$customer_country = WC()->customer->get_shipping_country();
		if( $customer_country && count( $allowed_countries ) && !in_array( $customer_country , $allowed_countries ) ) {
			return $name;
		}
	}

	if( isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] ) {
		$ecopart = get_post_meta( $cart_item['variation_id'], '_var_ecopart', true );
		$product_id = $cart_item['variation_id'];
	}
	else {
		$ecopart = get_post_meta( $cart_item['product_id'], '_ecopart', true );
		$product_id = $cart_item['product_id'];
	}

	$extra_ecopart = ecopart_yith_advanced_product_options_get_ecopart_from_addons( $cart_item );
	if( $extra_ecopart ) {
		$extra_ecopart = floatval( str_replace(',', '.', $extra_ecopart ) );
		$ecopart += $extra_ecopart;
	}
	
	if($ecopart) {
		$ecopart = str_replace( ',', '.', $ecopart );
		$product = wc_get_product( $product_id );
		$args = array(
			'price'	=> $ecopart,
			'qty'	=> 1,
		);
		if( get_option('woocommerce_tax_display_cart' )  != 'excl') {
			$ecopart = wc_get_price_including_tax( $product, $args );
		}
		else {
			$ecopart = wc_get_price_excluding_tax( $product, $args );
		}

		$name .= '<p class="ecopart ecopart_cart_item">' 
		. sprintf( 
			$ecopart_format,
			wc_price( $ecopart )
		)
		. '</p>';
	}
	return $name;

}


function ecopart_add_ecopart_row_in_totals( $totals, $WC_Order, $tax_display ) {


	$new_totals = array();
	$ecopart_in_totals = get_option('ecopart_in_totals');
	if( $ecopart_in_totals === 0 ) {
		return $totals;
	}

	$allowed_countries = get_option('ecopart_restrict_countries');
	$country = _ecopart_get_country_from_order( $WC_Order );
	if( $country && count( $allowed_countries ) && !in_array( $country , $allowed_countries ) ) {
		return $totals;
	}


	foreach( $totals as $total_key => $total_data ) {
		$new_totals[$total_key] = $total_data;
		if( 
			$total_key == $ecopart_in_totals
			||
			( $ecopart_in_totals == 'subtotal' && $total_key == 'cart_subtotal' ) 
		) {

			$total_ecoparticipation = get_post_meta( $WC_Order->ID, '_ecopart', true );
			if( !$total_ecoparticipation ) {
				$total_ecoparticipation = ecopart_get_total_from_order( $WC_Order);
			}

			$total_ecoparticipation = apply_filters( 'ecopart_add_ecopart_row_in_totals_total_ecoparticipation', $total_ecoparticipation, $totals, $WC_Order, $tax_display );
			
			//echo " total = $total_ecoparticipation";
			if( $total_ecoparticipation ) {
				$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );
				$new_totals['ecoparticipation'] = array(
					'label'	=> 	__('Éco-participation','ecopart'),
					'value'	=>	wc_price( $total_ecoparticipation ) 
				);
			}
		}
	}
	return $new_totals;
}


function ecopart_display_total_ecopart_mail( $WC_Order, $sent_to_admin, $plain_text, $WC_Email_Customer ) {

	$total_ecoparticipation = ecopart_get_total_from_order( $WC_Order );
	$ecopart_format = apply_filters( 'ecopart_format', get_option('ecopart_format_ecopart') );

	$total_ecoparticipation = apply_filters( 'ecopart_display_total_ecopart_mail_total_ecoparticipation', $total_ecoparticipation, $WC_Order, $sent_to_admin, $plain_text, $WC_Email_Customer );
	if( $total_ecoparticipation ) {
		echo '<div class="ecopart ecopart_total">';
		printf( 
			$ecopart_format,
			wc_price( $total_ecoparticipation )
		);
		echo '</div>';		

	}

	//echo " TEST";	echo current_action();	plouf($email );	plouf($order);	die('ok');

}