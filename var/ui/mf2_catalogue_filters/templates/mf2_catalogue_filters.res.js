var filtrec = function(){
	this.filters = [];
	this.url = '?';
	this.init = function(){
		var self = this;
	}
	this.registerFilter = function(obj)
	{
		this.filters.push(obj);
	}

	this.build = function(){
		var self = this;
		self.url = '?';
		$.each(this.filters,function(index,value){
			var p =  value.getParams();
			self.url = self.url + p +'&';

		});
	}
	this.get = function(){
		this.build();
		window.location = './'+this.url;
	}
}

$(document).ready(function(){
	flt = new filtrec;
	$('#fltDo').on('click',function(){
		flt.get();
	});
})
