<?php
##api key: AIzaSyBfNgCQIzlNLQiTyVI7qJfpHsufihMtVjE
define("API_KEY", "AIzaSyBfNgCQIzlNLQiTyVI7qJfpHsufihMtVjE");
## e.g. https://maps.googleapis.com/maps/api/geocode/json?address=University+of+Southern+California+CA&key=YOUR_API_KEY
define("GEO_API_URL", "https://maps.googleapis.com/maps/api/geocode/json");
## e.g. https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=34.0223519,-118.285117&radius=16090&type=cafe&keyword=usc&key=YOUR_API_KEY
define("NEARBY_SEARCH_URL", "https://maps.googleapis.com/maps/api/place/nearbysearch/json");
## e.g. https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJ7aVxnOTHwoARxKIntFtakKo&key=YOUR_API_KEY
define("PLACE_DETAILS_URL", "https://maps.googleapis.com/maps/api/place/details/json");
## e.g. https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference=&key=YOUR_API_KEY
define("PLACE_PHOTO_URL", "https://maps.googleapis.com/maps/api/place/photo");

## request_type: 'request_type'=>("near_by","place_info")
define('REQUEST_TYPE', 'request_type');
define('NEAR_BY', 'near_by');
define('PLACE_INFO', 'place_info');
define('DATA', 'data');

define('PHOTO_RESULTS', 'photo_results');
define('REVIEW_RESULTS', 'review_results');
define('PHOTOS', 'photos');
define('REVIEWS', 'reviews');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $rest_json_str = file_get_contents("php://input");
    $rest_json = json_decode($rest_json_str, true);
    if(isset($rest_json)) {
        switch ($rest_json[REQUEST_TYPE]){
            case NEAR_BY:
                nearBy($rest_json[DATA]);
                break;
            case PLACE_INFO:
                placeInfo($rest_json[DATA]);
                break;
            default:
                break;
        }
    }
    exit(0);
}

function placeInfo($request) {
    $response = '';
    if(isset($request)) {
        $place_id = $request['place_id'];
        $url = PLACE_DETAILS_URL."?place_id=".$place_id."&key=".API_KEY;
        $original_response = file_get_contents($url);
        $json_response = json_decode($original_response, true);
        $response = generate_photo_and_review_response($json_response);
    }
    else {
        $response = errorMsg('no place id');
    }
    echo $response;
}

function generate_photo_and_review_response($json_response) {
    $response_obj = null;
    if(isset($json_response['status']) && $json_response['status'] == 'OK') {
        if(!isset($json_response['result'])) {
            $response_obj = array(PHOTO_RESULTS=>'no', REVIEW_RESULTS=>'no');
            return json_encode($response_obj);
        }
        $response_obj = array(PHOTOS=>'', REVIEWS=>'', PHOTO_RESULTS=>'no', REVIEW_RESULTS=>'no');
        try {
            $result = $json_response['result'];
            if (!isset($result[PHOTOS])) {
                $response_obj[PHOTO_RESULTS] = 'no';
            } else {
                $photo_json_array = retrieve_top_5_photos($result[PHOTOS]);
                if (isset($photo_json_array)) {
                    $response_obj[PHOTOS] = $photo_json_array;
                    $response_obj[PHOTO_RESULTS] = 'yes';
                }
            }
            if (!isset($result[REVIEWS])) {
                $response_obj[REVIEWS] = 'no';
            } else {
                $review_json_array = retrieve_top_5_reviews($result[REVIEWS]);
                if (isset($review_json_array)) {
                    $response_obj[REVIEWS] = $review_json_array;
                    $response_obj[REVIEW_RESULTS] = 'yes';
                }
            }
        } catch (Exception $e) {
            $response_obj['error'] = $e;
        }
    }
    else {
        $response_obj = array(PHOTO_RESULTS => 'no', REVIEW_RESULTS => 'no');
    }
    return json_encode($response_obj);
}

function retrieve_top_5_photos($photos_array) {
    $photos_json_array = null;
    if (!is_array($photos_array)) {
        return $photos_json_array;
    }
    $length = count($photos_array);
    $photos_json_array = array();
    $counter = 0;
    for($i = 0; $i < $length; $i ++) {
        $photo = $photos_array[$i];
        if ($counter == 5) {
            break;
        }
        if (!isset($photo) || !isset($photo['photo_reference'])) {
            continue;
        }
        $obj = array();
        $photo_width = 800;
        if(isset($photo['width'])) {
            $photo_width = $photo['width'];
        }
        $obj['photo_url'] = download_photo($photo['photo_reference'], $photo_width,($counter+1).'.jpg');
        if(isset($obj['photo_url'])) {
            $photos_json_array[] = $obj;
            $counter += 1;
        }
    }
    return $photos_json_array;
}

