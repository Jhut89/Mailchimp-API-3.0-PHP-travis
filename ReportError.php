<?php

$script_name = $argv[0];
$file_under_test = $argv[1];
$json_file_report = $argv[2];

$linter_bot_token = getenv('LINTER_TOKEN');
$repo_slug = getenv('TRAVIS_REPO_SLUG');
$pull_request = getenv('TRAVIS_PULL_REQUEST');

$file_report = json_decode($json_file_report);

if (!$file_report) {
    print "\nUNABLE TO DESERIALIZE REPORT\n";
    exit(1);
}

foreach ($file_report->files as $file => $report) {
    $comment = "**FILE:** `" . $file_under_test . "` :x:";
    foreach ($report->messages as $message) {
        $comment .= "\n" . "`line: " . $message->line;
        $comment .= " | message: " . $message->message . "`";
    }

    $request_params = ["body" => $comment];
    $serialized_params = json_encode($request_params);
    if (!$serialized_params) {
        print "\nUNABLE TO SERIALIZE MESSAGE\n";
        exit(1);
    }

    $auth = [
        "Authorization: token $linter_bot_token",
        "User-Agent: jhut89/Mailchimp-API-3.0-PHP (https://github.com/Jhut89/Mailchimp-API-3.0-PHP)"
    ];

    print "\nReporting linting errors to github for $file_under_test...\n";

    $ch = curl_init("https://api.github.com/repos/" . $repo_slug . "/issues/" . $pull_request . "/comments");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $serialized_params);
    $response = curl_exec($ch);

    $http_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

    if ($http_code > 199 && $http_code < 300) {
        print "\nSuccessfully reported linting errors for $file_under_test...\n";
    } else {
        print "\nUnable to report errors\n";
    }
}



