<?php
require_once "drive_base.php";

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

$currentDir = getcwd();
$uploadDirectory = "/uploads/";

$fileName = $_FILES['fileToUpload']['name'];
$fileSize = $_FILES['fileToUpload']['size'];
$fileTmpName  = $_FILES['fileToUpload']['tmp_name'];
$fileType = $_FILES['fileToUpload']['type'];
$fileExtension = strtolower(end(explode('.',$fileName)));

$uploadPath = $currentDir . $uploadDirectory . uniqid() . ".". $fileExtension; 

  $file = new Google_Service_Drive_DriveFile(array(
    'name' => $fileName,
    'parents' => array('12WY926dH27SFPpXgaxczkXyeW_64tvy0')));

  $chunkSizeBytes = 1 * 1024 * 1024;
  //$chunkSizeBytes = 524288;
  
  // Call the API with the media upload, defer so it doesn't immediately return.
  $client->setDefer(true);
  $request = $service->files->create($file);

// Create a media file upload to represent our upload process.
$media = new Google_Http_MediaFileUpload(
    $client,
    $request,
    $fileType,
    null,
    true,
    $chunkSizeBytes
);
$media->setFileSize($_FILES['fileToUpload']['size']);

  // Upload the various chunks. $status will be false until the process is
  // complete.
  $status = false;
  $didUpload = move_uploaded_file($fileTmpName, $uploadPath);
  $handle = null;

  if ($didUpload){ 
      $handle = fopen($uploadPath, "rb");
  }

  while (!$status && !feof($handle)) {
    // read until you get $chunkSizeBytes from TESTFILE
    // fread will never return more than 8192 bytes if the stream is read buffered and it does not represent a plain file
    // An example of a read buffered file is when reading from a URL
    //$chunk = readVideoChunk($handle, $chunkSizeBytes);
    $chunk = fread($handle, $chunkSizeBytes);
    $status = $media->nextChunk($chunk);
  }
  // The final value of $status will be the data from the API for the object
  // that has been uploaded.
  $result = false;
  if ($status != false) {
    $result = $status;
  }
  fclose($handle);
  unlink($uploadPath);


function readVideoChunk ($handle, $chunkSize)
{
    $byteCount = 0;
    $giantChunk = "";
    while (!feof($handle)) {
        // fread will never return more than 8192 bytes if the stream is read buffered and it does not represent a plain file
        $chunk = fread($handle, 8192);
        $byteCount += strlen($chunk);
        $giantChunk .= $chunk;
        if ($byteCount >= $chunkSize)
        {
            return $giantChunk;
        }
    }
    return $giantChunk;
}