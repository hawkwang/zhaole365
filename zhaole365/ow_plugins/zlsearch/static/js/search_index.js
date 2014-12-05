$( function() {
  
//	var container1 = document.querySelector('#weixiao_another_main_content');
//	var msnry1 = new Masonry( container1, {
//	  // options
//	  //columnWidth: 220,
//	  itemSelector: '.item'
//	});

	var $container = $('#weixiao_main_content');
	// initialize
	$container.masonry({
		//columnWidth : 200,
		itemSelector : '.item'
	});


});

$(document).ready(function() {
	
	
	$("#zl_ow_site_panel").after($("#findNavBar"));
	//$("#findNavBar").hide();
	$("#content").hide();

    $(".ow_header").hide();
    $(".ow_page_container").hide();
	
    //init();
	
	// 显示缺省内容
	showDefaultContent();
	
//	$("#spinner").bind("ajaxSend", function() {
//        $(this).show();
//    }).bind("ajaxStop", function() {
//        $(this).hide();
//    }).bind("ajaxError", function() {
//        $(this).hide();
//    });	
	
	// search bar section
//	$("#id-simple-location").click(function(event) {
//		$("#simple-location").toggle();
//		$("#simple-radius").hide();
//		$("#simple-timerange").hide();
//	});
	
//	$("#id-simple-radius").click(function(event) {
//		$("#simple-radius").toggle();
//		$("#simple-location").hide();
//		//$("#simple-radius").hide();
//		$("#simple-timerange").hide();
//	});
//	
//	$("#id-simple-timerange").click(function(event) {
//		$("#simple-timerange").toggle();
//		$("#simple-location").hide();
//		$("#simple-radius").hide();
//		//$("#simple-timerange").hide();
//	});
	
	
	// 地点 FIXME － 扩展到全国城市
	$("#simple-location li a").click(function(event) {

		$("#id-simple-location span.value").text($(this).attr('data-copy'));
		$("#id-simple-location").attr('data-value',$(this).attr('data-value'));
		$("#id-simple-location").attr('data-areacode',$(this).attr('data-areacode'));
		
//		$("#simple-location").toggle();
		event.preventDefault();
	});
	
	// 获得距离范围
	$("#simple-radius li a").click(function(event) {
		
		$("#id-simple-radius span.value").text($(this).attr('data-copy'));
		$("#id-simple-radius").attr('data-value',$(this).attr('data-value'));
	    
//		$("#simple-radius").toggle();
		event.preventDefault();
	});
	
	// 获得时间范围
	$("#simple-timerange li a").click(function(event) {

		$("#id-simple-timerange span.value").text($(this).attr('data-copy'));
		$("#id-simple-timerange").attr('data-value',$(this).attr('data-value'));

//		$("#simple-timerange").toggle();
		event.preventDefault();
	});
	
	// 获得类型
	$("#simple-category li a").click(function(event) {
		
		$("#id-simple-category span.value").text($(this).attr('data-copy'));
		$("#id-simple-category").attr('data-value',$(this).attr('data-value'));
	    
		event.preventDefault();
	});	
	
	// 获得所需显示类型
    $('#lele_type button').click(function() {
    	
        $('#lele_type button').addClass('active').not(this).removeClass('active');

        //var $button = $( this );
        //var type = $button.attr('leletype');
        
		resetParametersAndInitialContent();
		ajaxGetContent();
        
    });
    
	// 获得优先级
	$("#simple_sort_algorithm li a").click(function(event) {

		$("#id_simple_sort_algorithm span.value").text($(this).attr('data-copy'));
		$("#id_simple_sort_algorithm").attr('data-value',$(this).attr('data-value'));

		event.preventDefault();
	});
	
	// go button
	$('#searchme').click(function(event) {
		
		event.preventDefault();
		
		resetParametersAndInitialContent();
		
		ajaxGetContent();
		
	});
	// end search bar section
	
    $('#hasmore').on('click', function(event) {
        event.preventDefault();

        ajaxGetContent();
    });	
	
	
});

