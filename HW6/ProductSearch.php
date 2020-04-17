<?php

define("BASE_URL_NEARBYSEARCH", "http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=YouzhiQu-qiegelaw-PRD-b16e5579d-34a883ad&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&paginationInput.entriesPerPage=20");
define("BASE_URL_ITEMDETAIL", "http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=YouzhiQu-qiegelaw-PRD-b16e5579d-34a883ad&siteid=0&version=967");
define("BASE_URL_SIMILAR", "http://svcs.ebay.com/MerchandisingService?OPERATION-NAME=getSimilarItems&SERVICE-NAME=MerchandisingService&SERVICE-VERSION=1.1.0&CONSUMER-ID=YouzhiQu-qiegelaw-PRD-b16e5579d-34a883ad&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $rest_json_str = file_get_contents("php://input");
    $rest_json = json_decode($rest_json_str, true);
    if(isset($rest_json)) {
        switch ($rest_json['request_type']){
            case 'nearbySearch':
                nearbySearch($rest_json['data']);
                break;
            case 'itemDetailSearch':
                itemDetailSearch($rest_json['data']);
                break;
            case 'similarItemsSearch':
                similarItemsSearch($rest_json['data']);
                break;
            default:
                break;
        }
    }
    
    exit(0);
}

function similarItemsSearch($request) {

    $response = '';
    if(isset($request)) {
        $url = BASE_URL_SIMILAR;
        if(isset($request['itemid'])) {
            $url .= "&itemId=".$request['itemid']."&maxResults=8";
            $original_response = file_get_contents($url);
            $response = json_decode($original_response, true);
            echo json_encode($response);
        }
    }
}

function itemDetailSearch($request) {
    $response = '';
    if(isset($request)) {
        $url = BASE_URL_ITEMDETAIL;
        if(isset($request['itemid'])) {
            $url .= "&ItemID=".$request['itemid']."&IncludeSelector=Description,Details,ItemSpecifics";
            $original_response = file_get_contents($url);
            $response = json_decode($original_response, true);
            echo json_encode($response);
        }
    }
}

