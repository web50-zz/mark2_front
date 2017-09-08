var brand_filter = function(){
	this.init = function(flt){
		flt.registerFilter(this);
	}
	this.getParams = function(){
		var v = $('#filter-brand select').val();
		return 'mans=["'+v+'"]';
	}
}

$(document).ready(function(){
	var ffilter = new brand_filter;
	ffilter.init(flt);
	$('#filter-brand select').on('change',function(){
		flt.get();
	})
	 var match = location.search.match(new RegExp("[?&]mans=([^&]+)(&|$)"));
	 if(match)
	 {
		var r = decodeURIComponent(match[1].replace(/\+/g, " "));
		var mans = $.parseJSON(r);
		$.each(mans, function(index,value){
			$('#filter-brand select').val(value);
		});
	 }

})
