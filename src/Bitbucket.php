<?php

namespace CoreAlg;

use CoreAlg\Curl;
use Log;
use Exception;

class Bitbucket
{
    private $host = "https://bitbucket.org";
    private $api = "https://api.bitbucket.org/2.0";

    protected $curl;

    protected $bitbucketClientId;
    protected $bitbucketClientSecret;
    protected $bitbucketUserAccountName;
    protected $bitbucketRepoSlug;
    protected $environment;
    private $dontCreateIssueFor;

    public function __construct()
    {
        $this->bitbucketClientId = config("bitbucket.client_id");
        $this->bitbucketClientSecret = config("bitbucket.client_secret");
        $this->bitbucketUserAccountName = config("bitbucket.user_account_name");
        $this->bitbucketRepoSlug = config("bitbucket.repo_slug");
        $this->environtment = strtoupper(config("app.env"));

        $this->dontCreateIssueFor = config("bitbucket.dontCreateIssueFor");

        $this->curl = new Curl();
    }

    public function createIssue($exception)
    {
        if ($this->environtment === "LOCAL") {
            return false;
        }

        try {

            // get exception title
            $title = trim($exception->getMessage());

            // check the error title exists in $this->dontCreateIssueFor list
            if (is_null($title) === true || empty($title) || $title === "" || in_array($title, $this->dontCreateIssueFor)) {
                return false;
            }

            // get access token
            $access_token = $this->getAccessToken();

            // check access token
            if (empty($access_token)) {
                Log::info("BitbucketService: no access token.");
                return false;
            }

            // endpoint to create issue
            $endpoint = "{$this->api}/repositories/{$this->bitbucketUserAccountName}/{$this->bitbucketRepoSlug}/issues?access_token={$access_token}";


            // preparing request payload
            $requestUrl = request()->url();

            $requestPayloadArray = [
                "network_ip" => request()->getClientIp(),
                "user_agent" => request()->Header('User-Agent'),
                "request" => request()->all()
            ];

            $requestPayload = json_encode($requestPayloadArray);

            $userPayloadArray = [];

            // preparing user payload for authorized user
            if (auth()->check() === true) {
                $currentUser = auth()->user();

                $userPayloadArray = [
                    "id" => $currentUser->id,
                    "email" => $currentUser->email
                ];
            }

            $userPayload = json_encode($userPayloadArray);

            // preparing issue content
            $content = "
### Error: {$title}
###### Location:
{$exception->getFile()}:{$exception->getLine()}
###### Request URL:
{$requestUrl}
###### Request Payload:
`{$requestPayload}`
###### User Payload:
`{$userPayload}`
";

            // preparing issue payload
            $data = [
                "title" => "{$title} [{$this->environtment}]",
                "content" => [
                    "raw" => $content,
                    "markup" => "markdown"
                ],
                "kind" => "bug",
                "priority" => "major",
            ];

            // preparing authorization header
            $options = [
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "Authorization: Bearer {$access_token}"
                ]
            ];

            // let's create issue
            $response = $this->curl->post($endpoint, $data, $options);

            if (in_array($response["code"], [200, 201])) {
                return true;
            } else {
                Log::info("Failed response from bitbucket issue creator: " . json_enocde($response));
                return false;
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    private function getAccessToken()
    {
        $endpoint = "{$this->host}/site/oauth2/access_token";

        $options = [
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_USERPWD => "{$this->bitbucketClientId}:{$this->bitbucketClientSecret}",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded"
            ]
        ];

        $response = $this->curl->post($endpoint, [], $options);

        return $response["data"]["access_token"] ?? "";
    }
}