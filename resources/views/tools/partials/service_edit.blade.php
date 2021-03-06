@extends('layouts.app')
@section('content-title', ($id==0)?"Website Builder | Services | Add Service":"Website Builder | Services | Edit Service")
@push('styles')
 <style>
     .dropzone {
        border: 2px solid #757575;
    }
    .glyphicon-remove:hover{
        color: red;
    }
    .catalog-image-preview{
        cursor: pointer;
    }
    .note-popover{
        display: none;
    }
 </style>
@endpush
@section('content')
<div class='x_panel x_panel_form'>
    <div class="x_title"><h3>{{($id==0)?"Add":"Edit"}} Service</h3></div>      
    <div class="x_content">
       
        <form action="{{route('places.service.save')}}" id="service_form" method="post" enctype="multipart/form-data">
        {{csrf_field()}} 
        <input type="hidden" id="id" name="id" value="{{$id}}" >
         <div class="row"> 
            <label class="col-sm-3 col-md-2">Category</label>
            <div class="col-sm-5 mb-3 col-md-4 input-group">
                <select class="form-control form-control-sm{{ $errors->has('cateservice_id') ? ' is-invalid' : '' }}" name="service_cate_id">
                    @foreach($list_services as $list_service)
                    <option {{(isset($service_item)&&$service_item->service_cate_id==$list_service->cateservice_id)?"selected":""}} value="{{$list_service->cateservice_id}}">{{$list_service->cateservice_name}}</option>
                    @endforeach
                </select>
                <span>{{$errors->first('cateservice_id')}}</span>
            </div>
            <label class="col-sm-1 col-md-1">&nbsp;</label>
            <label class="col-sm-3 col-md-2">Index</label>
            <div class="col-sm-3 col-md-3">
                <input type='text' onkeypress="return isNumberKey(event)" min="0" max="99" class="form-control form-control-sm" value="{{(isset($service_item))?$service_item->service_index:old('service_index')}}" name="service_index" />
             </div> 
                
         </div>      
         <div class="row">  
                <label class="col-sm-3 col-md-2">Name</label>
                <div class="col-sm-6 mb-3 col-md-4 input-group">
                    <input type='text' class="form-control form-control-sm{{ $errors->has('service_name') ? ' is-invalid' : '' }}" value="{{(isset($service_item))?$service_item->service_name:old('service_name')}}" name="service_name" required/>
                        <span>{{$errors->first('service_name')}}</span>
                </div> 
                <label class="col-sm-1 col-md-1">&nbsp;</label>
                <label class="col-sm-3 col-md-2">Price up & down</label>
                <div class="col-sm-3 col-md-3 form-inline">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="medium" {{(!isset($service_item))?"checked":""}} {{(isset($service_item) && $service_item->service_updown==0)?"checked":""}} value="0" name="service_updown">
                        <label class="custom-control-label" for="medium">Medium&nbsp;</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="up" {{(isset($service_item) && $service_item->service_updown==1)?"checked":""}} value="1" name="service_updown">
                        <label class="custom-control-label" for="up">Up&nbsp;</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="down" {{(isset($service_item) && $service_item->service_updown==2)?"checked":""}} value="2" name="service_updown">
                        <label class="custom-control-label" for="down">Down&nbsp;</label>
                    </div>
                </div>   
                  
                <!-- <label class="col-sm-3 col-md-2">Short Name</label>
                <div class="col-sm-5 col-md-4 input-group">
                    <input type='text' class="form-control form-control-sm" value="{{(isset($service_item))?$service_item->service_short_name:old('service_short_name')}}" name="service_short_name" />
                 </div>   -->                
         </div>   
         <div class="row">   
                <label class="col-sm-3 col-md-2">Price</label>
                <div class="input-group mb-3 input-group-sm col-sm-5 col-md-4">
                     <div class="input-group-prepend">
                       <span class="input-group-text">$</span>
                    </div>                       
                        <input min="0" type="text" onkeypress="return isNumberKey(event)" required class="form-control form-control-sm{{ $errors->has('service_price') ? ' is-invalid' : '' }}" value="{{(isset($service_item))?$service_item->service_price:old('service_price')}}" name="service_price">
                        <span>{{$errors->first('service_price')}}</span>
                </div>    
                <label class="col-sm-1 col-md-1"></label>
                <label class="col-sm-3 col-md-2" for="enable_booking">Enable online bookings</label>
                <div class="col-sm-2 col-md-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="enable_booking" name="booking_online_status" {{((isset($service_item) && $service_item->booking_online_status==1))||!isset($service_item)?"checked":""}}  >
                        <label class="custom-control-label" for="enable_booking"></label>
                    </div>
                </div> 
                
                <!-- <label class="col-sm-3 col-md-2">Tag Name</label>
                <div class="col-sm-5 col-md-4">
                    <input type='text' class="form-control form-control-sm" value="{{(isset($service_item))?$service_item->service_tag:old('service_tag')}}" name="service_tag" />
                 </div>    --> 
              
         </div>      
         <div class="row">   
                <label class="col-sm-3 col-md-2">Price Extra</label>
                <div class="input-group mb-3 input-group-sm col-sm-5 col-md-4">
                     <div class="input-group-prepend">
                       <span class="input-group-text">$</span>
                    </div>                        
                        <input required="" type="text" onkeypress="return isNumberKey(event)" min="0" class="form-control form-control-sm" value="{{(isset($service_item))?$service_item->service_price_extra:0}}" name="service_price_extra">
                </div>    
                <label class="col-sm-1 col-md-1">&nbsp;</label>
                <label class="col-sm-3 col-md-2">Status</label>
                
                <div class="col-sm-3 col-md-3 form-inline">
                    {{-- <div class="radio" style="margin-top: -20px;">
                        <label>
                          <input type="radio" class="flat icheckstyle" {{(!isset($service_item))?"checked":""}} {{(isset($service_item) && $service_item->enable_status==1)?"checked":""}} value="1" name="service_status">&nbsp;Enabled
                        </label>
                      </div>
                    <div class="radio" style="margin-left:10px;margin-top: -20px;">
                        <label>
                          <input type="radio" class="flat icheckstyle" {{(isset($service_item) && $service_item->enable_status==0)?"checked":""}} value="0" name="service_status">&nbsp;Disabled
                        </label>
                    </div> --}}
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="enabled" {{(!isset($service_item))?"checked":""}} {{(isset($service_item) && $service_item->enable_status==1)?"checked":""}} value="1" name="service_status">
                        <label class="custom-control-label" for="enabled">Enabled&nbsp;&nbsp;</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="disabled" {{(isset($service_item) && $service_item->enable_status==0)?"checked":""}} value="0" name="service_status">
                        <label class="custom-control-label" for="disabled">Disabled</label>
                    </div>
                </div>    
                <!-- <label class="col-sm-3 col-md-2">Price Repair</label>
                <div class="col-sm-5 col-md-4 input-group-spaddon">
                     <div class="input-group">
                        <span class="input-group-addon">$</span>                        
                        <input type="number" min="0" class="form-control form-control-sm{{ $errors->has('service_price_repair') ? ' is-invalid' : '' }}" value="{{(isset($service_item))?$service_item->service_price_repair:0}}" name="service_price_repair">
                        <span>{{$errors->first('service_price_repair')}}</span>
                    </div>
                 </div>     -->
                
         </div>      
         <div class="row">   
                <label class="col-sm-3 col-md-2">Duration</label>
                  <div class="input-group mb-3 input-group-sm col-sm-5 col-md-4">
                     <div class="input-group-prepend">
                       <span class="input-group-text">Mins</span>
                    </div>
                    <input type="text" onkeypress="return (event)" min="0" class="form-control form-control-sm{{ $errors->has('service_duration') ? ' is-invalid' : '' }}" value="{{(isset($service_item))?$service_item->service_duration:0}}" name="service_duration">
                  </div>

            
                <!-- <label class="col-sm-3 col-md-2">Hold</label>
                <div class="col-sm-5 col-md-4">
                    <input type='number' min="0" class="form-control form-control-sm" value="{{(isset($service_item))?$service_item->service_price_hold:old('service_price_hold')}}" name="service_price_hold" />
                 </div>    --> 
             
         </div>   
         <div class="row">   
                <!-- <label class="col-sm-3 col-md-2">Tax</label>
                <div class="col-sm-5 col-md-4 input-group-spaddon">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon">%</span>                        
                        <input type="number" min="0" class="form-control form-control-sm" value="{{(isset($service_item))?$service_item->service_tax:old('service_tax')}}" name="service_tax">
                    </div>
                 </div>     -->
          
                 
                
         </div> 
        <div class="row form_group">   
             
                                 
            
                                   
           
         </div>     
            
        <div class="row form_group">
                <label class="col-sm-3 col-md-2">Image</label>
                <div class="col-sm-10 col-md-10" style="overflow: hidden;">
                   <div class="catalog-image-upload" style="position:relative">
                          <div class="catalog-image-edit">
                              <input type='file' id="imageUpload2"  name="service_image" data-target="#catalogImagePreview2" accept=".png, .jpg, .jpeg" />
                              <input type="hidden" value="{{(isset($service_item))?$service_item->service_image:old('service_image')}}" name="service_image_hidden">
                              {{-- <label for="imageUpload2"></label> --}}
                          </div>
                          <div class="catalog-image-preview" style="height:200px">
                              <img id="catalogImagePreview2"  style='display:{{(isset($service_item)&&$service_item->service_image!="")?"":"none"}}' src="{{(isset($service_item->service_image))?config('app.url_file_view').$service_item->service_image:old('service_image')}}" height="100%" />
                          </div>
                          <i class="fas fa-trash delete-image text-primary" style="position:absolute;top:10px;right:10px;border: 1px solid red;border-radius: 50%;padding: 5px;"></i>
                      </div>
                </div>
       </div>
       <div class="row form-group">
                <input type="hidden" name="multi_image" id="multi_image" value="{{isset($service_item->service_list_image)? $service_item->service_list_image:old('service_list_image')}}">
                <span class="list_image"></span>
                <label class="col-sm-3 col-md-2">List Image Show</label>
                <div class="col-sm-10 col-md-10 pt-4 ">
                    @if(isset($service_item) && isset($service_item->service_list_image))
                    @foreach(explode(";",$service_item->service_list_image) as $key => $image)
                        @if($image)
                            <span id="{{$key}}">
                            {{-- <img class="img-rounded" style="max-width:100px;max-height:100px" src={{config('app.url_file_view').$image}}><i class="fa fa-times text-danger"  onclick="remove_image('{{$image}}','{{$key}}','{{$id}}',event)"  style="position: relative;z-index:1111;top: -30px;right: 9px"></i> --}}
                            <iframe class="img-rounded" style="max-width:100px;max-height:100px" src={{config('app.url_file_view').$image}}></iframe>
                            </span>
                        @endif
                    @endforeach
                    @endif
                </div>
                <div class="col-md-2 col-sm-2"></div> 
                 <div class="col-sm-10 col-md-10 ">
                    <span class="text-primary">Drag multiple files to the box below for multi upload or click to select files. This is for demonstration purposes only, the files are not uploaded to any server.</span>
                    <div id="multiUploadImages" required class="dropzone">
                        
                    </div>
                </div>    
            </div>  
       <div class="row form_group" style="margin-top:10px">
            <label class="col-sm-3 col-md-2">Description</label>
            <div class="col-sm-10 col-md-10">                
                <textarea id="message" class="form-control texteditor" 
                 value=""  name="service_description">{{(isset($service_item))?$service_item->service_descript_website:old('service_description')}}</textarea>                
            </div>     
       </div>           
       <div class="row form_group" style="margin-top:10px">
            <label class="col-sm-3 col-md-2">&nbsp;</label>
             <div class="col-sm-5 col-md-4">
                  <button id="submit" class="btn btn-sm btn-primary" >SUBMIT</button>
                  <button class="btn btn-sm btn-danger"  onclick="window.location='{{route('place.webbuilder',Session::get('place_id'))}}'"  type="reset">CANCEL</button>
             </div>
       </div>           
    </form>
    </div>        
