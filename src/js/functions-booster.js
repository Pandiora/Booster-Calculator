$(document).ready(function(){
	$('.nano').nanoScroller();
	
	$('.id-submit').click(function(){
		var input = $('.id-input').val();
		if(input.length){
			$.cookie('steam_id', input, { expires: 365, path: '/' });
			document.location.reload();
		}
	});
	
	$('.input-euro, .input-dollar').click(function(){
		var currency = $(this).text();
		if(currency.length){
			
			if(currency == '€'){ var curr = 'EUR'; }
			else if(currency == '$'){ var curr = 'USD'; }
			
			$.cookie('currency', curr, { expires: 365, path: '/' });
			document.location.reload();
		}
	});	
	
});