function nearbySearch($request) {
    $response = '';
    if(isset($request)) {
        $itemFilter = 0;
        $keyword = urlencode($request['keyword']);
        $url = BASE_URL_NEARBYSEARCH."&keywords=".$keyword;
        if(isset($request['zip']) && isset($request['maxdistance'])){
            $postalcode = $request['zip'];
            $distance = $request['maxdistance'];
            $url .= "&buyerPostalCode=".$postalcode;
            $url .= "&itemFilter(".$itemFilter.").name=MaxDistance&itemFilter(".$itemFilter.").value=".$distance;
            $itemFilter += 1;
        }
        if(isset($request['category'])){
            $category = $request['category'];
            $url .= "&categoryId=".$category;
        }
        if(isset($request['condition'])){
            $condition_i = 0;//index
            $condition = $request['condition'];
            $url .= "&itemFilter(".$itemFilter.").name=Condition";
            if($condition['new'] == true){
                $url .= "&itemFilter(".$itemFilter.").value(".$condition_i.")=New";
                $condition_i += 1;
            }
            if($condition['used'] == true){
                $url .= "&itemFilter(".$itemFilter.").value(".$condition_i.")=Used";
                $condition_i += 1;
            }
            if($condition['unspecified'] == true){
                $url .= "&itemFilter(".$itemFilter.").value(".$condition_i.")=Unspecified";
                $condition_i += 1;
            }
            $itemFilter += 1;
        }
        if(isset($request['pickup'])) {
            if($request['pickup'] == true) {
                $url .= "&itemFilter(".$itemFilter.").name=LocalPickupOnly&itemFilter(".$itemFilter.").value=true";
                $itemFilter += 1;
            }
        }
        if(isset($request['freeshipping'])) {
            if($request['freeshipping'] == true) {
                $url .= "&itemFilter(".$itemFilter.").name=FreeShippingOnly&itemFilter(".$itemFilter.").value=true";
                $itemFilter += 1;
            }
        }
        
        $url .= "&itemFilter(".$itemFilter.").name=HideDuplicateItems&itemFilter(".$itemFilter.").value=true";

        $original_response = file_get_contents($url);
        $json_response = json_decode($original_response, true);      
    }
    echo json_encode($json_response);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product Search</title>
    <style>
    h3 {
            margin: 0 0 0 0;
        }
        #search_box{
            width: 700px;
            height: 330px;
            border: lightgray 3px solid;
            background-color: #f9f9f9;
            position: relative;
            margin: 2% auto 0;

            font-family: Times, Times New Roman, Georgia, serif;
        }
        #search_box_title{
            text-align: center;
            border-bottom: lightgray 2px solid;
            margin: 0 10px 0 10px;
            padding: 10px 0 10px 0;
            font-family: Times, Times New Roman, Georgia, serif;
            font-style: italic;
            font-size: 36px;
        }
        #search_box_entity {
            margin: 10px 10px 10px 10px;
        }
        .label {
            display: inline-block;
            margin: 0 0 0 0;
        }
        .search_box_part {
            display: inline-block;
        }
        
        option {
            width: 80px;
        }

       
        
        .form_combine {
            margin-top: 5px;
        }

        #display_section {
            margin-bottom: 300px;
            margin: auto;
            font-family: Times, Times New Roman, Georgia, serif;
        }
        
       
        .detail_table_img{
            max-height: 100%;
            max-width : 100%;
        }
        #item_detail_title {
            text-align: center;
            height: 40px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        #show_seller, #show_similar {
            text-align: center;
        }
        #show_seller_text,#show_similar_text{
            font-size: 20px;
            opacity:0.5;
        }
        #seller_iframe {
            width: 80%;
            overflow:hidden;
        }
        #similar_table{
            display:inline-block;
            float: left;
            width:300px;
            height: 350px;
        }
        #similar_table img{
            margin-top: 30px;
        }
        #similar_table p{
            width:300px;
            font-size: 16px;
        }
        #similar_table_price {
            font-weight:bold;
        }
        #similar_table_div {
            vertical-align: baseline;
            margin:auto; 
            width: 2400px;
            height:350px;
        }
        #similar_out_div {
            margin:auto; 
            width: 80%;
            overflow-x: scroll;
            overflow-y: hidden;
            height:350px;
        }
        #search_btn_group {
            text-align: center;
            margin-top: 20px;
        }
        #submit_btn {
            margin-left: 5px;
            margin-right: 5px;
        }
        #clear_btn {
            margin-left: 5px;
            margin-right: 5px;
        }
        #search_table, #search_table tr th, #search_table tr td {
            border: lightgray 2px solid;
        }
        #search_table{
            margin:auto;
            padding: 0; 
            font-family: Times, Times New Roman, Georgia;
            border-collapse: collapse;
        }

        #search_table td {
            margin: 0 0 0 5px; 
            padding: 0; 
            vertical-align: middle;
        }
        #search_table img {
            max-height: 100%;
            max-width : 100%; 
        }
        #detail_table, #detail_table tr th, #detail_table tr td {
            border: lightgray 2px solid;
        }
        #detail_table {
            width: 80%;
            margin:auto; 
            border-collapse: collapse;
            font-size: 20px;
        }
        #detail_table h4, #detail_table p{
            margin: 0 0 0 8px;
        }

        #similar_out_div {
            border: lightgray 2px solid;
        }
        #empty_result {
            height: 30px;
            width: 80%;
            border: lightgray 2px solid;
            background-color: #E3E3E3;
            font-size: 18px;
            
            margin:auto;
        }
        #empty_result p{
            margin:auto;
            text-align: center;
            vertical-align: middle;
        }
         #empty_seller_result {
            height: 30px;
            width: 80%;
            border: lightgray 2px solid;
            background-color: #E3E3E3;
            font-size: 18px;
            margin:auto;
        }
        #empty_seller_result p{
            font-weight:bold;
            margin:auto;
            text-align: center;
            vertical-align: middle;
        }
        #empty_similar_result {
            height: 30px;
            width: 80%;
            border: lightgray 2px solid;
            font-size: 18px;
            margin:auto;
        }
        #empty_similar_result p{
            font-weight:bold;
            margin:auto;
        }
        #condition_new, #condition_used, #condition_unspecified{
            margin-left: 20px;
        }
        #local_pickup, #free_shipping {
            margin-left: 50px;
        }
        #distance_input {
            margin-left: 50px;
            width: 100px;
        }
        .clickable {
            display: inline-block;
            transition: 0.4s;
            user-select: none;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .clickable:hover {
            cursor: pointer;
            color: darkgray;
        }
     </style>
</head>
<body>
<div id="search_box">
    <p id="search_box_title">Product Search</p>
    <form id="search_box_entity" onsubmit="return submitForm();">
        <div class="search_box_part">
            <div class="form_combine">
                <h4 class="label">Keyword</h4>
                <input id="keyword-input" name="keyword" required/>
            </div>
            <div class="form_combine">
                <h4 class="label">Category</h4>
                <select id="category_select" name="category">
                    <option selected="selected">All Categories</option>
                    <option value="550">Art</option>
                    <option value="2984">Baby</option>
                    <option value="267">Books</option>
                    <option value="11450">Clothing, Shoes & Accessories</option>
                    <option value="58058">Computers/Tablets & Networking</option>
                    <option value="26395">Health & Beauty</option>
                    <option value="11233">Music</option>
                    <option value="1249">Video Games & Consoles</option>
                </select>
            </div>
            <div class="form_combine">
                <h4 class="label">Condition</h4>
                <input type="checkbox" id="condition_new" name="condition" value="new">New
                <input type="checkbox" id="condition_used" name="condition" value="used">Used
                <input type="checkbox" id="condition_unspecified" name="condition" value="unspecified">Unspecified<br>
            </div>
            <div class="form_combine">
                <h4 class="label">Shipping Options</h4>
                <input type="checkbox" id="local_pickup" name="shippingOptions" value="localPickup">Local Pickup
                <input type="checkbox" id="free_shipping" name="shippingOptions" value="freeShipping">Free Shipping<br>
            </div>
            <div class="form_combine">
                <input type="checkbox" id="nearby_enable" name="nearbyOptions" value="nearbyEnable" onchange="enableLoc(this)"><h4 class="label">Enable Nearby Search</h4>
                <input id="distance_input" name="distance" disabled="disabled" placeholder="10"/>
                <h4 class="label" id="grey_mf" disabled="disabled">miles from</h4>
            </div>
            <br/>
            </div>
            <div class="search_box_part">
                <input id="location_here" type="radio" name="location_select" checked="checked" disabled="disabled" onchange="changeLocation(this)"/><h4 class="label" id="grey_hr" disabled="disabled" >Here</h4>
                <br/>
                <input id="location_loc" type="radio" name="location_select" disabled="disabled" onchange="changeLocation(this)">
                <input id="location_value" name="location" placeholder="zip code" disabled="disabled" required>
            </div>
            <div id="search_btn_group">
                <input id="submit_btn" value="Search" type="submit">
                <input id="clear_btn" type="button" value="Clear" onclick="clearForm()">
            </div>
    </form>
</div>
<br/>
<div id="display_section">
</div>
<script type="text/javascript">

    const GET = 'GET', POST = 'POST', PHP_SELF_URL = 'main.php', IP_API_URL = "http://ip-api.com/json";
    const   TABLE_HEADER = ["Index", "Photo","Name","Price","Zip code","Condition","Shipping Option"];
    const   keyword_input = document.getElementById('keyword-input'),
            category_select = document.getElementById('category_select'),
            distance_input = document.getElementById('distance_input'),
            condition_new = document.getElementById('condition_new'),
            condition_used = document.getElementById('condition_used'),
            condition_unspecified = document.getElementById('condition_unspecified'),
            location_here = document.getElementById("location_here"),
            location_loc = document.getElementById("location_loc"),
            local_pickup = document.getElementById("local_pickup"),
            free_shipping = document.getElementById("free_shipping"),
            nearby_enable = document.getElementById("nearby_enable"),

            location_value = document.getElementById("location_value"),
            display_section = document.getElementById("display_section"),
            submit_input = document.getElementById("submit_btn"),
            
            grey_mf = document.getElementById("grey_mf");
            grey_hr = document.getElementById("grey_hr");

     const config = {
        request_type: 'request_type',
        data_body: 'data',
        data_request_type: {
            nearby_Search: "nearbySearch",
            itemDetail_Search: "itemDetailSearch",
            similarItems_Search: "similarItemsSearch",
        }
    };
    grey_mf.style.opacity = 0.5;;
    grey_hr.style.opacity = 0.5;;

    var IP_ADDRESS_ZIP;
    getLocation();
    var similar_message;
    var seller_message;
    var show_similar_text;
    var show_similar_btn;
    var show_seller_text;
    var show_seller_btn;

function getLocation(loc) {
    var result = null;
    try{
        var xhr = new XMLHttpRequest();
        xhr.open(GET, IP_API_URL);
        xhr.send();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4){
                if (xhr.status == 200) {
                    var loc;
                    loc = JSON.parse(xhr.responseText);
                    if(loc.hasOwnProperty("zip")) {
                        IP_ADDRESS_ZIP = loc["zip"];
                    }
                }
            }
        };    
    }catch(err){
        console.log(err);
    }
}


