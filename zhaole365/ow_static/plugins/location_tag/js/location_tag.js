var OW_StatusUpdateLocationTag = function($, google)
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
                geocoder = new google.maps.Geocoder();
            }

            if (!map)
            {
                var options = {
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    disableDefaultUI: false,
                    draggable: false,
                    mapTypeControl: false,
                    overviewMapControl: false,
                    panControl: false,
                    rotateControl: false,
                    scaleControl: false,
                    scrollwheel: false,
                    streetViewControl: false,
                    zoomControl: false,
                    zoom: 1
                };

                map = new google.maps.Map(mapDiv.get(0), options);
            }

            var autocomplite = $('#' + elementId).autocomplete({
                delay: 250,

                source: function(request, response) {
                    var icon = $('span.ic_locationtag_pin');
                    icon.removeClass('ic_locationtag_pin');
                    icon.addClass('ow_inprogress');

                   // $('#'+dataInputId).val("");

                    geocoder.geocode({
                        'address': request.term,
                        'region': 'region'
                    }, function(results, status) {

                        icon.removeClass('ow_inprogress');
                        icon.addClass('ic_locationtag_pin');

                        response($.map(results, function(item) {

                            //var label = item.formatted_address.substring(0,32);

                            var result = {                          
                                label: item.formatted_address,
                                lat: item.geometry.location.lat(),
                                lng: item.geometry.location.lng(),
                                southWestLat: item.geometry.location.lat(),
                                southWestLng: item.geometry.location.lng(),
                                northEastLat: item.geometry.location.lat(),
                                northEastLng: item.geometry.location.lng(),
                                value: item.formatted_address,
                                result: item
                            };

                            if( item.geometry.bounds ) 
                            {
                                result.southWestLat = item.geometry.bounds.getSouthWest().lat();
                                result.southWestLng = item.geometry.bounds.getSouthWest().lng();
                                result.northEastLat = item.geometry.bounds.getNorthEast().lat();
                                result.northEastLng = item.geometry.bounds.getNorthEast().lng();
                            }

                            return result;
                        }));
                    })
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

                        var location = new google.maps.LatLng(item.lat, item.lng);

                        if (!marker)
                        {
                            marker = new google.maps.Marker({
                                map: map,
                                draggable: true
                            });
                        }

                        marker.setPosition(location);

                        google.maps.event.trigger(map, 'resize');
                        map.fitBounds(item.result.geometry.viewport);

                    });
                };
            }
        }
    }
} (locationTagJquey, google)
