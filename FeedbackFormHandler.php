<?php
/**
 * Created by PhpStorm.
 * User: pat
 * Date: 12/13/17
 * Time: 10:24 AM
 */

include(dirname(__FILE__) . "/FeedbackAnswer.php");

ini_set('display_errors', '1');

//Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}

/*
//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    throw new Exception('Content type must be: application/json');
}
*/

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if(!is_array($decoded)){
    throw new Exception('Received content contained invalid JSON!');
}

// Get the components from the POSTed object.
$reference_id = $decoded['reference_id'];
$permission = $decoded['permission'];
$data = $decoded['data'];

//If the data object isn't in the shape we expect, that's a problem.
if(!is_array($data)){
    throw new Exception('Invalid data.');
}

//Process the JSON.
$answers = array();
foreach ($data as $question_id => $values) {
    // Perform data checks.
    if(!is_array($values)){
        throw new Exception(("The values for question '{$question_id}' must include 'rating' and a 'comment'."));
    }
    if(!is_null($values['rating']) && !is_numeric($values['rating'])){
        throw new Exception("The rating for question '{$question_id}' is not numeric.");
    }
    // It looks as though we're good to go.
    array_push($answers, new \StearnsConnect\FeedbackAnswer($question_id, $values['rating'], $values['comment']));
}

// ---------------------------------------------------------------------------//
// TODO: Push the data through to SalesForce.                                 //
// ---------------------------------------------------------------------------//

// Prepare our response.
// Simplify the answer objects.
$answer_arrays = array();
foreach($answers as $answer){
    array_push($answer_arrays, $answer->toArray());
}
// Create the response object.
$response = array(
    'success'=>true,
    'reference_id'=>$reference_id,
    'data'=>$answer_arrays,
    'permission'=>$permission);
// Format it.
$response_json = json_encode($response);
// That's that!
header('Content-type: application/json');
echo $response_json;