function Call(method="GET", url, data, success, async = false, timeout=30000) {

        var xhr = new XMLHttpRequest();
        if (method != "GET" && method != "POST") {
            console.error('wrong http request method '+method);
            return;
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
            }
        };
        
        if(method == "POST") {
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.setRequestHeader("Cache-Control", "no-cache, must-revalidate");
            xhr.send(data);
        }else {
            xhr.send();
        }
        
        

}
function submitForm() {
    data = {};
    data[config.request_type] = config.data_request_type.nearby_Search;
    data[config.data_body] = {};
    data[config.data_body] = convertToJson();

    if(nearby_enable.checked && location_loc.checked){
        if(checkZipCode(data[config.data_body]['zip']) == false)
            return false;
    }
    var success = function (content) {
            var result = null;
            try {
                result = JSON.parse(content);
            }catch (err) {
                console.error(err);
                result = null;
            }
            createTable(result);
        };
    Call(POST,PHP_SELF_URL, JSON.stringify(data), success, true);
    return false;

    function checkZipCode(zip) {

        if(/^\d{5}$/.test(zip) == false){
            var section = document.createElement("div");   
            section.id = 'empty_result';
            var text = document.createElement('p');
            text.innerHTML = "Zipcode is invalid";
            section.appendChild(text);
            display_section.innerText = '';
            display_section.appendChild(section);
            return false;
        }      
        return true;
    }
}

