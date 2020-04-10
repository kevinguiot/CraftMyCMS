if(fileExist('../images/close.png')) {
	var imgClose = '../images/close.png';
} else {
	var imgClose = 'images/close.png';
}

//On détermine la fonction permettant d'afficher le popup.
function fade(popID) {
	//Informations concernant le popup
	a = 'a[rel=' + popID + ']';
	a = $(a).attr('href');
	
	if (a == "" || a == "#" || a == undefined) {
		var popURL = '#?w=500';
	} else {
		var popURL = a;
	}
	
	var query = popURL.split('?');
	var dim = query[1].split('&');
	var popWidth = dim[0].split('=')[1];
	
	$('#' + popID).fadeIn().css({ 'width': Number( popWidth ) }).prepend('<a href="#" class="close"><img src="' + imgClose + '" class="btn_close" title="Close Window" alt="Close" /></a>');
	
	var popMargTop = ($('#' + popID).height() + 80) / 2;
	var popMargLeft = ($('#' + popID).width() + 80) / 2;
	$('#' + popID).css({ 
		'margin-top' : -popMargTop,
		'margin-left' : -popMargLeft
	});
	$('body').append('<div id="fade"></div>');
	$('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn();
}

//Programme permettant de quitter le popup.
$('button, #fade, .btn_close').live('click', function() {
	$('#fade , .popup_block').fadeOut(function() {
		$('#fade, a.close').remove();  
		});
	return false;
});

//On execute la fonction au click.
$('a.poplight, img.poplight').click(function() {
	var popID = $(this).attr('rel');
	fade(popID);
	
	return false;
});