function download_photo($reference, $max_width, $file_name) {
    if (!isset($max_width)) {
        $max_width = '1000';
    }
    $url = PLACE_PHOTO_URL.'?maxwidth='.$max_width.'&photoreference='.$reference.'&key='.API_KEY;
    $file = file_get_contents($url);
    if (isset($file)) {
        file_put_contents('./' . $file_name, $file);
    }
    else {
        $file_name = null;
    }
    return $file_name;
}

function retrieve_top_5_reviews($reviews_array) {
    $reviews_json_array = null;
    if (!is_array($reviews_array)) {
        return $reviews_json_array;
    }
    $length = count($reviews_array);
    $reviews_json_array = array();
    $counter = 5;
    for($i = 0; $i < $length; $i ++) {
        if ($counter == 0) {
            break;
        }
        $obj = array();
        if(isset($reviews_array[$i]['author_name'])) {
            $obj['author_name'] = $reviews_array[$i]['author_name'];
        }
        if(isset($reviews_array[$i]['profile_photo_url'])) {
            $obj['profile_photo_url'] = $reviews_array[$i]['profile_photo_url'];
        }
        if(isset($reviews_array[$i]['text'])) {
            $obj['text'] = $reviews_array[$i]['text'];
        }
        if (!isset($obj['text'])){
            $obj['text'] = '';
        }
        if(count($obj) > 0) {
            $reviews_json_array[] = $obj;
            $counter -= 1;
        }
    }
    if(count($reviews_json_array) == 0) {
        return null;
    }
    return $reviews_json_array;
}

function nearBy($request) {
    $keyword = $request['keyword'];
    $category = $request['category'];
    $distance = $request['distance'];
    $radius = (float)$distance;
    $radius = (string)($radius * 1609.34);
    $geo = null;
    $target_geo = null;
    if(isset($request['here'])) {
        $geo = $request['here'];
    }
    elseif (isset($request['location'])){
        $target_geo = array('lat'=>0, 'lon'=>0);
        $geo = loc2geo($request['location'], $target_geo);
    }
    else{
        echo errorMsg('no location tag');
        return;
    }
    $url = NEARBY_SEARCH_URL.'?location='.urlencode($geo).'&radius='.urlencode($radius).'&type='.urlencode($category).'&keyword='.urlencode($keyword).'&key='.API_KEY;
    $json_response_str = file_get_contents($url);
    if ($target_geo != null) {
        $json_response = json_decode($json_response_str, true);
        $json_response['current_location'] = $target_geo;
        $json_response_str = json_encode($json_response);
    }
    echo $json_response_str;
}

function loc2geo($location, &$current_location) {
    $url = GEO_API_URL.'?'.'address='.urlencode($location).'&key='.API_KEY;
    $lat_lon = '';
    try {
        $result = file_get_contents($url);
        $results = json_decode($result, true);
        if (isset($results) && isset($results['results']) && isset($results['results'][0])) {
            $lat = $results["results"][0]["geometry"]["location"]["lat"];
            $lon = $results["results"][0]["geometry"]["location"]["lng"];
            $lat_lon = $lat . ',' . $lon;
            $current_location['lat'] = $lat;
            $current_location['lon'] = $lon;
        }
    } catch (Exception $e) {
        $lat_lon = '';
        $current_location = null;
    }
    return $lat_lon;
}

function errorMsg($msg) {
    $msg_obj = array('error' => $msg);
    return json_encode($msg_obj);
}

if($_GET){
    echo json_encode($_GET);
    exit(0);
}

function geocode($address) {
    $request_url = GEO_API_URL.'?address='.$address.'&key='.API_KEY;
    return file_get_contents($request_url);
}

function placeNearbySearch($location, $radius, $type, $keyword) {
    $request_url = NEARBY_SEARCH_URL.'?location='.$location.'&radius='.$radius.'&type='.$type.'&keyword='.$keyword.'&key='.API_KEY;
    return file_get_contents($request_url);
}

function placeDetails($place_id) {
    $request_url = PLACE_DETAILS_URL.'?placeid='.$place_id.'&key='.API_KEY;
    return file_get_contents($request_url);
}

