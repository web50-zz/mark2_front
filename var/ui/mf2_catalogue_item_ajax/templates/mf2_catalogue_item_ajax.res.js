
$(document).ready(function(){

	var open_item = function(url,el){
		$.ajax({
			type: "POST",
			url: url,
			data: { mode: "ajax"}
		})
		.done(function( msg ) {
			$.fancybox.close();
			$.fancybox({
				content: msg.data,
				minWidth: '800px',
				minHeight: '500px',
				openSpeed: 'slow',
				openEffect: 'fade',
				beforeShow: function(){
					init_item();
					basket.init_target('basket_btn');
					 $('#p_anno_tip').tipsy({gravity: 'sw',opacity: 1,html: true});
				},
				afterShow: function(){
					$('.fancybox-inner').after('<div class="item-prev"></div>');
					$('.fancybox-inner').after('<div class="item-next"></div>');
					$('.fancybox-outer .item-prev').on('click',function(){
							var p_el = el.prev();
							var url =  p_el.attr('data-url');
						$('a[rel=tipsy]').tipsy('hide');
							if(url)
							{
								open_item(url,p_el);
							}
					});
					$('.fancybox-outer .item-next').on('click',function(){
							var n_el = el.next();
							var url =  n_el.attr('data-url');
						$('a[rel=tipsy]').tipsy('hide');
							if(url)
							{
								open_item(url,n_el);
							}
						
					});
				}
			});
		});

	}
	$('.item').on('click',function(){
		url =  $(this).attr('data-url');
		open_item(url,$(this));
		return false;
	});
		
});
