var price_filter = function(){
	this.init = function(flt){
		flt.registerFilter(this);
	}
	this.getParams = function(){
		var pstart = $('.filter-price').find('[name=start_price]').val();
		var pend = $('.filter-price').find('[name=end_price]').val();
		return '&pstart='+pstart+'&pend='+pend;
	}
}
$(document).ready(function(){
	$("#ex2").slider({tooltip:'hide',tooltip_position:'bottom'});
	$("#ex2").on("slide", function(slideEvt) {
		$('#sprice').val(slideEvt.value[0]);
		flt.pstart = slideEvt.value[0];
		$('#eprice').val(slideEvt.value[1]);
		flt.pend = slideEvt.value[1];
	});
	var priceflt = new price_filter();
	priceflt.init(flt);
})
