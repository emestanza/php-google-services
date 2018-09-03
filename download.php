<?php
require_once "drive_base.php";

function getFileInfo($service, $fileId) {
    try {
      $file = $service->files->get($fileId);
      return $file;

    } catch (Exception $e) {
      print "An error occurred: " . $e->getMessage();
    }
  }

/*
if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}
*/

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);
$fileId = $_GET["id"];
$file = getFileInfo($service, $fileId);

header('Content-Type: application/octet-stream');
$fileName = $file->name;

header("Content-Disposition: attachment; filename='".basename($fileName)."'");
$response = $service->files->get($fileId, 
    array('alt' => 'media')
);
$content = $response->getBody()->getContents();
echo $content;
exit;