<?php

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
    <p id="search-box-title">Product Search</p>
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
</body>
</html>