$(document).on('mouseenter','.thumb', function (event) {
	$( this ).find('.imagecaption').slideDown();
});

$(document).on('mouseleave','.thumb', function (event) {
	$( this ).find('.imagecaption').slideUp();
});

function init()
{
    //获取要定位元素距离浏览器顶部的距离
	var navH = $("#findNavBar").offset().top;
	//滚动条事件
	$(window).scroll(function() {
		//获取滚动条的滑动距离
		var scroH = $(this).scrollTop();
		//滚动条的滑动距离大于等于定位元素距离浏览器顶部的距离，就固定，反之就不固定
		if (scroH >= navH) {
			$("#findNavBar").css({
				"position" : "fixed",
				"top" : 0
			});
		} else if (scroH < navH) {
			$("#findNavBar").css({
				"position" : "relative"
			});
		}
	});
}


function showDefaultContent()
{
	// step1 - 得到action并改变对应的type
	var action = $("#action").val();
	switch (action) {
		case 'noaction':
			break;
		case 'event':
			set_type('event');
			break;
		case 'group':
			set_type('group');
			break;
	}
	
	// steo2 - 根据当前检索设置进行搜索
	ajaxGetContent();
	
}

function ajaxGetContent()
{
	$("#spinner").show();
	
    var json = generateParameters();
    debug(json);
    
    var requestData = {parameters: json};
    var lele_type = get_type(); 

    var search_url = $("#baseurl").val();
    
    $.get(search_url, requestData, function(data) {

    	debug(data);
    	
    	json_obj = JSON.parse(data);
    	
    	switch (lele_type) {
		case 'event':
			displayPrivateEvent(json_obj);
			break;
		case 'group':
			displayGroup(json_obj);
			break;
    	}
    	
        $("#spinner").hide();
    });
    
}

function content_clear()
{

	$('#weixiao_main_content').html("");
	$('#weixiao_main_content').masonry('destroy');
	$('#weixiao_main_content').masonry({
		//columnWidth : 200,
		itemSelector : '.item'
	});
	$('#hasmore').hide();
}

function content_append(content)
{
	$('#weixiao_main_content').html( $('#weixiao_main_content').html() + content );
}

function resetParametersAndInitialContent()
{
	set_numfound('0');
	set_hasmoreitems('0');

	// parameters
	set_offset('0');
	//set_limit('20');
	
	//clear content
	content_clear();
}

function generateParameters()
{
	// construct json value as parameters
	// 1. type - event, private group, public group, calendar
	// 2. offset
	// 3. limit
	// 4. category
	// 5. key
	// 6. area
	// 7. position (longitude, latitude)
	// 8. radius
	// 9. timerange, indicate the time range [0,timerange]
	var json_value =  {};
	
	json_value.type = get_type();
	json_value.offset = get_offset();
	json_value.limit = get_limit();
	json_value.category = get_category();
	json_value.key = get_key();
	json_value.areacode = get_area();
	json_value.longitude = get_longitude();
	json_value.latitude = get_latitude();
	json_value.radius = get_radius();
	json_value.timerange = get_timerange();
	json_value.sort = get_algorithm();
	
	var json = JSON.stringify(json_value);

    return json;
}

function displayGroup(json_obj)
{
	//content_append(json_obj.html);
	// generate html content
	htmlcontent = generateGroupsHtmlSnippet(json_obj.items);
	
	// append to existing content
	//content_append(htmlcontent);	
    
    
    // 
    if (json_obj.hasmore == false)
    {
	    createhtml = generateCreateNewGroupHtmlSnippet();
//	    content_append(createhtml);
    }
    
    // update the 'has more' button status
    updateHasMoreButton(json_obj.hasmore);

    set_numfound(json_obj.numFound);
    set_hasmoreitems(json_obj.hasmore);
}

