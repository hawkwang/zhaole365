$(function() {
	
    var map = undefined;
    var marker = undefined;
    
    var mapDiv = $("<div id='locationtag_autocomplite_map' class='ow_location_tag_map_item' style='width:100%; height:300px;'></div>");
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
	
    $(document).on('click', '.get-address', function() {
    	var address = $('input[name="l_origin_address"]').val();
    	debug(address);
    	
    	//$('#location_tag_error').hide();
    	
    	if(address=='')
    		{
    		showErrorMessage($('#location_tag_error'), '请输入地址信息！');
    		return;
    		}
    	
        // remove previous marker
        map.removeOverlay(marker);
        $('input[name="l_description"]').val(address);
        var geocoder = new BMap.Geocoder();
        geocoder.getPoint(address, function(point){

	            if (point) {
	            	
	            	map.centerAndZoom(point, 16);
	            	marker = new BMap.Marker(point);
	            	map.addOverlay(marker);
			    
                      geocoder.getLocation(point, function(item){  
                          var result = {                          
                                  description: item.address,
                                  lat: item.point.lat,
                                  lng: item.point.lng,
                                  address: item.addressComponents.province + item.addressComponents.city + item.addressComponents.district + item.addressComponents.street + item.addressComponents.streetNumber,
                                  result: item
                              };
                          
                          var info = result.address + "(" + result.lng+ "," + result.lat + ")"; 
                         
                        // not valid address
                        var invalid_lng = 116.395645;
                        var invalid_lat = 39.929985;
                        var delta_lng = Math.abs(item.point.lng-invalid_lng);
                        var delta_lat = Math.abs(item.point.lat-invalid_lat);
                      	if( (delta_lng<0.0001) && (delta_lat<0.0001) )
                        {
                      	  $('input[name="l_address"]').val('');
                    	  $('input[name="l_province"]').val('');
                    	  $('input[name="l_city"]').val('');
                    	  $('input[name="l_district"]').val('');
                    	  $('input[name="l_longitude"]').val('');
                    	  $('input[name="l_latitude"]').val('');
                    	  
                    	  //$('#location_tag_error').show();
                    	  showErrorMessage($('#location_tag_error'), '您确认这是正确的地址吗？');
                        }
                      	else
                      	{
                    	  $('input[name="l_address"]').val(item.address);
                    	  $('input[name="l_province"]').val(item.addressComponents.province);
                    	  $('input[name="l_city"]').val(item.addressComponents.city);
                    	  $('input[name="l_district"]').val(item.addressComponents.district);
                    	  $('input[name="l_longitude"]').val(item.point.lng);
                    	  $('input[name="l_latitude"]').val(item.point.lat);
                          debug(info);
                          hideErrorMessage($('#location_tag_error'));
                      	}
                  	})  
			  }
			  else
		      {
				  showErrorMessage($('#location_tag_error'), '无法识别您提供的地址！');
		      }
			}, 
			"北京市"
			)
    });
    
}
);

function showErrorMessage(messagebox, message)
{
	messagebox.text(message);
	messagebox.show();
}

function hideErrorMessage(messagebox)
{
	messagebox.hide();
}

function debug(message)
{
	var debug = true;
	if(debug)
		alert(message);
}