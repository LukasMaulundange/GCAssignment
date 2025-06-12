<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON input"]);
        exit;
    }

    $converted = convertPayload($input);
    $response = callRemoteApi($converted);

    $rate = null;
    $availability = 'Unavailable';

    if (!empty($response['Legs'][0])) {
        $firstLeg = $response['Legs'][0];
        $rate = $firstLeg['Effective Average Daily Rate'] ?? null;

        //  Determine availability based on Rooms
        $rooms = $response['Rooms'] ?? 0;
        $availability = $rooms > 0 ? 'Available' : 'Unavailable';
    }

    echo json_encode([
        "Unit Name" => $input["Unit Name"],
        "Rate" => $rate,
        "Availability" => $availability,
        "Date Range" => $input["Arrival"] . " to " . $input["Departure"],
        "Raw API Response" => $response
    ]);
}
?>
