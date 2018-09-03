<?php
require __DIR__ . '/vendor/autoload.php';

/*
if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
*/

define("CREDENTIALS_PATH", "credentials/credentials.json");
define("TOKEN_PATH", "token.json");
define("APP_NAME", "MVCS - Google PHP API Service");


/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName(APP_NAME);
    $client->setScopes(Google_Service_Drive::DRIVE_METADATA);
    $client->setScopes(Google_Service_Drive::DRIVE_FILE);
    $client->setScopes(Google_Service_Drive::DRIVE);
    $client->setAuthConfig(CREDENTIALS_PATH);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    //$credentialsPath = 'token.json';

    if (file_exists(TOKEN_PATH)) {
        $accessToken = json_decode(file_get_contents(TOKEN_PATH), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }

        // Store the credentials to disk.
        if (!file_exists(dirname(TOKEN_PATH))) {
            mkdir(dirname(TOKEN_PATH), 0700, true);
        }
        file_put_contents(TOKEN_PATH, json_encode($accessToken));
        printf("Credentials saved to %s\n", TOKEN_PATH);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents(TOKEN_PATH, json_encode($client->getAccessToken()));
    }
    return $client;
}
