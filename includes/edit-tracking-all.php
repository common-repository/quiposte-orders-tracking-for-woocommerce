
<?php
	global $post;
	$order_id = $post->ID;
    
   $list_corriere = quiposte_query_db::get_list_corriere();
   $list_item = quiposte_query_db::get_list_order_item($order_id);
     if (count($list_item) > 0){
      echo '
            <table width="100%" class="qp_tracking_list_table" cellpadding="0" cellspacing="0"> 
               <thead>
                 <td width="60%">'.esc_html__('Item','quiposte-orders-tracking-for-woocommerce').'</td>
                 <td width="40%">'.esc_html__('Tracking','quiposte-orders-tracking-for-woocommerce').'</td>
               </thead>
              <tbody>';
      foreach ( $list_item as $item ) {
           $meta = quiposte_query_db::get_woo_item_metadata($item->order_item_id);
           if (!is_object($meta))
           quiposte_query_db::insert_woo_item_metadata($item->order_item_id,"");
               
           $track_info = json_decode($meta->meta_value);
                        
           echo '<tr>
                   <td>'. esc_html($item->order_item_name).'</td>
                   <td>
                       <select name="select_'.esc_html($item->order_item_id).'" class="qp_text_corriere" id="select_'.esc_html($item->order_item_id).'">';
                   foreach ( $list_corriere as $item_corrire ) {
                      echo '<option value="'.esc_html($item_corrire->id_corriere).'" '.($track_info->corriere == $item_corrire->name ? ' selected' : '').'>'.esc_html($item_corrire->name).'</option>';
                   }  
                 echo '</select>
                 <input type="text" name="text_'.esc_html($item->order_item_id).'"  id="text_'.esc_html($item->order_item_id).'" value = "'.esc_html($track_info->tracking_number).'" size="12" class="qp_text_track_number">';
           echo '</td>
                 </tr>';
      }
          echo '</tbody></table>';
    }


?>
<br>
<input type="hidden" id="qp_hidden_order_id" value="<?php echo  esc_html($order_id); ?>" readonly>
<div><span id="popup_save_ajax_animate"></span><input type="button" value="<?php echo esc_html__('Save','quiposte-orders-tracking-for-woocommerce');?>" id="button_save_tracking_all"></div>