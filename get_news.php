<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// URL RSS Google News khusus topik Emas / XAUUSD & Suku Bunga Fed
$rssUrl = "https://news.google.com/rss/search?q=gold+price+XAUUSD+OR+fed+interest+rates&hl=en-US&gl=US&ceid=US:en";

// Atur User-Agent agar request dianggap dari browser biasa (mencegah blokir HTTP 403)
$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($rssUrl, false, $context);

if ($response === FALSE) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Gagal mengambil data dari RSS Feed"
    ]);
    exit();
}

// Convert format XML menjadi objek PHP
$xml = simplexml_load_string($response);
$articles = [];

if ($xml && isset($xml->channel->item)) {
    foreach ($xml->channel->item as $item) {
        $sourceName = (string) $item->source;
        if (empty($sourceName)) {
            $sourceName = "Market News";
        }

        $articles[] = [
            "title"    => (string) $item->title,
            "link"     => (string) $item->link,
            "pub_date" => (string) $item->pubDate,
            "source"   => $sourceName,
        ];
    }

    // Ambil 10 berita paling terbaru saja
    $articles = array_slice($articles, 0, 10);

    echo json_encode([
        "status" => "success",
        "data"   => $articles
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Format berita tidak sesuai"
    ]);
}
?>