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

/*
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
*/
$(document).ready(function(){
//	$("#ex2").slider({tooltip:'hide',tooltip_position:'bottom'});
	var pSlider = new Slider('#ex2',{tooltip:'hide',tooltip_position:'bottom'});
	$("#ex2").on("slide", function(slideEvt) {
		$('#sprice').val(slideEvt.value[0]);
		$('#eprice').val(slideEvt.value[1]);
		$('[name=pstart]').val(slideEvt.value[0]);
		$('[name=pend]').val(slideEvt.value[1]);
	});
	$(".tnp").on('click',function(){
		$('#brand_flt').submit();
	});
	$('#sprice').focusout(function(){
		var s = parseInt($('#sprice').val());
		var p = parseInt($('#eprice').val());
		pSlider.setValue([s,p]);
		$('[name=pstart]').val(s);
		$('[name=pend]').val(p);
	});
	$('#eprice').focusout(function(){
		var s = parseInt($('#sprice').val());
		var p = parseInt($('#eprice').val());
		pSlider.setValue([s,p]);
		$('[name=pstart]').val(s);
		$('[name=pend]').val(p);
	});

})
