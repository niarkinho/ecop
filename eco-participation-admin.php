<?php
// from https://www.speakinginbytes.com/2014/07/woocommerce-settings-tab/
class WC_Settings_Tab_EcoPart {
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_ecoparticipation', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_settings_ecoparticipation', __CLASS__ . '::update_settings' );
    }
    
    
    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_ecoparticipation'] = __( 'Éco-participation', 'ecopart' );
        return $settings_tabs;
    }
    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        ?>

        <div id="ecopart_settings">
            <?php
        
        woocommerce_admin_fields( self::get_settings() );

        do_action('ecopart_settings_before');
        ?>
    </div>
    <?php 
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );

    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {


    	$display_ecopart_product_options = array(
    		'0'												=> __("Pas d'affichage", 'ecopart' ) ,
    		'woocommerce_before_single_product'				=> __('Haut de page', 'ecopart'),
    		'woocommerce_before_single_product_summary'		=> __('Haut de page 2', 'ecopart'),
    		'woocommerce_single_product_summary'			=> __('Près du prix ( recommandé )', 'ecopart'),
    		'woocommerce_before_add_to_cart_form'			=> __("Avant le formulaire d'ajout", 'ecopart' ),
    		'woocommerce_before_variations_form'			=> __('Avant le tableau de variations','ecopart'),
            'woocommerce_before_add_to_cart_form '          => __('Avant le formulaire d\'ajout au panier', 'ecopart' ),
    		'woocommerce_before_add_to_cart_button'			=> __("Avant l'ajout au panier", 'ecopart'),
    		'woocommerce_after_add_to_cart_button'			=> __("Après le bouton d'ajout au panier", 'ecopart' ),
    		'woocommerce_product_meta_start'				=> __("Avant les données méta", 'ecopart' ),
    		'woocommerce_product_meta_end'					=> __('Après les données méta', 'ecopart'),
    		'woocommerce_after_single_product_summary'		=> __('Avant la description','ecopart'),
    		'woocommerce_after_single_product'				=> __('Fin de page','ecopart'),
    	);
    	$display_ecopart_product_options = apply_filters( 'ecopart_display_ecopart_product_options', $display_ecopart_product_options);

		$display_ecopart_cart_item_options = array(
			'0'								=> __("Pas d'affichage", 'ecopart' ) ,
			'woocommerce_cart_item_name'	=> __('Avec le nom du produit', 'ecopart'),
		);
		$display_ecopart_cart_item_options = apply_filters( 'ecopart_display_ecopart_cart_item_options', $display_ecopart_cart_item_options );

		$display_ecopart_cart_options = array(
			'0'								=> __("Pas d'affichage", 'ecopart' ) ,
            'woocommerce_before_cart_table '    => __('Avant le tableau', 'ecopart' ),
            'woocommerce_before_cart_contents'  => __('Avant le contenu du panier', 'ecopart'),
            'woocommerce_cart_contents'     => __('Dans le panier', 'ecopart'),
            'woocommerce_after_cart_contents'   => __('Après le contenu du panier', 'ecopart'),
			'woocommerce_after_cart_table'	=> __('Après le tableau du panier', 'ecopart'),
            'woocommerce_cart_totals_before_shipping'   => __('Totaux: avant l\'expédition', 'ecopart'),
            'woocommerce_cart_totals_after_shipping'   => __('Totaux : après l\'expédition', 'ecopart'),
		);
		$display_ecopart_cart_options = apply_filters( 'ecopart_display_ecopart_cart_options', $display_ecopart_cart_options);

		$display_ecopart_checkout_options = array(
			'0'											=> __("Pas d'affichage", 'ecopart' ) ,
            'woocommerce_before_order_notes'            => __('Avant les notes de commande', 'ecopart' ),
            'woocommerce_after_order_notes'             => __('Après les notes de commande', 'ecopart' ),
            'woocommerce_review_order_after_cart_contents'  => __('Avant le sous total', 'ecopart'),
            'woocommerce_review_order_before_shipping'      => __('Avant le montant d\'expédition', 'ecopart'),
            'woocommerce_review_order_before_order_total'   => __('Avant le total', 'ecopart' ),
            'woocommerce_review_order_before_shipping'  => __('Totaux : Avant l\'expédition', 'ecopart' ),
            'woocommerce_review_order_after_shipping'   => __('Totaux : Après l\'expédition', 'ecopart' ),
            'woocommerce_checkout_after_order_review'   => __('Après les détails', 'ecopart'),
			'woocommerce_checkout_before_order_review'	=> __('Avant les totaux', 'ecopart'),
		);
		$display_ecopart_checkout_options = apply_filters( 'ecopart_display_ecopart_checkout_options', $display_ecopart_checkout_options);


        $display_ecopart_order_details_options = array(
            '0'                                         => __("Pas d'affichage", 'ecopart' ) ,
            'woocommerce_order_details_before_order_table_items'    => __('Avant les produits', 'ecopart'),
            'woocommerce_order_details_after_order_table_items'            => __('Après les produits', 'ecopart' ),
            'woocommerce_order_details_after_order_table'   => __('Après le listing de commande', 'ecopart') ,
            'woocommerce_after_order_details'           => __('En bas de page', 'ecopart' ),

        );

		$display_ecopart_email_options = array(
			'0'										=> __( "Pas d'affichage", 'ecopart' ),
            'woocommerce_email_before_order_table'  => __('Avant la liste produits', 'ecopart'),
			'woocommerce_email_customer_details'	=> __('Après la liste produits', 'ecopart'),
            'woocommerce_email_after_order_table'   => __('Après la note client', 'ecopart'),
		);
		$display_ecopart_email_options = apply_filters( 'ecopart_display_ecopart_email_options', $display_ecopart_email_options );



        $settings = array(
            'section_title' => array(
                'name'     => __( 'Licence', 'ecopart' ),
                'type'     => 'title',
                'desc'     => __('La clef de licence utilisée pour les mises à jours', 'ecopart' ),
                'id'       => 'ecopart_section_title_clef'
            ),

            KeyPressEcoPart::OPTIONKEY_LICENSE    => array(
                'name'      => __('Clef d\'activation et de mises à jour', 'ecopart'),
                //'type'      => get_option( KeyPressEcoPart::OPTIONKEY_LICENSE) ? 'password' : 'text',
                'type'  => 'text',
                'class'     => 'keypress_ecopart_key',
                'id'        => KeyPressEcoPart::OPTIONKEY_LICENSE,
                'desc'   => __('Vous pouvez récupérer votre clef sur <a target="_blank" rel="noopener" href="https://solutions.fluenx.com/mon-compte/">votre compte</a>', 'ecopart'),
            ),

            'section_title' => array(
                'name'     => __( 'Intégration', 'ecopart' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'ecopart_section_title_integration'
            ),

            'format_ecopart'    => array(
                'name'      => __('Format d\'affichage', 'ecopart'),
                'type'      => 'text',
                'default'   => __("Dont éco-participation : %s", 'ecopart'),
                'id'        => 'ecopart_format_ecopart',
                'desc'   => __('Vous pouvez modifier la chaîne d\'affichage en utilisant le tag \'%s\' pour afficher le montant', 'ecopart'),
            ),
            'display_product' => array(
                'name' 		=> __( 'Affichage sur la fiche produit', 'ecopart' ),
                'type' 		=> 'select',
                'options' 	=> $display_ecopart_product_options,
                'default'	=> 'woocommerce_single_product_summary',
                'desc'   => __('Vous pouvez aussi utiliser le shortcode [ecopartproduit] dans le contenu de la page produit', 'ecopart' ),
                //'desc' => __( 'This is some helper text', 'ecopart' ),
                'id'   		=> 'ecopart_display_product'
            ),
            'display_cart_item' => array(
                'name' 		=> __( 'Affichage dans la liste des produits du panier', 'ecopart' ),
                'type' 		=> 'select',
                'options' 	=> $display_ecopart_cart_item_options,
                'default'	=> 'woocommerce_cart_item_name',
                'desc' 		=>  sprintf( 
                	__("Si vous affichez l'éco-participation dans le panier mais souhaitez la masquer dans le mini-panier, vous pouvez utiliser cette règle CSS :<br />
                	.widget_shopping_cart .ecopart { display: none; }<br />
                	par exemple <a href='%s'>dans le CSS additionnel de votre theme</a>
                	", 'ecopart' ),
                	admin_url( 'customize.php')
                ),
                'id'   		=> 'ecopart_display_cart_item'
            ),
            'display_cart' => array(
                'name' 		=> __( 'Affichage du total sur la page panier', 'ecopart' ),
                'type' 		=> 'select',
                'options' 	=> $display_ecopart_cart_options,
                'default'	=> 'woocommerce_after_cart_table',
                'desc' 		=> '',
                'id'   		=> 'ecopart_display_cart'
            ),
            'display_checkout' => array(
                'name' 		=> __( 'Affichage du total sur la page de commande', 'ecopart' ),
                'type' 		=> 'select',
                'options' 	=> $display_ecopart_checkout_options,
                'default'	=> 'woocommerce_checkout_before_order_review',
                'desc' 		=> '',
                'id'   		=> 'ecopart_display_checkout'
            ),
            'display_confirmation' => array(
                'name'      => __( 'Affichage du total sur la confirmation de commande', 'ecopart' ),
                'type'      => 'select',
                'options'   => $display_ecopart_order_details_options,
                'default'   => 'woocommerce_email_customer_details',
                'desc'      => '',
                'id'        => 'ecopart_display_confirmation'
            ),

            
          	'display_email' => array(
                'name' 		=> __( 'Affichage dans les emails clients', 'ecopart' ),
                'type' 		=> 'select',
                'options' 	=> $display_ecopart_email_options,
                'default'	=> 'woocommerce_email_customer_details',
                //'desc' => __( 'This is some helper text', 'ecopart' ),
                'id'   		=> 'ecopart_display_email'
            ), 


            'display_in_totals' => array(
                'name'      => __( 'Affichage de l\'éco-participation  dans les totaux (panier, commande, email)', 'ecopart' ),
                'type'      => 'select',
                'options'   => array(
                    0           => __('Désactivé', 'ecopart'),
                    'subtotal'  => __('Après le sous total', 'ecopart'),
                    'shipping'  => __('Après l\'expédition', 'ecopart'),
                    'total'    => __('Après le total', 'ecopart') ,
                    ),
                'default'   => 0,
                'desc'      => __('Si vous cochez cette case, vous pouvez désactiver l\'affichage dans les emails clients', 'ecopart'),
                'id'        => 'ecopart_in_totals',
            ),

            'display_in_quick_edit' => array(
                'name'      => __( 'Permettre la modification du champ en édition rapide', 'ecopart'),
                'type'      => 'checkbox',
                'default'   => 1,
                'id'        => 'ecopart_display_quick_edit',
            ),
            
            'hide_empty_ecopart' => array(
                'name'      => __( 'Ne pas afficher si l\'éco-participation est à 0 &euro;', 'ecopart'),
                'type'      => 'checkbox',
                'default'   => 1,
                'id'        => 'ecopart_hide_empty_ecopart',
            ),
            
            'ecopart_restrict_countries' => array(
                'name'      => __( 'Afficher seulement pour les codes pays', 'ecopart'),
                'type'      => 'multiselect',
                'options'   => WC()->countries->get_allowed_countries(),
                'description'   => __('Sélectionner les pays pour lesquels afficher l\'éco-participation sur la facture', 'ecopart'),
                'id'        => 'ecopart_restrict_countries',
            ),
        );



        if ( class_exists('SitePress') ) {

            $settings['display_in_quick_edit']['disabled'] = true;
            $settings['display_in_quick_edit']['description'] = __('La modification rapide est impossible quand WPML est actif', 'ecopart');
         
        }
        if( class_exists( 'WPO_WCPDF' ) ) {
            $settings['display_invoice'] = array(
                'name'      => __( 'Afficher sur les factures PDF', 'ecopart'),
                'type'      => 'checkbox',
                'default'   => 1,
                'id'        => 'ecopart_show_in_woocommerce-pdf-invoices-packing-slips',
            );

        }


        $settings['section_end']  = array(
             'type' => 'sectionend',
             'id' => 'ecopart_section_end'
        );
        return apply_filters( 'ecopart_settings', $settings );
    }
}
