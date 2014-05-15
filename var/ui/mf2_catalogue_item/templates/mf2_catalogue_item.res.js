var init_item = function(){
	 CloudZoom.quickStart();
	 basket.init_target('basket_btn');
	 $('#p_anno_tip').tipsy({gravity: 'sw',opacity: 1,html: true});
}
$(document).ready(function(){
	init_item();
});
