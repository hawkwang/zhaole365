$(function() {
	
    var map = undefined;
    var marker = undefined;
    
    var mapDiv = $("<div id='locationtag_autocomplite_map' class='ow_location_tag_map_item' style='width:50%; height:200px;'></div>");
    $(".newsfeed_update_status_info").append(mapDiv);

//    if (!geocoder)
//    {
//        //geocoder = new google.maps.Geocoder();
//    	geocoder = new BMap.Geocoder();
//    }
    
    if (!map)
    {
        //map = new google.maps.Map(mapDiv.get(0), options);
        map = new BMap.Map("locationtag_autocomplite_map");
        var point = new BMap.Point(116.331398,39.897445);
        map.centerAndZoom(point,16);
        map.enableScrollWheelZoom();    //启用滚轮放大缩小，默认禁用
        map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用
        map.addControl(new BMap.NavigationControl());  //添加默认缩放平移控件
        map.addControl(new BMap.OverviewMapControl());              //添加默认缩略地图控件
        map.addControl(new BMap.OverviewMapControl({isOpen:false, anchor: BMAP_ANCHOR_TOP_RIGHT}));   //右上角，打开
        map.addControl(new BMap.ScaleControl());                    // 添加默认比例尺控件
        map.addControl(new BMap.ScaleControl({anchor: BMAP_ANCHOR_BOTTOM_LEFT}));                    // 左下
        map.addControl(new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT}));                    // 左上
        
        marker = new BMap.Marker(point);
        //marker.enableDragging();    //可拖拽
	    map.addOverlay(marker);
	    
	    map.centerAndZoom(point,16);
    }
	
    $(document).on('change', '.ow_locationtag_location_input', function() {
    	var address = $(".ow_locationtag_location_input").val();
    	debug(address);
    	
    	$('#location_tag_error').hide();
    	
        var icon = $('span.locationtag_input_address_icon');
        icon.removeClass('ic_locationtag_pin');
        icon.addClass('ow_inprogress');
        
        // remove previous marker
        map.removeOverlay(marker);
        
        var geocoder = new BMap.Geocoder();
        geocoder.getPoint(address, function(point){
			    var icon = $('span.locationtag_input_address_icon');
	            icon.removeClass('ow_inprogress');
	            icon.addClass('ic_locationtag_pin');

	            if (point) {
	            	
	            	map.centerAndZoom(point, 16);
	            	marker = new BMap.Marker(point);
	            	map.addOverlay(marker);
			    

                      geocoder.getLocation(point, function(item){  
                          var result = {                          
                                  label: item.address,
                                  lat: item.point.lat,
                                  lng: item.point.lng,
                                  southWestLat: item.point.lat,
                                  southWestLng: item.point.lng,
                                  northEastLat: item.point.lat,
                                  northEastLng: item.point.lng,
                                  value: item.addressComponents.province + item.addressComponents.city + item.addressComponents.district + item.addressComponents.street + item.addressComponents.streetNumber,
                                  result: item
                              };
                          
                          var info = result.value + "(" + result.lng+ "," + result.lat + ")"; 
                          debug(info);
                  	})  
			  }
			  else
		      {
				  $('#location_tag_error').show();
		      }
			}, 
			"北京市"
			)
    });
    
}
);

function debug(message)
{
	var debug = true;
	if(debug)
		alert(message);
}