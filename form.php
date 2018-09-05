<!DOCTYPE html>
<html>
<body>

<form method="post" enctype="multipart/form-data" id="target">
    Select image to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload Image" name="submit">
</form>


<script
  src="https://code.jquery.com/jquery-2.2.4.min.js"
  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
  crossorigin="anonymous"></script>

<script>

$( "#target" ).submit(function( event ) {
  //alert( "Handler for .submit() called." );
  event.preventDefault();

var form = $('#target')[0];

var data = new FormData(form);

    $.ajax({
        xhr: function()
        {
            var xhr = $.ajaxSettings.xhr();
            xhr.upload.onprogress = function(e) {
                console.log(Math.floor(e.loaded / e.total *100) + '%');
            };
            return xhr;
        },
        type: 'POST',
        url: "uploadChunks.php",
        enctype: 'multipart/form-data',
        processData: false,  // Important!
        contentType: false,
        timeout: 600000,
        data: data,
        success: function(data){
            //Do something success-ish
            console.log(data);
        }
    });

});




</script>

</body>
</html>