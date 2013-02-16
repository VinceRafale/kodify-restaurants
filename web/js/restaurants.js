if (!window.console) window.console = {};
if (!window.console.log) window.console.log = function () { };

function createMarker(marker)
{
    $('#map_canvas').gmap('addMarker', {
        'position': new google.maps.LatLng(marker.lat, marker.lon),
        'bounds': true,
        'tags': marker.tags,
        'price': marker.price
    }).click(function() {
        var markerContent = '';

        markerContent += marker.name; 
        markerContent += '<div class="markerContent">';
        markerContent += marker.address + '<br/>';
        if (marker.description != undefined) {
            markerContent += marker.description + '<br/>';
        }

        if (marker.tags != undefined) {
            markerContent += '<br /><b>Tags:</b> ' + '<br/>';
            for (i = 0; i < marker.tags.length; i++) {
                markerContent += marker.tags[i].name + ' - ';
            }
        }
        markerContent += '</div>';

        $('#map_canvas').gmap('openInfoWindow', { 'content': markerContent }, this);
    });
}

$(document).ready(function() {
    $('#map_canvas').gmap({'center': defaultCenter.center, 'zoom': defaultCenter.zoom, 'disableDefaultUI':true});

    $('#map_canvas').gmap().bind('init', function(event, map) {
    	window.map = map;

        $.getJSON(baseApiDomain + '/restaurant/', function(data) {

            $.each(data, function(i, marker) {
                createMarker(marker);
               
            });
        });

        var input = document.getElementById('restaurant_name');
        var autocomplete = new google.maps.places.Autocomplete(input, {'types' : ['establishment']});
        autocomplete.bindTo('bounds', map);

        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            console.log(place);
            console.log(autocomplete);

            setTimeout(function(){ $("#restaurant_name").val(place.name); }, 1);
            
            description = '';
            if (place.formatted_phone_number != '') {
                description = description + 'Tel: ' + place.formatted_phone_number
            }

            $("#restaurant_address").val(place.vicinity);
            $("#restaurant_website").val(place.website);
            $("#restaurant_description").val(description);            
        });
    });


    $.each(tags, function(i, tag) {
        $('#restaurantTagSelector').append(('<input type="checkbox" data-mini="true" name="tag[]" id="restaurant_tag-'+tag.id+'" class="custom" value="'+tag.id+'" /><label for="restaurant_tag-'+tag.id+'">'+tag.name+'</label>'));
        $('#searcherTagSelector').append(('<input type="checkbox" name="tag-' + tag.id + '" id="tag-' + tag.id + '" class="custom" value="' + tag.id + '" /><label for="tag-' + tag.id+ '">' + tag.name + '</label>'));
        //$("input[type='checkbox']").checkboxradio("refresh");
    });

    $('input:checkbox').live("click", function() {

        $('#map_canvas').gmap('closeInfoWindow');
        $('#map_canvas').gmap('set', 'bounds', null);
        var filters = [];
        $('input:checkbox:checked').each(function(i, checkbox) {
            filters.push($(checkbox).val());
        });

        if ( filters.length > 0 ) {
            $('#map_canvas').gmap('find', 'markers', { 'property': 'tags', 'value': filters, 'operator': 'OR' }, function(marker, found) {

                if (found) {
                    $('#map_canvas').gmap('addBounds', marker.position);
                }
                marker.setVisible(found);
            });
        } else {
            $.each($('#map_canvas').gmap('get', 'markers'), function(i, marker) {
                $('#map_canvas').gmap('addBounds', marker.position);
                marker.setVisible(true);
            });
        }
    });

    $( "#slider-max-price" ).on( "change", function(event, ui) {
        var maxPrice = $(this).val();

        $.each($('#map_canvas').gmap('get', 'markers'), function(i, marker) {
            if (marker.price > maxPrice) {
                marker.setVisible(true);
            } else {
                marker.setVisible(false);
            }
        });
    });
