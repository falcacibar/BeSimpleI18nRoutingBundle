(function($)
{
	$.fn.tokenize = function(options)
	{
		// Set the options.
		options = $.extend({}, $.fn.tokenize.defaults, options);

		// Go through the matched elements and return the jQuery object.
		return this.each(function()
		{
			$(this).css({'height':'0', 'visibility':'hidden'});
			
			var this_input = $('<input type="' + $(this).attr('type') + '" name="' + $(this).attr('name') + '" id="' + $(this).attr('id') + '" autocomplete="off" maxlength="20" />')
				.keydown(function(e)
				{
					var code = (e.keyCode ? e.keyCode : e.which);
					if(code == 13)
					{
						$(this).css({ 'margin-top' : 2, 'margin-bottom': 2 });
						return $.fn.tokenize.maketoken($(this));
					} else
					{
						$.fn.tokenize.extendbox($(this), code);
					}
				});
			var this_li = $('<li />').addClass('input-token').append(this_input);
			var this_ul = $('<ul />')
				.addClass('tokens')
				.addClass($(this).attr('id'))
				.append(this_li)
				.insertAfter($(this))
				.click(function()
				{
					$('input', this).focus();
				});
			$(this).remove();
			
		});
	};
	// Public defaults.
	$.fn.tokenize.defaults = {
		property: 'value'
	};
	// Private functions.
	function func()
	{
		return;
	};
	// Public functions.
	$.fn.tokenize.maketoken = function(box)
	{
		if(box.val().length > 0)
		{
			new_token = $('<li />')
				.append('<p>' + box.val() + '</p>')
				.append('<p class="x">x</p>')
				.addClass('token')
				.insertBefore(box.parent());
			
			$('p.x', new_token).click(function()
			{
				$.fn.tokenize.removeToken($(this).parent());
			});
			
			box.val('');
			box.css({'width' : 45});
		}
		
		return false;
	};

	$.fn.tokenize.extendbox = function(box, code)
	{
		if(code != 8)
		{
			if(box.val().length > 4)
			{
				box.css({'width':box.width()+8});
			}
		} else
		{
			if(box.val().length > 4)
			{
				box.css({'width':box.width()-8});
			}
		}
	};
	
	$.fn.tokenize.removeToken = function(token)
	{
		token.remove();
	};
	
	$.fn.serializeTokens = function()
	{
		data = new Array();
		this.each(function()	
		{
			var tokens = $('li.token', $(this));
			tokens.each(function()
			{
				data.push($('p:first-child', this).html());
			});
		});
				
		return data;
	};
		
	
})(jQuery);