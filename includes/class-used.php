<?php
///------------------------------------DB--------------------------------------------
		class quiposte_query_db{

			public static function get_corrire($id_corriere){
				global $wpdb;
				$query = $wpdb->prepare("SELECT * FROM qp_tracking_info WHERE id_corriere=%d",array($id_corriere));
			    $result = $wpdb->get_results($query);   
				return $result[0];
			}

			public static function table_qp_exist(){
				global $wpdb;
				$query = $query = $wpdb->prepare("SHOW TABLES LIKE 'qp_tracking_info'");
				$result = $wpdb->get_results($query); 
				if (count($result)> 0)
				    return 1;
				return 0;
			} 

			public static function get_woo_item_metadata($id_item){
				global $wpdb;
				$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE
										 order_item_id=%d AND meta_key='_quiposte_order_trackin_data'",
										 array($id_item));
				$result = $wpdb->get_results($query); 
			    return $result[0];
			}

			public static function set_woo_item_metadata($id_metadata,$value){
				global $wpdb;
				$query = $wpdb->prepare("UPDATE {$wpdb->prefix}woocommerce_order_itemmeta SET
										 meta_value=%s WHERE meta_id=%d",array($value,$id_metadata));
				$wpdb->query($query);
			}

			public static function insert_woo_item_metadata($item_id, $value){
				global $wpdb;
				$query =  $wpdb->prepare("INSERT INTO {$wpdb->prefix}woocommerce_order_itemmeta 
										  (order_item_id,meta_key,meta_value) values(%d,'_quiposte_order_trackin_data',%s)"
										  ,array($item_id,$value));
				$wpdb->query($query);
			}

			public static function get_woo_items_meta($order_id, $count){		
				global $wpdb;	
				$query =  $wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta as m LEFT JOIN  
				          {$wpdb->prefix}woocommerce_order_items as i  ON m.order_item_id = i.order_item_id 
						  WHERE i.order_id=%d AND m.meta_key='_quiposte_order_trackin_data' LIMIT 0,%d",array($order_id,$count));
				return $wpdb->get_results($query); 
			}

			public static function get_list_corriere(){
				global $wpdb;
				$query =  $wpdb->prepare("select * from qp_tracking_info");
				return $wpdb->get_results($query);
			}

			public static function get_list_order_item($order_id){
				global $wpdb;
				$query = $wpdb->prepare("select * from {$wpdb->prefix}woocommerce_order_items 
										WHERE order_id=%d AND order_item_type='line_item'",array($order_id));
				return $wpdb->get_results($query);
			}

			public static function create_data_table_qp(){
				global $wpdb;
				$sql =  $wpdb->prepare("DROP TABLE IF EXISTS `qp_tracking_info`");
				$wpdb->query($sql);
				$sql = $wpdb->prepare("
				CREATE TABLE `qp_tracking_info` (
				  `id_corriere` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(45) NOT NULL,
				  `url` text NOT NULL,
				  `hidden` int(10) unsigned NOT NULL,
				  `external_id` int(10) unsigned NOT NULL,
				  `created_dt` datetime NOT NULL,
				  `modified_dt` datetime NOT NULL,
				  PRIMARY KEY (`id_corriere`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;");
				$wpdb->query($sql);
			}

			public static function insert_corriere_qp_table($corriere){
				global $wpdb;
				$date   = date('Y-m-d H:i:s');
				$query = $wpdb->prepare("INSERT INTO qp_tracking_info(name,url,external_id, created_dt) 
										values(%s,%s,%d,%s)",array($corriere->name,$corriere->url,$corriere->external_id,$date));
				$wpdb->query($query);
     		}

			 function uninstall_db(){
				global $wpdb;
				$query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key='_quiposte_order_trackin_data'");
				$wpdb->query($query);
				$query = $wpdb->prepare("DROP TABLE qp_tracking_info");
				$wpdb->query($query);
			 }
		}
//----------------------------------------------------------------------------------------

//--------------------------Utilities-----------------------------------------------------
		class quiposte_utilities{
			public static function get_json_to_item_metadata($corriere,  $item){
				$item_meta['corriere'] =  $corriere->name;
				$item_meta['url']  =  str_replace("@",$item->tracking_number,$corriere->url);
				$item_meta['tracking_number'] =  $item->tracking_number;
				$item_meta['item_id'] =  $item->item_id;
				return json_encode($item_meta);	
			}


			public static function set_item_metadata($item){
				$corriere = quiposte_query_db::get_corrire($item->corriereId);
				$json =  quiposte_utilities::get_json_to_item_metadata($corriere, $item);
				//Buscar en el metada si existe el campo usado por el plugin
				$metadata = quiposte_query_db::get_woo_item_metadata($item->item_id);
				if ($metadata == null) //No existe se agrega el campo al metadata		
					quiposte_query_db::insert_woo_item_metadata($item->item_id,$json);
				else  //Se modifica
					quiposte_query_db::set_woo_item_metadata($metadata->meta_id,$json);
			    return 	$json;
			}

			public static function get_html_widget_list_info($order_id, $count){
				$list_item = quiposte_query_db::get_woo_items_meta($order_id,$count);
				$i = 0;
				$html = '';
				 if (count($list_item ) > 0){ 
					foreach ( $list_item as $item ) {
						$track_info = json_decode($item->meta_value);
						$html .='<div>
						          <span><a href="'.@esc_html($track_info->url).'">'.@esc_html($track_info->corriere).'</a></span> 
							      <span>'.@esc_html($track_info->tracking_number).' </span> 
							     </div>';
							 $i++;
						if ($i > 2){
							$html .= '<div>...</div>';
							break;
						}
					}
				}
				return $html;
			}



		}
//------------------------Ajax Connection CallBack----------------------------------------
		class quiposte_ajax_connection{
			public function __construct(){
				//ajax salvar todos los item
				add_action('wp_ajax_qp_save_tracking_all',  array($this,'qp_save_tracking_all'));
				add_action('wp_ajax_nopriv_qp_save_tracking_all', array($this,'qp_save_tracking_all'));

				//salvar un item
				add_action('wp_ajax_qp_save_tracking_item',  array($this,'qp_save_tracking_item'));
				add_action('wp_ajax_nopriv_qp_save_tracking_item', array($this,'qp_save_tracking_item'));		
			}

			function qp_save_tracking_all(){
				$json = sanitize_text_field($_POST['items']);
				$json  = str_replace('\"','"',$json);
				$list_item = json_decode($json);
				$risposta = "[";
				$i = 0;
				 if (count($list_item ) > 0){ 
					foreach ( $list_item as $item ) {
						$risposta .= ($i!= 0 ? ',' : '').quiposte_utilities::set_item_metadata($item) ;	
						$i++;
					}
				}
				$risposta .= "]";
				echo esc_html($risposta);
				die();
			}

			function qp_save_tracking_item(){				
				$json = sanitize_text_field($_POST['items']);
				$json  = str_replace('\"','"',$json);
				$item = json_decode($json);				
				$risposta = quiposte_utilities::set_item_metadata($item);
				echo esc_html('['.$risposta.']');
				die();
			 }
		}
//-------------------------Configured-----------------------------------------------------
		class quiposte_configured{
			static public function set_js(){
				$path =  getAppUrl().'/js/';
				wp_register_script('edit-tracking-js', $path. 'edit-order.js', array('jquery'), '1.0.2', true );
				wp_enqueue_script('edit-tracking-js');
			}

			static public function set_style(){
				$path =  getAppUrl().'/css/';
				wp_enqueue_style( 'custom-style', $path. '/custom.css', array(), "1.0.1");
			}

			public static function add_corrire_default_table(){
				$corriere = '[
							   {"name" : "BRT", "url":"https://vas.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&ChiSono=@","external_id":1},
							   {"name" : "TNT", "url":"https://www.tnt.it/tracking/Tracking.do?serach=@","external_id":2},
							   {"name" : "GLS", "url":"https://gls-group.com/IT/it/servizi-online/ricerca-spedizioni.html?match=@&type=NAT","external_id":3},
							   {"name" : "PaccoJ", "url":"https://www.poste.it/cerca/index.html#/risultati-spedizioni/@","external_id":4},
							   {"name" : "SDA", "url":"https://www.sda.it/wps/portal/Servizi_online/ricerca_spedizioni?locale=it&tracing.letteraVettura=@","external_id":5},
							   {"name" : "DHL", "url":"https://www.dhl.com/it-it/home/tracking/tracking-express.html?submit=1&tracking-id=@","external_id":6}
							 ]';  
				 $list_item = json_decode($corriere);

				 if (count($list_item ) > 0)
					 foreach ( $list_item as $item ) 
						 quiposte_query_db::insert_corriere_qp_table($item);
		 }

		}

//-----------------------all Callback----------------------------------------------------
       class quiposte_callback{
		public function __construct(){
			//Nueva Columna sobre la lista de Ordenes
			add_filter( 'manage_shop_order_posts_columns', array($this, 'add_column_list_order'), 99 );
			add_action( 'manage_shop_order_posts_custom_column', array($this,'set_order_list_column')  );

			//Cuando se entra a una orden
			add_filter( 'woocommerce_hidden_order_itemmeta', array($this,'woo_hidden_order_itemmeta'));
			add_action( 'woocommerce_after_order_itemmeta', array($this, 'qp_woo_after_order_itemmeta'), 10, 3 );

			//Agrego un box
			add_action( 'add_meta_boxes', array($this,'order_details_add_meta_box'));
			//Agrega al metadata del item un nuevo campo para la info del envio
			add_action('woocommerce_checkout_create_order_line_item', array($this,'order_checkout'),0,4 );
		}

		//Filter, añade una nueva columna a las que se crean por defaul woocomerce
	    public function add_column_list_order( $columns ) {
			$columns['qp_woocommerce-shipment-tracking'] = __( 'Shipment Tracking', 'quiposte-orders-tracking-for-woocommerce' );
			return $columns;
		}
	    //Muestra el valor en de la columna correspondiente por cada fila
	 	public function set_order_list_column( $column){
			if ( 'qp_woocommerce-shipment-tracking' === $column ) {
				global $post;
				echo quiposte_utilities::get_html_widget_list_info($post->ID,2);
			}
		}
		//Muestra al final de cada item		
		public function qp_woo_after_order_itemmeta($item_id, $item, $product){
		$item = quiposte_query_db::get_woo_item_metadata($item_id);

		$track_info = json_decode($item->meta_value);

		echo '<div class="qp_widget_tracking_meta"><span class="widget_meta_item_tracking" id="qpwidgettracking_'.esc_html($item_id).'">
					<span><a href="'.esc_html(@$track_info->url).'">'.esc_html(@$track_info->corriere).'</a> </span> 
					<span>'.esc_html(@$track_info->tracking_number).' </span> 
					</span>
					<input type="button" onclick="qp_edit_item_tracking('.esc_html($item_id).',\''.esc_html($product->name).'\');" value="Edit" class="qp_widget_tracking_meta_button">
			 </div>';
		}
		//Oculta el contenido del metada 
		public function woo_hidden_order_itemmeta($itemmeta) {
			$itemmeta[] = '_vi_wot_order_item_tracking_data';
			$itemmeta[] = '_quiposte_order_trackin_data';
			return $itemmeta;
		}
		//Para crear un metabox
		public function order_details_add_meta_box() {
			global $pagenow;
			if ( $pagenow === 'post.php') { //En la pgina de editar orden
				add_meta_box('qp_meta_box_tracking_box',esc_html__('Tracking Number', 'quiposte-orders-tracking-for-woocommerce'),array($this,'meta_box_tracking'),'','side','core');
			}
		}
		
		//Crea el box para el tracking
		public function meta_box_tracking() {
		global $post;
    	$order_id = $post->ID;
		$order    = wc_get_order( $order_id );
		echo   '<div>'.esc_html__('You can add and edit shipments here ...', 'quiposte-orders-tracking-for-woocommerce').'</div>	
		        <p>
		           <button type="button" id = "showMess"title="'.esc_html__('Add Shipping', 'quiposte-orders-tracking-for-woocommerce').'">'.esc_html__('Shipping', 'quiposte-orders-tracking-for-woocommerce').'</button>
		        </p>';
		
		$popup_title   = esc_html__('Order Tracking','quiposte-orders-tracking-for-woocommerce');
		$popup_id = "edit_tracking_order";
		$path = getAppPath();
	    $popup_body    =  $path .'includes'.DIRECTORY_SEPARATOR.'edit-tracking-all.php';
	    include($path.'includes'.DIRECTORY_SEPARATOR.'popup-new.php');
		
		
	    $popup_title   = esc_html__('Item Tracking','quiposte-orders-tracking-for-woocommerce');
	    $popup_id = "edit_tracking_item_order";
	    $path = getAppPath();
	    $popup_body    =  $path .'includes'.DIRECTORY_SEPARATOR.'edit-tracking-item.php';
		include($path.'includes'.DIRECTORY_SEPARATOR.'popup-new.php');
		}	

		function order_checkout( $item, $cart_item_key, $values, $order ) {
				$item->update_meta_data( '_quiposte_order_trackin_data', "");
		}
	}
?>