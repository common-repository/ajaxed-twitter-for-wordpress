if (typeof jQuery == 'function') {

jQuery.twitter = {
	getTweets: function(el, options) {
		if (options.retries < 0) return;
		options.retries--;
		
		var existing = el.children('ul.twitter');
		if (existing.length != 0) existing.remove();
		
		jQuery.ajax({
			url: options.url,
			dataType: 'html',
			data: {options: options.options},
				
			
			error: function() {
				jQuery.twitter.getTweets(el, options);
			},
			
			success: function(data) {
				data = jQuery(data);
				
				if (data.find('.twitter-error').length != 0) {
					jQuery.twitter.getTweets(el, options);
				} else {
					if (options.animate) data.css('display', 'none');
					el.append(data);
					if (options.animate) data.fadeIn(800);
				}
			}
		});
	}
};

jQuery.fn.twitter = function(options) {
	options = jQuery.extend({
		url: null,
		retries: 0,
		animate: false,
		autostart: true,
		options: ''
	}, options);
	
	jQuery.twitter.retries = options.retries;
	jQuery.twitter.getTweets(this, options);
	return this;
};

} // end check if jQuery exists