function searchItemDetails(itemid) {
    data = {};
    data[config.request_type] = config.data_request_type.itemDetail_Search;
    data[config.data_body] = {
        itemid : itemid,
    };

    var success = function (content) {
            var result = null;
            try {
                result = JSON.parse(content);
            }catch (err) {
                console.error(err);
                result = null;
            }

            createDetail(result);
        };
    Call(POST,PHP_SELF_URL, JSON.stringify(data), success, true);
    return false;
}


function searchSimilarItems(itemid) {
    
    data = {};
    data[config.request_type] = config.data_request_type.similarItems_Search;
    data[config.data_body] = {
        itemid : itemid,
    };
    var success = function (content) {
            var result = null;
            try {
                result = JSON.parse(content);
            }catch (err) {
                console.error(err);
                result = null;
            }

            createSimilarItems(result);
        };
    Call(POST,PHP_SELF_URL, JSON.stringify(data), success, true);
    return false;
}

function createSimilarItems(content) {
    if(content == null) {
        return;
    }
    var result, html, title;
    try{
        var flag = true;
        if(content.hasOwnProperty("getSimilarItemsResponse")){
            if(content["getSimilarItemsResponse"].hasOwnProperty("ack")) {
                if(content["getSimilarItemsResponse"]["ack"] == "Success") {
                    if(content["getSimilarItemsResponse"].hasOwnProperty("itemRecommendations")) {
                        response = content["getSimilarItemsResponse"]["itemRecommendations"];
                        if(response.hasOwnProperty("item")){
                            result = response["item"];
                            if(result.length == 8){
                                flag = false;
                                similar_message = makeSimilarItemsTable(result);
                            }
                        }
                    }
                }
            }
        }
        if(flag) {
            html = createNoSimilarItems();
        }      
    }catch(err){
        console.error(err);
        html = createNoSimilarItems();
    }
}

function makeSimilarItemsTable(result) {
    var out = document.createElement('div');
    out.id = "similar_out_div";
    var div = document.createElement('div');
    div.id = "similar_table_div";
    for(var i = 0; i < result.length; i++) {
        div.appendChild(makeItemTable(result[i]));
    }
    out.appendChild(div);
    return out;
}

function makeItemTable(result) {
    var div = document.createElement('div');
    div.id = "similar_table";
    if(result.hasOwnProperty("imageURL")) {
        div.appendChild(makeItemImg(result["imageURL"]));
    }
    if(result.hasOwnProperty("title") && result.hasOwnProperty("itemId")) {
        div.appendChild(makeItemTitle(result["itemId"], result["title"]));
    }
    if(result.hasOwnProperty("buyItNowPrice")) {
        div.appendChild(makeItemPrice(result["buyItNowPrice"]));
    }
    return div;

    function makeItemImg(content) {
        var img = document.createElement('img');
        img.src = content;
        return img;
    }

    function makeItemTitle(id, content) {

        var p = document.createElement('p');
        p.className = 'clickable';
        p.onclick = function () {
            searchItemDetails(id);
        };
        p.appendChild(document.createTextNode(content));;
        return p;
    }

    function makeItemPrice(content) {
        var p = document.createElement('p');
        p.id ="similar_table_price";
        var price, sign ="$";
        if(content.hasOwnProperty("__value__")) {
            price = content["__value__"];
        }else {
            price = 0.00;
        }

        p.appendChild(document.createTextNode( sign + price ));

        return p;
    }
}

