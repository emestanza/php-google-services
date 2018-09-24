<?php
require_once "drive_base.php";

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

$fileName = $_FILES['fileToUpload']['name'];
$fileSize = $_FILES['fileToUpload']['size'];
$fileTmpName  = $_FILES['fileToUpload']['tmp_name'];
$fileType = $_FILES['fileToUpload']['type'];
//$fileExtension = strtolower(end(explode('.',$fileName)));

$data = $_POST['sender_information'];
$json_data = json_decode($data , true);
$folder = $json_data['folder'];

$file = new Google_Service_Drive_DriveFile(array(
  'name' => $fileName,
  'parents' => array($folder)
));

$chunkSizeBytes = 1 * 1024 * 1024;

// Call the API with the media upload, defer so it doesn't immediately return.
$client->setDefer(true);
$request = $service->files->create($file, array('fields' => 'id'));

// Create a media file upload to represent our upload process.
$media = new Google_Http_MediaFileUpload(
    $client,
    $request,
    $fileType,
    null,
    true,
    $chunkSizeBytes
);
$media->setFileSize($fileSize);

// Upload the various chunks. $status will be false until the process is complete.
$status = false;
$handle = fopen($fileTmpName, "rb");

while (!$status && !feof($handle)) {
  // read until you get $chunkSizeBytes from TESTFILE
  // fread will never return more than 8192 bytes if the stream is read buffered and it does not represent a plain file
  // An example of a read buffered file is when reading from a URL
  //$chunk = readVideoChunk($handle, $chunkSizeBytes);
  $resumeUri = $media->getResumeUri();
  $chunk = fread($handle, $chunkSizeBytes);
  $status = $media->nextChunk($chunk);

  /*if(!$status){ //nextChunk() returns 'false' whenever the upload is still in progress
    echo 'sucessfully uploaded file up to byte ' . $media->getProgress() . 
    ' which is ' . ( $media->getProgress() / $chunkSizeBytes ) . '% of the whole file';
  }*/

}

// The final value of $status will be the data from the API for the object
// that has been uploaded.
fclose($handle);

$result = false;
if ($status != false) {
  $result = $status;
  $response = array('success' => true, 'message' => "https://drive.google.com/file/d/". $result->id ."/view" );
  echo json_encode($response);
  exit;
}

$response = array('success' => false, 'message' => "error" );
echo json_encode($response);
//unlink($uploadPath);

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