</div>
@stop
@push('scripts')
<script type="text/javascript">
Dropzone.autoDiscover = false;    
function initializeDropZone() {
    /*references: https://smarttutorials.net/ajax-image-upload-using-dropzone-js-normal-form-fields-button-click-using-php/*/
    myDropzone = new Dropzone('div#multiUploadImages', {
           url: '{{ route('upload-multi-images-service') }}',
           headers: {
               'X-CSRF-TOKEN': '{!! csrf_token() !!}'
           },
           addRemoveLinks: true,
           autoProcessQueue: true,
           uploadMultiple: true,
           parallelUploads: 10,
           maxFiles: 10,
           maxFilesize: 2,
           acceptedFiles: ".jpeg,.jpg,.png,.gif",
           dictFileTooBig: 'Image is bigger than 2MB',
           addRemoveLinks: true,
            removedfile: function(file) {
                var name = file.name;
                $('#'+name.replace(/[^A-Z0-9]+/ig,'_')).val('');
                var _ref;
                 return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;        
              },
             init: function () {

            var myDropzone = this;

            this.on('sending', function (file, xhr, formData) {
                // Append all form inputs to the formData Dropzone will POST
                var data = $("#service_form").serializeArray();
                $.each(data, function (key, el) {
                    formData.append(el.name, el.value);
                });
                // console.log(formData);

            });
        },
        successmultiple: function (file, response) {
            // console.log(file);
            // console.log(response);
            $.each( response, function( i, val ) {
                var str = val.slice(val.lastIndexOf("/")+1);
                
            $('.list_image').append('<input type="hidden" name="multi_image_add[]" id="'+str.replace(/[^A-Z0-9]+/ig,'_')+'" value="'+val+'">');
            });

            $("#success-icon").attr("class", "fas fa-thumbs-up");
            $("#success-text").html(response.message);
        },
    });
}
function remove_image(src_image,id,service_id,e){
    if(window.confirm("Are you sure you want to delete this service ?")){
        $('#'+id).remove();
        $.ajax({
            url: '{{route('remove-image-service')}}',
            type: 'GET',
            dataType: 'html',
            data: {service_id: service_id,src_image:src_image},
        })
        .done(function(response) {
            $('#multi_image').val(response);
            toastr.success('Remove Image Menu Success!');
            //console.log(response);
        })
        .fail(function() {
            toastr.error('Remove Image Menu Error!')
            //console.log("error");
        });
    }
    else{
        e.preventDefault();
    }
    
}
     $(document).ready(function(){
        $("#submit").on( "click", function(event){
            // validate form
            var validatorResult = $("#service_form")[0].checkValidity();
            $("#service_form").addClass('was-validated');
            if(!validatorResult){
                event.preventDefault();
                event.stopPropagation();           
                return;
            }else
            //form = document.createElement('#customer_form');
            $('#service_form').submit();
        });

    });
