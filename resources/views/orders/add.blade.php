@extends('layouts.app')
@section('content-title')
    NEW ORDER
@endsection
@push('styles')
<style>
    .form-group {
        margin-bottom: .5rem;
    }
    .card-header{
        padding: 0.5rem 0.75rem;
    }
    .select2-container .select2-selection--single{
            height:34px !important;
        }
        .select2-container--default .select2-selection--single{
                 border: 1px solid #ccc !important; 
             border-radius: 0px !important; 
        }
        .select2-container {
            width: 100%!important;
        }
</style>
@endpush
@section('content')
    <div class="">
    <form action="{{route('post-add-order')}}" method="post">
        @csrf()

    @if(empty($customer_info))
    <div class="col-md-12 form-group row">
        <label class="col-md-2">Order By</label>
        <div class="col-md-4">
            <select name="created_by" id="created_by" class="select2 form-control form-control-sm">
                @foreach($user_list as $user)
                    <option {{ \Auth::user()->user_id==$user->user_id?"selected":"" }} value="{{ $user->user_id }}">{{ $user->user_lastname." ".$user->user_firstname." (".$user->user_nickname." )" }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif
    
    <div class="form-group col-md-12 row">
        <div class="col-md-2">
            <label class="required">Customer Cell phone:</label>
        </div>
        @if(empty($customer_info))
        <div class="col-md-4" >
            <input type="text" class="input-sm form-control form-control-sm" id="customer_phone"  name="customer_phone" />
            <input type="hidden" class="input-sm form-control form-control-sm" id="customer_id" value=""  name="customer_id" />
        </div>
        <div class="col-md-1">
            <button class="btn btn-sm btn-secondary btn-search" type="button">Search</button>
        </div>
        @else
        <div class="col-md-4" >
            <input type="text" disabled class="input-sm form-control form-control-sm" id="customer_phone" value="{{$customer_info->ct_cell_phone}}" name="customer_phone" />
            <input type="hidden"  class="input-sm form-control form-control-sm" id="customer_phone" value="{{$customer_info->ct_cell_phone}}" name="customer_phone" />
            <input type="hidden" class="input-sm form-control form-control-sm" id="customer_id" value="{{!empty($customer_info)?$customer_info->id:""}}"  name="customer_id" />
        </div>
        @endif
    </div>
    <div class="form-group col-md-12 row">
        <div class="col-md-2">
            <label class="required">FullName:</label>
        </div>
        <div class="col-md-4" >
            <input type="text" class="input-sm form-control form-control-sm" value="{{!empty($customer_info)?$customer_info->getFullname():""}}" disabled id="customer_fullname"  name=""/>
        </div>
    </div>
    <div class="form-group col-md-12 row">
        <div class="col-md-2">
            <label class="required">Business:</label>
        </div>
        <div class="col-md-10 row"  id="salon_list">
            @if($place_list_assign->count() > 0)
                @foreach($place_list_assign as $place)
                    <div class="col-md-3">
                        <label class="ml-3 text-uppercase text-dark">
                            <input style="width:20px;height: 20px" type="radio" class="place_id"
                                   business_name="{{$place->business_name}}"
                                   business_phone="{{$place->business_phone}}"
                                   customer_id_assign="{{$place->id}}"
                                   email_assign="{{$place->email}}"
                                   website_assign="{{$place->website}}"
                                   address_assign="{{$place->address}}"
                                   name="place_id" value="0">
                            <b>{{$place->business_name}}</b>
                        </label>
                    </div>
                @endforeach
                    <input type="hidden" id="business_name_assign" name="business_name">
                    <input type="hidden" id="business_phone_assign" name="business_phone">
                    <input type="hidden" id="customer_id_assign" name="customer_id_assign">
                    <input type="hidden" id="email_assign" name="email">
                    <input type="hidden" id="website_assign" name="website">
                    <input type="hidden" id="address_assign" name="address">
            @endif
            @if(isset($place_list))
            @foreach($place_list as $place)
            <div class="col-md-3">
                <label class="ml-3 text-uppercase text-dark">
                    <input style="width:20px;height: 20px" type="radio" class="place_id"  name="place_id" value="{{$place->place_id}}"><b>{{$place->place_name}}</b>
                </label>
            </div>
            @endforeach
            @endif
        </div>
    </div>
    <hr>
	<div class="col-12 row">
        <div class="col-md-2">
            <label class="required">Services</label>
        </div>
        <div class="col-md-10">
            <div id="accordion">
                @foreach($combo_service_type as $key =>  $type)
                <div class="card">
                    <div class="card-header">
                        <a class="card-link" data-toggle="collapse" href="#t{{$type->id}}">
                            <div class="text-uppercase text-info">{{$type->name}}</div>
                        </a>
                    </div>
                    <div id="t{{$type->id}}" class="collapse " data-parent="#accordion">
                        <div class="card-body row">
                             @foreach($combo_service_list->where('cs_combo_service_type',$type->id) as $service)
                                 @if(in_array($service->id,$service_permission_arr))
                                <label class="col-md-6">
                                    <input style="width:20px;height: 20px" type="checkbox" max_discount="{{$type->max_discount}}" class="combo_service" cs_price="{{$service->cs_price}}" name="cs_id[]"  value="{{$service->id}}"> <b>{{$service->cs_name}}</b>{{$service->cs_type==1?"(Combo)":"(Service)"}} - ${{$service->cs_price}}</br>
                                <i>{{$service->cs_description}}</i></label>
                                <br>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
                @if($combo_service_orther->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <a class="card-link" data-toggle="collapse" href="#other">
                                <div class="text-uppercase text-info">Others</div>
                            </a>
                        </div>
                        <div id="other" class="collapse " data-parent="#accordion">
                            <div class="card-body row">
                                @foreach($combo_service_orther as $service)
                                    <label class="col-md-6"><input style="width:20px;height: 20px" type="checkbox" class="combo_service" cs_price="{{$service->cs_price}}" name="cs_id[]"  value="{{$service->id}}"> {{$service->cs_name}}{{$service->cs_type==1?"(Combo)":"(Service)"}} - ${{$service->cs_price}}</label><br>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <hr>
    <div class="col-md-12 form-group row">
        <label class="col-md-2 required">Service Price</label>
        <div class="col-md-4">
            <input disabled type="text" class="form-control form-control-sm" id="service_price" name="service_price" value="{{old('service_price')}}">
            <input type="hidden" class="form-control form-control-sm" id="service_price_hidden" name="service_price_hidden" value="{{old('service_price_hidden')}}">
        </div>
    </div>
    <div class="col-md-12 form-group row">
        <label class="col-md-2">Discount($)<span id="max-discount" class="text-danger"></span></label>
        <div class="col-md-4">
            <input class="form-control form-control-sm"  type="text" id="discount" name="discount" value="{{old('discount')}}">
        </div>
    </div>
    <div class="col-md-12 form-group row">
        <label class="col-md-2 required">Payment Amount($)</label>
        <div class="col-md-4">
            <input class="form-control form-control-sm" type="hidden" id="payment_amount" name="payment_amount" value="{{old('payment_amount')}}">
            <input class="form-control form-control-sm" type="text" disabled id="payment_amount_disable" name="payment_amount_disable" value="{{old('payment_amount')}}">
            <input class="form-control form-control-sm" type="hidden" id="payment_amount_hidden" name="payment_amount_hidden" value="{{old('payment_amount_hidden')}}">
        </div>
    </div>
    <div class="col-md-12 form-group row">
        <label class="col-md-2">Payment Method</label>
        <div class="col-md-4 row">
            <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" checked id="credit" name="csb_payment_method" value="2">
                <label class="custom-control-label" for="credit">CREDIT &nbsp; &nbsp;</label>
            </div>
            <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="check" name="csb_payment_method" value="3">
                <label class="custom-control-label" for="check">CHECK &nbsp; &nbsp;</label>
            </div>
            <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="other" name="csb_payment_method" value="1">
                <label class="custom-control-label" for="other">OTHER</label>
            </div>
        </div>
    </div>
    <div class="form-group col-md-12">
        <div class="col-md-6 float-right">
        <button type="submit" class="btn btn-primary">Submit</button>

        </div>
    </div>
    </form>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
 $(document).ready(function() {

    var combo_sevice_arr = [];
    var total_price = 0;
    var max_discount = 0;
    var place_id_arr = [];

    $('.select2').select2();

   $(".combo_service").click(function(){

    var cs_price = $(this).attr('cs_price');
    var discount = $("#discount").val();
    var cs_id = $(this).val();
    var percent_discount = $(this).attr('max_discount');
    var service_discount = parseFloat(percent_discount)*parseFloat(cs_price)/100;

    if(discount == "")
        discount = 0;

    if(combo_sevice_arr.includes(cs_id)){
        total_price -= parseFloat(cs_price);
        combo_sevice_arr.splice( $.inArray(cs_id, combo_sevice_arr), 1 );
        max_discount -= service_discount;
    }else{
        combo_sevice_arr.push(cs_id);
        total_price += parseFloat(cs_price);
        max_discount += service_discount;
    }

    $("#payment_amount").val(total_price-parseFloat(discount));
    $("#payment_amount_disable").val(total_price-parseFloat(discount));
    $("#payment_amount_hidden").val(total_price-parseFloat(discount));
    $("#service_price").val(total_price);
    $("#service_price_hidden").val(total_price);
    // max_discount= total_price*10/100;
    $("#max-discount").text('( Max: $'+max_discount+' )');

   });
   $("#discount").keyup(function(event){

       $(this).val($(this).val().replace(/[^0-9\.]/g,''));
       if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
           event.preventDefault();
       }
       var payment_amount = $("#payment_amount").val();
       if(payment_amount == "" || payment_amount == 0){
           toastr.error('Choose Service, please!');
           event.preventDefault();
       }

    discount = $(this).val();
    if(discount == "")
        discount = 0;

    if(discount > max_discount){
        discount = max_discount;
        $("#discount").val(max_discount);
        toastr.error('Max discount is '+max_discount );
    }
     $("#payment_amount_disable").val(total_price-parseFloat(discount));
     $("#payment_amount").val(total_price-parseFloat(discount));
     $("#payment_amount_hidden").val(total_price-parseFloat(discount));
   });

   $(".btn-search").click(function(){
        seachCustomer();
   });
   function seachCustomer(){

    var customer_phone = $("#customer_phone").val();
    var created_by = $("#created_by").val();

    if(customer_phone != "")
    {
        $.ajax({
            url: '{{route('get-customer-infor')}}',
            type: 'GET',
            dataType: 'html',
            data: {
                customer_phone: customer_phone,
                created_by: created_by,
            },
        })
        .done(function(data) {
            data = JSON.parse(data);
            if(data.status == 'error'){
                $("#customer_bussiness").val("");
                $("#customer_fullname").val("");
                $("#customer_id").val("");
                $("#salon_list").html("");
                toastr.error(data.message);
            }
            else{
                // $("#customer_bussiness").val(data.customer_info.ct_salon_name);
                $("#customer_fullname").val(data.customer_info.ct_firstname+" "+data.customer_info.ct_lastname);
                $("#customer_id").val(data.customer_info.id);

                var salon_html ="";

                if(data.place_list != ""){

                    $.each(data.place_list, function(index, val) {
                        salon_html += '<div class="col-md-3"><label class="ml-3 text-uppercase text-dark"><input style="width:20px;height: 20px" type="radio" class="place_id"  name="place_id" value="'+val.place_id+'"><b>'+val.place_name+'</b></label></div>';
                    });
                }
                if(data.place_list_assign != ""){
                    $.each(data.place_list_assign, function(index, val) {
                        salon_html += `<div class="col-md-3">
                                        <label class="ml-3 text-uppercase text-dark">
                                            <input style="width:20px;height: 20px" type="radio" class="place_id"
                                                   business_name="`+val.business_name+`"
                                                   business_phone="`+val.business_phone+`"
                                                   customer_id_assign="`+val.id+`"
                                                   email_assign="`+val.email+`"
                                                   website_assign="`+val.website+`"
                                                   address_assign="`+val.address+`"
                                                   name="place_id" value="0">
                                            <b>`+val.business_name+`</b>
                                        </label>`;
                    });
                    salon_html += `
                        <input type="hidden" id="business_name_assign" name="business_name">
                        <input type="hidden" id="business_phone_assign" name="business_phone">
                        <input type="hidden" id="customer_id_assign" name="customer_id_assign">
                        <input type="hidden" id="email_assign" name="email">
                        <input type="hidden" id="website_assign" name="website">
                        <input type="hidden" id="address_assign" name="address">
                    `;
                }
                
                    $("#salon_list").html(salon_html);
            }
        })
        .fail(function() {
            console.log("error");
        });
    }
   }
   $("#credit_card_type").change(function(event) {
       var credit_card_type = $('#credit_card_type :selected').val();
       if(credit_card_type == 'E-CHECK'){
            $(".check").css('display', '');
            $(".credit").css('display', 'none');
       }else{
            $(".check").css('display', 'none');
            $(".credit").css('display', '');
        }
   });
   $(document).on("click",".place_id",function () {
       var place_id = $(this).val();
       if(place_id == 0){
           var business_name = $(this).attr('business_name');
           var business_phone = $(this).attr('business_phone');
           var customer_id_assign = $(this).attr('customer_id_assign');
           var email = $(this).attr('email_assign');
           var website = $(this).attr('website_assign');
           var address = $(this).attr('address_assign');

           $("#business_name_assign").val(business_name);
           $("#business_phone_assign").val(business_phone);
           $("#customer_id_assign").val(customer_id_assign);
           $("#email_assign").val(email);
           $("#website_assign").val(website);
           $("#address_assign").val(address);
       }
   });
   $("#created_by").change(function(){
        seachCustomer();
   });
});
</script>
@endpush