/*
    $('#map_canvas').gmap().bind('init', function(event, map) {
        $(map).click( function(event) {
            $('#map_canvas').gmap('addMarker', {
                'position': event.latLng,
                'draggable': true,
                'bounds': false
            }, function(map, marker) {
                $('#body').append('<div id="dialog'+marker.__gm_id+'" data-role="page" data-fullscreen="true"><div data-role="header"><h3>Add nre rest.</h3></div><div data-role="content"><form action="/restaurant" method="post">      <label for="name">Name:</label>         <input type="text" name="name" id="name" data-mini="true" />                                <label for="address">Address:</label>                        <input type="text" name="name" id="address" data-mini="true" />                                <label for="price">Price:</label>                        <input type="number" name="name" id="price" data-mini="true" />                                <label for="textarea-a">Description:</label>                        <textarea name="textarea" id="textarea-a">                                </textarea>                                <div data-role="fieldcontain">                                <fieldset data-role="controlgroup" id="restaurantTagSelector">                                <legend>Tags:</legend>                                </fieldset>                                </div> <button type="submit" data-theme="b" name="submit" value="submit-value" class="ui-btn-hidden" aria-disabled="false">Submit</button></form></div></div>');
                findLocation(marker.getPosition(), marker);
            }).dragend( function(event) {
                findLocation(event.latLng, this);
            }).click( function() {

                openDialog(this);
            })
        });
    });
*/
    function findLocation(location, marker) {
        $('#map_canvas').gmap('search', {'location': location}, function(results, status) {
            if ( status === 'OK' ) {
               
                $.each(results[0].address_components, function(i,v) {
                    if ( v.types[0] == "administrative_area_level_1" ||
                            v.types[0] == "administrative_area_level_2" ) {
                        $('#state'+marker.__gm_id).val(v.long_name);
                    } else if ( v.types[0] == "country") {
                        $('#country'+marker.__gm_id).val(v.long_name);
                    }
                });
                marker.setTitle(results[0].formatted_address);
                $('#address'+marker.__gm_id).val(results[0].formatted_address);
                openDialog(marker);
            }
        });
    }

    function openDialog(marker) {
        $('#dialog'+marker.__gm_id).dialog();
        $.mobile.changePage('#dialog'+marker.__gm_id, { transition: "pop", role: "dialog", reverse: false } );

        console.log($('#dialog'+marker.__gm_id));
        console.log($.mobile.changePage('#dialog'+marker.__gm_id));
    }

    $('#addRestaurant').on('submit', function (e) {
        var $this = $(this);

         e.preventDefault();

        //show the default loading message while the $.post request is sent
        $.mobile.showPageLoadingMsg();
        address = $("#restaurant_address").val();

        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {

            $("#restaurant_lat").val(results[0].geometry.location.Ya);
            $("#restaurant_lon").val(results[0].geometry.location.Za);

            //send $.post request to server, `$this.serialize()` adds the form data to the request
            $.post($this.attr('action'), $this.serialize(), function (response) {

                //you can now access the response from the server via the `response` variable
                $.mobile.hidePageLoadingMsg();
                

                $.gritter.add({
                    title: response.title,
                    text: response.text
                });

                console.log(response);

                createMarker(response.restaurant);

                map.setCenter(results[0].geometry.location);
                map.setZoom(18);

                $.mobile.changePage($('#home'), 'pop', false, true);
            }).fail(function(response) { 
            	console.log(response);
            	alert("Error: status " + response.status + " statusText: " + response.statusText);
            	$.mobile.hidePageLoadingMsg();
            });

         
            
          } else {
            alert("Geocode was not successful for the following reason: " + status);
          }
        });

        return false;
    });

    $('#addNewRestaurantDialog').live('pagehide',function(event) {
        $("#addNewRestaurantDialog").find('form')[0].reset();    
    });
});

$.extend($.gritter.options, { time: 5000 });