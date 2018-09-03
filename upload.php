<?php
require_once "drive_base.php";

/*
if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
*/

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

$fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => $_FILES["fileToUpload"]["name"],
    'parents' => array('12WY926dH27SFPpXgaxczkXyeW_64tvy0')));

$content = file_get_contents($_FILES['fileToUpload']['tmp_name']);

$file = $service->files->create($fileMetadata, array(
    'data' => $content,
    'mimeType' => 'application/octet-stream',
    'uploadType' => 'multipart',
    'fields' => 'id'));

$response = array('success' => false, 'message' => "error" );

if ($file->id != null)
    $response = array('success' => true, 'message' => "https://drive.google.com/file/d/". $file->id ."/view" );
    

echo json_encode($response);
exit;