function displayPrivateEvent(json_obj)
{
	// generate html content
	htmlcontent = generateEventsHtmlSnippet(json_obj.items);
	
	// append to existing content
	//content_append(htmlcontent);
	
    
    // 
    if (json_obj.hasmore == false)
    {
	    createhtml = generateCreateNewEventHtmlSnippet();
	    //content_append(createhtml);
    }

    // update the 'has more' button status
    updateHasMoreButton(json_obj.hasmore);
    
    set_numfound(json_obj.numFound);
    set_hasmoreitems(json_obj.hasmore);
}

//function generateGroupsHtmlSnippet(groups)
//{
//	var html = '';
//	var formatclass = '';
//	
//	var len = groups.length;
//	for (var i = 0; i < len; i++) {
//		var group = groups[i];
//
//		html += '<div class="item  col-xs-6 col-sm-3 col-md-3 ' + formatclass + '">';
//		html += '<div class="thumbnail shadow_bottom">';
//		html += '<div class="imageholder">';
//		html += '<a href="' + group.url + '" target="_blank">';
//		html += '<div class="element" style="background-size: cover; background-image: url(\'' + group.logo + '\');">';
//		html += '<div class="nametag-photo-name">';
//		html += '<h4 class="loading">'+ group.title + '</h4>';
//		html += '<div class="nametag-photo-role"></div>';
//		html += '</div>';
//		html += '</div>';
//		html += '</a>';
//		html += '</div>';
//		html += '<div class="caption">';
//		html += '<h4 class="group inner list-group-item-heading">'+ group.title + '</h4>';
//		html += '<p class="group inner list-group-item-text">' + group.description + '</p>';
//		html += '<div class="doc-content group-info">';
//		html += '<h4 class="ellipsize">';
//		html += '<span class="count">乐友数：' + group.members + '</span>';
//		//html += '人';
//		html += '</h4>';
//		html += '<p class="muted">';
//		html += '<a class="unlink muted" href="' + group.latesteventurl + '"> 下次: ' + group.latesteventtime + ' </a>';
//		html += '</p>';
//		html += '</div>';
//		html += '</div>';
//		html += '<div class="clear"></div>';
//		html += '</div>';
//		html += '</div>';
//		
//	}
//	
//	return html;
//}

