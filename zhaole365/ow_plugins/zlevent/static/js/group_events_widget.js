$(function() {
	$( "#tabs" ).tabs();
	
	$( "#zl_more_latestEvents" ).on('click', function(){
		
		$("#latest .ajax-spinner").show();
		
		ajaxGetLatestContent();
	});
	
	$( "#zl_more_historyEvents" ).on('click', function(){
		
		$("#history .ajax-spinner").show();
		
		ajaxGetLatestContent();
	});
	
	$( "#zl_more_myEvents" ).on('click', function(){
		
		$("#my .ajax-spinner").show();
		
		ajaxGetLatestContent();
	});
	
	
});

function ajaxGetLatestContent()
{
	$("#latest .ajax-spinner").show();
	
    var json = generateLatestParameters();
    debug(json);
    
    var requestData = {parameters: json};

    var search_url = $("#latest .baseurl").val();
    
    $.get(search_url, requestData, function(data) {

    	debug(data);
    	json_obj = JSON.parse(data);
    	
		displayLatestEvents(json_obj);
    	
		$("#latest .ajax-spinner").hide();
    });
    
}

function displayLatestEvents(json_obj)
{
	// generate html content
	generateLatestEventsHtmlSnippet(json_obj.events);
	//htmlcontent = generateLatestEventsHtmlSnippet(json_obj.events);
	
	// append to existing content
	//content_append(htmlcontent);

    // update the 'has more' button status
	updateLatestHasMoreButton(json_obj.hasmore);
    
    //set_numfound(json_obj.offset);
    //set_hasmoreitems(json_obj.hasmore);
}

function generateLatestEventsHtmlSnippet(events)
{
	
	var len = events.length;
	for (var i = 0; i < len; i++) {
		var event = events[i];
		html = '';
		html += '<div class="ow_ipc ow_smallmargin clearfix">';
		html += '	<div class="ow_ipc_picture">';
		html += '		<img alt="'+event.title+'"';
		html += '			src="'+event.logo+'">';
		html += '	</div>';
		html += '	<div class="ow_ipc_info">';
		html += '		<div class="ow_ipc_header">';
		html += '			<a href="'+event.url+'">'+event.title+'</a>';
		html += '		</div>';
		html += '		<div class="ow_ipc_content">'+event.description+'</div>';
		html += '		<div class="clearfix">';
		html += '			<div class="ow_ipc_toolbar ow_remark">';
		html += '				<span class="ow_nowrap ow_icon_control ow_ic_user"> <a';
		html += '					href="'+event.userurl+'"> '+event.username+' </a>';
		html += '				</span> <span class="ow_nowrap ow_ipc_date"> '+event.starttime+' </span>';
		html += '			</div>';
		html += '		</div>';
		html += '	</div>';
		html += '</div>';
		
		debug(html);
		$('#latest_content').html($('#latest_content').html() + html);
		
	}
	
	
	 
}

function updateLatestHasMoreButton(hasmore)
{
	if (hasmore==true)
	{
		var offset = parseInt($('#latest .offset').val()) + parseInt($('#latest .limit').val());
		$('#latest .offset').val( offset );
		
		$('#zl_more_latestEvents').show();
	}
	else
		$('#zl_more_latestEvents').hide();
}

function generateLatestParameters()
{
	var json_value =  {};
	
	json_value.groupId = $('#groupId').val();
	json_value.offset = $('#latest .offset').val();
	json_value.limit = $('#latest .limit').val();
	json_value.total = $('#latest .total').val();
	
	var json = JSON.stringify(json_value);

    return json;
}

// history events
function ajaxGetHistoryContent()
{
	$("#history .ajax-spinner").show();
	
    var json = generateHistoryParameters();
    debug(json);
    
    var requestData = {parameters: json};

    var search_url = $("#history .baseurl").val();
    
    $.get(search_url, requestData, function(data) {

    	debug(data);
    	json_obj = JSON.parse(data);
    	
		displayHistoryEvents(json_obj);
    	
		$("#history .ajax-spinner").hide();
    });
    
}

