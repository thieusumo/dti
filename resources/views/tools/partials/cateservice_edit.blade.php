@extends('layouts.app')
@section('content-title', ($id!=0)?'Website Builder | Edit Category':'Website Builder | Add Category')
@push('styles')
 <style>
     .dropzone {
        border: 2px solid #757575;
    }
    .glyphicon-remove:hover{
        color: red;
    }
 </style>
@endpush
@section('content')
<div class='x_panel x_panel_form'>
    <div class="x_title"><h3>@if($id!=0) Edit CateService @else Add CateService @endif</h3>
    </div>        
    <div class="x_content">
        <form method="post" action="{{ route('places.cateservice.save') }}" id="cateservice_form" class="form-horizontal form-label-left" enctype="multipart/form-data">    
          @csrf 
          <input type="hidden" name="cateservice_id" value="{{$id}}" />
          <input type="hidden" name="place_id" value="{{ $place_id }}">             
            <div class="row">
               <label class="control-label col-md-2 col-sm-2 col-xs-12">Name</label>
               <div class="col-md-9 col-sm-9 col-xs-12">
                 <input type='text' class="form-control form-control-sm{{ $errors->has('cateservice_name') ? ' is-invalid' : '' }}" id="cateservice_name" value="{{isset($cateservice_item->cateservice_name)? $cateservice_item->cateservice_name:old('cateservice_name')}}" required name="cateservice_name" />
               </div>
             </div>
             <div class="row">
               <label class="control-label col-md-2 col-sm-2 col-xs-12">Index</label>
               <div class="col-md-9 col-sm-9 col-xs-12">
                   <input type='text' onkeypress="return isNumberKey(event)" class="form-control form-control-sm{{ $errors->has('cateservice_index') ? ' is-invalid' : '' }}" id="cateservice_index" value="{{isset($cateservice_item->cateservice_index)? $cateservice_item->cateservice_index:old('cateservice_index')}}" name="cateservice_index" />
               </div>
             </div>   
             <div id="collapseonlinebooking" class="onlinebooking">
                <div class="row" style="padding-bottom:10px;">
                 <label class="control-label col-md-2 col-sm-2 col-xs-12">Image</label>
                 <div class="col-md-9 col-sm-9 col-xs-12" style="overflow: hidden;">
                    <div class="catalog-image-upload" style="position:relative">
                           <div class="catalog-image-edit">
                              <input type="hidden" name="cateservice_image_old" id="cateservice_image_old" value="{{isset($cateservice_item->cateservice_image)? $cateservice_item->cateservice_image:old('cateservice_image')}}">
                               <input type='file' class="cateservice_image" name="cateservice_image" data-target="#catalogImagePreview1" accept=".png, .jpg, .jpeg" />
                               {{-- <label for="cateservice_image"></label> --}}
                           </div>
                           <div class="catalog-image-preview" style="height:200px">
                               <img id="catalogImagePreview1" style='display:{{(isset($cateservice_item)&&$cateservice_item->cateservice_image!="")?"":"none"}}' src ="{{isset($cateservice_item->cateservice_image)?config('app.url_file_view').$cateservice_item->cateservice_image:old('cateservice_image')}}" height ="100%" /> 
                           </div>
                           <i class="fas fa-trash delete-image text-primary" style="position:absolute;top:10px;right:10px;border: 1px solid red;border-radius: 50%;padding: 5px;"></i>

                    </div>
                 </div>
               </div>  

                <div class="row" style="padding-bottom:10px;">
                 <label class="control-label col-md-2 col-sm-2 col-xs-12">Icon Image</label>
                 <div class="col-md-9 col-sm-9 col-xs-12" style="overflow: hidden;">
                    <div class="catalog-image-upload" style="position:relative">
                           <div class="catalog-image-edit">
                              <input type="hidden" name="cateservice_icon_image_old" id="cateservice_icon_image_old" value="{{isset($cateservice_item->cateservice_icon_image)? $cateservice_item->cateservice_icon_image:old('cateservice_icon_image')}}">
                               <input type='file' class="cateservice_image" name="cateservice_icon_image" data-target="#catalogImagePreview2" accept=".png, .jpg, .jpeg" />
                               {{-- <label for="cateservice_image"></label> --}}
                           </div>
                           <div class="catalog-image-preview" style="height:200px">
                               <img id="catalogImagePreview2" style='display:{{(isset($cateservice_item)&&$cateservice_item->cateservice_icon_image!="")?"":"none"}}' src ="{{isset($cateservice_item->cateservice_icon_image)?config('app.url_file_view').$cateservice_item->cateservice_icon_image:old('cateservice_icon_image')}}" height ="100%" /> 
                           </div>
                           <i class="fas fa-trash delete-image2 text-primary" style="position:absolute;top:10px;right:10px;border: 1px solid red;border-radius: 50%;padding: 5px;"></i>

                    </div>
                 </div>
               </div>   

                 <div class="row" style="padding-bottom:10px;">
                 <label class="control-label col-md-2 col-sm-2 col-xs-12">Description</label>
                 <div class="col-md-9 col-sm-9 col-xs-12">
                   <textarea id="message"  class="form-control{{ $errors->has('cateservice_description') ? ' is-invalid' : ' texteditor' }}" name="cateservice_description" data-parsley-trigger="keyup" data-parsley-minlength="20" data-parsley-maxlength="100" data-parsley-minlength-message="Come on! You need to enter at least a 20 caracters long comment.."
                          data-parsley-validation-threshold="10">{{isset($cateservice_item->cateservice_description)? $cateservice_item->cateservice_description:old('cateservice_description')}}</textarea>
                 </div>
               </div>   
             </div>   

             <div class="row">
                <label class="control-label col-md-2 col-sm-2 col-xs-12">&nbsp;</label>
                <div class="col-sm-6 col-md-6  form-group">
                   <button id="submit" class="btn btn-sm btn-primary" >SUBMIT</button>
                   <button onclick="window.location='{{route('place.webbuilder',Session::get('place_id'))}}'" class="btn btn-sm btn-danger" type="button">CANCEL</button>
                </div>            
            </div>  
        </form>
    </div>        
