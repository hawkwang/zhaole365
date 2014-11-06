var OW_StatusUpdateLocationTagBaidu = function($, baidu)
{
    return function()
    {
        var self = this;

        var geocoder = undefined;
        var map = undefined;
        var marker = undefined;
        var mapDiv = $("<div id='locationtag_autocomplite_map' class='ow_location_tag_map_item' style='display:none;height:150px;'></div>");

        this.elementId = '';
        this.dataInputId = '';
        this.locatioTagDiv = undefined;


        this.replaceAll = function (find, replace, str) {
            return str.replace(new RegExp(find, 'g'), replace);
        }

        this.refresh = function()
        {
            $('#'+self.dataInputId).val('');
            $('#'+self.dataInputId).trigger("change");

            $('.location_tag_input_box').remove();
            $('#'+self.elementId).val('');
            $('#'+self.elementId).show();
        }

        this.initLocationAutocomplite = function(elementId, dataInputId)
        {
            self.elementId = elementId;
            self.dataInputId = dataInputId;

            if (!geocoder)
            {
                //geocoder = new google.maps.Geocoder();
            	geocoder = new BMap.Geocoder();
            }

            if (!map)
            {
                //map = new google.maps.Map(mapDiv.get(0), options);
                map = new BMap.Map("locationtag_autocomplite_map");
                var point = new BMap.Point(116.331398,39.897445);
                map.centerAndZoom(point,12);
                map.enableScrollWheelZoom();    //启用滚轮放大缩小，默认禁用
                map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用
                map.addControl(new BMap.NavigationControl());  //添加默认缩放平移控件
                map.addControl(new BMap.OverviewMapControl());              //添加默认缩略地图控件
                map.addControl(new BMap.OverviewMapControl({isOpen:true, anchor: BMAP_ANCHOR_TOP_RIGHT}));   //右上角，打开
                map.addControl(new BMap.ScaleControl());                    // 添加默认比例尺控件
                map.addControl(new BMap.ScaleControl({anchor: BMAP_ANCHOR_BOTTOM_LEFT}));                    // 左下
                map.addControl(new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT}));                    // 左上
            }

            var autocomplite = $('#' + elementId).autocomplete({
                delay: 250,

                source: function(request, response) {
                    var icon = $('span.ic_locationtag_pin');
                    icon.removeClass('ic_locationtag_pin');
                    icon.addClass('ow_inprogress');

                   // $('#'+dataInputId).val("");
                    
                    geocoder.getPoint(request.term, function(point){
					  if (point) {
					    $.map.centerAndZoom(point, 16);
					    marker = new BMap.Marker(point);
					    marker.enableDragging();    //可拖拽
					    $.map.addOverlay(marker);
					    
                        icon.removeClass('ow_inprogress');
                        icon.addClass('ic_locationtag_pin');

                        response(
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
                                    
                                    return result;
                            	})  
                        );
					  }
					}, 
					"中华人民共和国"
					)
                    
                },
                select: function(event, ui) 
                {
                    $('#'+dataInputId).val(self.replaceAll('"','\"',JSON.stringify(ui.item)));
                    $('#'+dataInputId).trigger("change");

                    var width = $('#'+elementId).width();
                    var symbolCount = Math.ceil( (width - 43) / 7 );
                    var label = ui.item.label;

                    if ( label.length > symbolCount )
                    {
                       label = label.substring(0,symbolCount) + '...';
                    }

                    $('#'+elementId).hide();

                    var div = $('<div class="location_tag_input_box location_tag_label_div location_tag_box">\n\
                                    <span class="location_tag_label">' + label + '</span>\n\
                                    <span class="location_tag_close"></span>\n\
                                 </div>');

                    div.find('span.location_tag_close').click(function(){
                        self.refresh();
                    });

                    $($('#'+elementId).parents("div").get(0)).append(div);           
                }
            }).data('ui-autocomplete');

            if ( autocomplite )
            {	
                autocomplite._renderMenu = function(ul, items)
                {
                    ul.addClass('location_tag_autocomplite ac_results');
                    ul.width($("#"+elementId).width());

                    var self = this;
                    mapDiv.hide();
                    $mapItem = $("<li class='ow_location_tag_map_item clearfix ui-menu-item'>").append(mapDiv);
                    ul.append($mapItem);

                    ul.mouseout(function()
                    {
                        mapDiv.hide();
                    });
                    var count = 0
                    $.each(items, function(index, item) {
                        if ( count < 6 )
                        {
                            self._renderItemData(ul, item);
                        }
                        count++;
                    });
                }

                autocomplite._renderItemData = function(ul, item)
                {
                    var $itemElement = $("<li class='location_tag_autocomplite_item clearfix ui-menu-item'></li>").data( "ui-autocomplete-item", item ).append("<a>" + item.label + "</a>");
                    $itemElement.insertBefore($('li.ow_location_tag_map_item', ul));

                    $itemElement.mouseover(function()
                    {
                        var element = $('div.ow_location_tag_map_item', ul);
                        element.show();

                        //var location = new google.maps.LatLng(item.lat, item.lng);
                        var point = new BMap.Point(item.lng,item.lat);

                        if (!marker)
                        {
//                            marker = new google.maps.Marker({
//                                map: map,
//                                draggable: true
//                            });
                            marker = new BMap.Marker(point);
                        }

					    marker.enableDragging();    //可拖拽
					    map.addOverlay(marker);
					    
//                        google.maps.event.trigger(map, 'resize');
//                        map.fitBounds(item.result.geometry.viewport);
					    map.centerAndZoom(point,12);

                    });
                };
            }
        }
    }
} (locationTagJquey, baidu)
