

jQuery(document).ready(function($) {
    
	$('.dropzone').on('submit', function(e) {
	    alert("upload files");
		e.preventDefault();
		var $form = $(this);
	
		$.post($form.attr('action'), $form.serialize(), function(data) {
			alert('This is data returned from the server ' + data);
		}, 'json');
				
	});
		
 
		
});
 
 
 









