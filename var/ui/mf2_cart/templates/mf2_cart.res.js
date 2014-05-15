var cart = function(){
	this.jsonned = {},
	this.defaults = {
		add_handler_url: '/cart/add/',
		remove_handler_url:'/cart/remove/'
	},
	this.recalc = function(){
		var total = 0;
		this.jsonned = {};
		this.jsonned.records = [];
		var self = this;
		$('.cart_record').each(function(){
			var rec = {};	
			var c = $(this).find('.count').val();
			var p = $(this).find('[name=price]').val();
			var item_price = parseInt(c) * parseInt(p) ;
			rec.count = c;
			rec.price = p;
			rec.item_price = item_price;
			rec.id =  $(this).attr('data-id');
			total =  total + item_price;
			$(this).find('.item_price').html(item_price);
			self.jsonned.records.push(rec);
		});
		$('.cart_total_val').html(total);
		this.jsonned.total = total;
		$('[name=cart_json]').val(JSON.stringify(this.jsonned));
	}
	this.remove =  function(el){
		var id = el.attr('data-id');
		var self = this;
		$.ajax({
			type: "POST",
			url: this.defaults.remove_handler_url,
			data: { id: id}
		})
		.done(function( msg ) {
			if(msg.success == true){
				$('.cart_record[data-id='+id+']').remove()
				if($('.cart_record').length == 0){
					$('.wizard').remove();	
					$('.cart_table').replaceWith('<center>Корзина пуста</center>');
				};
			}
		});
	}
}

$(document).ready(function(){
	var options = {
		firstStep:'step1',
		error_messages:[],
		defaults:{
			onSuccessSubmit:function(data){},
			message:function(type,str,title){
					var opts = {
					 title: title,
					 text: str,
					 type:type,
					 width: '600px',
					 history:false
					};
				$.pnotify(opts);
			}
		},
		handlers:{
			step1:{
				construct:function(o,obj){
					$.pnotify.defaults.styling = "jqueryui";
				},
				init:function(o){
				},
				check:function(o,obj){
					return true;
				}
			}
		}
	}
	a = new fwizard(options);
	cart = new cart();
	$('.count').each(function(){
		$(this).on('keyup',function(){
			cart.recalc();
		});
	});
	$('.remove_item').on('click',function(){
		cart.remove($(this));
	});
});
