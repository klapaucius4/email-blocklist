<?php

$blocklistFile = '../blocklist.json';
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

?>
