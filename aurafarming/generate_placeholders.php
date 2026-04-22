<?php
// generate_placeholders.php

$images = [
    "tomato.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/8/89/Tomato_je.jpg/800px-Tomato_je.jpg",
    "potato.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/Patates.jpg/800px-Patates.jpg",
    "onion.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/2/25/Onion_on_White.JPG/800px-Onion_on_White.JPG",
    "carrot.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/13-08-31-wien-redaktion-Europa3-87.jpg/800px-13-08-31-wien-redaktion-Europa3-87.jpg",
    "chilli.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Green_chilli_peppers.jpg/800px-Green_chilli_peppers.jpg",
    "mango.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/Hapus_Mango.jpg/800px-Hapus_Mango.jpg",
    "rice.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/White_rice.jpg/800px-White_rice.jpg",
    "default.png" => "https://upload.wikimedia.org/wikipedia/commons/thumb/2/23/Market_vegetables.jpg/800px-Market_vegetables.jpg"
];

foreach ($images as $filename => $url) {
    // Setting a user agent is sometimes required by Wikimedia
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) FarmDirect/1.0\r\n'
        ]
    ];
    $context = stream_context_create($options);
    $content = file_get_contents($url, false, $context);
    if ($content !== false) {
        file_put_contents(__DIR__ . '/assets/images/' . $filename, $content);
        echo "Downloaded $filename\n";
    } else {
        echo "Failed to download $filename\n";
    }
}
?>