function generateGroupsHtmlSnippet(groups)
{
	var finalhtml = '';
	var formatclass = '';

	
	var len = groups.length;
	for (var i = 0; i < len; i++) {
		var group = groups[i];
        html='';
		html += '<div class="item col-lg-3 col-md-4 col-sm-6 col-xs-12">';
		html += '<section class="panel shadow_bottom" style="border-left-width: 0px; border-right-width: 0px; border-top-width: 0px;">';
		html += '<div class="thumb">';
		html += '<a class="thumblink" href="' + group.url + '" target="_blank">';
		html += '<div class="imagecaption ellipsize"> <h3>'+ group.title +'</h3> <p>' + group.description + '</p> </div>';
		html += '<img class="img-responsive" width="300" height="500" style="display: block; clear: both; margin: auto; width: 100%; border-top-left-radius: 4px; border-top-right-radius: 4px;" src="' + group.logo + '" />';
		html += '</a>';
		html += '</div>';
		html += '<div class="panel-body" ';
		//if((event.pcount!=0) || (event.mcount!=0))
			html += 'style="padding-bottom: 0px;"';
		html += '>';
		html += '<div class="caption">';
		html += '<h4 class="group inner list-group-item-heading">'+ group.title + '</h4>';
//		html += '<p class="group inner list-group-item-text">' + group.description + '</p>';
		html += '<div class="doc-content group-info">';
//		html += '<h4 class="ellipsize" style="padding-top: 1px;">';
		//html += '<span class="count"></span>' + group.members + '</span>';
		//html += '乐友';
		html += '</h4>';
		html += '<p class="muted">';
		html += '<a class="unlink muted" href="' + group.latesteventurl + '"> 下次乐子: ' + group.latesteventtime + ' </a>';
		html += '</p>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<ul class="list-group">';
		
//		html += '<li class="list-group-item" style="padding-top: 0px; padding-bottom: 0px;">';
//		html += '<div class="doc-content group-info">';
//		html += '<h4 class="ellipsize" style="padding-top: 1px;">';
//		html += '<span class="count"><span class="glyphicon glyphicon-time"></span>' + event.time + '</span>';
//		html += '</h4>';
//		html += '<p class="muted">';
//		html += '<a class="unlink muted ellipsize" target="_blank" href="http://api.map.baidu.com/marker?location=' + event.latitude + ',' + event.longitude + '&content=' + event.location + '&output=html"><span class=" glyphicon glyphicon-map-marker"></span>' + event.location + ' </a>';
//		html += '</p>';
//		html += '</div>';
//		html += '</li>';
		//if((event.pcount!=0) || (event.mcount!=0))
		{
			html += '<li class="list-group-item ">';
			html += '<div class="text-right">';
			if(group.members!=0)
			{
				html += '&nbsp;';
				html += '<a href="'+group.joinurl+'" class="unlink muted"><span class="glyphicon glyphicon-heart" style="margin-right: 0px; color:red;"></span>' + group.members + '</a>';
			}
			else
				html += '<a href="/event/follow/?id=" class="unlink muted"><span class="glyphicon glyphicon-heart" style="margin-right: 0px; color:#a7a7a7;"></span></a>';
			html += '</div>';
			html += '</li>';
		}
		html += '</ul>';
		html += '</section>';
		html += '</div>';
		
		// append to existing content
/*		var element = $(html);
		$('#weixiao_main_content').append( element ).masonry( 'appended', element );
		$('#weixiao_main_content').masonry();
*/		
		var $items = $(html);
		var msnry = $('#weixiao_main_content').data('masonry');
		var itemSelector = msnry.options.itemSelector;
		$items.hide();
		$('#weixiao_main_content').append( $items );
		$items.imagesLoaded().progress(function(imgLoad, image) {
			// get item
			// image is imagesLoaded class, not <img>, <img> is image.img
			var $item = $(image.img).parents(itemSelector);
			// un-hide item
			$item.show();
			// masonry does its thing
			msnry.appended($item);
			msnry.layout();
		});
		
		finalhtml += html;
	}
	
	//$('#weixiao_main_content').masonry();

//	var $items = $(finalhtml);
//	var msnry = $('#weixiao_main_content').data('masonry');
//	var itemSelector = msnry.options.itemSelector;
//	$items.hide();
//	$('#weixiao_main_content').append( $items );
//	$items.imagesLoaded().progress(function(imgLoad, image) {
//		// get item
//		// image is imagesLoaded class, not <img>, <img> is image.img
//		var $item = $(image.img).parents(itemSelector);
//		// un-hide item
//		$item.show();
//		// masonry does its thing
//		msnry.appended($item);
//		msnry.layout();
//	});
	
	return finalhtml;
}

