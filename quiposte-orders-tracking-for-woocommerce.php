<?php
/**
 * Plugin Name: QUIPOSTE Orders Tracking for WooCommerce
 * Plugin URI: https://www.quiposte.com/
 * Description: Allows tracking of the shipment of woocommerce orders and integration with quiposte.com
 * Version: 1.2.0
 * Author: Quiposte
 * Author URI: https://www.quiposte.com/plugins/woocommerce/quiposte-orders-tracking-for-woocommerce
 * Text Domain: quiposte-orders-tracking-for-woocommerce
 * WordPress Tested: 6.1.1
 * WooCommerce Tested : 7.3.0
 * Requires PHP: 7.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 **/
 
register_activation_hook( __FILE__,  'qposte_install');
register_uninstall_hook( __FILE__,  'qposte_uninstall');

require_once(getAppPath().'includes'.DIRECTORY_SEPARATOR.'class-used.php');

function qposte_install() {
    require_once(getAppPath().'includes'.DIRECTORY_SEPARATOR.'class-used.php');
		quiposte_query_db::create_data_table_qp();
	    quiposte_configured::add_corrire_default_table();
	echo "installed";
 }

function getAppPath(){
	return plugin_dir_path(__FILE__). DIRECTORY_SEPARATOR;
}
 
function getAppUrl(){
	return plugins_url('/',__FILE__);
}

add_action( 'init', 'load_test_domain');

function load_test_domain(){
	load_plugin_textdomain('quiposte-orders-tracking-for-woocommerce', false, dirname(plugin_basename(__FILE__)).DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR);}

$allcalback = new quiposte_callback();
$ajax_connection = new quiposte_ajax_connection();
quiposte_configured::set_js();
quiposte_configured::set_style();

function qposte_uninstall() {
        require_once(getAppPath().'includes'.DIRECTORY_SEPARATOR.'class-used.php');
	echo "uninstalled";
 }