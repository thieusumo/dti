@extends('layouts.app')
@section('title')
@endsection
@push('styles')
    <style>
        .form-group {
            margin-bottom: .5rem;
        }
        .loader {
          border: 8px solid #f3f3f3;
          border-radius: 50%;
          border-top: 8px solid blue;
          border-right: 8px solid green;
          border-bottom: 8px solid red;
          border-left: 8px solid pink;
          width: 80px;
          height: 80px;
          -webkit-animation: spin 2s linear infinite; /* Safari */
          animation: spin 2s linear infinite;
          position: fixed;
          top: 50%;
          left: 50%;
          z-index: 100000;
          display: none; 
        }
        /* Safari */
        @-webkit-keyframes spin {
          0% { -webkit-transform: rotate(0deg); }
          100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
    </style>
@endpush
@section('content')
    <h4 class="border border-info border-top-0 mb-3 border-right-0 border-left-0 text-info">PAYMENT ORDER #{{$order_id}}</h4>

    <div class="">
        <form action="{{ route('authorize') }}" method="POST">
            <input type="hidden" name="order_id" value="{{$order_id}}">
            @csrf()
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Business Phone</label>
                <div class="col-md-4">
                    <input disabled type="text" value="{{$customer_info->place_phone}}" class="form-control form-control-sm" >
                </div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Business Name</label>
                <div class="col-md-4">
                    <input disabled class="form-control form-control-sm" value="{{$customer_info->place_name}}" type="text">
                </div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2 ">Services</label>
                <div class="col-md-4">
                    @foreach($service_list as $service)
                        <span>+<b>{{$service->cs_name}}</b> - ${{$service->cs_price}}</span><br>
                    @endforeach
                </div>
            </div>
            <hr>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Service Price</label>
                <div class="col-md-4"><input disabled type="text" class="form-control form-control-sm" id="service_price" name="service_price" value="{{$order_info->csb_amount}}"><input type="hidden" class="form-control form-control-sm" id="service_price_hidden" name="service_price_hidden" value="{{old('service_price_hidden')}}"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Discount($)</label>
                <div class="col-md-4"><input disabled class="form-control form-control-sm" type="text" id="discount" name="discount" value="{{$order_info->csb_amount_deal}}"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Payment Amount($)</label>
                <div class="col-md-4">
                    <input class="form-control form-control-sm" type="hidden" id="payment_amount" name="payment_amount" value="{{$order_info->csb_charge}}">
                    <input class="form-control form-control-sm" type="text" disabled id="payment_amount_disable" name="payment_amount_disable" value="{{$order_info->csb_charge}}">
                    <input class="form-control form-control-sm" type="hidden" id="payment_amount_hidden" name="payment_amount_hidden" value="{{$order_info->csb_charge}}">
                </div>
            </div>
                <div class="col-md-12 form-group row">
                    <label class="col-md-2 required">Credit Card Type</label>
                    <div class="col-md-4">
                        <select class="form-control form-control-sm" name="credit_card_type" id="credit_card_type">
                        @if($order_info->csb_payment_method == 3)
                            <option value="E-CHECK">E-CHECK</option>
                        @else
                            <option value="MasterCard">MasterCard</option>
                            <option value="VISA">VISA</option>
                            <option value="Discover">Discover</option>
                            <option value="American Express">American Express</option>
                        @endif
                        </select>
                    </div>
                </div>
            @if($order_info->csb_payment_method == 3)
                <div class="col-md-12 form-group row check">
                    <label class="col-md-2 required">Number Check</label>
                    <div class="col-md-4"><input class="form-control form-control-sm" type="text" name="routing_number"  value="{{old('routing_number')}}"></div>
                </div>{{-- 
                <div class="col-md-12 form-group row check">
                    <label class="col-md-2 required">Account Number</label>
                    <div class="col-md-4"><input class="form-control form-control-sm" type="text" name="account_number"  value="{{old('account_number')}}"></div>
                </div>
                <div class="col-md-12 form-group row check">
                    <label class="col-md-2 required">Bank Name</label>
                    <div class="col-md-4"><input class="form-control form-control-sm" type="text" name="bank_name"  value="{{old('bank_name')}}"></div>
                </div> --}}
            @else
                <div class="col-md-12 form-group row credit">
                    <label class="col-md-2 required">Credit Card Number</label>
                    <div class="col-md-4"><input class="form-control form-control-sm" type="text" name="credit_card_number"  value="{{old('credit_card_number')}}"></div>
                </div>
                <div class="col-md-12 form-group row credit">
                    <label class="col-md-2 required">Experation Date</label>
                    <div class="col-md-2"><select class="form-control form-control-sm"  name="experation_month">
                            @for($i=1;$i<13;$i++)
                                <option value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select></div>
                    <div class="col-md-2"><select class="form-control form-control-sm" name="experation_year">
                            @php
                            $current_year = date('Y');

                            @endphp
                            @for($i=2019;$i<$current_year+200;$i++)
                                <option value="{{$i}}">{{$i}}</option>
                            @endfor
                        </select></div>
                </div>
                <div class="col-md-12 form-group row credit">
                    <label class="col-md-2 required">CVV Number</label>
                    <div class="col-md-4"><input class="form-control form-control-sm" type="text"  value="{{old('cvv_number')}}" name="cvv_number"></div>
                </div>
                <div class="col-md-12 form-group row">
                    <label class="col-md-2 required">Name On Card</label>
                    <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="{{old('fullname')}}" name="fullname" placeholder="Last Name"></div>
                </div>
            @endif
                
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Address</label>
                <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="{{old('address')}}"  name="address"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">City</label>
                <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="{{old('city')}}"  name="city"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">State</label>
                <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="{{old('state')}}"  name="state"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Zip Code</label>
                <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="{{old('zip_code')}}"  name="zip_code"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Country</label>
                <div class="col-md-4"><input class="form-control form-control-sm" type="text" value="{{old('country')}}"  name="country"></div>
            </div>
            <div class="col-md-12 form-group row">
                <label class="col-md-2">Note</label>
                <div class="col-md-4"><textarea class="form-control form-control-sm" name="note" value="{{old('note')}}"  rows="5"></textarea></div>
            </div>
            <div class="form-group col-md-12">
                <div class="col-md-6 float-right mb-5">
                    <button type="submit" id="" class="btn-submit btn btn-primary">Submit</button>

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

            $(".combo_service").click(function(){

                var cs_price = $(this).attr('cs_price');
                var discount = $("#discount").val();
                var cs_id = $(this).val();

                if(discount == "")
                    discount = 0;

                if(combo_sevice_arr.includes(cs_id)){
                    total_price -= parseFloat(cs_price);
                    combo_sevice_arr.splice( $.inArray(cs_id, combo_sevice_arr), 1 );
                }else{
                    combo_sevice_arr.push(cs_id);
                    total_price += parseFloat(cs_price);
                }

                $("#payment_amount").val(total_price-parseFloat(discount));
                $("#payment_amount_disable").val(total_price-parseFloat(discount));
                $("#payment_amount_hidden").val(total_price-parseFloat(discount));
                $("#service_price").val(total_price);
                $("#service_price_hidden").val(total_price);
                max_discount= total_price*10/100;

            });
            $("#discount").keyup(function(){

                discount = $(this).val();
                if(discount == "")
                    discount = 0;

                if(discount > max_discount){
                    discount = max_discount;
                    $("#discount").val(max_discount);
                    toastr.error('Max discount is 10% Service Price');
                }
                $("#payment_amount_disable").val(total_price-parseInt(discount));
                $("#payment_amount").val(total_price-parseInt(discount));
                $("#payment_amount_hidden").val(total_price-parseFloat(discount));
            });

            $(".btn-search").click(function(){

                var customer_phone = $("#customer_phone").val();

                if(customer_phone != "")
                {
                    $.ajax({
                        url: '{{route('get-customer-infor')}}',
                        type: 'GET',
                        dataType: 'html',
                        data: {customer_phone: customer_phone},
                    })
                        .done(function(data) {
                            console.log(data);
                            data = JSON.parse(data);
                            if(data.status == 'error'){
                                $("#customer_bussiness").val("");
                                $("#customer_fullname").val("");
                                $("#customer_id").val("");
                                $("#salon_list").html("");
                                toastr.error(data.message);
                            }
                            else{
                                $("#customer_bussiness").val(data.customer_info.ct_salon_name);
                                $("#customer_fullname").val(data.customer_info.ct_firstname+" "+data.customer_info.ct_lastname);
                                $("#customer_id").val(data.customer_info.id);
                                if(data.place_list != ""){

                                    var salon_html ="";
                                    $.each(data.place_list, function(index, val) {
                                        salon_html += '<div class="col-md-3"><label class="ml-3 text-uppercase text-dark"><input style="width:20px;height: 20px" type="radio" class="place_id"  name="place_id" value="'+val.place_id+'"><b>'+val.place_name+'</b></label></div>';
                                    });
                                    $("#salon_list").html(salon_html);
                                }
                            }
                        })
                        .fail(function() {
                            console.log("error");
                        });
                }
            });
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
            $(".btn-submit").click(function(){
                ableProcessingLoader();
            })
            function ableProcessingLoader(){
                $('.loader').css('display','inline');
                $("#content").css('opacity',.5);
            }
            function unableProcessingLoader(){
                $('.loader').css('display','none');
                $("#content").css('opacity',1);
            }
        });
    </script>
@endpush