function createDetail(content) {

    if(content == null) {
        return;
    }
    var result, html, title, show, similar, table_content;
    var flag = true;
    try{
        if(content.hasOwnProperty("Ack")) {
            if(content["Ack"] == "Success") {
                if(content.hasOwnProperty("Item")) {
                    result = content["Item"];
                    if(result.length != 0){//create table
                        flag = false;
                        detail_content = convertDetail(result);
                        title = makeDetailTitle();
                        html = makeDetailTable(detail_content);
                        show = makeSellerMessage(detail_content);
                        similar = makeSimilarItems(detail_content);
                    }
                }
            }
        }
        if(flag) {
            html = createNoItemMessage();
        }
        
    }catch(err){
        console.error(err);
        html = createNoItemMessage();
    }
    if(flag) {
        display_section.innerText = '';
        display_section.appendChild(html);
    }else {
        display_section.innerText = '';
        display_section.appendChild(title);
        display_section.appendChild(html);
        display_section.appendChild(show);
        display_section.appendChild(similar);
    }
    
}

function makeSellerMessage(content) {

    var show_seller = document.createElement('div');
    show_seller.setAttribute('show', 'false');
    show_seller.id = 'show_seller';
    var show_seller_div = document.createElement('div');

    show_seller_text = document.createElement('p');
    show_seller_text.id = 'show_seller_text';
    show_seller_text.innerText = 'click to show seller message';
    show_seller_btn = document.createElement('img');
    show_seller_btn.id = 'show_seller_btn';
    show_seller_btn.width = 40;
    show_seller_btn.src = 'http://csci571.com/hw/hw6/images/arrow_down.png';

    show_seller_div.appendChild(show_seller_text);
    show_seller_div.appendChild(show_seller_btn);

    show_seller.appendChild(show_seller_div);
    var flag = false;
    if(content.hasOwnProperty("discription")) {
        if(content['discription'].length != 0) {
            flag = true;
            seller_message = createMessage(content['discription']);
        }
    }
    if(!flag) {
        seller_message = createNoSellerMessage();
    }
    
    show_seller_btn.onclick  = function() {
        if(show_seller.getAttribute('show') == 'false') {
            show_seller.setAttribute('show', 'true');
            show_seller_btn.src = 'http://csci571.com/hw/hw6/images/arrow_up.png';
            show_seller_text.innerText = 'click to hide seller message';
            if(content.hasOwnProperty("discription")) {
                show_seller.appendChild(seller_message);
                if(show_similar.contains(similar_message)){
                    show_similar.setAttribute('show', 'false');
                    show_similar_btn.src = 'http://csci571.com/hw/hw6/images/arrow_down.png';
                    show_similar_text.innerText = 'click to show similar items';
                    show_similar.removeChild(similar_message);
                }
            }
        }else {
            show_seller.setAttribute('show', 'false');
            show_seller_btn.src = 'http://csci571.com/hw/hw6/images/arrow_down.png';
            show_seller_text.innerText = 'click to show seller message';
            show_seller.removeChild(seller_message);
        }
    };
    return show_seller;

    function createMessage(content) {
        var message = document.createElement('iframe');
        message.id = "seller_iframe";
        message.frameBorder="0";
        message.onload = function() {
            var height =  message.contentWindow.document.documentElement.offsetHeight + 20;
            if(message.contentWindow.document.body.offsetHeight > height) {
                height = message.contentWindow.document.body.offsetHeight + 20;
            }
            message.style.height = height + 'px';
        };

        message.srcdoc = content;
        return message;
    }
}

function makeSimilarItems(content) {

    var show_similar = document.createElement('div');
    show_similar.setAttribute('show', 'false');
    show_similar.id = 'show_similar';

    var show_similar_div = document.createElement('div');
    show_similar_text = document.createElement('p');
    show_similar_text.id = 'show_seller_text';
    show_similar_text.innerText = 'click to show similar items';
    show_similar_btn = document.createElement('img');
    show_similar_btn.id = 'show_similar_btn';
    show_similar_btn.width = 40;
    show_similar_btn.src = 'http://csci571.com/hw/hw6/images/arrow_down.png';
    show_similar_div.appendChild(show_similar_text);
    show_similar_div.appendChild(show_similar_btn);
    show_similar.appendChild(show_similar_div);
    
    
    searchSimilarItems(content['itemid']);
    

    show_similar_btn.onclick  = function() {
        if(show_similar.getAttribute('show') == 'false') {
            show_similar.setAttribute('show', 'true');
            show_similar_btn.src = 'http://csci571.com/hw/hw6/images/arrow_up.png';
            show_similar_text.innerText = 'click to hide similar items';
            show_similar.appendChild(similar_message);
            if(show_seller.contains(seller_message)){
                show_seller.setAttribute('show', 'false');
                show_seller_btn.src = 'http://csci571.com/hw/hw6/images/arrow_down.png';
                show_seller_text.innerText = 'click to show seller message';
                show_seller.removeChild(seller_message);
            }
        }else {
            show_similar.setAttribute('show', 'false');
            show_similar_btn.src = 'http://csci571.com/hw/hw6/images/arrow_down.png';
            show_similar_text.innerText = 'click to show similar items';
            show_similar.removeChild(similar_message);
        }
    };
    return show_similar;
}