</div>
@stop
@push('scripts')
<script type="text/javascript">
  $(document).ready(function(){
        $("#submit").on( "click", function(event){
            // validate form
            var validatorResult = $("#cateservice_form")[0].checkValidity();
            $("#cateservice_form").addClass('was-validated');
            if(!validatorResult){
                event.preventDefault();
                event.stopPropagation();           
                return;
            }else
            //form = document.createElement('#customer_form');
            $('#cateservice_form').submit();
        });

    });
$(document).ready(function() {    
     if ($("input.checkFlat")[0]) {
        $('input.checkFlat').iCheck({
            checkboxClass: 'icheckbox_flat-green'
        });       
       
    }
}); 
$(document).ready(function() {    
    if ($("input.icheckstyle")[0]) {
        $('input.icheckstyle').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });  
    }
    $('textarea.texteditor').summernote({
        height: 150,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['codeview']
        ]
    });
});
</script>   
<script>
  $(document).ready(function(){
    $(".catalog-image-preview").on('click',function(){
        $(this).parent().find(".cateservice_image").trigger("click");
    });
    $('.cateservice_image').change(function(){     
        var image_target = $(this).parent().siblings('.catalog-image-preview').children('img');
         try{
            var name = $(this)[0].files[0].name;
            if (this.files[0] && this.files[0]) {
                image_target.show();
                var reader = new FileReader();
                reader.onload = function(e) {
                    image_target.attr('src', e.target.result);
                    image_target.hide();
                    image_target.fadeIn(500);
                }
                reader.readAsDataURL(this.files[0]);
            }    
         }catch(err){            
            image_target.hide();       
         }        
    });
  });
</script>   


<script>
    //check validate
    $(document).ready(function(){

        var check = 0;

        $("input[name='cateservice_name']").on("blur",function(e){
            var str = $(this).val();
            if(str.length <=0){
                $(this).addClass('is-invalid');
                check = 1;
            }else {
                $(this).removeClass('is-invalid').addClass('is-valid');
                check = 0;
            }
            checkSubmit(check);
        });


        function checkSubmit(check){
            if(check == 1){
                $("#submit").attr('disabled',true);
            } else {
                $("#submit").attr('disabled',false);
            }
        }
        $(".delete-image").click(function(){
            let image = $("#catalogImagePreview1");
            if(image.attr('src') != ""){
                if(confirm('Do you want delete this image?')){
                    image.attr('src','').hide();
                    $("#cateservice_image_old").val("");
                    $(this).siblings('.catalog-image-edit').children('.cateservice_image').val("");
                }
                else
                    return;
            }
        })
        $(".delete-image2").click(function(){
            let image = $("#catalogImagePreview2");
            if(image.attr('src') != ""){
                if(confirm('Do you want delete this image?')){
                    image.attr('src','').hide();
                    $("cateservice_icon_image_old").val("");
                    $(this).siblings('.catalog-image-edit').children('.cateservice_image').val("");
                }
                else
                    return;
            }
        })

    });
</script> 
@endpush

