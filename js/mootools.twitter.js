Twitter = new Class
({
	Implements: Options,
	
	element: null,
	request: null,
	
	options: {
		url: null,
		retries: 0,
		animate: false,
		autostart: true
	},
	
	initialize: function(elementID, options)
	{
		this.setOptions(options);
		options = this.options;
		var that = this;
		
		this.request = new Request.HTML({
			url: options.url,
			method: 'get',
			link: 'cancel',
			
			onRequest: function() {
				options.retries--;
				that.element.setStyle('opacity', 0);
			},
			
			onFailure: function() {
				if (options.retries >= 0) that.request.send();
			},
			
			onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				var element = new Element('div', {
					id: that.element.id,
					styles: {
						opacity: 0
					},
					'html': responseHTML
				});
				element.replaces(that.element);
				that.element = element;
				
				if (element.getElements('.twitter-error').length > 0) {
					if (options.retries >= 0) {
						that.request.send();
					} else {
						element.dispose();
					}
				} else {
					if (options.animate) {
						element.fade('in');
					} else {
						element.setStyle('opacity', 1);
					}
				}
			}
		});
		
		if (options.autostart) {
			window.addEvent('domready', function() {
				this.element = document.id(elementID);
				if (this.element != null && this.options.autostart) this.request.send();
			}.bind(this));
		}
	}
});
