(function($) { 
	$(document).ready(function() {
		$('.relationchips input').focus(function() {
			$('label[for='+$(this).attr('id')+'].hint').hide();
		});
		$('.relationchips input').blur(function() {
			if (!$(this).val()) {
				$('label[for='+$(this).attr('id')+'].hint').show();
			}	
		});
		$('.relationchips label.reset').click(function() {
			var input = $(this).next();
			input.val('');
			doSearch(false, input.next());
		});
		$('.relationchips input').keyup(handleEvent);
		$('.relationchips li:not(.warning)').live('click',function() {
			$(this).find('span').removeClass('highlight');
			var itemClass = $(this).attr('id');
			var id = parseInt($(this).attr('id').match(/\d+/));
			var config = $(this).parent().metadata();
			var select = $(this).parent().prevAll('select');
			if ($(this).hasClass('selected')) {
				select.find('.'+itemClass).remove();
			} else {
				str = '<option class="'+itemClass+'" selected="selected" value="'+id+'">'+id+'</option>';
				if (config.multiSelect) {
					select.append(str);
				} else {
					select.html(str);
					$(this).siblings().removeClass('selected');
				}
			}
			$(this).toggleClass('selected');
		});
	});
	var jqXHR;
	function handleEvent(event) {
		if (event.type == 'keyup') {
	        switch (event.keyCode) {
		        case 37: case 38: case 39: case 40: { return true; } // arrow keys
		        case 224: case 17: case 16: case 18: { return true; } // CTRL, ALT, CTRL ALT, SHIFT
		        case 8: { break; } // delete
		        case 9: case 13: { return false; } // return and tab
		        case 27: { return true; } // ESC
		        case 32: { return true; } // space
		    }
		}
        var search = $.trim($(this).val());
        var ul = $(this).next();
        var config = $(this).metadata();
        doSearch(search, ul, config);
	}
	function doSearch(search, ul, config) {
		if (jqXHR) jqXHR.abort();
		if (!search) {
			ul.find('li:not(.selected):not(.warning)').remove();
			return;
		}
		var data = { s: search } 
		selected = ul.prevAll('select').val();
		if (selected && $.isArray(selected)) data.n = selected.join(',');
		else if (selected) data.n = selected;
		jqXHR = $.ajax({
			url : config.url,
			dataType : 'json',
			data : data,
			success : function (result) {
				ul.find('li:not(.selected):not(.warning)').remove();
				for(var i = 0; i < result.length; i++)
					ul.append('<li id=\"'+config.idPrefix+result[i].ID+'\">'+result[i].Text+'</li>');
			}
		});
	}
})(jQuery);