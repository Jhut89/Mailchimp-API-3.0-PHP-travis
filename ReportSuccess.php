<?php

$build_php_version = $argv[1];

$linter_bot_token = getenv('LINTER_TOKEN');
$repo_slug = getenv('TRAVIS_REPO_SLUG');
$pull_request = getenv('TRAVIS_PULL_REQUEST');
