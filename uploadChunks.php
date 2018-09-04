<?php
require_once "drive_base.php";

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

  /************************************************
   * We'll setup an empty 20MB file to upload.
   ************************************************/
  DEFINE("TESTFILE", 'files/How_to_Change_the_World_v1.01_A4.pdf');
  if (!file_exists(TESTFILE)) {
    $fh = fopen(TESTFILE, 'w');
    fseek($fh, 1024*1024*95);
    fwrite($fh, "!", 1);
    fclose($fh);
  }
  else{
      echo "bien";
  }

  $file = new Google_Service_Drive_DriveFile(array(
    'name' => "Big File",
    'parents' => array('12WY926dH27SFPpXgaxczkXyeW_64tvy0')));

  //$file->name = "Big File";
  $chunkSizeBytes = 1 * 1024 * 1024;

  // Call the API with the media upload, defer so it doesn't immediately return.
  $client->setDefer(true);
  $request = $service->files->create($file);

// Create a media file upload to represent our upload process.
$media = new Google_Http_MediaFileUpload(
    $client,
    $request,
    'application/pdf',
    null,
    true,
    $chunkSizeBytes
);
$media->setFileSize(filesize(TESTFILE));

//echo "fdfdss";


// Upload the various chunks. $status will be false until the process is
  // complete.
  $status = false;
  $handle = fopen(TESTFILE, "rb");
  while (!$status && !feof($handle)) {
    // read until you get $chunkSizeBytes from TESTFILE
    // fread will never return more than 8192 bytes if the stream is read buffered and it does not represent a plain file
    // An example of a read buffered file is when reading from a URL
    $chunk = readVideoChunk($handle, $chunkSizeBytes);
    $status = $media->nextChunk($chunk);
  }
  // The final value of $status will be the data from the API for the object
  // that has been uploaded.
  $result = false;
  if ($status != false) {
    $result = $status;
  }
  fclose($handle);


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