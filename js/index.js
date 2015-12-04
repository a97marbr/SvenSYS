$(document).ready(function() {
	$('#helpbox').prepend("<a href='#'><img src='images/cancel.png'/></a>");
	$('#helpboxbutton a').click(function(e) {
		showHelpbox();
		e.preventDefault();
		e.stopPropagation();
	});
});

function showHelpbox() {
	$('#helpbox').fadeIn(500);
	$('body, #helpbox a').click(function() {
		$('#helpbox').fadeOut(500);
		$('body').removeAttr('onclick');
	});
	$('#helpbox').click(function(e) {
		e.stopPropagation();
	});
}