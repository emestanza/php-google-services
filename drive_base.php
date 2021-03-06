<?php
require __DIR__ . '/vendor/autoload.php';

define("CREDENTIALS_PATH", "credentials/credentials.json");
define("TOKEN_PATH", "token.json");
define("APP_NAME", "MVCS - Google PHP API Service");


/**
 * Retorna un cliente de API autorizado
 * @return Google_Client objeto cliente autorizado
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

    if (file_exists(TOKEN_PATH)) {
        $accessToken = json_decode(file_get_contents(TOKEN_PATH), true);
    } else {
        //Realiza petición al usuario para autorización 
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
