<?php

/**
 * I'm the backend for the management tab IDs.
 *  
 * I am expecting to receive a POST request,
 * where the request data contains JSON data in the format:
 * `{ tab_id: tab_id }`.
 * 
 * Obviously, I should check that the request uses the POST method, and
 * that the request data is in the expected JSON data format.
 */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
    http_response_code(405);
    exit;
}

if (!array_key_exists('tab_id', $_POST)) {
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
    http_response_code(400);
    exit;
}

if (!is_string($_POST['tab_id'])) {
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422
    http_response_code(422);
    exit;
}
