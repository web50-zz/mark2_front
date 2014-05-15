$(document).ready(function(){
$('#slider').anythingSlider({
	animationTime   : 0, //9* for the fade effects we should short slide duration as possible. Comment this if fade is not needed.
	buildNavigation: false,
	hashTags: false,
	buildStartStop: false
}).anythingSliderFx({
	// 9* for fade effect 6 line below must be uncommented
		inFx: {
			'img' : { opacity: 1, duration: 1000 },
			'li' : { opacity: 1, duration: 1000 }
		},
		outFx: {
			'img' : { opacity: 0, duration: 0 },
			'li' : { opacity: 0, duration: 0 }
		},
//9* some examples of effects due sliding
//	'.quoteSlide:first > *' : [ 'grow', '24px', '400', 'easeInOutCirc' ], 
//	'.quoteSlide:last'      : [ 'top', '500px', '400', 'easeOutElastic' ], 
//	'.expand'               : [ 'expand', '10%', '400', 'easeOutBounce' ], 
//	'.textSlide h3'         : [ 'top fade', '200px', '500', 'easeOutBounce' ], 
//	'.textSlide img,.fade'  : [ 'fade' ], 
//	'.textSlide li'         : [ 'listLR' ] 
      }); 
});

