$(".preparation").draggable({ cursor: "move", 
                            revert: "invalid", 
                            snap: "#menu ul li > div", 
                            snapMode: "inner",
                            helper: 'clone',
                            
                         });

$("#menu ul li > div" ).droppable({
  drop: function( event, ui ) {
      $(this).html(ui.draggable.text());
      $(this).css("border-style", "solid");
      var pre_id = ui.draggable.data("id");
      var lunch_id = $(this).data("id");
      action = "set";
      $.get({l}link setLunchPreparation!{r}, { "lunch_id": lunch_id, "preparation_id": pre_id });
  }
});

function test() {
          $("#menu ul li > div" ).droppable({
            drop: function( event, ui ) {
          $(this).html(ui.draggable.text());
          $(this).css("border-style", "solid");
          var pre_id = ui.draggable.data("id");
          var lunch_id = $(this).data("id");
      action = "set";
          $.get({l}link setLunchPreparation!{r}, { "lunch_id": lunch_id, "preparation_id": pre_id });
  }
});
}

$('div.remove_lp').click(function (event) {    
  var li = $(this).parent();
  $('div.loading').fadeIn(300);
  event.preventDefault();
  action = "remove";
  $.get($(this).attr("href"), function (payload) {
      $.nette.success(payload);
    });
});