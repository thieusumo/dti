function perviewImage(){
  $("input[type='file']").on("change", function(event){
    var preview = $(this).attr('previewImageId');
    if(preview){
      $("#"+preview+"").attr("src",URL.createObjectURL(event.target.files[0]));
    }
  });
  $(".previewImage img").on('click',function(){
            var id = $(this).attr("id");
            $("input[previewImageId='"+id+"']").trigger('click');
  });
}

function summernote(){
  $('.summernote').summernote();
  $('.note-icon-trash').trigger('click');
}
function isNumberKey(evt){
  var charCode = (evt.which) ? evt.which : event.keyCode
  if (charCode > 31 && (charCode < 48 || charCode > 57))
      return false;
  return true;
}  



