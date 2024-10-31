jQuery(document).ready(function ($) {
  $("#showMess").on("click",function() {
     $("#popup_edit_tracking_order").fadeIn("slow");
  });

  $("#btn_close_edit_tracking_order").on("click",function() {
    $("#popup_edit_tracking_order").fadeOut("slow");
  });
 
 $("#btn_close_edit_tracking_item_order").on("click",function() {
   $("#popup_edit_tracking_item_order").fadeOut("slow");
 });

  $("#button_save_tracking_all").on("click",function() {
    $("#button_save_tracking_all").fadeOut(10);
    $('#popup_save_ajax_animate').html('<div class="loading">Saving, Please Wait</div>');

    item_json = get_json_components();
    $.ajax(
        {
            url : 'admin-ajax.php',
            type : 'POST',
            data:{
                action : 'qp_save_tracking_all',
                order_id : $("#qp_hidden_order_id").val(),
                items : item_json
             },
             success:function(e){
                $('#popup_save_ajax_animate').html('');
                $('#button_save_tracking_all').fadeIn(10);
                 refresh_widget_tracking(e);
               },
             error:function(e){
                console.log('error:'+ $('.respond').append(e));
             }
        }
    );
  });

  $("#button_save_tracking_item").on("click",function() {
    $("#button_save_tracking_item").fadeOut(10);
    $('#popup_save_ajax_animate_item').html('<div class="loading">Saving, Please Wait</div>');

    var item = {
      "item_id" : $("#qp_hidden_item_id").val(),
      "corriereId"   : $("#select_item").val(),
      "tracking_number" : $("#text_item").val()
    };

   $("#text_"+item.item_id).val(item.tracking_number);
   $("#select_"+item.item_id).val(item.corriereId);
   
    $.ajax(
      {
          url : 'admin-ajax.php',
          type : 'POST',
          data:{
              action : 'qp_save_tracking_item',
              order_id : $("#qp_hidden_order_id").val(),
              items : JSON.stringify(item)
           },
           success:function(e){
              $('#popup_save_ajax_animate_item').html('');
              $('#button_save_tracking_item').fadeIn(10);
              refresh_widget_tracking(e);            
           },
           error:function(e){
              console.log('error:'+ $('.respond').append(e));
           }
      }
  );

  });
  
  function htmlDecode(input) {
    var doc = new DOMParser().parseFromString(input, "text/html");
    return doc.documentElement.textContent;
  }

function refresh_widget_tracking(risposta){
  risposta = htmlDecode(risposta);
  items = JSON.parse(risposta);
  for (i = 0; i< items.length;i++){
    widget = 	'<span><a href="'+items[i].url+'">' + items[i].corriere + '</a> </span> <span>'+items[i].tracking_number+' </span>';
    $('#qpwidgettracking_'+items[i].item_id).html(widget);
  }
}

function get_json_components(class_name){
    components = document.getElementsByClassName("qp_text_corriere");
    var list_item  = [];
    for (i=0;i<components.length;i++){
        var component = components[i];
        var newItem = {
            "item_id" : component.name.substring(component.name.indexOf("_")+1),
            "corriereId"   : component.value,
            "tracking_number" : ""
        };

        list_item.push(newItem);
    }

    components = document.getElementsByClassName("qp_text_track_number");
     for (i=0;i<components.length;i++){
        var component = components[i];
        id =  component.name.substring(component.name.indexOf("_")+1);
        mod_item = list_item.find(({ item_id }) => item_id === id);
        mod_item["tracking_number"] = component.value;
    }
       return JSON.stringify(list_item);
}

});
//Fuera del Ready-----------------------------------------------------------
function qp_edit_item_tracking(item_id,product_name){
  $ = jQuery;
  data = $("#text_"+item_id).val();
  $("#text_item").val(data);
  $("#select_item").val($("#select_"+item_id).val());
  $("#qp_hidden_item_id").val(item_id);
  $("#qp_div_item_name").html(product_name);
  $("#popup_edit_tracking_item_order").fadeIn("slow");
}