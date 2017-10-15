$(function() {
    $('#trials img').click(function() {
	var div = $(this).parent();
	$.post(
	    'record-trial.php',
	    { user: $('#trials').data('user'),
	      experiment: $('#trials').data('experiment'),
	      trial: div.index(),
	      left: div.children('img.left').data('item'),
	      right: div.children('img.right').data('item'),
	      choice: $(this).data('item')
	    });
	div.hide();
	div.next().show();
    });
 
    $('button.break').click(function() {
	var div = $(this).parent();
	div.hide();
	div.next().show();
    });

    function start() {
	$('#tutorial').hide();
	$('#trials div:first').show();
    };
    
    function tutorial() {
	$('#consent').hide();
        $("#tutorial button[action]").each(function() {
            $(this).click(
		function() {
                    var div = $(this).parents('.section');
                    div.hide();
                    if ($(this).hasClass("prev"))
			div.prev().show();
		    else if ($(this).hasClass("home"))
			$('#tutorial div.section:first').show();
		    else 
			div.next().show();
		});
        });
	if ($('#start-btn').length == 0)
	    $('#tutorial').append('<div class="section"><button type="button" class="btn btn-primary" id="start-btn">Start the experiment</button></div>');
	$('#start-btn').click(start);
	$('#tutorial').show();
        $('#tutorial div.section').hide();
        $('#tutorial div.section:first').show();
    };
    
    window.setTimeout(
	function() {
            $("img.lazy").each(function() {
		$(this).attr("src", $(this).data("src"));
            });
	}, 0);
    $('#agree').click(tutorial);
    $('#disagree').click(function() { $('#goodbye').show(); });
});
