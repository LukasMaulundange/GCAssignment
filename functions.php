<?php
function convertPayload($data) {
    // Date format conversion
    $arrival = DateTime::createFromFormat('d/m/Y', $data['Arrival'])->format('Y-m-d');
    $departure = DateTime::createFromFormat('d/m/Y', $data['Departure'])->format('Y-m-d');

    // Convert ages to age groups
    $guests = array_map(function($age) {
        return [
            "Age Group" => $age >= 12 ? "Adult" : "Child"
        ];
    }, $data['Ages']);

    // Use provided Unit Type ID or fall back to default
    $unitTypeId = $data['Unit Type ID'] ?? -2147483637;

    return [
        "Unit Type ID" => $unitTypeId,
        "Arrival" => $arrival,
        "Departure" => $departure,
        "Guests" => $guests
    ];
}

function callRemoteApi($convertedPayload) {
    $url = 'https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php';

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($convertedPayload)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return ['error' => 'Failed to connect to remote API'];
    }

    return json_decode($result, true);
}
?>
