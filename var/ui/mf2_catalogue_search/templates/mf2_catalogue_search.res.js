$(document).ready(function(){
	$('.search_button').on('click',function(){
		var  val = $('#search_form input[name=search]').val();
		if(val != '')
		{
			$('#search_form').submit();
		}
	});
})
