
var init_item = function(){
	 CloudZoom.quickStart();
	 basket.init_target('basket_btn');
	 $('#p_anno_tip').tipsy({gravity: 'sw',opacity: 1,html: true});
	function msg(type,str,title){
				var opts = {
			         title: title,
				 text: str,
				 type:type,
				 width: '300px',
				 history:false
			};
			$.pnotify(opts);
		};

	 function real()
	 {
		$('.hider').on('click',function(){
			$('a[rel=tipsy]').tipsy('hide');
		});

		 $('#snp .sp').on('click',function(){
			var frm = $('#snp');
			var params =  frm.serialize();
			$.post(
				'/raschet/save/',
				params,
				function(data){
					if(data.success == true){
						msg('success',data.message,'Сообщение');
						$('a[rel=tipsy]').tipsy('hide');
					}else{
						msg('error',data.message,'Ошибка');
					}

				}
			)
			return false;
		 });
	 }


	 $('a[rel=tipsy]').tipsy({trigger: 'manual',gravity: 'sw', opacity:1, html:true,onShow:real});
}
$(document).ready(function(){
	init_item();
});