function displayHistoryEvents(json_obj)
{
	// generate html content
	generateHistoryEventsHtmlSnippet(json_obj.events);
	//htmlcontent = generateHistoryEventsHtmlSnippet(json_obj.events);
	
	// append to existing content
	//content_append(htmlcontent);

    // update the 'has more' button status
	updateHistoryHasMoreButton(json_obj.hasmore);
    
    //set_numfound(json_obj.offset);
    //set_hasmoreitems(json_obj.hasmore);
}

function generateHistoryEventsHtmlSnippet(events)
{
	
	var len = events.length;
	for (var i = 0; i < len; i++) {
		var event = events[i];
		html = '';
		html += '<div class="ow_ipc ow_smallmargin clearfix">';
		html += '	<div class="ow_ipc_picture">';
		html += '		<img alt="'+event.title+'"';
		html += '			src="'+event.logo+'">';
		html += '	</div>';
		html += '	<div class="ow_ipc_info">';
		html += '		<div class="ow_ipc_header">';
		html += '			<a href="'+event.url+'">'+event.title+'</a>';
		html += '		</div>';
		html += '		<div class="ow_ipc_content">'+event.description+'</div>';
		html += '		<div class="clearfix">';
		html += '			<div class="ow_ipc_toolbar ow_remark">';
		html += '				<span class="ow_nowrap ow_icon_control ow_ic_user"> <a';
		html += '					href="'+event.userurl+'"> '+event.username+' </a>';
		html += '				</span> <span class="ow_nowrap ow_ipc_date"> '+event.starttime+' </span>';
		html += '			</div>';
		html += '		</div>';
		html += '	</div>';
		html += '</div>';
		
		debug(html);
		$('#history_content').html($('#history_content').html() + html);
		
	}
	
	
	 
}

function updateHistoryHasMoreButton(hasmore)
{
	if (hasmore==true)
	{
		var offset = parseInt($('#history .offset').val()) + parseInt($('#history .limit').val());
		$('#history .offset').val( offset );
		
		$('#zl_more_historyEvents').show();
	}
	else
		$('#zl_more_historyEvents').hide();
}

function generateHistoryParameters()
{
	var json_value =  {};
	
	json_value.groupId = $('#groupId').val();
	json_value.offset = $('#history .offset').val();
	json_value.limit = $('#history .limit').val();
	json_value.total = $('#history .total').val();
	
	var json = JSON.stringify(json_value);

    return json;
}

// my
function ajaxGetMyContent()
{
	$("#my .ajax-spinner").show();
	
    var json = generateMyParameters();
    debug(json);
    
    var requestData = {parameters: json};

    var search_url = $("#my .baseurl").val();
    
    $.get(search_url, requestData, function(data) {

    	debug(data);
    	json_obj = JSON.parse(data);
    	
		displayMyEvents(json_obj);
    	
		$("#my .ajax-spinner").hide();
    });
    
}

function displayMyEvents(json_obj)
{
	// generate html content
	generateMyEventsHtmlSnippet(json_obj.events);
	//htmlcontent = generateMyEventsHtmlSnippet(json_obj.events);
	
	// append to existing content
	//content_append(htmlcontent);

    // update the 'has more' button status
	updateMyHasMoreButton(json_obj.hasmore);
    
    //set_numfound(json_obj.offset);
    //set_hasmoreitems(json_obj.hasmore);
}

function generateMyEventsHtmlSnippet(events)
{
	
	var len = events.length;
	for (var i = 0; i < len; i++) {
		var event = events[i];
		html = '';
		html += '<div class="ow_ipc ow_smallmargin clearfix">';
		html += '	<div class="ow_ipc_picture">';
		html += '		<img alt="'+event.title+'"';
		html += '			src="'+event.logo+'">';
		html += '	</div>';
		html += '	<div class="ow_ipc_info">';
		html += '		<div class="ow_ipc_header">';
		html += '			<a href="'+event.url+'">'+event.title+'</a>';
		html += '		</div>';
		html += '		<div class="ow_ipc_content">'+event.description+'</div>';
		html += '		<div class="clearfix">';
		html += '			<div class="ow_ipc_toolbar ow_remark">';
		html += '				<span class="ow_nowrap ow_icon_control ow_ic_user"> <a';
		html += '					href="'+event.userurl+'"> '+event.username+' </a>';
		html += '				</span> <span class="ow_nowrap ow_ipc_date"> '+event.starttime+' </span>';
		html += '			</div>';
		html += '		</div>';
		html += '	</div>';
		html += '</div>';
		
		debug(html);
		$('#my_content').html($('#my_content').html() + html);
		
	}
	
	
	 
}

