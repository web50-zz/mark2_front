var sort_filter = function(){
	this.init = function(flt){
		flt.registerFilter(this);
	}
	this.getParams = function(){
		var v = $('#filter-sort select').val();
		if(v > 0)
		{
			return 'sort='+v+'&limit=1';
		}
		return '';
	}
}

$(document).ready(function(){
	var sortfilter = new sort_filter;
	sortfilter.init(flt);
	$('#filter-sort select').on('change',function(){
		flt.get();
	})
})