function readURL(input) {
    if (input.files && input.files[0]) {
        $('img').show();
        var reader = new FileReader();
        reader.onload = function(e) {
            $($(input).attr("data-target")).attr('src', e.target.result);
            $($(input).attr("data-target")).hide();
            $($(input).attr("data-target")).fadeIn(100);
        }
        reader.readAsDataURL(input.files[0]);
    }      
}
$(document).ready(function() {    
    if ($("input.icheckstyle")[0]) {
        $('input.icheckstyle').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });  
    }
    $("input[type=file]").change(function() {
        readURL(this);
    });

    $('#message').summernote({
        height: 150,   //set editable area's height
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['500px']],
            ['codeview']
        ],
    });
     if ($("input.checkFlat")[0]) {
        $('input.checkFlat').iCheck({
            radioClass: 'iradio_flat-green',
            checkboxClass: 'icheckbox_flat-green'
        });       
    }        
    initializeDropZone();
    $("input[type=file]").change(function() {
        readURL(this);
    });

    $(".catalog-image-preview").on('click',function(){
        $("#imageUpload2").trigger("click",);
    });

    $('#imageUpload2').change(function(){            
     try{
        var name = $(this)[0].files[0].name;            
     }catch(err){            
        $("#catalogImagePreview2").hide();          
     }        
    });

    var check = 0;
    $("input[name='service_price'],input[name='service_price_extra'],input[name='service_duration']").on("blur",function(e){
        var str = parseInt($(this).val());
        if(isNaN(str)){
            $(this).addClass('is-invalid');
            check = 1;
        }else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            check = 0;
        }
        checkSubmit(check);
    });
    
    $("input[name='service_name']").on("blur",function(e){
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
        let image = $("#catalogImagePreview2");
        if(image.attr('src') != ""){
            if(confirm('Do you want delete this image?')){
                $(this).siblings('.catalog-image-edit').children('input').val("");
                image.attr('src','').hide();
            }
                
            else
                return;
        }
    })

    });
</script> 
@endpush

