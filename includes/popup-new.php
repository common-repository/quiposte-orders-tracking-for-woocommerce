
<div id="popup_<?php echo esc_html($popup_id); ?>" class="popup_tracking">
		<header><div><?php echo esc_html($popup_title); ?>
		<span id="btn_close_<?php echo esc_html($popup_id);  ?>" style="float:right;"><a>X</a></span>
		</div>
	</header>
	<div class="popup_tracking_outer" >
	   <div class="popup_tracking_innera" >
		    <div style="height:350px">
             <?php if ($popup_body) include_once($popup_body); ?>
        </div>
	   </div>
	</div>
	</div>