function placePhoto($maxwidth, $photoreference) {
    $request_url = PLACE_PHOTO_URL.'?maxwidth='.$maxwidth.'&photoreference='.$photoreference.'$key='.API_KEY;
    return file_get_contents($request_url);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHP Test</title>
    <style>
        h3 {
            margin: 0 0 0 0;
        }
        #search-box{
            width: 700px;
            border: lightgray 3px solid;
            background-color: #f9f9f9;
            position: relative;
            margin: 2% auto 0;
            height: 230px;
        }
        #search-box-title{
            text-align: center;
            border-bottom: lightgray 2px solid;
            margin: 0 10px 0 10px;
            padding: 10px 0 10px 0;
            font-family: "Apple Braille";
            font-style: italic;
            font-size: 36px;
        }
        #search-box-entity {
            margin: 10px 10px 10px 10px;
        }
        .label {
            display: inline-block;
            margin: 0 0 0 0;
        }
        .search-box-col {
            display: inline-block;
        }
        #search-btn-group {
            margin-left: 60px;
            margin-top: 20px;
        }
        option {
            width: 80px;
        }
        #display-table {
            margin-left: auto;
            margin-right: auto;
            margin-top: 30px;
        }
        .clickable {
            display: inline-block;
            transition: 0.4s;
            user-select: none;
            margin-top: 5px;
            margin-bottom: 5px;
            font-size: 23px;
        }
        .clickable:hover {
            cursor: pointer;
            color: darkgray;
        }
        td {
            padding-left: 1vw;
        }
        .category-cell {
            min-width: 10vw;
            max-width: 15vw;
        }
        .name-cell {
            min-width: 35vw;
            max-width: 40vw;
        }
        .address-cell {
            min-width: 25vw;
            max-width: 30vw;
        }
        #google-map {
            width: 400px;
            height: 300px;
            position: absolute;
        }
        #google-map-container {
            display: flex;
            position: absolute;
        }
        #google-map-options{
            z-index: 99;
        }
        .google-map-option {
            width: 100px;
            height: auto;
            background-color: #e4e4e4;
            text-align: center;
            top: 50%;
            cursor: pointer;
            transition: 0.2s;
        }
        .google-map-option:hover {
            background-color: #aaaaaa;
        }
        .google-map-option-text {
            display: inline-block;
        }
        .photo-image {
            display: block;
            padding: 20px 20px 20px 20px;
        }
        .image-unit {
            border-bottom: lightgray 2px solid;
        }
        #photos-reviews-display{
            margin-right: auto;
            margin-left: auto;
            text-align: center;
        }
        #photo-table {
            text-align: center;
            margin: 0 auto 0 auto;
            width: 700px;
            border: lightgray 2px solid;
            border-bottom: transparent 0 solid;
        }
        #review-table {
            margin: 0 auto 0 auto;
            border: lightgray 2px solid;
            border-bottom: transparent 0 solid;
            width: 700px;
        }
        .profile-img {
            width: 40px;
        }
        .author-name {
            display: inline-block;
            margin: 5px 0 5px 0;
        }
        .review-unit {
            border-bottom: lightgray 2px solid;
        }
        .review-unit-person {
            border-bottom: lightgray 2px solid;
            padding-top: 5px;
        }
        .review-unit-text > p {
            text-align: left;
            margin: 0 0 0 0;
        }
        #show-reviews {
            width: 300px;
            margin: 30px auto 0 auto;
        }
        #show-reviews-btn:hover {
            cursor: pointer;
        }
        #show-photos {
            width: 300px;
            margin: 30px auto 0 auto;
        }
        #show-photos-btn:hover {
            cursor: pointer;
        }
        #show-photos-text {
            margin: 0 0 0 0;
            font-size: 20px;
        }
        #show-photos-text:hover {
            cursor: default;
            user-select: none;
        }
        #show-reviews-text {
            margin: 0 0 0 0;
            font-size: 20px;
        }
        #show-reviews-text:hover {
            cursor: default;
            user-select: none;
        }
        #display-section {
            margin-bottom: 300px;
        }
        .form-unit {
            margin-top: 5px;
        }
        .no-result-tag {
            width: 700px;
            border: lightgray 2px solid;
            margin: 0 auto 20px auto;
        }
        .no-result-tag > h4 {
            margin: 0 0 0 0 ;
        }
        #no-record {
            width: 900px;
            border: lightgray 2px solid;
            background-color: #f9f9f9;
            margin: 0 auto 0 auto;
            text-align: center;
        }
        #no-record > p {
            margin: 0 0 0 0;
        }
    </style>