function makeDetailTitle() {
    var title = document.createElement('div');
    title.id = 'item_detail_title';
    var title_text = document.createElement('h1');
    title_text.innerText = 'Item Details';
    title.appendChild(title_text);
    return title;
}

function convertDetail(content) {
    var obj = {};
    var keys = Object.keys(content);
    for (var i = 0; i < keys.length; i++) {
        switch (keys[i]){
            case 'ItemID':
                obj["itemid"] = content[keys[i]];
                break;
            case 'Description':
                obj["discription"] = content[keys[i]];
                break;
            case 'PictureURL':
                obj["picture"] = content[keys[i]][0];
                break;
            case 'Title':
                obj["title"] = content[keys[i]];
                break;
            case 'Subtitle':
                obj["subtitle"] = content[keys[i]];
                break;
            case 'CurrentPrice':
                obj["price"] = content[keys[i]];
                break;
            case 'Location':
                obj["location"] = content[keys[i]];
                break;
            case 'Seller':
                obj["seller"] = content[keys[i]];
                break;
            case 'ReturnPolicy':
                obj["policy"] = content[keys[i]];
                break;
            case 'ItemSpecifics':
                obj["specifics"] = content[keys[i]];
                break;
            default:
                break;
        }
    }
    return obj;
}

function makeDetailTable(content) {
    var table = document.createElement('table');
    table.id = "detail_table";
    table.border = '0';
    var tableBody = document.createElement('tbody');
    if(content.hasOwnProperty("picture")) {
         tableBody.appendChild(makeDetailPicRow(content["picture"]));
    }
    if(content.hasOwnProperty("title")) {
         tableBody.appendChild(makeDetailNorRow("Title", content["title"]));
    }
    if(content.hasOwnProperty("subtitle")) {
         tableBody.appendChild(makeDetailNorRow("Subtitle", content["subtitle"]));
    }
    if(content.hasOwnProperty("price")) {
         tableBody.appendChild(makeDetailPriceRow(content["price"]));
    }
    if(content.hasOwnProperty("location")) {
         tableBody.appendChild(makeDetailNorRow("Location", content["location"]));
    }
    if(content.hasOwnProperty("seller")) {
        if(content["seller"].hasOwnProperty("UserID"))
            tableBody.appendChild(makeDetailNorRow("Seller", content["seller"]["UserID"]));
    }
    if(content.hasOwnProperty("policy")) {
        if(content["policy"].hasOwnProperty("ReturnsWithin")) {
            var policy = "Returns Accepted within " + content["policy"]["ReturnsWithin"];
            tableBody.appendChild(makeDetailNorRow("Return Policy(US)", policy));
        }
    }
    if(content.hasOwnProperty("specifics")) {
        if(content["specifics"].hasOwnProperty("NameValueList")) {
            makeDetailSpeRow(tableBody, content["specifics"]["NameValueList"]);
        }
    }
    table.appendChild(tableBody);
    return table;
}

function makeDetailNorRow(name, content) {
    var row = document.createElement('tr');
    var left_td = document.createElement('td');
    var h4 = document.createElement('h4');
    h4.appendChild(document.createTextNode(name));
    left_td.appendChild(h4);
    var right_td = document.createElement('td');
    var p = document.createElement('p');
    p.innerHTML = content;
    right_td.appendChild(p);
    row.appendChild(left_td);
    row.appendChild(right_td);
    return row;
}

function makeDetailSpeRow(tbody, content) {
    for(var i = 0; i < content.length; i++) {
        if(content[i].hasOwnProperty("Name") && content[i].hasOwnProperty("Value"))
            tbody.appendChild(makeDetailNorRow(content[i]["Name"], content[i]["Value"]));
    }
}

function makeDetailPicRow(content) {
    var row = document.createElement('tr');
    var left_td = document.createElement('td');
    var h4 = document.createElement('h4');
    h4.appendChild(document.createTextNode("Photo"));
    left_td.appendChild(h4);
    var right_td = document.createElement('td');
    right_td.style.height = "200px";
    var img = document.createElement('img');
    img.className = "detail_table_img";
    img.src = content;
    right_td.appendChild(img);
    row.appendChild(left_td);
    row.appendChild(right_td);
    return row;
}

