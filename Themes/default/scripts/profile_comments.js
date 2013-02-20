/**
 * I release this mod and all the code in it to anyone who wants to use it in hopes that it may be found useful.  Attribution is not necessary,
 * nor do you need to provide a link back to my website.  You are free to use this mod for any purpose, including commercial works,
 * redistribution and derivative works.  The only contingency is that the link back to the LED icon set (http://led24.de/iconset/) must remain, as this mod uses two icons
 * from that set. Namely comment_delete.png and comment_edit.png. Both images are found in the root directory of the package. All other
 * images are made by myself and can be used freely. Lastly, this mod comes with no guarantees that it will work well on all servers and
 * configurations, and I will not be held responsible for damages, expenses or problems that may have been caused by the mod's use.
**/
var profile_comments = function()
{
	
	this.load_new_comments = function()
	{
		ui.show_loader();
		$.ajax({
			url: smf_scripturl + '?profile_ajax=load_new_comments',
			type: 'POST',
			data: {profile_id: profileid, last_id: most_recent},
			dataType: 'json',
			success: function(response)
			{
				most_recent = parseInt(response.latest_post);
				ui.hide_loader();
				ui.show_new_posts(response.comments);
			}
		});
	}
	
	this.load_older = function(oldest)
	{
		var oldest = parseInt(oldest);
		
		ui.show_loader();
		
		$.ajax({
			url: smf_scripturl + '?profile_ajax=load_older',
			type: 'POST',
			data: {starting_point: oldest, profile: profileid},
			dataType: 'json',
			success: function(response)
			{
				switch (response.status)
				{
					case 'loaded':
						oldest_shown = parseInt(response.oldest.id);
						ui.hide_loader();
						
						if (response.total < 20)
							ui.hide_more_button();
						
						ui.show_previous_comments(response.comments);
						break;
					
					case 'no_more':
						ui.hide_more_button();
						ui.ajax_result('error', 'show');
						setTimeout(function()
						{
							ui.ajax_result('error', 'hide');
						}, 2000);
						break;
						
					case 'permission_denied':
						ui.ajax_result('error', 'show');
						setTimeout(function()
						{
							ui.ajax_result('error', 'hide');
						}, 2000);
						break;
				}
			}
		});
	}
	
	this.add_comment = function()
	{
		var pid = parseInt($('#profile_id').val());
		var title = $('#input_title').val();
		var body = $('#input_body').val();
		
		if (common.empty(Array(title, body, pid, sessionid)))
			return false;
		
		ui.show_loader();
		
		$.ajax({
			url: smf_scripturl + '?profile_ajax=add_comment',
			type: 'POST',
			data: {c_title: title, c_body: body, profile_id: pid, sc: sessionid},
			success: function(response)
			{
				switch (response)
				{
					case 'empty_value':
						ui.ajax_result('error', 'show');
						setTimeout(function()
						{
							ui.ajax_result('error', 'hide');
						}, 2000);
						break;
						
					case 'permission_denied':
						ui.ajax_result('error', 'show');
						setTimeout(function()
						{
							ui.ajax_result('error', 'hide');
						}, 2000);
						break;
						
					case 'comment_added':
						ui.hide_loader();
						ui.ajax_result('success', 'show');
						pc.load_new_comments();
						total_comments++;
						setTimeout(function()
						{
							ui.hide_reply(true);
							ui.ajax_result('success', 'hide');
						}, 2500);
						break;
				}
			}
		});
		
		return false; // Don't you dare submit that form.
	}
	
	this.update_comment = function(comment_id)
	{
		var id = parseInt(comment_id);
		var title = $('#comment_' + id + ' .modify_title').val();
		var body = $('#comment_' + id + ' .modify_body').val();
		
		if (common.empty(Array(title, body)) || id < 0)
			return false;
			
		$.ajax({
			url: smf_scripturl + '?profile_ajax=update_comment',
			type: 'POST',
			data: {comment_id: id, comment_title: title, comment_body: body, sc: sessionid},
			dataType: 'json',
			success: function(response)
			{
				if (response.status == 'updated')
				{
					ui.ajax_result('success', 'show');
					
					ui.display_updated_comment(response.data);
					
					setTimeout(function()
					{
						ui.ajax_result('success', 'hide');
					}, 5000);
				}
				else
				{
					ui.ajax_result('error', 'show');
					setTimeout(function()
					{
						ui.ajax_result('error', 'hide');
					}, 5000);
				}
			}
		});
		
		return false;
	}
	
	this.show_modify = function(comment_id)
	{
		var comment_id = parseInt(comment_id);
		if (comment_id < 0)
			return false;
		
		ui.show_loader();
		$.ajax({
			url: smf_scripturl + '?profile_ajax=return_comment_data',
			type: 'POST',
			data: {id: comment_id, sc: sessionid},
			dataType: 'json',
			success: function(response)
			{
				ui.hide_loader();
				ui.edit_form(response);
			}
		});
	}
	
	this.delete_comment = function(comment_id)
	{
		if (common.empty(Array(comment_id, sessionid)))
			return false;
		
		var okay = confirm('Delete comment?');
		if (okay)
		{
			ui.show_loader();
			$.ajax({
				url: smf_scripturl + '?profile_ajax=delete_comment',
				type: 'POST',
				data: {cid: comment_id, sc: sessionid},
				success: function(response)
				{
					ui.hide_loader();
					switch (response)
					{
						case 'deleted':
							ui.hide_comment(comment_id);
							total_comments--;
							break;
						case 'permission_denied':
							alert('Permission Denied');
							break;
						case 'not_logged':
							alert ('Please log in to perform this action.');
							break;
					}
				}
			});
		}
	}
}