</head>
<body>

<div id="search-box">
    <p id="search-box-title">Travel and Entertainment Search</p>
    <form id="search-box-entity" onsubmit="return SubmitForm();">
        <div class="search-box-col">
            <div class="form-unit">
                <h4 class="label">Keyword</h4>
                <input id="keyword-input" name="keyword" required/>
            </div>
            <div class="form-unit">
                <h4 class="label">Category</h4>
                <select id="category-select" name="category">
                </select>
            </div>
            <div class="form-unit">
                <h4 class="label">Distance (miles)</h4>
                <input id="distance-input" name="distance" placeholder="10"/>
                <h4 class="label">from</h4>
            </div>
            <br/>
        </div>
        <div class="search-box-col">
            <input id="radio-here" type="radio" name="location_select" checked="checked" onchange="RadioChange(this)"/>Here
            <br/>
            <input id="radio-location" type="radio" name="location_select" onchange="RadioChange(this)">
            <input id="location-input" name="location" placeholder="location" disabled="disabled" required>
        </div>
        <div id="search-btn-group">
            <input id="submit-btn" value="Search" type="submit">
            <input id="clear-btn" type="button" value="Clear" onclick="clearForm()">
        </div>
    </form>
</div>
<br/>
<div id="display-section">
</div>
<script type="text/javascript">

    var IP_GEO_URL = "http://ip-api.com/json";

    const GET = 'GET', POST = 'POST', PHP_SELF_URL = 'index.php';

    const keyword_input = document.getElementById('keyword-input'),
        category_select = document.getElementById('category-select'),
        distance_input = document.getElementById('distance-input'),
        radio_here = document.getElementById("radio-here"),
        radio_loc = document.getElementById("radio-location"),
        location_input = document.getElementById("location-input"),
        display_section = document.getElementById("display-section"),
        submit_input = document.getElementById("submit-btn"),
        clear_input = document.getElementById("clear-btn");

    const config = {
        request_type: 'request_type',
        data_body: 'data',
        table_headers: ['Category', 'Name', 'Address'],
        category:['default', 'cafe', 'bakery', 'restaurant', 'beauty salon', 'casino', 'movie theater', 'lodging', 'airport', 'train station', 'subway station', 'bus station'],
        data_request_type: {
            near_by: "near_by",
            place_info: "place_info"
        }
    };

    const location_option = {
        here: 'here',
        location: 'location'
    };

    var drop_down_select = document.getElementById('category-select');
    for (var i = 0; i < config.category.length; i++) {
        var option = document.createElement('option');
        option.innerHTML = config.category[i];
        option.value = config.category[i];
        drop_down_select.appendChild(option);
    }

    var google_map_element = document.createElement('div');
    google_map_element.id = 'google-map';
    var google_map_options = document.createElement('div');
    google_map_options.id = 'google-map-options';
    google_map_options.setAttribute('disabled', '');
    var google_map_walk = document.createElement('div');
    var google_map_bike = document.createElement('div');
    var google_map_drive = document.createElement('div');
    google_map_walk.id = 'google-map-walk';
    google_map_bike.id = 'google-map-bike';
    google_map_drive.id = 'google-map-drive';
    google_map_walk.className = 'google-map-option';
    google_map_bike.className = 'google-map-option';
    google_map_drive.className = 'google-map-option';
    google_map_walk.value = 'WALKING';
    google_map_bike.value = 'BICYCLING';
    google_map_drive.value = 'DRIVING';
    google_map_walk.innerHTML = '<p class="google-map-option-text">Walk there</p>';
    google_map_bike.innerHTML = '<p class="google-map-option-text">Bike there</p>';
    google_map_drive.innerHTML = '<p class="google-map-option-text">Drive there</p>';
    google_map_options.appendChild(google_map_walk);
    google_map_options.appendChild(google_map_bike);
    google_map_options.appendChild(google_map_drive);
    var google_map_container = document.createElement('div');
    google_map_container.id = 'google-map-container';
    google_map_container.appendChild(google_map_options);
    google_map_container.appendChild(google_map_element);
    var currentGeo = {lat:34.0223519, lon: -118.285117};

    getCurrentGeo();

    function getCurrentGeo() {
        var success = function (content) {
            var result = '';
            try {
                result = JSON.parse(content);
                if (result['lat'] && result['lon']) {
                    currentGeo['lat'] = result['lat'];
                    currentGeo['lon'] = result['lon'];
                }
            }catch (err) {
                currentGeo = {lat:34.0223519, lon: -118.285117};
            }
            submit_input.disabled = '';
        };
        var complete = function (status) {
        };
        var errorHandler = function (status) {
            if(status == 0) {
                console.error('ipapi timeout');
            } else {
                currentGeo['lat'] = 34.0223519;
                currentGeo['lon'] = -118.285117;
                submit_input.disabled = '';
            }
        };
        submit_input.disabled = 'disabled';
        ajaxCall(GET, IP_GEO_URL, null, success, complete, true, errorHandler, 100000);
    }

    function getCurrentGeoString() {
        var geoStr = currentGeo['lat']+','+currentGeo['lon'];
        return geoStr;
    }

    function clearForm() {
        RadioChange(radio_here);
        display_section.innerText = '';
        submit_input.disabled = '';
        document.getElementById("search-box-entity").reset();
    }

    function currentLoc() {
        var result = {};
        if(radio_here.checked) {
            result[location_option.here] = getCurrentGeoString();
        }
        else {
            result[location_option.location] = location_input.value;
        }
        return result;
    }

    function wrapFormToJson() {
        var requestEntity = {};
        requestEntity[keyword_input.name] = keyword_input.value;
        requestEntity[category_select.name] = category_select.value;
        requestEntity[distance_input.name] = '10';
        if (distance_input.value && distance_input.value.trim()) {
            requestEntity[distance_input.name] = distance_input.value;
        }
        Object.assign(requestEntity, currentLoc());
        return requestEntity;
    }

    function RadioChange(radio) {
        radio.checked = true;
        if(radio_here == radio) {
            location_input.disabled = "disabled";
        }
        else {
            location_input.disabled = "";
        }
    }

    function ajaxCall(method="GET", url, data, success, complete, async = false, errorFunc = null, timeout=30000) {
        var xhr = new XMLHttpRequest();
        if (method != "GET" && method != "POST") {
            console.error('wrong http request method '+method);
            return;
        }
        if (method == "GET") {
            if(data != null && Object.keys(data).length != 0) {
                url += '?';
                for (var key in data) {
                    url += key + '=' + data[key]+'&';
                }
                url = url.substring(0, url.length - 1);
            }
        }
        xhr.open(method, url, async);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4){
                if (xhr.status == 200) {
                    if(typeof success != 'function') {
                        console.error('no success function passed');
                    }
                    else {
                        success(xhr.response);
                    }
                }
                else {
                    if (errorFunc && typeof errorFunc == 'function'){
                        errorFunc(xhr.status);
                    }
                    console.error('error code ' + xhr.status);
                    console.error('error context ' + xhr.responseText);
                }
                if (complete && typeof complete == "function") {
                    complete(xhr.status);
                }
            }
            else {//waiting
            }
        };
        xhr.timeout = timeout; // default 30s to timeout

        if(method == "POST") {
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.setRequestHeader("Cache-Control", "no-cache, must-revalidate");
            xhr.send(data);
        }else {
            xhr.send();
        }
    }

    var start_location = {
        lat: currentGeo['lat'],
        lon: currentGeo['lon']
    };

    function SubmitForm() {
        var data = {};
        data[config.request_type] = config.data_request_type.near_by;
        data[config.data_body] = {};

        data[config.data_body] = wrapFormToJson();
        var success = function (content) {
            var result = null;
            try {
                // console.log('form response: \n'+content);
                result = JSON.parse(content);
            }catch (err) {
                console.error(err);
                result = null;
            }
            try {
                createNearByTable(result);
                if (result && result['current_location']) {
                    start_location['lat'] = parseFloat(result['current_location']['lat']);
                    start_location['lon'] = parseFloat(result['current_location']['lon']);
                }else {
                    start_location['lat'] = currentGeo['lat'];
                    start_location['lon'] = currentGeo['lon'];
                }
                getCurrentGeo();
            }catch (err) {
                console.error(err)
            }
        };
        var complete = function (status) {
        };
        var errorHandler = function (status) {
            if (status == 0) {
                alert("nearby places information fetching timeout:(");
            }
            console.error('error code '+status+' from form submit');
        };
        ajaxCall(POST,PHP_SELF_URL, JSON.stringify(data), success, complete, true, errorHandler);
        return false;
    }

    function fetchPlaceDetails(place_name, place_id) {
        var data = {};
        data[config.request_type] = config.data_request_type.place_info;
        var data_body = {
            place_id: place_id
        };
        data[config.data_body] = data_body;
        submit_input.disabled = 'disabled';
        var success = function (content) {
            var response = null;
            try {
                console.log(content);
                response = JSON.parse(content);
            }catch (err) {
                console.error(err);
                response = null;
            }
            try {
                photo_reviews_display(place_name, response);
            } catch (err) {
                console.error(err)
            }
        };
        var complete = function (status) {
            submit_input.disabled = '';
        };
        var errorHandler = function (status) {
            if (status == 0) {
                alert("place details fetching timeout:(");
            }
            console.error('place details error '+status);
        };
        console.log(data);
        ajaxCall(POST,PHP_SELF_URL, JSON.stringify(data), success, complete, true, errorHandler);
    }

    function photo_reviews_display(name, response) {
        var html = document.createElement('div');
        html.id = 'photos-reviews-display';
        var title = document.createElement('h2');
        title.innerHTML = name;
        html.appendChild(title);

        var reviews = document.createElement('div');
        reviews.setAttribute('show', 'false');
        html.appendChild(reviews);

        var photos = document.createElement('div');
        photos.setAttribute('show', 'false');
        html.appendChild(photos);

        var show_reviews = document.createElement('div');
        show_reviews.id = 'show-reviews';
        var show_reviews_text = document.createElement('p');
        show_reviews_text.id = 'show-reviews-text';
        show_reviews_text.innerText = 'click to show reviews';
        var show_reviews_btn = document.createElement('img');
        show_reviews_btn.id = 'show-reviews-btn';
        show_reviews_btn.width = 40;
        show_reviews_btn.src = 'http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png';
        show_reviews.appendChild(show_reviews_text);
        show_reviews.appendChild(show_reviews_btn);
        reviews.appendChild(show_reviews);

        var show_photos = document.createElement('div');
        show_photos.id = 'show-photos';
        var show_photos_text = document.createElement('p');
        show_photos_text.id = 'show-photos-text';
        show_photos_text.innerText = 'click to show photos';
        var show_photos_btn = document.createElement('img');
        show_photos_btn.id = 'show-photos-btn';
        show_photos_btn.width = 40;
        show_photos_btn.src = 'http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png';
        show_photos.appendChild(show_photos_text);
        show_photos.appendChild(show_photos_btn);
        photos.appendChild(show_photos);

        if (response == null) {
            response = {'reviews':'no', 'photos':'no'};
        }

        var reviews_table = createReviewsTable(response['review_results'], response['reviews']);
        var photos_table = createPhotosTable(response['photo_results'], response['photos']);
        show_reviews_btn.onclick = function (ev) {
            clickReviewsList();
        };
        show_photos_btn.onclick = function (ev) {
            clickPhotosList();
        };

        display_section.innerText = '';
        display_section.appendChild(html);

        function clickPhotosList() {
            if(photos.getAttribute('show') == 'false') {
                photos.setAttribute('show', 'true');
                photos.appendChild(photos_table);
                show_photos_btn.src = 'http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png';
                show_photos_text.innerText = 'click to hide photos';
                if(reviews.getAttribute('show') != 'false') {
                    clickReviewsList();
                }
            }
            else {
                photos.setAttribute('show', 'false');
                photos.removeChild(photos_table);
                show_photos_btn.src = 'http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png';
                show_photos_text.innerText = 'click to show photos';
            }
        }

        function clickReviewsList() {
            if(reviews.getAttribute('show') == 'false') {
                reviews.setAttribute('show', 'true');
                reviews.appendChild(reviews_table);
                show_reviews_btn.src = 'http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png';
                show_reviews_text.innerHTML = 'click to hide reviews';
                if(photos.getAttribute('show') != 'false') {
                    clickPhotosList();
                }
            }
            else {
                reviews.setAttribute('show', 'false');
                reviews.removeChild(reviews_table);
                show_reviews_btn.src = 'http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png';
                show_reviews_text.innerHTML = 'click to show reviews';
            }
        }

        function createPhotosTable(photo_results, photos) {
            if (photo_results == 'no' || photos == null || photos.length == 0) {
                return createNoResult('No Photos Found');
            }
            var list = document.createElement('div');
            list.id = 'photo-table';
            for (var i = 0; i < photos.length; i++) {
                var url = photos[i]['photo_url'];
                var image_unit = createImageUnit(url);
                list.appendChild(image_unit);
            }
            return list;

            function createImageUnit(url) {
                var unit = document.createElement('div');
                unit.className = 'image-unit';
                var link = document.createElement('a');
                link.className = 'image-link';
                link.href = url+'?'+new Date().getTime();
                link.target = '_blank';
                var image = document.createElement('img');
                image.className = 'photo-image';
                image.src = url+'?'+new Date().getTime();
                image.width = 650;
                link.appendChild(image);
                unit.appendChild(link);
                return unit;
            }
        }

        function createReviewsTable(review_results, reviews) {
            if (review_results == 'no') {
                return createNoResult('No Reviews Found');
            };
            var list = document.createElement('div');
            list.id = 'review-table';
            for (var i = 0; i < reviews.length; i++) {
                var unit = createAuthorUnit(reviews[i]['author_name'], reviews[i]['profile_photo_url'], reviews[i]['text']);
                if (unit) {
                    list.appendChild(unit);
                }
            };
            if(list.children.length == 0) {
                return createNoResult('No Reviews Found');
            }
            return list;

            function createAuthorUnit(author_name, profile_url, text) {
                if (!author_name && !profile_url && !text) {
                    return null;
                }
                if (!author_name) {
                    author_name = 'Anonymous';
                }
                if (!profile_url) {
                    profile_url = ''
                }
                var unit = document.createElement('div');
                var profile = document.createElement('img');
                profile.src = profile_url;
                profile.className = 'profile-img';
                var name = document.createElement('h3');
                name.innerHTML = author_name;
                name.className = 'author-name';
                var text_elem = document.createElement('p');
                text_elem.innerHTML = text;
                var first_div = document.createElement('div');
                first_div.className = 'review-unit-person';
                var second_div = document.createElement('div');
                second_div.className = 'review-unit-text';
                first_div.appendChild(profile);
                first_div.appendChild(name);
                second_div.appendChild(text_elem);
                unit.appendChild(first_div);
                unit.appendChild(second_div);
                unit.className = 'review-unit';
                return unit;
            }
        }

        function createNoResult(text) {
            var html = document.createElement('div');
            html.className = 'no-result-tag';
            var text_html = document.createElement('h4');
            text_html.innerHTML = text;
            html.appendChild(text_html);
            return html;
        }
    }

    function isGoogleMapShowed() {
        if(document.getElementById("google-map") == null) {
            return false;
        }
        return true;
    }

    function removeGoogleMap() {
        var container = document.getElementById("google-map-container");
        if (container == null) {
            return;
        }
        container.parentElement.removeChild(container);
    }

    var preElement = null;

    function googleMapGeo(element, lat, lng) {
        if(isGoogleMapShowed() && preElement == element) {
            removeGoogleMap();
        }
        else if(isGoogleMapShowed()) {
            removeGoogleMap();
            element.parentElement.append(google_map_container);
            showMap(lat, lng);
        }
        else {
            element.parentElement.append(google_map_container);
            showMap(lat, lng);
        }
        preElement = element;
    }

    function createNearByTable(response) {

        //create table to html page
        if(response == null) {
            return null;
        }
        var html = null;
        try {
            var results = response['results'];
            if (results.length == 0) {
                html = noResultTable();
            }
            else {
                html = document.createElement('table');
                html.id = 'display-table';
                html.border = "2";
                html.appendChild(createHeader());
                html.appendChild(createTableBody(results));
            }
        } catch (err) {
            console.error(err);
            html = noResultTable();
        }
        display_section.innerText = '';
        display_section.appendChild(html);

        function createHeader() {
            var header = document.createElement('thead');
            var row = document.createElement('tr');
            for (var i = 0; i < config.table_headers.length; i++) {
                var header_elem = document.createElement('th');
                var text_elem = document.createElement('h3');
                text_elem.innerHTML = config.table_headers[i];
                header_elem.appendChild(text_elem);
                row.appendChild(header_elem);
            }
            header.appendChild(row);
            return header;
        }

        function createTableBody(results) {
            var info_obj_list = extractInfoList(results);
            var body = document.createElement('tbody');
            for (var i = 0; i < info_obj_list.length; i++) {
                body.appendChild(createTableBodyRow(info_obj_list[i]));
            }
            return body;
        }

        function createTableBodyRow(info_obj) {
            var row = null;
            if(!info_obj) {
                return row;
            }
            row = document.createElement('tr');
            row.appendChild(createIconElement(info_obj['icon']));
            row.appendChild(createNameElement(info_obj['name'], info_obj['place_id']));
            row.appendChild(createAddressElement(info_obj['vicinity'], info_obj['location']));
            return row;

            function createIconElement(icon_url) {
                var element = document.createElement('td');
                element.className = 'category-cell';
                var img_elem = document.createElement('img');
                img_elem.src = icon_url;
                element.appendChild(img_elem);
                return element;
            }

            function createNameElement(name, placeId) {
                var element = document.createElement('td');
                element.className = 'name-cell';
                var paragraph_elem = document.createElement('p');
                paragraph_elem.className = 'clickable';
                paragraph_elem.onclick = function () {
                    fetchPlaceDetails(name, placeId);
                };
                paragraph_elem.innerHTML = name;
                element.appendChild(paragraph_elem);
                return element;
            }

            function createAddressElement(vicinity, geo_obj) {
                var lat = geo_obj['lat'];
                var lng = geo_obj['lng'];
                var element = document.createElement('td');
                element.className = 'address-cell';
                var paragraph_elem = document.createElement('p');
                paragraph_elem.className = 'clickable';
                paragraph_elem.onclick = function (event) {
                    googleMapGeo(paragraph_elem, lat, lng);
                };
                paragraph_elem.innerHTML = vicinity;
                element.appendChild(paragraph_elem);
                return element;
            }
        }

        function extractInfoList(results) {
            var list = [];
            for(var i = 0; i < results.length; i++) {
                list.push(extractInfo(results[i]));
            }
            return list;
        }

        function extractInfo(result) {
            var obj = {};
            var keys = Object.keys(result);
            for (var i = 0; i < keys.length; i++) {
                switch (keys[i]){
                    case 'icon':
                        obj[keys[i]] = result[keys[i]];
                        break;
                    case 'name':
                        obj[keys[i]] = result[keys[i]];
                        break;
                    case 'vicinity':
                        obj[keys[i]] = result[keys[i]];
                        break;
                    case 'place_id':
                        obj[keys[i]] = result[keys[i]];
                        break;
                    case 'geometry':
                        if(result[keys[i]]['location']) {
                            obj['location'] = result[keys[i]]['location'];
                        }
                        break;
                    default:
                        break;
                }
            }
            return obj;
        }
    }

    function noResultTable() {
        var element = document.createElement("div");
        var text = document.createElement('p');
        text.innerHTML = "No Records has been found";
        element.id = 'no-record';
        element.appendChild(text);
        return element;
    }

    var directionsService;
    var directionsDisplay;
    var google_map_marker;
    var google_map_target;

    function initMap() {
        directionsService = new google.maps.DirectionsService();
        directionsDisplay = new google.maps.DirectionsRenderer();
        google_map_target = {
            lat: start_location['lat'],
            lng: start_location['lon']
        };
    }

    function showMap(lat, lng) {
        google_map_target['lat'] = parseFloat(lat);
        google_map_target['lng'] = parseFloat(lng);
        directionsService = new google.maps.DirectionsService();
        directionsDisplay = new google.maps.DirectionsRenderer();
        var map = new google.maps.Map(document.getElementById("google-map"), {
            zoom: 14,
            center: google_map_target
        });
        directionsDisplay.setMap(map);
        google_map_marker = new google.maps.Marker({
            position: google_map_target,
            map: map
        });
    }

    google_map_walk.addEventListener('click', function (ev) {
        calculateRoute(google_map_walk.value, function () {
            google_map_marker.setMap(null);
        }, function () {
            google_map_options.disabled = 'false';
        });
    });
    google_map_bike.addEventListener('click', function () {
        calculateRoute(google_map_bike.value,function(){
            google_map_marker.setMap(null);
        }, function () {
            google_map_options.disabled = 'false';
        });
    });
    google_map_drive.addEventListener('click', function () {
        calculateRoute(google_map_drive.value, function () {
            google_map_marker.setMap(null);
        }, function () {
            google_map_options.disabled = 'false';
        });
    });

    function calculateRoute(mode, success, complete) {
        var origin_position = {};
        origin_position['lat'] = start_location['lat'];
        origin_position['lng'] = start_location['lon'];
        directionsService.route({
            origin: origin_position,
            destination: google_map_target,
            travelMode: google.maps.TravelMode[mode]
        }, function (response, status) {
            if(status == 'OK') {
                directionsDisplay.setDirections(response);
                if(success && typeof success == 'function') {
                    success();
                }
            } else {
                console.error('Google maps directions request failed due to ' + status);
            }
            if (complete && typeof complete == 'function') {
                complete();
            }
        });
    }

</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfNgCQIzlNLQiTyVI7qJfpHsufihMtVjE&callback=initMap">
</script>
</body>
</html>

