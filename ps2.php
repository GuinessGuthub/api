<?php
header('Content-Type: application/json');

$filename = 'list-ps2.json';

// Get the search query and limit from URL parameters
$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

// Check if the JSON file already exists
if (file_exists($filename)) {
    // Read the JSON file and decode its contents
    $fullData = json_decode(file_get_contents($filename), true);
} else {
    // Fetch the HTML content from the URL
    $url = "https://myrient.erista.me/files/Redump/Sony%20-%20PlayStation%202/";
    $html = file_get_contents($url);

    // Load the HTML content into DOMDocument
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    // Initialize an array to hold the full JSON data
    $fullData = [];

    // Get the table rows
    $rows = $doc->getElementById('list')->getElementsByTagName('tbody')->item(0)->getElementsByTagName('tr');

    // Loop through each row and extract data
    foreach ($rows as $row) {
        $cols = $row->getElementsByTagName('td');
        
        if ($cols->length == 3) {
            $linkElement = $cols->item(0)->getElementsByTagName('a')->item(0);
            $link = $url . $linkElement->getAttribute('href');
            $title = $linkElement->nodeValue;
            $size = $cols->item(1)->nodeValue;
            $date = $cols->item(2)->nodeValue;

            // Skip the 'Parent directory' row
            if (strpos($title, 'Parent directory') === false) {
                $fullData[] = [
                    'date' => $date,
                    'title' => $title,
                    'link' => $link,
                    'size' => $size
                ];
            }
        }
    }

    // Convert the full data to JSON format and save it to the file
    file_put_contents($filename, json_encode($fullData, JSON_PRETTY_PRINT));
}

// Initialize an array to hold the filtered JSON data
$jsonDataArray = [];

// Apply filters
foreach ($fullData as $entry) {
    if ($searchQuery === '' || stripos($entry['title'], $searchQuery) !== false) {
        $jsonDataArray[] = $entry;
    }

    // Check if limit is reached
    if ($limit > 0 && count($jsonDataArray) >= $limit) {
        break;
    }
}

// Output the filtered JSON data
echo json_encode($jsonDataArray, JSON_PRETTY_PRINT);
?>