function makeDetailPriceRow(content) {
    var row = document.createElement('tr');
    var left_td = document.createElement('td');
    var h4 = document.createElement('h4');
    h4.appendChild(document.createTextNode("Price"));
    left_td.appendChild(h4);
    var right_td = document.createElement('td');
    var price;
    if(content.hasOwnProperty("Value")) {
        price = content["Value"] + " ";
    }else {
        price = 0.00 + " ";
    }
    if(content.hasOwnProperty("CurrencyID")) {
        price = price + content["CurrencyID"];
    }else {
        price = price + " USD";
    }
    var p = document.createElement('p');
    p.innerHTML = price;
    right_td.appendChild(p);
    row.appendChild(left_td);
    row.appendChild(right_td);
    return row;
}

function createTable(content) {
    if(content == null) {
        return;
    }
    var result, html, table_content;
    try{
        var flag = true;
        if(content["findItemsAdvancedResponse"]["0"]["ack"]["0"] == "Success") {
            if(content["findItemsAdvancedResponse"]["0"].hasOwnProperty("searchResult")) {
                if(content["findItemsAdvancedResponse"]["0"]["searchResult"]["0"].hasOwnProperty("item")) {
                    result = content["findItemsAdvancedResponse"]["0"]["searchResult"]["0"]["item"];
                    if(result.length != 0){//create table
                        flag = false;
                        table_content = convertToContent(result);
                        html = makeTable(table_content);
                    }
                }
            }
        }
        if(flag) {
            html = createNoResult();
        }
        
    }catch(err){
        console.error(err);
        html = createNoResult();
    }
    display_section.innerText = '';
    display_section.appendChild(html);

    function makeTable(content) {
        var table = document.createElement('table');
        table.id = "search_table";
        var header = document.createElement('tr');
        for(var h = 0; h < TABLE_HEADER.length; h++) {
            var theader = document.createElement('th');
            theader.innerHTML = TABLE_HEADER[h];
            header.appendChild(theader);
        }
        var tableBody = document.createElement('tbody');

        for (var i = 0; i < content.length; i++) {
            var row;
            tableBody.appendChild(makeTableRow(content[i]));
        }
        table.appendChild(header);
        table.appendChild(tableBody);
        return table;
    }

    function makeTableRow(content) {
        if(content == null)
            return null;
        var row = document.createElement('tr');
        row.appendChild(createNormalElement(content['index']));
        row.appendChild(createPhotoElement(content['photo']));
        row.appendChild(createNameElement(content['name'],content['itemid']));
        row.appendChild(createPriceElement(content['price']));
        row.appendChild(createNormalElement(content['postalCode']));
        row.appendChild(createConditionElement(content['condition']));
        row.appendChild(createShippingElement(content['shippingInfo']));

        return row;
    }

    function createNameElement(itemname, itemid) {
        var td = document.createElement('td');
        //td.className = 'item_name';
        var p = document.createElement('p');
        p.className = 'clickable';
        p.onclick = function () {
            searchItemDetails(itemid);
        };
        p.innerHTML = itemname;
        td.appendChild(p);
        return td;
    }

    function createNormalElement(element) {
        var td = document.createElement('td');
        if(!element){
            td.appendChild(document.createTextNode("N/A"));
        }else {
            td.appendChild(document.createTextNode(element));
        }
        return td;
    }

    function createConditionElement(Info) {
        var td = document.createElement('td');
        if(!Info){
            td.appendChild(document.createTextNode("N/A"));
        }else{
            if(Info.hasOwnProperty("conditionDisplayName")) {
                var condition = Info["conditionDisplayName"][0];
                td.appendChild(document.createTextNode(condition));
            }else {
                td.appendChild(document.createTextNode("N/A"));
            }
        }  
        return td;
    }

    function createPriceElement(Info) {
        var td = document.createElement('td');
        if(Info.hasOwnProperty("currentPrice")) {
            var server = Info["currentPrice"][0];
            if(server.hasOwnProperty("__value__")){
                var cost = server["__value__"];
                if(cost != null) {
                    var price = "$" + cost;
                    td.appendChild(document.createTextNode(price));
                }else {
                    td.appendChild(document.createTextNode("$0.00"));
                }
            }else {
                td.appendChild(document.createTextNode("$0.00"));
            }
        }else {
            td.appendChild(document.createTextNode("$0.00"));
        } 
        return td;
    }

    function createPhotoElement(url) {
        var td = document.createElement('td');
        var img = document.createElement('img');
        img.src = url;
        td.appendChild(img);
        return td;
    }
    function createShippingElement(Info) {
        var td = document.createElement('td');
        if(Info.hasOwnProperty("shippingServiceCost")) {
            var server = Info["shippingServiceCost"][0];
            if(server.hasOwnProperty("__value__")){
                var cost = server["__value__"];
                if(cost == 0) {
                    td.appendChild(document.createTextNode("Free Shipping"));
                }else {
                    var price = "$" + cost;
                    td.appendChild(document.createTextNode(price));
                }
            }else {
                 td.appendChild(document.createTextNode("N/A"));
            }
        }else {
            td.appendChild(document.createTextNode("N/A"));
        }       
        return td;
    }
    function convertToContent(results) {
        var list = [];
        for(var i = 0; i < results.length; i++) {
            list.push(extractInfo(results[i], i+1));
        }
        return list;
    }
    function extractInfo(result, index) {
        var obj = {};
        obj["index"] = index;
        var keys = Object.keys(result);
        for (var i = 0; i < keys.length; i++) {
            switch (keys[i]){
                case 'itemId':
                    obj["itemid"] = result[keys[i]][0];
                    break;
                case 'galleryURL':
                    obj["photo"] = result[keys[i]][0];
                    break;
                case 'sellingStatus':
                    obj["price"] = result[keys[i]][0];
                    break;
                case 'title':
                    obj["name"] = result[keys[i]][0];
                    break;
                case 'postalCode':
                    obj[keys[i]] = result[keys[i]][0];
                    break;
                case 'condition':
                    obj[keys[i]] = result[keys[i]][0];
                    break;
                case 'shippingInfo':
                    obj["shippingInfo"] = result[keys[i]][0];
                    break;
                default:
                    break;
            }
        }
        return obj;
    }
}

