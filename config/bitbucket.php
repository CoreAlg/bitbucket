<?php

return [
    "client_id" => env("BITBUCKET_CLIENT_ID"),
    "client_secret" => env("BITBUCKET_CLIENT_SECRET"),
    "user_account_name" => env("BITBUCKET_USER_ACCOUNT_NAME"),
    "repo_slug" => env("BITBUCKET_REPO_SLUG", ""),

    "dontCreateIssueFor" => [
        "The given data was invalid.",
        "The resource owner or authorization server denied the request.",
        "The user credentials were incorrect.",
        "Unauthenticated."
    ]
];