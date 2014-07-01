<div id="map-canvas"></div>

<script>
    var map;
    function drawPolygon(zip_code, total, points)
    {
        var zip;

        // Define the LatLng coordinates for the polygon's path.
        var coords = [];

        $.each(points, function(i, point) {
            //console.log(point);
            coords.push(new google.maps.LatLng(point.lat, point.long));
        });

        // Construct the polygon.
        zip = new google.maps.Polygon({
            paths: coords,
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 1,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            clickable: true,
            name: "Zip Code: " + zip_code,
            total: "Contributions: $" + total
        });
        zip.setMap(map);
        google.maps.event.addListener(zip, "mouseover", function(event) {
            this.setOptions({fillColor: "#00FF00"});
            
        });
        google.maps.event.addListener(zip,"mouseout",function(){
            this.setOptions({fillColor: "#FF0000"});
        });
        google.maps.event.addListener(zip,"click",function(event){
            var contentString = '<div class="viewContribution"><b>'+this.name+'</b><br>' + this.total +
                //'Clicked location: <br>' + event.latLng.lat() + ',' + event.latLng.lng() +
                '<br></div>';
            // Replace the info window's content and position.
            infoWindow.setContent(contentString);
            infoWindow.setPosition(event.latLng);
            infoWindow.open(map);
        });
        infoWindow = new google.maps.InfoWindow();

    }

    function loadShapes(offset)
    {
        // Fetch zip codes
        var jqxhr = $.getJSON("/api/polygon?map_id=<?php echo $this->map_id; ?>&offset=" + offset, function(response) {
            //console.log( "success" );
            $.each(response.data.results, function(index, item) {
                drawPolygon(item.zip_code, item.total, item.shape);
            });
            /*
            if (response.data.next_page != -1)
            {
                loadShapes(response.data.next_page);
            }
            */

        })
        .done(function() {
            //console.log( "second success" );
        })
        .fail(function() {
            //console.log( "error" );
        })
        .always(function() {
            //console.log( "complete" );
        });
    }

    $(document).ready(function() {
        // This example creates a simple polygon representing the Bermuda Triangle.
        function initialize() {
            var mapOptions = {
                zoom: <?php echo $this->map->center_zoom; ?>,
                center: new google.maps.LatLng(<?php echo $this->map->center_latitude; ?>, <?php echo $this->map->center_longitude; ?>),
                /*
                 * HYBRID
                 * ROADMAP
                 * SATELLITE
                 * TERRAIN
                 */
                mapTypeId: google.maps.MapTypeId.<?php echo $this->map->type; ?>
            };
            map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            $.ajaxSetup({
                async: true
            });
            for(i=0;i<16;i++) {
                loadShapes((i*50));
            }

        }
        google.maps.event.addDomListener(window, 'load', initialize);



    });

</script>