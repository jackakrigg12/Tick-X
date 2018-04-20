<?php
/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 11/04/2018
 */


// OK, start off by getting the raw page mark up
$curl = curl_init("https://www.tickx.co.uk/manchester/gigs/");

curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

$page = curl_exec($curl);

// Where there any errors?
if(curl_errno($curl)) {
    echo 'Error:' . curl_error($curl);
    exit;
}

curl_close($curl);



// Now find the necessary info
$OverallEventInfo = array();


// Event ID
$regex = '/id="event[0-9]+/s';
preg_match_all($regex, $page, $list);
$list = $list[0];
foreach ($list as $key => $value) {
    $OverallEventInfo[$key]['event_id'] = substr($value, 9);
}


// Event title
$regex = '/<div class="listing_event_title">(.*?)<\/div>/s';
preg_match_all($regex, $page, $list);
$list = $list[0];
foreach ($list as $key => $value) {
    $OverallEventInfo[$key]['event_title'] = strip_tags($value);
}



// Event time
$regex = '/<div class="listing_event_showtimes">(.*?)<\/div>/s';
preg_match_all($regex, $page, $list);
$list = $list[0];
foreach ($list as $key => $value) {
    $OverallEventInfo[$key]['event_time'] = strip_tags($value);
}


// Event venue
$regex = '/<div class="listing_event_venue">(.*?)<\/div>/s';
preg_match_all($regex, $page, $list);
$list = $list[0];
foreach ($list as $key => $value) {
    $OverallEventInfo[$key]['event_venue'] = strip_tags($value);
}


// Event picture
$regex = '/<div class="listing_event_pic"(.*?)<\/div>/s';
preg_match_all($regex, $page, $list);
$list = $list[0];

// Picture url regex
$pattern = '/http(.*).(jpg|png)/';
foreach ($list as $key => $value) {
    preg_match($pattern, $value, $matches);
    $OverallEventInfo[$key]['event_picture'] = $matches[0];
}



// Only show twenty records - do this now to prevent redundant scrapes in loop
$OverallEventInfo = array_slice($OverallEventInfo, 0, 20);


// Loop through each event and get the specific event info
foreach($OverallEventInfo as $key => $value){


    $event_curl = curl_init("https://www.tickx.co.uk/event/".$value['event_id']);

    curl_setopt($event_curl, CURLOPT_RETURNTRANSFER, TRUE);

    $event_page = curl_exec($event_curl);

    // Where there any errors?
    if(curl_errno($event_curl)) {
        echo 'Error:' . curl_error($event_curl);
        exit;
    }

    curl_close($event_curl);


    // Event Description
    $regex = '/<div class="event-details__content-text">(.*?)<\/div>/s';
    preg_match($regex, $event_page, $list);
    $list = $list[0];
    $list = preg_replace('/\s+/', ' ', $list);
    $OverallEventInfo[$key]['event_description'] = strip_tags($list);


    /**************************************************************
     *  Missing info
     *
     *  2. Event Date
     *  6. Array of ticket prices available (bonus points for getting ticket seller and buy link as well)
     *
     */

} // End loop through event


//echo '<pre>'.print_r($OverallEventInfo,1).'</pre>';

// return the scraped information as a well formatted JSON string.
echo json_encode($OverallEventInfo);