function generateEventsHtmlSnippet(events)
{
	var finalhtml = '';
	var formatclass = '';

	
	var len = events.length;
	for (var i = 0; i < len; i++) {
		var event = events[i];
        html='';
		html += '<div class="item col-lg-3 col-md-4 col-sm-6 col-xs-12">';
		html += '<section class="panel shadow_bottom" style="border-left-width: 0px; border-right-width: 0px; border-top-width: 0px;">';
		if(event.category.length)
			html += '<span class="entry-thumbnail-category"> <a title="查看所有' + event.category + '">'+ event.category + '</a></span>';
		html += '<div class="thumb">';
		html += '<a href="' + event.url + '" target="_blank">';
		html += '<div class="imagecaption ellipsize"> <h3>'+ event.title +'</h3> <p>' + event.description + '</p> </div>';
		html += '<img class="img-responsive" width="300" height="500" style="display: block; clear: both; margin: auto; width: 100%; border-top-left-radius: 4px; border-top-right-radius: 4px;" src="' + event.logo + '" />';
		html += '</a>';
		html += '</div>';
		html += '<div class="panel-body" ';
		//if((event.pcount!=0) || (event.mcount!=0))
			html += 'style="padding-bottom: 0px;"';
		html += '>';
		html += '<div class="caption">';
		html += '<h4 class="group inner list-group-item-heading">'+ event.title + '</h4>';
		html += '<div class="doc-content group-info">';
		html += '<h4 class="ellipsize" style="padding-top: 1px;">';
		html += '<span class="count"><span class="glyphicon glyphicon-time"></span>' + event.time + '</span>';
		//html += '乐友';
		html += '</h4>';
		html += '<p class="muted">';
		html += '<a class="unlink muted ellipsize" target="_blank" href="http://api.map.baidu.com/marker?location=' + event.latitude + ',' + event.longitude + '&content=' + event.location + '&output=html"><span class=" glyphicon glyphicon-map-marker"></span>' + event.location + ' </a>';
		html += '</p>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '<ul class="list-group">';
		
//		html += '<li class="list-group-item" style="padding-top: 0px; padding-bottom: 0px;">';
//		html += '<div class="doc-content group-info">';
//		html += '<h4 class="ellipsize" style="padding-top: 1px;">';
//		html += '<span class="count"><span class="glyphicon glyphicon-time"></span>' + event.time + '</span>';
//		html += '</h4>';
//		html += '<p class="muted">';
//		html += '<a class="unlink muted ellipsize" target="_blank" href="http://api.map.baidu.com/marker?location=' + event.latitude + ',' + event.longitude + '&content=' + event.location + '&output=html"><span class=" glyphicon glyphicon-map-marker"></span>' + event.location + ' </a>';
//		html += '</p>';
//		html += '</div>';
//		html += '</li>';
		//if((event.pcount!=0) || (event.mcount!=0))
		{
			html += '<li class="list-group-item ">';
			html += '<div class="text-right">';
	//		html += '<a href="/group/?id=' + event.gid + '" class="">';
	//		html += '隶属乐群<img src="' + event.glogo + '" style="width:40px; height:40px;">';
	//		html += '</a>';
			if(event.pcount!=0)
			{
				html += '<a href="/event/pin/?id=' + event.eid + '" class="unlink muted"><span class="glyphicon glyphicon-pushpin" style="margin-right: 0px; color:#a7a7a7;"></span>4</a>';
			}
			
			if(event.mcount!=0)
			{
				html += '&nbsp;';
				html += '<a href="/event/follow/?id=' + event.eid + '" class="unlink muted"><span class="glyphicon glyphicon-heart" style="margin-right: 0px; color:red;"></span>' + event.mcount + '</a>';
			}
			else
				html += '<a href="/event/follow/?id=' + event.eid + '" class="unlink muted"><span class="glyphicon glyphicon-heart" style="margin-right: 0px; color:#a7a7a7;"></span></a>';
			html += '</div>';
			html += '</li>';
		}
		html += '</ul>';
		html += '</section>';
		html += '</div>';
		
		// append to existing content
/*		var element = $(html);
		$('#weixiao_main_content').append( element ).masonry( 'appended', element );
		$('#weixiao_main_content').masonry();
*/		
		var $items = $(html);
		var msnry = $('#weixiao_main_content').data('masonry');
		var itemSelector = msnry.options.itemSelector;
		$items.hide();
		$('#weixiao_main_content').append( $items );
		$items.imagesLoaded().progress(function(imgLoad, image) {
			// get item
			// image is imagesLoaded class, not <img>, <img> is image.img
			var $item = $(image.img).parents(itemSelector);
			// un-hide item
			$item.show();
			// masonry does its thing
			msnry.appended($item);
			msnry.layout();
		});
		
		finalhtml += html;
	}
	
	//$('#weixiao_main_content').masonry();

//	var $items = $(finalhtml);
//	var msnry = $('#weixiao_main_content').data('masonry');
//	var itemSelector = msnry.options.itemSelector;
//	$items.hide();
//	$('#weixiao_main_content').append( $items );
//	$items.imagesLoaded().progress(function(imgLoad, image) {
//		// get item
//		// image is imagesLoaded class, not <img>, <img> is image.img
//		var $item = $(image.img).parents(itemSelector);
//		// un-hide item
//		$item.show();
//		// masonry does its thing
//		msnry.appended($item);
//		msnry.layout();
//	});
	
	return finalhtml;
}

