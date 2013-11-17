var map;
var myClusterer;

$().ready(function() {
    // Resize map to fit window
    $(window).resize(resizeMap);
    resizeMap();
    // When ready - init map
    ymaps.ready(mapInit);
    $('.btnNearMe').click(btnNearMeClickHandler);
    $('input[type=checkbox]').change(requestPoints);
    $('input#search').keydown(function(e) {
        if (e.which === 13) { //keyCode for Enter key is 13
            requestPoints();
        }
    });
    $('#btnSearch').click(requestPoints);
});

function resizeMap() {
    var mapPosition = $('#map').position();
    $('#map').css('height', window.innerHeight - mapPosition.top);
}

function mapInit() {
    map = new ymaps.Map("map", {
        center: [47.22, 39.74],
        zoom: 13
    });
    map.controls.add('zoomControl');
    map.controls.add('scaleLine');
    map.behaviors.enable('scrollZoom');
}

function btnNearMeClickHandler() {
    navigator.geolocation.getCurrentPosition(GetLocation);
}

function GetLocation(location) {
    map.setCenter([location.coords.latitude, location.coords.longitude], map.getZoom());
//    alert(location.coords.latitude);
//    alert(location.coords.longitude);
//    alert(location.coords.accuracy);
}

function prepareUrl() {
    // boostraped checkboxes
    var wep = $('#wep').is(':checked');
    var wps = $('#wps').is(':checked');
    var wpa = $('#wpa').is(':checked');
    var wpa2 = $('#wpa2').is(':checked');
    //
    var url = URI('/ajax/getApsJson.php');
    if (wep)
        url.addQuery('wep');
    if (wps)
        url.addQuery('wps');
    if (wpa)
        url.addQuery('wpa');
    if (wpa2)
        url.addQuery('wpa2');
    //
    var text = $('#search').val();
    if (text) {
        trimmedText = $.trim(text);
        if (trimmedText.match(/^\w\w:\w\w:\w\w:\w\w:\w\w:\w\w$/)) {
            // MAC address
            url.addQuery('mac', trimmedText);
        } else {
            // Network name
            url.addQuery('network', trimmedText);
        }
    }
    return url.toString();
}

function requestPoints() {
    var url = prepareUrl();
    $.ajax({
        url: url,
        dataType: 'json'
    }).done(dataRequestComplete);
}

function dataRequestComplete(data) {
//    map.geoObjects.removeAll();
    var myGeoObjects = [];
    data.forEach(function(point) {
        var description = point.network;
        if (point.wps)
            description = description + '[WPS]';
        if (point.wep)
            description = description + '[WEP]';
        if (point.wpa)
            description = description + '[WPA]';
        if (point.wpa2)
            description = description + '[WPA2]';
        //
        var myPlacemark = new ymaps.Placemark([parseFloat(point.lon), parseFloat(point.lat)], {content: description, balloonContent: point.network});
        //
        myGeoObjects.push(myPlacemark);
//        map.geoObjects.add(myPlacemark);
    });
    // Delete old data
    if (typeof(myClusterer) !== 'undefined') {
        map.geoObjects.remove(myClusterer);
        delete myClusterer;
    }
    
    //
    myClusterer = new ymaps.Clusterer({
    });
    myClusterer.add(myGeoObjects);
    map.geoObjects.add(myClusterer);
}
