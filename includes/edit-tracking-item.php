<?php
  $list_corriere = quiposte_query_db::get_list_corriere();;
?>

<table width="100%" class="qp_tracking_list_table" cellpadding="0" cellspacing="0"> 
               <thead>
               <td width="60%"><?php echo esc_html__('Item','quiposte-orders-tracking-for-woocommerce'); ?></td>
               <td width="40%"><?php echo esc_html__('Tracking','quiposte-orders-tracking-for-woocommerce'); ?></td>
               </thead>
   <tbody>
      <tr>
         <td><div id="qp_div_item_name"></div></td>
         <td> 
         <select id="select_item">
            <?php
                   foreach ( $list_corriere as $item_corrire ) {
                      echo '<option value="'.esc_html($item_corrire->id_corriere).'">'.esc_html($item_corrire->name).'</option>';
                   } 
                   ?> 
          </select>
         <input type="text" id="text_item" value="" size="12">
         </td>
      </tr>
   </tbody>
</table>
<br>
<input type="hidden" id="qp_hidden_item_id" value="" readonly>
<div><span id="popup_save_ajax_animate_item"></span><input type="button" value="<?php echo esc_html__('Save','quiposte-orders-tracking-for-woocommerce');?>" id="button_save_tracking_item"></div>
