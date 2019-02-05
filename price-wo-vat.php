<?php
/*
* Plugin Name: Cena bez DPH a konec slevy v detailu produktu
* Plugin URI: http://webstudionovetrendy.eu/
* Description: Zobrazí cenu bez DPH a konec akce pod SKU v detailu produktu
* Version: 161128
* Author: Webstudio Nove Trendy
* Author URI: http://webstudionovetrendy.eu/
* License: GPL2
* GitHub Plugin URI: https://github.com/novetrendy/Price-without-VAT-for-woocommerce
*/
/* 5.5.2016 - Přidána podpora pro varianty produktů
* 7.5.2016 - Opravena chyba pokud není zadána u produktu cena - dělení nulou - division zero error
*/

defined( 'ABSPATH' ) or die( 'HAHAHA' );

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

/**
* Frontend CSS
*/
function nt_add_PWOVAT_css(){
        wp_enqueue_style( 'price-wo-vat', plugins_url('assets/css/price-wo-vats.css', __FILE__), false, '1.0.0', 'all');
    }
    add_action('wp_enqueue_scripts', "nt_add_PWOVAT_css");

/**
 * Backend CSS
*/
 function nt_admin_PWOVAT_style() {
    wp_enqueue_style('price-wo-vat-admin', plugins_url('assets/css/price-wo-vat-admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'nt_admin_PWOVAT_style');
add_action('login_enqueue_scripts', 'nt_admin_PWOVAT_style');

/**
 * Localization
 */
 add_action('plugins_loaded', 'nt_pwovat_plugin_localization_init');
 function nt_pwovat_plugin_localization_init() {
 load_plugin_textdomain( 'nt-PWOVAT', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}

/* **************************************************************************************** */

/**
 * Add page to menu
 */



add_action( 'admin_menu', 'nt_price_wo_vat_admin_menu' );
function nt_price_wo_vat_admin_menu() {
     if ( empty ( $GLOBALS['admin_page_hooks']['nt-admin-page'] ) ){
    add_menu_page( __('New Trends','nt-PWOVAT'), __('New Trends','nt-PWOVAT'), 'manage_options', 'nt-admin-page', 'nt_PWOVAT_page', 'dashicons-admin-tools', 3  );
}
}

function nt_PWOVAT_page() {
    echo '<h1>' . __( 'Mainpage for setting plugins from New Trends', 'nt-PWOVAT' ) . '</h1>';
    echo '<a target="_blank" title="' .  __('WebStudio New Trends','nt-PWOVAT') . '" href="http://webstudionovetrendy.eu"><img alt="' .  __('WebStudio New Trends','nt-PWOVAT') . '" title="' .  __('WebStudio New Trends','nt-PWOVAT') . '" class="ntlogo" src=" '. plugin_dir_url( __FILE__ ) .'admin/images/logo.png" /><br /></a><hr />';
    do_action('nt_after_main_content_admin_page_loop_action');
 }

add_action('nt_after_main_content_admin_page_loop_action', 'price_wo_vat_print_details');

function price_wo_vat_print_details(){
  echo  '<br /><a href="' . admin_url(). 'admin.php?page=price_wo_vat">' . __('Price Without VAT, New Badge, Discount Badge and End Sale Date', 'nt-PWOVAT') . '</a><br /><p>'. __( 'Plugin Price Without VAT allow show price without VAT, Discount Badge in percent and amount, New product Badge and also show End Sale date ', 'nt-PWOVAT' ) .'</p><br /><hr />';
}

/**
 * Load class
 */
  require_once plugin_dir_path( __FILE__ ) . 'admin/class-price-wo-vat-admin.php';


if( is_admin() )

    $PWOVAT_settings_page = new PWOVATPage();

/*****************************************************************/

/** Global functions */
    function nt_currency_symbol(){return get_woocommerce_currency_symbol();}
    function nt_action_price(){global $product;return $product->get_sale_price();}
    function nt_action_price_variable(){global $product;
    if($product->product_type=='variable') {
    #Step 1: Get product variations
    $available_variations = $product->get_available_variations();
    #Step 2: Get product variation id
    //$variation_id=$available_variations[0]['variation_id']; // Getting the variable id of just the 1st product. You can loop $available_variations to get info about each variation.
                $i= 0;
                foreach ($available_variations as $var_id) {
                $variation_id = $var_id['variation_id'];
                $i++;
                }
    #Step 3: Create the variable product object
    $nt_variable_product= new WC_Product_Variation( $variation_id );
    #Step 4: You have the data. Have fun :)
    $sales_price = $nt_variable_product ->get_sale_price();
    return $sales_price;
    }};
    function nt_standard_price_variable(){global $product; if($product->product_type=='variable') { $available_variations = $product->get_available_variations();$i= 0;foreach ($available_variations as $var_id) {$variation_id = $var_id['variation_id'];$i++;}
    $nt_variable_product= new WC_Product_Variation( $variation_id );$regular_price = $nt_variable_product ->get_regular_price();return $regular_price;} }
    function nt_standard_price(){global $product;return $product->get_regular_price();}
    function nt_spare(){return nt_standard_price() - nt_action_price();}
    function nt_spare_percent(){return number_format(nt_spare()/(nt_standard_price()/100), 0, ',', '.');}
    function nt_VAT_rate(){global $product;
        $_tax = new WC_Tax();//looking for appropriate vat for specific product
        $rates = array_shift($_tax->get_rates( $product->get_tax_class() ));
        if (isset($rates['rate'])) { //vat found
        if ($rates['rate'] == 0) {$nt_VAT = $nt_zero_VAT;} //if 0% vat
        else {$nt_VAT = round($rates['rate'])."%";}
        }
        else {$nt_VAT = $failSafe;} // FailSafe: just in case ;-)
        return $nt_VAT;
 }
/*****************************************************************************************/
$nt_price_wo_vat_option = get_option('nt_price_wo_vat');
/** Show price without VAT bellow SKU in product details */
if (isset($nt_price_wo_vat_option['pwovat_support']) && ($nt_price_wo_vat_option['pwovat_support']== 1)) {
    function nt_price_without_vat()
    {   global $product;
        if( $product->is_type( 'simple' )){
        $nt_action_price = nt_action_price();
        $nt_standard_price = nt_standard_price();
        $nt_action_price_variable = nt_action_price_variable();
        $nt_standard_price_variable = nt_standard_price_variable();
        if ( empty($nt_action_price)){$nt_action_price = $nt_action_price_variable;}
        if ( empty($nt_standard_price)){$nt_standard_price = $nt_standard_price_variable;}

        if ( !empty ($nt_standard_price) && !empty ($nt_action_price)){

        echo '<div style="' .get_option('nt_css_price_wo_vat')['css_pwovat']. '"><strong>' . number_format($product->get_price_excluding_tax(), 2, ',', '.') . ' '.nt_currency_symbol().'</strong>'. __('without', 'nt-PWOVAT') . nt_VAT_rate() . __('VAT', 'nt-PWOVAT'). '</div>';
        }
        elseif (empty ($nt_action_price)){
        echo '<div style="' .get_option('nt_css_price_wo_vat')['css_pwovat'].'margin:0px 0px 15px;"><strong>' . number_format($product->get_price_excluding_tax(), 2, ',', '.') . ' '.nt_currency_symbol().'</strong> '. __('without', 'nt-PWOVAT') .' '. nt_VAT_rate() .' '. __('VAT', 'nt-PWOVAT'). '</div>';
        }
    }}
    add_action( 'woocommerce_single_product_summary', 'nt_price_without_vat', 12 );
    }


/** Zobrazí výši slevy pod SKU v detailu produktu */

    if (isset($nt_price_wo_vat_option['badge_sleva']) && ($nt_price_wo_vat_option['badge_sleva']== 1)) {
    function sleva()
    {
        global $product;
        if( $product->is_type( 'simple' )){
        $nt_action_price = nt_action_price();
        $nt_standard_price = nt_standard_price();
        $nt_action_price_variable = nt_action_price_variable();
        $nt_standard_price_variable = nt_standard_price_variable();
        if ( empty($nt_action_price)){$nt_action_price = $nt_action_price_variable;}
        if ( empty($nt_standard_price)){$nt_standard_price = $nt_standard_price_variable;}
        if ( !empty($nt_action_price)) {
        $nt_spare = $nt_standard_price - $nt_action_price;
        $nt_spare_percent = number_format($nt_spare/($nt_standard_price/100), 0, ',', '.');}

        if ( !empty($nt_action_price)){
        echo '<div style="font-size: 16px;line-height: 26px;color: rgb(99, 99, 99);margin:0px 0px 15px;">Ušetříte <strong>' . ($nt_standard_price - $nt_action_price) . '</strong> '.nt_currency_symbol().' vč. '.nt_VAT_rate(). ' DPH (<span style="color: #E5663F;"> - '.$nt_spare_percent.'%</span>)</div>';}
    }
    }
    add_action( 'woocommerce_single_product_summary', 'sleva', 13 );
    }
/* ----------------------------------------------------------------------------------------------------------- */
// Add New Variation Settings
add_filter( 'woocommerce_available_variation', 'load_nt_price_without_vat' );
/**
 * Show custom fields for variations in frontend
*/
function load_nt_price_without_vat( $variations ) {
    $nt_variation_availability = get_post_meta( $variations[ 'variation_id' ], '_stock_status', true );
    if ($nt_variation_availability == 'outofstock'){$nt_variation_availability = '<span style="font-weight:100;padding:0px 5px;font-size:16px;background-color:#404040;color:#FFF;line-height:2.1em;">' . __('Out of stock','woocommerce') . '</span>';}
    else $nt_variation_availability = '';
    $nt_variable_price_wo_VAT = number_format(get_post_meta( $variations[ 'variation_id' ], '_price',true) / 1.21, 2, ',', '.');
    $nt_variable_price = get_post_meta( $variations[ 'variation_id' ], '_regular_price', true);
    $nt_variable_sale_price = get_post_meta( $variations[ 'variation_id' ], '_sale_price', true);
    if (!empty($nt_variable_sale_price)) {
    $nt_variable_spare = $nt_variable_price - $nt_variable_sale_price;
    $nt_variable_spare_percent = number_format($nt_variable_spare/($nt_variable_price/100), 0, ',', '.');

    $nt_variable_spare_output = '<div style="font-size: 16px;line-height: 26px;color: rgb(99, 99, 99);margin:0px 0px 15px;">Ušetříte <strong>' .      $nt_variable_spare . '</strong> '.nt_currency_symbol().' vč. '.nt_VAT_rate(). ' DPH (<span style="color: #E5663F;"> - '.$nt_variable_spare_percent.'%</span>)</div>';}

    $nt_output_variable_price_without_vat = '<div style="' .get_option('nt_css_price_wo_vat')['css_pwovat']. '"><strong>' . $nt_variable_price_wo_VAT . '</strong> '.nt_currency_symbol().' bez '.nt_VAT_rate(). ' DPH</div>' . $nt_variable_spare_output;
    $variations['availability_html'] = $nt_variation_availability . $nt_output_variable_price_without_vat;
	return $variations;
}
/* ----------------------------------------------------------------------------------------------------------- */


/** Zobrazí v badge produktu výši slevy v % */
if (isset($nt_price_wo_vat_option['badge_sleva']) && ($nt_price_wo_vat_option['badge_sleva']== 1)) {
    function badge_sleva( $badge ) {
    if ( !empty( $badge ) ) {
        $nt_action_price = nt_action_price();
        $nt_standard_price = nt_standard_price();
        $nt_action_price_variable = nt_action_price_variable();
        $nt_standard_price_variable = nt_standard_price_variable();
        if ( empty($nt_action_price)){$nt_action_price = $nt_action_price_variable;}
        if ( empty($nt_standard_price)){$nt_standard_price = $nt_standard_price_variable;}
        if ( !empty($nt_action_price)) {
        $nt_spare = $nt_standard_price - $nt_action_price;
        $nt_spare_percent = number_format($nt_spare/($nt_standard_price/100), 0, ',', '.');}

        if ( !empty($nt_action_price)) {
            if ($nt_spare_percent<=10){return '<span class="onsale" style="background-color:#90C63F;"> - '.$nt_spare_percent.'%</span>';}//{return '<span class="onsale"> AKCE </span>';}
            if ($nt_spare_percent<=25){return '<span class="onsale" style="background-color:#0094DE;"> - '.$nt_spare_percent.'%</span>';}
            elseif ($nt_spare_percent>=26){return '<span class="onsale" style="background-color:#EF3F32;"> - '.$nt_spare_percent.'%</span>';}
        }
    }
    return $badge;
}
add_filter( 'woocommerce_sale_flash', 'badge_sleva' );
}

/** New products Badge */

if (isset($nt_price_wo_vat_option['new_products_badge']) && ($nt_price_wo_vat_option['new_products_badge']== 1)) {
        function new_badge($badge) {

				$postdate 		= get_the_time( 'Y-m-d' );			// Post date
				$postdatestamp 	= strtotime( $postdate );			// Timestamped post date
                $newness 		= get_option('nt_price_wo_vat')['days_new_products_badge'];
				if ( ( time() - ( 60 * 60 * 24 * $newness ) ) < $postdatestamp ) { // If the product was published within the newness time frame display the new badge
					return '<span class="wc-new-badge">' . __( 'NOVÉ', 'woocommerce' ) . '</span>';

				}
                return $badge;

			}
  add_filter( 'woocommerce_sale_flash', 'new_badge' );
           }
 /** Zobrazí datum ukončení akce v detailu produktu **/
add_action( 'woocommerce_single_product_summary', 'nt_output_sale_end_date', 11 );
function nt_output_sale_end_date() {
$sale_end_date = get_post_meta( get_the_ID(), '_sale_price_dates_to', true );
if ( ! empty( $sale_end_date ) )
echo '<div style="font-size: 18px;line-height: 26px;color: #E34000;margin:-15px 0px 15px;">' . __( 'Akce končí: ', 'woocommerce' ) . date( 'd.m.Y', $sale_end_date ) . '</div>';
}
}
?>