function createNoResult() {
    var section = document.createElement("div");   
    section.id = 'empty_result';
    var text = document.createElement('p');
    text.innerHTML = "No Records has been found";
    section.appendChild(text);
    return section;
}

function createNoSellerMessage() {
    var section = document.createElement("div");   
    section.id = 'empty_result';
    var text = document.createElement('p');
    text.innerHTML = "No Seller Message Found";
    section.appendChild(text);
    seller_message = section;
    return section;
}
function createNoItemMessage() {
    var section = document.createElement("div");   
    section.id = 'empty_result';
    var text = document.createElement('p');
    text.innerHTML = "Item Detail Not Found";
    section.appendChild(text);
    seller_message = section;
    return section;
}

function createNoSimilarItems() {
    var section = document.createElement("div");   
    section.id = 'empty_similar_result';
    var text = document.createElement('p');
    text.innerHTML = "No Similar ItemsFound";
    section.appendChild(text);
    similar_message = section;
    return section;
}

function convertToJson() {
    var requestEntity = {};
    if(nearby_enable.checked) {
        if(distance_input.value == ""){
            requestEntity["maxdistance"]= 10;
        }else {
            requestEntity["maxdistance"] = distance_input.value;
        }
        if(location_here.checked) {
            requestEntity["zip"] = IP_ADDRESS_ZIP;
        }else {
            requestEntity["zip"] = location_value.value;
        }
    }
    
    requestEntity["keyword"] = keyword_input.value;
    if(!isNaN(category_select.value)) {
        requestEntity["category"] = category_select.value;
    }
    if(condition_new.checked || condition_used.checked || condition_unspecified.checked) {
        requestEntity["condition"] = {
            new : condition_new.checked, 
            used : condition_used.checked, 
            unspecified : condition_unspecified.checked };
    }
    if(local_pickup.checked){
        requestEntity["pickup"] = local_pickup.value;
    }
    if(free_shipping.checked){
        requestEntity["freeshipping"] = free_shipping.value;
    }
    return requestEntity;
}

function setNearbyDisable(initial) {
    if(initial == true) {
        location_value.disabled = "disabled";
        location_here.disabled = "disabled";
        location_loc.disabled = "disabled";
        distance_input.disabled = "disabled";
        grey_mf.style.opacity = 0.5;
        grey_hr.style.opacity = 0.5;
    }else {
        distance_input.disabled = "";
        grey_mf.style.opacity = 1;
        location_here.disabled = "";
        location_loc.disabled = "";
        if(location_loc.checked == true) {
            grey_hr.style.opacity = 0.5;
            location_value.disabled = "";
        }else {
            grey_hr.style.opacity = 1;
            location_value.disabled = "disabled";
        }
    } 
}

function enableLoc(radio) {
    if(radio.checked == true) {
        setNearbyDisable(false);
    }else{
        setNearbyDisable(true);
    }
}

function changeLocation(radio) {

    if(location_loc == radio) {
        location_value.disabled = "";
        grey_hr.style.opacity = 0.5;
    }else {
        location_value.disabled = "disabled";
        location_value.value = "";
        grey_hr.style.opacity = 1;
    }
}

function clearForm() {//change to default setting
    setNearbyDisable(true);
    display_section.innerText = '';
    submit_input.disabled = false;
    document.getElementById("search_box_entity").reset();
}
</script>
</body>
</html>
