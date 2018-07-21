<?php

$build_php_version = $argv[1];

$linter_bot_token = getenv('LINTER_TOKEN');
$repo_slug = getenv('TRAVIS_REPO_SLUG');
$pull_request = getenv('TRAVIS_PULL_REQUEST');
$linter_github_id = getenv('LINTER_USER_ID');

print "\nGetting Comments...\n\n";

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
    print "\nCould Not Deserialize Comments\n";
}

$linter_comment_urls = [];

// if the response was not an empty array get linters comments from the array
if (!empty($comments)) {
    foreach ($comments as $comment) {
        if ($comment->user->id == $linter_github_id) {
            $linter_comment_urls[] = $comment->url;
        }
    }
} else {
    print "\nNo Comments Were Found\n";
}

if (!empty($linter_comment_urls)) {
    print "\nDeleting Linter Comments...\n";
    foreach ($linter_comment_urls as $comment_url) {
        $handle = curl_init($comment_url);
        print "$comment_url";
        curl_setopt($handle, CURLOPT_HTTPHEADER, $auth);
        curl_setopt($handle, CURLOPT_HEADER, true);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));

        if ($http_code == 204) {
            print "\nDeleted Linter Comment\n";
        } else {
            print "\nUnable To Delete Comment\n";
        }
    }
} else {
    print "\nCould Not Find Any Linter Comments\n";
}

$apology = "The **Lint Wizard** redacts anything he may have said before! :sparkles::sparkles::sparkles:";
$apology .= "\n\n`Lint Wizard Approved` :heavy_check_mark: ";

$serialized_apology = json_encode(["body" => $apology]);
$apology_handle = curl_init("https://api.github.com/repos/" . $repo_slug . "/issues/" . $pull_request . "/comments");
curl_setopt($apology_handle, CURLOPT_HTTPHEADER, $auth);
curl_setopt($apology_handle, CURLOPT_HEADER, true);
curl_setopt($apology_handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($apology_handle, CURLOPT_POST, true);
curl_setopt($apology_handle, CURLOPT_POSTFIELDS, $serialized_apology);
$response = curl_exec($apology_handle);

$http_code = intval(curl_getinfo($apology_handle, CURLINFO_HTTP_CODE));
if ($http_code > 199 && $http_code < 300) {
    print "\nLint Wizard Apologized...\n";
} else {
    print "\nUnable to Apologize\n";
}

$serialized_label = json_encode(["Linter Approved"]);

$label_handle = curl_init("https://api.github.com/repos/" . $repo_slug . "/issues/" . $pull_request . "/labels");
curl_setopt($label_handle, CURLOPT_HTTPHEADER, $auth);
curl_setopt($label_handle, CURLOPT_HEADER, true);
curl_setopt($label_handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($label_handle, CURLOPT_POST, true);
curl_setopt($label_handle, CURLOPT_POSTFIELDS, $serialized_label);
$response = curl_exec($label_handle);

$http_code = intval(curl_getinfo($label_handle, CURLINFO_HTTP_CODE));

if ($http_code > 199 && $http_code < 300) {
    print "\nApplied Linter Label...\n";
} else {
    print "\nUnable to Apply Linter Label\n";
}
