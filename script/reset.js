$("img[alt=closeMsg]").live( "click", function() {
	$(this).parent().fadeOut(1000);
});

//On détermine si un fichier existe ou pas.
function fileExist(url) {
	filename = url; 
	
	var response = jQuery.ajax({
		url: filename,
		type: 'HEAD',
		async: false
	}).status;	
	
	return (response != "200") ? false : true;
}