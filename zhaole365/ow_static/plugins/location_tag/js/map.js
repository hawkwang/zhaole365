var OW_GoogleMap = function($, google)
{
    return function( elementId )
    {
        var self = this;

        var geocoder;
        var map;
        var marker = {};
        var infowindow = {};
        var infowindowState = [];

        var mapElementId = elementId;

        this.initialize = function(options)
        {
            var params = options;

            if( !params )
            {
                var latlng = new google.maps.LatLng(0, 0);

                params = {
                    zoom: 9,
                    center: latlng,
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
                    zoomControl:false
                };
            }

            map = new google.maps.Map(document.getElementById(mapElementId), params);

            //geocoder = new google.maps.Geocoder();
        }

        this.setCenter = function(lat, lon)
        {
            var latlng = new google.maps.LatLng(lat, lon);
            map.setCenter(latlng);
        }

        this.setZoom = function(zoom)
        {
            map.setZoom(zoom);
        }

        this.fitBounds = function(bounds)
        {
            map.fitBounds(bounds);
        }

        this.getBounds = function()
        {
            map.getBounds();
        }

        this.addPoint = function(lat, lon, title, windowContent, isOpen)
        {
            marker[lat + ' ' + lon] = self.createMarker(lat, lon);

            if ( title )
            {
                marker[lat + ' ' + lon].setTitle(title);
            }

            if ( windowContent )
            {
                infowindow[lat + ' ' + lon] = self.createInfoWindow( windowContent );

                //infowindow[lat + ' ' + lon].setContent(windowContent);

                infowindowState[lat + ' ' + lon] = false;
                if ( isOpen )
                {
                    infowindow[lat + ' ' + lon].open(map, marker[lat + ' ' + lon]);
                    infowindowState[lat + ' ' + lon] = true;
                }

                google.maps.event.addListener(marker[lat + ' ' + lon], 'click', function() {
                    if( infowindowState[lat + ' ' + lon] )
                    {
                        self.closeInfoWindow(lat, lon);
                    }
                    else
                    {
                        infowindow[lat + ' ' + lon].open(map, marker[lat + ' ' + lon]);
                        infowindowState[lat + ' ' + lon] = true;

                        $.each( infowindow, function( key, value ) {
                            if ( value )
                            {
                                if ( key != lat + ' ' + lon )
                                {
                                    self.closeInfoWindow(lat, lon);
                                }
                            }
                        } );
                    }
                });

                google.maps.event.addListener(infowindow[lat + ' ' + lon], 'closeclick', function() {
                    self.closeInfoWindow(lat, lon);
                });
            }
        }

        this.getMap = function()
        {
            return map;
        }

        this.getMarkerList = function()
        {
            return markerList;
        }

        this.getInfoWindowList = function()
        {
            return infowindow;
        }

        this.getInfoWindowStatus = function( lat , lon )
        {
            return infowindowState[lat + ' ' + lon];
        }

        this.createInfoWindow = function( windowContent )
        {
            var infowindow = new InfoBubble({
                    content: windowContent,
                    shadowStyle: 0,
                    padding: 9,
                    backgroundColor: '#fff',
                    borderRadius: 4,
                    arrowSize: 10,
                    maxHeight: 350,
                    borderWidth: '4px',
                    borderColor: '#fff',
                    disableAutoPan: false,
                    hideCloseButton: false,
                    arrowPosition: 25,
                    arrowStyle: 0,
                    borderWidth: 0
                  });

            return infowindow; 
        }

        this.closeInfoWindow = function( lat, lon )
        {
            if( infowindowState[lat + ' ' + lon] )
            {
                infowindow[lat + ' ' + lon].close();
                infowindowState[lat + ' ' + lon] = false;
            }
        }

        this.deleteMarker = function( lat , lon )
        {
            if ( marker[lat + ' ' + lon] )
            {
                marker[lat + ' ' + lon].setMap(null);
            }
        }

        this.createMarker = function( lat , lon )
        {
            var marker = new google.maps.Marker({
                map: map
            });

            var latlng = new google.maps.LatLng(lat, lon);
            marker.setPosition(latlng);
            return marker;
        }

        this.resize = function()
        {
            var bounds = map.getBounds();
            google.maps.event.trigger(map, 'resize');
            
            if ( bounds )
            {
                map.fitBounds(bounds);
            }
        }
    }
} (locationTagJquey, google)