var common_functions = function()
{
	this.empty = function(input)
	{
		if ($.isArray(input))
		{
			var evaluation;
			$.each(input, function(index, value)
			{
				if (input[index] == null || input[index] == '')
				{
					evaluation = true;
					return;
				}
			});
			if (evaluation == true)
				return true;
			else
				return false;
		}
		else
		{
			if (input == null || input == '')
				return true;
			else
				return false;
		}
	}
}

var comments_ui = function()
{
	this.show_reply = function()
	{
		$('#show_reply_button').hide(300, function()
		{
			$('#form_container').slideDown(700);
		});
		
	}
	this.hide_reply = function(clear)
	{
		$('#form_container').slideUp(300, function()
		{
			if (clear)
				$('#input_title, #input_body').val('');

			$('#show_reply_button').show(300);
		});
	}
	
	this.cancel_modify = function(comment_id)
	{
		var comment_id = parseInt(comment_id);
		if (comment_id < 0)
			return false;
		
		ui.show_loader();
		$.ajax({
			url: smf_scripturl + '?profile_ajax=return_comment_data',
			type: 'POST',
			data: {id: comment_id, bbc: 'true', sc: sessionid},
			dataType: 'json',
			success: function(response)
			{
				ui.hide_loader();
				ui.display_updated_comment(response);
			}
		});
		
		return false;
	}
	
	this.edit_form = function(response)
	{
		// If there's no comment information, we need to kill the function... DIE!!!
		if (common.empty(response.title))
			return false;
		
		// Shorten the variable cause I can.
		var c = response;
		
		// Spread the pieces on the table.
		var open_form = '<form action="' + smf_scripturl + '" method="post" onsubmit="return pc.update_comment(' + c.id + ');">';
		var title = '<input type="text" class="modify_title" id="modify_title_' + c.id + '" value="' + c.title + '" />';
		var body = '<textarea class="modify_body" id="modify_body_' + c.id + '">' + c.body + '</textarea>';
		var submit = '<input class="modify_submit" type="submit" value="' + pc_submit + '" />';
		var cancel = '<input class="modify_cancel" type="submit" value="' + pc_cancel + '" onclick="return ui.cancel_modify(' + c.id + ');" />';
		var clear = '<br class="clear" />';
		var close_form = '</form>';
		
		// Put all the pieces together like a puzzle.
		var elements = new Array(open_form, title, body, submit, cancel, clear, close_form);
		var html = elements.join("\n");
		
		// Finally make the comment editable.
		$('#comment_' + c.id + ' .comment_body').html(html);
	}
	
	this.hide_comment = function(id)
	{
		id = parseInt(id);
		if (id > 0)
		{
			$('#comment_' + id).slideUp(400, function()
			{
				$(this).detach();
			});
			return true;
		}
		else
			return false;
	}
	
	this.ajax_result = function(type, show_hide)
	{
		type = type.toLowerCase();
		show_hide = show_hide.toLowerCase();
		
		var type_possibles = new Array('success', 'error');
		var action_possbiles = new Array('show', 'hide');
		
		// Check that nothing is empty, icon type is valid, and the action is really an action.
		if (common.empty(Array(type, show_hide)) && $.inArray(type, type_possibles) && $.inArray(show_hide, action_possibles))
			return false;
		
		switch (show_hide)
		{
			case 'show':
				$('#comment_success, #comment_error').css({display: 'none'});
				$('#comment_' + type).fadeIn(500);
				break
			case 'hide':
				$('#comment_' + type).fadeOut(500);
				break;
		}
		
		return true;
	}
		
	
	this.show_new_posts = function(info)
	{
		if (common.empty(info))
			return false;
			
		$.each(info, function(index, data)
		{
			var can_modify = data.can_modify ? '<a class="comment_modify" href="javascript:pc.show_modify(' + data.id + ');"><img src="' + smf_images_url + '/comment_edit.png" alt="" /></a>' : '';
			var can_delete = data.can_delete ? '<a class="comment_delete" href="javascript:pc.delete_comment(' + data.id + ');"><img src="' + smf_images_url + '/comment_delete.png" alt="" /></a>' : '';
			
			// Container.
			$('#comments').prepend('<div id="comment_' + data.id + '" class="comment_container nodisplay_comment"></div>');
			
			// User info and comment data divs.
			$('#comment_' + data.id).append('<div class="comment_user_info"></div><div class="comment_body windowbg2"></div>');
			
			// Fill user info.
			$('#comment_' + data.id + ' .comment_user_info').append('<a href="' + smf_scripturl + '?action=profile;u=' + data.poster_id + '"><h2>' + data.poster_name + '</h2></a><img class="comment_avatar" src="' + data.poster_avatar + '" alt="" />');
			
			// Post title and actions. Long innit?
			$('#comment_' + data.id + ' .comment_body').append('<div class="comment_action_bar">' + can_modify + can_delete + '<h3>' + data.title + '</h3></div>');
			
			// The post body (finally)
			$('#comment_' + data.id + ' .comment_body').append(data.body);
		});
		
		// Show the new comments. :D
		$('.nodisplay_comment').slideDown(500);
	}
	
	this.display_updated_comment = function(data)
	{
		if (common.empty(data))
			return false;
			
		$('#comment_' + data.id + ' .comment_body').html('');
		$('#comment_' + data.id + ' .comment_body').append('<div class="comment_action_bar"></div>');
		$('#comment_' + data.id + ' .comment_action_bar').append('<a class="comment_modify" href="javascript:pc.show_modify(' + data.id + ');"><img src="' + smf_default_theme_url + '/images/comment_edit.png" alt="" /></a><a class="comment_delete" href="javascript:pc.delete_comment(' + data.id + ');"><img src="' + smf_default_theme_url + '/images/comment_delete.png" alt="" /></a><h3>' + data.title + '</h3>');
		$('#comment_' + data.id + ' .comment_body').append(data.body);
	}
	
	this.show_previous_comments = function(comments)
	{
		if (common.empty(comments))
			return false;
			
		$.each(comments, function(i, c)
		{
			var can_modify = c.can_modify ? '<a class="comment_modify" href="javascript:pc.show_modify(' + c.id + ');"><img src="' + smf_images_url + '/comment_edit.png" alt="" /></a>' : '';
			var can_delete = c.can_delete ? '<a class="comment_delete" href="javascript:pc.delete_comment(' + c.id + ');"><img src="' + smf_images_url + '/comment_delete.png" alt="" /></a>' : '';
			
			$('#comments').append('<div id="comment_' + c.id + '" class="comment_container nodisplay_comment"></div>');
			$('#comment_' + c.id).append('<div class="comment_user_info"><h2>' + c.poster_name + '</h2><img class="comment_avatar" src="' + c.poster_avatar + '" alt="" /></div>');
			$('#comment_' + c.id).append('<div class="comment_body windowbg2"></div>');
			$('#comment_' + c.id + ' .comment_body').append('<div class="comment_action_bar">' + can_modify + can_delete + '<h3>' + c.title +'</h3></div>');
			$('#comment_' + c.id + ' .comment_body').append(c.body);
		});
		
		$('.nodisplay_comment').slideDown(400);
	}
	
	this.show_loader = function()
	{
		$('#comment_loader').css({'display': 'block'});
	}
	this.hide_loader = function()
	{
		$('#comment_loader').css({'display': 'none'});
	}
	
	this.hide_more_button = function()
	{
		$('#show_more_button').hide(600);
	}
}

var common = new common_functions;
var ui = new comments_ui;
var pc = new profile_comments;