function generateCreateNewEventHtmlSnippet()
{
	var html = '';
	var formatclass = '';
	
	var static_url = $("#staticurl").val();
	
		html += '<div class="item col-lg-3 col-md-4 col-sm-6 col-xs-12">';
		html += '<section class="panel shadow_bottom" style="border-left-width: 0px; border-right-width: 0px; border-top-width: 0px;">';
		html += '<div class="thumb">';
		html += '<a href="'+ $('#createeventurl').val() +'" target="_blank">';
		html += '<img class="img-responsive" width="300" height="600" style="display: block; clear: both; margin: auto; width: 100%" src="'+ static_url +'img/create.jpeg" />';
		html += '</a>';
		html += '</div>';
		html += '<div class="panel-body">';
		html += '<div class="caption">';
		html += '<h4 class="group inner list-group-item-heading">虚位以待</h4>';
		html += '<div class="doc-content group-info">';
		html += '<h4 class="ellipsize">';
		html += '<span class="count">独乐乐，与人乐乐，孰乐？ 有乐子？ 请分享！</span>';
		html += '</h4>';
		html += '<p class="muted text-right">';
		html += '<a class="unlink muted ellipsize" href="'+$("#createeventurl").val()+'" target="_blank"> <span class="glyphicon glyphicon-plus"></span>创建新乐子？！ </a>';
		html += '</p>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</section>';
		html += '</div>';
		
		
		// append to existing content
/*		var element = $(html);
		$('#weixiao_main_content').append( element ).masonry( 'appended', element );
		$('#weixiao_main_content').masonry();
*/		
		var $items = $(html);
		var msnry = $('#weixiao_main_content').data('masonry');
		var itemSelector = msnry.options.itemSelector;
		$items.hide();
		$('#weixiao_main_content').append( $items );
		$items.imagesLoaded().progress(function(imgLoad, image) {
			// get item
			// image is imagesLoaded class, not <img>, <img> is image.img
			var $item = $(image.img).parents(itemSelector);
			// un-hide item
			$item.show();
			// masonry does its thing
			msnry.appended($item);
			msnry.layout();
		});		

		
	return html;
}

function generateCreateNewGroupHtmlSnippet()
{
	var html = '';
	var formatclass = '';
	
	var static_url = $("#staticurl").val();
	
		html += '<div class="item col-lg-3 col-md-4 col-sm-6 col-xs-12">';
		html += '<section class="panel shadow_bottom" style="border-left-width: 0px; border-right-width: 0px; border-top-width: 0px;">';
		html += '<div class="thumb">';
		html += '<a href="'+ $('#createeventurl').val() +'" target="_blank">';
		html += '<img class="img-responsive" width="300" height="600" style="display: block; clear: both; margin: auto; width: 100%" src="'+ static_url +'img/create.jpeg" />';
		html += '</a>';
		html += '</div>';
		html += '<div class="panel-body">';
		html += '<div class="caption">';
		html += '<h4 class="group inner list-group-item-heading">虚位以待</h4>';
		html += '<div class="doc-content group-info">';
		html += '<h4 class="ellipsize">';
		html += '<span class="count">请创建乐群！</span>';
		html += '</h4>';
		html += '<p class="muted text-right">';
		html += '<a class="unlink muted ellipsize" href="'+$("#creategroupurl").val()+'" target="_blank"> <span class="glyphicon glyphicon-plus"></span>创建新乐群？！ </a>';
		html += '</p>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</section>';
		html += '</div>';
		
		
		// append to existing content
/*		var element = $(html);
		$('#weixiao_main_content').append( element ).masonry( 'appended', element );
		$('#weixiao_main_content').masonry();
*/		
		var $items = $(html);
		var msnry = $('#weixiao_main_content').data('masonry');
		var itemSelector = msnry.options.itemSelector;
		$items.hide();
		$('#weixiao_main_content').append( $items );
		$items.imagesLoaded().progress(function(imgLoad, image) {
			// get item
			// image is imagesLoaded class, not <img>, <img> is image.img
			var $item = $(image.img).parents(itemSelector);
			// un-hide item
			$item.show();
			// masonry does its thing
			msnry.appended($item);
			msnry.layout();
		});		

		
	return html;
}


