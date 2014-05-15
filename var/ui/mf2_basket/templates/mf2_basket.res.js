var basket = function(){
	this.exp = 'basket_btn';
	this.defaults ={
		add_btn_class: 'add_to_cart',
		add_handler_url: '/cart/add/',
		remove_handler_url:'/cart/remove/',
		basket_url: '/cart/basket/'
		
	},
	this.init = function(){
		var self =  this;
		$('.add_to_cart').on('click',function(e){
			e.preventDefault();
			self.add_to_cart($(this));
			return false;
		});
		$('.remove_from_cart').on('click',function(e){
			e.preventDefault();
			self.remove_from_cart($(this));
			return false;
		});

	},
	this.init_target = function(css_name){
		this.exp = css_name;
		var self =  this;
		var exp= '.'+ css_name;
		var exp2= '.'+ css_name+'_remove';
		$(exp).on('click',function(e){
			e.preventDefault();
			self.add_to_cart($(this));
			return false;
		});
		$(exp2).on('click',function(e){
			e.preventDefault();
			self.remove_from_cart($(this));
			return false;
		});
	},
	this.add_to_cart = function(el){
		var id = el.attr('data-id');
		var self = this;
		$.ajax({
			type: "POST",
			url: this.defaults.add_handler_url,
			data: { id: id}
		})
		.done(function( msg ) {
			if(msg.success == true){
				self.refreshBasket();
				$('.add_to_cart[data-id='+id+']').hide();
				$('.remove_from_cart[data-id='+id+']').show();
				$('.'+self.exp+'[data-id='+id+']').hide();
				$('.'+self.exp+'_remove[data-id='+id+']').show();
			}
		});

	},
	this.remove_from_cart = function(el){
		var id = el.attr('data-id');
		var self = this;
		$.ajax({
			type: "POST",
			url: this.defaults.remove_handler_url,
			data: { id: id}
		})
		.done(function( msg ) {
			if(msg.success == true){
				self.refreshBasket();
				$('.add_to_cart[data-id='+id+']').show();
				$('.remove_from_cart[data-id='+id+']').hide();
				$('.'+self.exp+'[data-id='+id+']').show();
				$('.'+self.exp+'_remove[data-id='+id+']').hide();
			}
		});

	},

	this.refreshBasket = function(){
		$.ajax({
			type: "POST",
			url: this.defaults.basket_url,
			data: { op: 'refresh'}
		})
		.done(function( msg ) {
			$('.payload').html(msg.payload);
		})
	}

}
basket = new basket();
$(document).ready(function(){
	basket.init();
});
