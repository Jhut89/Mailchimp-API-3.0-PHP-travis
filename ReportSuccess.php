<?php

$build_php_version = $argv[1];

$linter_bot_token = getenv('LINTER_TOKEN');
$repo_slug = getenv('TRAVIS_REPO_SLUG');
$pull_request = getenv('TRAVIS_PULL_REQUEST');

print "Getting Comments";

$auth = [
    "Authorization: token $linter_bot_token",
    "User-Agent: jhut89/Mailchimp-API-3.0-PHP (https://github.com/Jhut89/Mailchimp-API-3.0-PHP)"
];

$ch = curl_init("https://api.github.com/repos/" . $repo_slug . "/issues/" . $pull_request . "/comments");
curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

var_dump($response);