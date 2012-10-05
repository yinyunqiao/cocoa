$(document).ready(function() { 
  $('#UploadForm').on('submit', function(e) {
    e.preventDefault();
    $('#SubmitButton').attr('disabled', '');
    $("#output").html('<div style="padding:10px"><img src="images/ajax-loader.gif" alt="Please Wait"/> <span>Uploading...</span></div>');
    $(this).ajaxSubmit({
      target: '#output',
      success:  afterSuccess 
    });
  });
}); 

function afterSuccess(responseText, statusText, xhr, $form)  { 
  $('#UploadForm').resetForm();
  $('#SubmitButton').removeAttr('disabled');
  alert(responseText);
} 