//methods to falicitate - getting search parameters
function get_area()
{
	return $("#id-simple-location").attr('data-areacode');
}

function get_latitude()
{
	var location = $("#id-simple-location").attr('data-value');
    var split_locations = location.split(',');
    var latitude = split_locations[0];
    
    return latitude; 
}

function get_longitude()
{
	var location = $("#id-simple-location").attr('data-value');
    var split_locations = location.split(',');
    var longitude = split_locations[1];
    
    return longitude; 
}

function get_radius()
{
	return $("#id-simple-radius").attr('data-value');
}

function get_timerange()
{
	return $("#id-simple-timerange").attr('data-value');
}

function get_category()
{
	return $("#id-simple-category").attr('data-value');
}

function get_type()
{
	return $('#lele_type button.active').attr('leletype');
}

function set_type(type)
{
	$("div#lele_type button[leletype="+ type +"]").addClass('active');
	$("div#lele_type button:not([leletype="+ type +"])").removeClass('active');
}


function get_offset()
{
	return $('#offset').val();
}

function set_offset(offset)
{
	$('#offset').val(offset);
}

function get_numfound()
{
	return $('#numFound').val();
}

function set_numfound(numfound)
{
	$('#numFound').val(numfound);
}

function get_limit()
{
	return $('#limit').val();
}

function set_limit(limit)
{
	$('#limit').val(limit);
}

function get_hasmoreitems()
{
	return $('#hasMoreItems').val();
}

function set_hasmoreitems(hasmoreitems)
{
	$('#hasMoreItems').val(hasmoreitems);
}

function get_key()
{
	return $('#mainKeywords').val();
}

function get_algorithm()
{
	return $("#id_simple_sort_algorithm").attr('data-value');
}

// end

function updateHasMoreButton(hasmore)
{
	if (hasmore==true)
	{
		var offset = parseInt($('#offset').val()) + parseInt($('#limit').val());
		$('#offset').val( offset );
		
		$('#hasmore').show();
	}
	else
		$('#hasmore').hide();
}

function debug(message)
{
	var debug = false;
	if(debug)
		alert(message);
}

//////////////////////////////////////////////

(function( $ ) {'use strict';
	var _elements = {},
	_methods = {
			showSearchBar: function()
	        {
	            _elements.searchbar.insertAfter(_elements.header);
	            _elements.content.insertAfter(_elements.searchbar);
				$("#content").show();
				//$("#findNavBar").show();
	        },
	};

	window.searchBar = Object.defineProperties({},
	    {
	        init: {
	            value: function()
	            {
	                $.extend(_elements, {
	                    header: $(document.getElementById('zl_ow_site_panel')),
	                    searchbar: $(document.getElementById('findNavBar')),
	                    content: $(document.getElementById('content')),
	                });
	                
	            	_methods.showSearchBar();
	                return;
	            }
	        }
	    });

})(jQuery);