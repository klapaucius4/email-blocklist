<?php

$blocklistFile = '../blocklist.json';
$metaFile = '../blocklist-meta.json';
$blocklist = json_decode(file_get_contents($blocklistFile), true);

if ($blocklist === null) {
    exit;
}

$domainsFile = 'domains.txt';
$domains = file($domainsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if ($domains === false) {
    exit;
}

foreach ($domains as $domain) {
    if (!in_array($domain, $blocklist)) {
        $blocklist[] = $domain;
    }
}

sort($blocklist);

file_put_contents($blocklistFile, json_encode($blocklist));

$metaData = json_decode(file_get_contents($metaFile), true);

if ($metaData !== null) {
    $metaData['utc_time_of_last_update'] = gmdate('Y-m-d H:i:s');
    $metaData['blocklist_version'] += 1;
} else {
    $metaData = [
        'utc_time_of_last_update' => gmdate('Y-m-d H:i:s'),
        'blocklist_version' => 1
    ];
}

file_put_contents($metaFile, json_encode($metaData));

?>