function updateMyHasMoreButton(hasmore)
{
	if (hasmore==true)
	{
		var offset = parseInt($('#my .offset').val()) + parseInt($('#my .limit').val());
		$('#my .offset').val( offset );
		
		$('#zl_more_myEvents').show();
	}
	else
		$('#zl_more_myEvents').hide();
}

function generateMyParameters()
{
	var json_value =  {};
	
	json_value.groupId = $('#groupId').val();
	json_value.offset = $('#my .offset').val();
	json_value.limit = $('#my .limit').val();
	json_value.total = $('#my .total').val();
	
	var json = JSON.stringify(json_value);

    return json;
}

//invite
function ajaxGetInviteContent()
{
	$("#invite .ajax-spinner").show();
	
    var json = generateInviteParameters();
    debug(json);
    
    var requestData = {parameters: json};

    var search_url = $("#invite .baseurl").val();
    
    $.get(search_url, requestData, function(data) {

    	debug(data);
    	json_obj = JSON.parse(data);
    	
		displayInviteEvents(json_obj);
    	
		$("#invite .ajax-spinner").hide();
    });
    
}

function displayInviteEvents(json_obj)
{
	// generate html content
	generateInviteEventsHtmlSnippet(json_obj.events);
	//htmlcontent = generateInviteEventsHtmlSnippet(json_obj.events);
	
	// append to existing content
	//content_append(htmlcontent);

    // update the 'has more' button status
	updateInviteHasMoreButton(json_obj.hasmore);
    
    //set_numfound(json_obj.offset);
    //set_hasmoreitems(json_obj.hasmore);
}

function generateInviteEventsHtmlSnippet(events)
{
	
	var len = events.length;
	for (var i = 0; i < len; i++) {
		var event = events[i];
		html = '';
		html += '<div class="ow_ipc ow_smallmargin clearfix">';
		html += '	<div class="ow_ipc_picture">';
		html += '		<img alt="'+event.title+'"';
		html += '			src="'+event.logo+'">';
		html += '	</div>';
		html += '	<div class="ow_ipc_info">';
		html += '		<div class="ow_ipc_header">';
		html += '			<a href="'+event.url+'">'+event.title+'</a>';
		html += '		</div>';
		html += '		<div class="ow_ipc_content">'+event.description+'</div>';
		html += '		<div class="clearfix">';
		html += '			<div class="ow_ipc_toolbar ow_remark">';
		html += '				<span class="ow_nowrap ow_icon_control ow_ic_user"> <a';
		html += '					href="'+event.userurl+'"> '+event.username+' </a>';
		html += '				</span> <span class="ow_nowrap ow_ipc_date"> '+event.starttime+' </span>';
		html += '			</div>';
		html += '		</div>';
		html += '	</div>';
		html += '</div>';
		
		debug(html);
		$('#invite_content').html($('#invite_content').html() + html);
		
	}
	
	
	 
}

function updateInviteHasMoreButton(hasmore)
{
	if (hasmore==true)
	{
		var offset = parseInt($('#invite .offset').val()) + parseInt($('#invite .limit').val());
		$('#invite .offset').val( offset );
		
		$('#zl_more_inviteEvents').show();
	}
	else
		$('#zl_more_inviteEvents').hide();
}

function generateInviteParameters()
{
	var json_value =  {};
	
	json_value.groupId = $('#groupId').val();
	json_value.offset = $('#invite .offset').val();
	json_value.limit = $('#invite .limit').val();
	json_value.total = $('#invite .total').val();
	
	var json = JSON.stringify(json_value);

    return json;
}

function debug(message)
{
	var debug = false;
	if(debug)
		alert(message);
}
