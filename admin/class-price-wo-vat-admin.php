<?php
class PWOVATPage
{
    /** Holds the values to be used in the fields callbacks */
    private $options;
    /** Construct  */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_price_wo_vat_page' ) );
        add_action( 'admin_init', array( $this, 'price_wo_vat_page_init' ) );
    }
    /**
     * Add settings page
     */
    public function add_price_wo_vat_page()
    {
        // Add submenu page will be under "New Trends" page
        add_submenu_page(
            'nt-admin-page',
             __('Settings Price Without VAT, New Badge, Discount Badge and End Sale Date','nt-PWOVAT'),
            __('Price Without VAT, New Badge, Discount Badge and End Sale Date','nt-PWOVAT'),
            'manage_woocommerce',
            'price_wo_vat',
            array( $this, 'nt_create_admin_price_wo_vat_page', )
        );

    }

    /**
     * Options page callback
     */
    public function nt_create_admin_price_wo_vat_page()
    {
        // Set class property
        $this->options = get_option( 'nt_price_wo_vat' );
        $this->options_css = get_option( 'nt_css_price_wo_vat' );
        ?>
<!-- <script>
jQuery(document).ready(function(){

    if(document.getElementById('delivery_date_support').checked) {
    jQuery(".section_delivery_date, .del_date").show(1000);
} else {
    jQuery(".section_delivery_date, .del_date").hide(1000);
}

jQuery('#delivery_date_support').click(function() {
    jQuery(".section_delivery_date, .del_date").toggle(1000);
});
})
</script> -->
<?php settings_errors(); ?>

        <div class="wrap">
            <?php echo '<a target="_blank" title="' .  __('WebStudio New Trends','nt-PWOVAT') . '" href="http://webstudionovetrendy.eu"><img alt="' .  __('WebStudio New Trends','nt-PWOVAT') . '" title="' .  __('WebStudio New Trends','nt-PWOVAT') . '" class="ntlogo" src=" '. plugin_dir_url( __FILE__ ) .'images/logo.png" /><br /></a><hr />';?>
            <h1><?php _e('Price Without VAT, New Badge, Discount Badge and End Sale Date - Plugin settings', 'nt-PWOVAT')?></h1>
            <!--  -->
            <?php
                $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'plugin_options';
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="?page=price_wo_vat&tab=plugin_options" class="nav-tab <?php echo $active_tab == 'plugin_options' ? 'nav-tab-active' : ''; ?>">Plugin functions</a>
            <a href="?page=price_wo_vat&tab=css_options" class="nav-tab <?php echo $active_tab == 'css_options' ? 'nav-tab-active' : ''; ?>">Frontend CSS Settings</a>
        </h2>
            <form method="post" action="options.php">
            <?php
            if( $active_tab == 'plugin_options' ) {
                settings_fields( 'nt_price_wo_vat_option_group' );
                do_settings_sections( 'nt_price_wo_vat' );
            } else if( $active_tab == 'css_options' ) {
                settings_fields( 'nt_css_price_wo_vat_option_group' );
                do_settings_sections( 'nt_css_price_wo_vat' );
            }
            submit_button(); ?>

            </form>
        </div>

        <?php  }

    /**
     * Register and add settings
     */
    public function price_wo_vat_page_init()
    {
        register_setting(
            'nt_price_wo_vat_option_group', // Group settings
            'nt_price_wo_vat', // Name of setting
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_nt_price_wo_vat', // ID
             __('Settings plugin functions','nt-PWOVAT'), // Title
            array( $this, 'print_section_info' ), // Callback
            'nt_price_wo_vat' // Page
        );

        // Add fields
        add_settings_field(
            'pwovat_support', // Enable price w/o VAT ?
            __('Enable Price Without VAT ?','nt-PWOVAT'),
            array( $this, 'pwovat_support_callback'),
            'nt_price_wo_vat',
            'setting_section_nt_price_wo_vat'
        );
        add_settings_field(
            'badge_sleva', // Enable support for Percent Sale Badge ?
            __('Enable Sale Percent Badge ?','nt-PWOVAT'),
            array( $this, 'badge_sleva_support_callback'),
            'nt_price_wo_vat',
            'setting_section_nt_price_wo_vat'
        );

        add_settings_field(
            'amount_sale', // Enable support for Amount and percentage of discounts ?
            __('Enable Amount and percentage of discounts ?','nt-PWOVAT'),
            array( $this, 'amount_sale_add_string_callback'),
            'nt_price_wo_vat',
            'setting_section_nt_price_wo_vat'
        );
        add_settings_field(
            'new_products_badge', // Enable New Products Badge ?
            __('Enable New Products Badge ?','nt-PWOVAT'),
            array( $this, 'new_products_badge_callback'),
            'nt_price_wo_vat',
            'setting_section_nt_price_wo_vat'
        );
        add_settings_field(
            'days_new_products_badge', // Enable New Products Badge ?
            __('Days for New Product Badge ?','nt-PWOVAT'),
            array( $this, 'days_new_products_badge_callback'),
            'nt_price_wo_vat',
            'setting_section_nt_price_wo_vat'
        );



        // Register and add settings for second tab CSS
        register_setting(
            'nt_css_price_wo_vat_option_group', // Group settings
            'nt_css_price_wo_vat', // Name of setting
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_css_price_wo_vat', // ID
             __('Settings frontend CSS','nt-PWOVAT'), // Title
            array( $this, 'print_section_css' ), // Callback
            'nt_css_price_wo_vat' // Page
        );

        // Add Fields
        add_settings_field(
            'css_pwovat', // CSS Title Descriptions
            __('CSS rules for Price Without VAT.','nt-PWOVAT'),
            array( $this, 'css_pwovat_callback'),
            'nt_css_price_wo_vat',
            'setting_section_css_price_wo_vat',
            array( 'class' => 'css_rules' )
        );


        /*************************************/
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['pwovat_support'] ) ) $new_input['pwovat_support'] = (int)( $input['pwovat_support'] );
        if( isset( $input['badge_sleva'] ) ) $new_input['badge_sleva'] = (int)( $input['badge_sleva'] );
        if( isset( $input['amount_sale'] ) ) $new_input['amount_sale'] = (int)( $input['amount_sale'] );
        if( isset( $input['new_products_badge'] ) ) $new_input['new_products_badge'] = (int)( $input['new_products_badge'] );
        if( isset( $input['days_new_products_badge'] ) ) $new_input['days_new_products_badge'] = sanitize_text_field( $input['days_new_products_badge'] );



        // css settings (second tab)
        if( isset( $input['css_pwovat'] ) ) $new_input['css_pwovat'] = sanitize_text_field( $input['css_pwovat'] );
        return $new_input;
    }



    // Callback plugin option (first tab)
    public function pwovat_support_callback(){
    ?><input name="nt_price_wo_vat[pwovat_support]" type="checkbox" value="1" <?php checked( isset( $this->options['pwovat_support'] ) );?> />
    <?php
    echo '<br /><em>'
            . __('Sample from frontend:  ','nt-PWOVAT') . '</em><span style="background:#BBEE00;padding:5px;font-size: 16px;line-height: 36px;color: rgb(99, 99, 99);"><strong>5.602,48 </strong>'. nt_currency_symbol() .'&nbsp'. __('without 21% VAT','nt-PWOVAT') .'</span><br />';

     }

    public function badge_sleva_support_callback(){
    ?><input name="nt_price_wo_vat[badge_sleva]" type="checkbox" value="1" <?php checked( isset( $this->options['badge_sleva'] ) );?> />
    <br />
    <?php
    add_thickbox();
    echo '<em style="vertical-align: top;"><br />' . __('Sample from frontend:  ','nt-PWOVAT') . '</em><br /><span style="vertical-align:top;">' . __('Click to enlarge:  ','nt-PWOVAT') . '</span><a href="'. plugin_dir_url( __FILE__ ) .'images/Sale-percent-badge.jpg?TB_iframe=true&width=580&height=279" class="thickbox"><img alt="' .  __('Price Without VAT','nt-PWOVAT') . '" title="' .  __('Price Without VAT','nt-PWOVAT') . '" class="nt_img" src=" '. plugin_dir_url( __FILE__ ) .'images/Sale-percent-badge-small.jpg" /></a>';
     }

    public function amount_sale_add_string_callback(){
    ?><input name="nt_price_wo_vat[amount_sale]" type="checkbox" value="1" <?php checked( isset( $this->options['amount_sale'] ) );?> />
    <br />
    <?php
    echo '<br /><em>'
            . __('Sample from frontend:  ','nt-PWOVAT') . '</em><span style="background:#BBEE00;padding:5px;font-size: 16px;line-height: 26px;color: rgb(99, 99, 99);margin:0px 0px 15px;">' . __('You will Save:  ','nt-PWOVAT') . '<strong>753 </strong>'. nt_currency_symbol() .'&nbsp'. __('with 21% VAT','nt-PWOVAT') .'(<span style="color: #E5663F;"> - 10%</span>)</span><br />';
     }

    public function new_products_badge_callback(){
        ?><input name="nt_price_wo_vat[new_products_badge]" type="checkbox" value="1" <?php checked( isset( $this->options['new_products_badge'] ) );?> />
        <br />
    <?php
    add_thickbox();
    echo '<em style="vertical-align: top;"><br />' . __('Sample from frontend:  ','nt-PWOVAT') . '</em><br /><span style="vertical-align:top;">' . __('Click to enlarge:  ','nt-PWOVAT') . '</span><a href="'. plugin_dir_url( __FILE__ ) .'images/New-badge.jpg?TB_iframe=true&width=289&height=279" class="thickbox"><img alt="' .  __('Price Without VAT','nt-PWOVAT') . '" title="' .  __('Price Without VAT','nt-PWOVAT') . '" class="nt_img" src=" '. plugin_dir_url( __FILE__ ) .'images/New-badge-small.jpg" /></a>';
     }

    public function days_new_products_badge_callback(){
        $default = '14';
        printf('<textarea name="nt_price_wo_vat[days_new_products_badge]" type="textarea" cols="6" rows="1">%s</textarea>',
            !isset( $this->options['days_new_products_badge'] ) ? $default : $this->options['days_new_products_badge']);
            echo '<br /><em>' . __('Default: ', 'nt-PWOVAT') . $default . '</em><br />';
        }



    /** Callback CSS settings option */
    //function curr(){return get_woocommerce_currency_symbol();}

    public function css_pwovat_callback(){
         $default = 'font-size: 16px;line-height: 26px;color: rgb(99, 99, 99);';
         echo '<em>'
            . __('Sample from frontend:  ','nt-PWOVAT') . '</em><span style="background:#BBEE00;padding:5px;font-size: 16px;line-height: 36px;color: rgb(99, 99, 99);"><strong>5.602,48 </strong>'. nt_currency_symbol() .'&nbsp'. __('without 21% VAT','nt-PWOVAT') .'</span><br />';
        printf('<textarea name="nt_css_price_wo_vat[css_pwovat]" type="textarea" cols="80" rows="2">%s</textarea>',
            empty( $this->options_css['css_pwovat'] ) ? $default : $this->options_css['css_pwovat']);
            echo '<br /><em>' . __('Default: ', 'nt-PWOVAT') . $default . '</em><br />';
        }


    /** Print Plugin Settings Section*/
    public function print_section_info()
    {_e('Here you can activate or deactivate some plugin functions.','nt-PWOVAT');}

    /** Print the CSS Section */
    public function print_section_css()
    {_e('Here you can setting some CSS rules for woocommerce frontend.','nt-PWOVAT');}




}
?>