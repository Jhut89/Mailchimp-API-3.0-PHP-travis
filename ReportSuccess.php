<?php

$build_php_version = $argv[1];

$linter_bot_token = getenv('LINTER_TOKEN');
$repo_slug = getenv('TRAVIS_REPO_SLUG');
$pull_request = getenv('TRAVIS_PULL_REQUEST');
$linter_github_id = getenv('LINTER_USER_ID');

print "Getting Comments";

$auth = [
    "Authorization: token $linter_bot_token",
    "User-Agent: jhut89/Mailchimp-API-3.0-PHP (https://github.com/Jhut89/Mailchimp-API-3.0-PHP)"
];

$ch = curl_init("https://api.github.com/repos/" . $repo_slug . "/issues/" . $pull_request . "/comments");
curl_setopt($ch, CURLOPT_HTTPHEADER, $auth);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$serialized_comments = curl_exec($ch);

$comments = json_decode($serialized_comments);

if ($comments === false) {
    print "\nCOULD NOT DESERIALIZE COMMENTS\n";
}

$linter_comment_ids = [];

// if the response was not an empty array get linters comments from the array
if (!empty($comments)) {
    foreach ($comments as $comment) {
        if ($comment->user->id == $linter_github_id) {
            $linter_comment_ids[] = $comment->id;
        }
    }
} else {
    print "\nNO COMMENTS WERE FOUND\n";
}

if (!empty($linter_comment_ids)) {
    foreach ($linter_comment_ids as $comment_id) {
        $handle = curl_init("https://api.github.com/repos/" . $repo_slug . "/issues/" . $pull_request . "/comments/" . $comment_id);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($handle, CURLOPT_HEADER, true);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));

        if ($http_code == 204) {
            print "\nDELETED LINTER COMMENT\n";
        } else {
            print "\nUNABLE TO DELETE LINTER COMMENT\n";
        }
    }
} else {
    print "\nCOULD NOT FIND ANY LINTER COMMENTS\n";
}