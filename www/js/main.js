jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		success: function (payload) {
			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}
			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}
		    $('div.loading').fadeOut(400);
		}
	}
});

jQuery.ajaxSetup({
	success: jQuery.nette.success,
	dataType: "json"
});

$(function(){
	$('.flash').hide().delay(500).fadeIn(1000);
	$('.flash:not(.error)').delay(3200).fadeOut(800);
    
    $('.flash').click(function() {
      $(this).fadeOut(800);
    });
});

