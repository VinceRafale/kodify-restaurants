if (!window.console) window.console = {};
if (!window.console.log) window.console.log = function () { };

function createMarker(marker)
{
    $('#map_canvas').gmap('addMarker', {
        'position': new google.maps.LatLng(marker.lat, marker.lon),
        'bounds': true,
        'tags': marker.tags,
        'price': marker.price,
        'markerId': marker.id,
        'icon': '/images/ico/32x32/row13/1.png'
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
        markerContent += '<div class="markerAction">'
        markerContent += '<a href="" data-marker-id="' + marker.id + '" class="marker-rate-action" title="rate this restaurant"><img src="/images/ico/1361663720_keditbookmarks_16x16.png"></a> ';

        if (marker.current_user_owner != undefined && marker.current_user_owner == true) {            
            markerContent += '<a href="" data-marker-id="' + marker.id + '" class="marker-move-action" title="move restaurant position"><img src="/images/ico/dark/16px/16_move.png"></a> ';
            markerContent += '<a href="" data-marker-id="' + marker.id + '" class="marker-delete-action" title="delete this restaurant"><img src="/images/ico/16x16/row2/1.png"></a>';
        }

        markerContent += '</div>';

        //console.log(this);
        markers[marker.id] = this;
        $('#map_canvas').gmap('openInfoWindow', { 'content': markerContent }, this);
    }).dragend( function(event) {

        $.post(baseApiDomain + '/restaurant/' + this.markerId + '/?userToken=' + userToken, { lat: event.latLng.lat(), lon: event.latLng.lng() }, function (response) { 
            console.log(response); 
        }).fail(function(response) { 
            console.log(response);

            alert("Error: status " + response.status + " statusText: " + response.statusText);           
        });;
    });

}

$(document).ready(function() {
    //validate current token
    $.ajax({
      url: baseApiDomain + '/user/token/?userToken=' + userToken
    }).done(function(response) {
        if (response == 'KO') {
            $('.logout-option').show();
            $('.logedin-option').hide();
        } else if (response == 'OK') {
            $('.logout-option').hide();
            $('.logedin-option').show();
        }
    });

    $('#map_canvas').gmap({'center': defaultCenter.center, 'zoom': defaultCenter.zoom, 'disableDefaultUI':true});

    $("body").delegate('.marker-move-action', 'click', function () {        
       marker = markers[$(this).data('marker-id')];
       marker.setDraggable(true);    
       marker.setIcon('/images/ico/dark/24px/24_move.png')  
    });

    $("body").delegate('.marker-delete-action', 'click', function () {
        $("#deleteRestaurant").attr('action', baseApiDomain + '/restaurant/' + $(this).data('marker-id') + '/remove/?userToken=' + userToken);
        $("#deleteRestaurantDialog").popup('open');
    });

    $('#deleteRestaurant').on('submit', function (e) {
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function (response) { 
             markers[response.markerId].setMap(null);
            $("#deleteRestaurantDialog").popup('close');    
        }).fail(function(response) {
            console.log(response);
        });

        return false;
    });

    $('.logout-action').on('click', function (e) {
        $('.logout-option').show();
        $('.logedin-option').hide();
        $.cookie("userToken", '');
        userToken = null;
        $(this).hide();
        reloadMarkers();
    });
    
    $('#map_canvas').gmap().bind('init', function(event, map) {
    	window.map = map;

        reloadMarkers();

        var input = document.getElementById('restaurant_name');
        var autocomplete = new google.maps.places.Autocomplete(input, {'types' : ['establishment']});
        autocomplete.bindTo('bounds', map);

        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            console.log(place);
            
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

    $('input:checkbox').bind("click", function() {

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

    $('#registerForm').on('submit', function (e) {
        var $this = $(this);
        e.preventDefault();

        if ($("#register_password").val() != $("#register_repeat_password").val()) {
            $("#register_password_error").show();

            return false;
        } else {
            $("#register_password_error").hide();
        }

        $.post($this.attr('action'), $this.serialize(), function (response) {     

            console.log(response)    

            if (response.errors == undefined) {
                $("#popupRegister").popup('close'); 

                $.cookie("userToken", response.token);


                $.gritter.add({                    
                    text: response.msg
                });
            } else {
                var errorsHtml = '';
                for (i = 0; i < response.errors.length; i++) {
                    errorsHtml = errorsHtml + response.errors[i] + "<br>";
                }

                $("#register_error_messages").html(errorsHtml);
                $("#register_error_messages").show();
            }            
        });

        return false;
    });

    $('#loginForm').on('submit', function (e) {
        var $this = $(this);

        e.preventDefault();

        $.post($this.attr('action'), $this.serialize(), function (response) {     

            console.log(response) 

            if (response.error != undefined) {
                $("#login_error_messages").html(response.error);
                $("#login_error_messages").show();
            } else {
                $.cookie("userToken", response.token);
                userToken = response.token;
                $("#popupLogin").popup('close'); 

                $('.logout-option').hide();
                $('.logedin-option').show();

                $.gritter.add({                    
                    text: response.msg
                });

                reloadMarkers()
            }

        });

        return false;
    });

    $('#addRestaurant').on('submit', function (e) {
        var $this = $(this);

         e.preventDefault();

        //show the default loading message while the $.post request is sent
        $.mobile.showPageLoadingMsg();
        address = $("#restaurant_address").val();

        geocoder.geocode( { 'address': address}, function(results, status) {
          if (status == google.maps.GeocoderStatus.OK) {

            $("#restaurant_lat").val(results[0].geometry.location.lat());
            $("#restaurant_lon").val(results[0].geometry.location.lng());

            //send $.post request to server, `$this.serialize()` adds the form data to the request
            $.post($this.attr('action') + '?userToken=' + userToken, $this.serialize(), function (response) {

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

    $('#addNewRestaurantDialog').bind('pagehide',function(event) {
        $("#addNewRestaurantDialog").find('form')[0].reset();    
    });
});

$.extend($.gritter.options, { time: 5000 });

function reloadMarkers()
{
    $.getJSON(baseApiDomain + '/restaurant/?userToken=' + userToken, function(data) {
        markers.forEach(function(marker) {
            marker.setMap(null);
        })
        markers = new Array();

        $.each(data, function(i, marker) {
            createMarker(marker);               
        });
    });
}