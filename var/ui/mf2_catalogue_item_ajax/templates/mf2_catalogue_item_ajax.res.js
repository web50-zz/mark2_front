
$(document).ready(function(){

	$('.item').on('click',function(){
		url =  $(this).attr('data-url');
		$.ajax({
			type: "POST",
			url: url,
			data: { mode: "ajax"}
		})
		.done(function( msg ) {
			$.fancybox({
				content: msg.data,
				minWidth: '500px',
				minHeight: '500px',
				beforeShow: function(){
					init_item();
				},
				afterShow: function(){
				}
			});
		});
		return false;
	});
		
});
