@extends('layouts.app')
@section('content-title')
    ALL CUSTOMER
@endsection
@push('style')
  <style>
  </style>
@endpush
@section('content')

    @if(Gate::denies('permission','customer-admin'))
        <style>
            .an{
                display: none;
            }
        </style>
    @endif
<div class="table-responsive">
    <form>
    <div class="form-group col-md-12 row">
        <div class="col-md-4">
            <label for="">Created date</label>
            <div class="input-daterange input-group" id="created_at">
              <input type="text" class="input-sm form-control form-control-sm" id="start_date" name="start" />
              <span class="input-group-addon">to</span>
              <input type="text" class="input-sm form-control form-control-sm" id="end_date" name="end" />
            </div>
        </div>
        <div class="col-md-2">
            <label for="">Address</label>
            <input type="text" id="address" name="address" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <label for="">Status</label>
            <select id="status-customer" name="status_customer" class="form-control form-control-sm">
                <option value="">-- ALL --</option>
                @foreach ($customer_status as $key =>  $element)
                    <option value="{{$key}}">{{$element}}</option>
                @endforeach
            </select>
        </div>
        @if(Gate::allows('permission','customer-admin'))
        <div class="col-md-2">
            <label for="">Team</label>
            <select id="team_id" name="team_id" class="form-control form-control-sm">
                    @foreach ($teams as $key =>  $team)
                        <option value="{{$team->id}}">{{$team->team_name}}</option>
                    @endforeach
            </select>
        </div>
        @endif
        <div class="col-2 " style="position: relative;">
            <div style="position: absolute;top: 50%;" class="">
            <input type="button" class="btn btn-primary btn-sm" id="search-button" value="Search">
            <input type="button" class="btn btn-secondary btn-sm" id="formReset" value="Reset">
            </div>
        </div>
    </div>
    </form>
    <hr>
    
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-toggle="tab" href="#home">ALL CUSTOMERS</a>
    </li>
    @if(\Gate::allows('permission','serviced-customer'))
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#menu1">SERVICED CUSTOMERS</a>
      </li>
    @endif
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div id="home" class="tab-pane active"><br>
      <div style="height:700px" style="overflow:auto">
        <table class="table table-sm table-hover" id="dataTableAllCustomer" width="100%" cellspacing="0">
            <thead>
                <tr class="sticky-top bg-primary text-white"  style="z-index: 9">
                  <th>ID</th>
                  <th>Business</th>
                  <th>Contact Name</th>
                  <th>Business Phone</th>
                  <th>Cell Phone</th>
                  <th>Note</th>
                  <th>Status</th>
                  <th>Created Date</th>
                  <th style="width: 15%">Action</th>
                </tr>
            </thead>
        </table>
      </div>
    </div>
    @if(\Gate::allows('permission','serviced-customer'))
      <div id="menu1" class="tab-pane fade"><br>
        <div style="height:700px" style="overflow:auto">
          <table class="table table-sm table-hover" id="servicedCustomer" width="100%" cellspacing="0">
              <thead>
                  <tr class="sticky-top bg-primary text-white"  style="z-index: 9">
                    <th>ID</th>
                    <th>Business</th>
                    <th>Contact Name</th>
                    <th>Business Phone</th>
                    <th>Cell Phone</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th style="width: 15%">Action</th>
                  </tr>
              </thead>
          </table>
        </div>
      </div>
    @endif
  </div>
</div>

<!-- Modal view-->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div style="max-width: 70%" class="modal-dialog modal-sm" role="document">
    <div class="modal-content modal-content-view">
    </div>
  </div>
</div>
{{-- MODAL IMPORT --}}
<div class="modal fade" id="import-modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
          <form  method="post" id="customer-import-form" enctype="multipart/form-data" name="customer-import-form">
            <div class="col-md-12">
                <div class="row col-md-12">
                  <a href="{{ route('get_import_template_customer') }}" class="blue">Download an import template spreadsheet</a>
                </div>
                <div class="row col-md-12">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input form-control form-control-sm" id="file" name="file">
                    <label class="custom-file-label" for="file">Choose file</label>
                  </div>
                </div><br>
                <div class="row col-md-12">
                  <label class="col-md-6">Begin Row Index</label>
                  <input type='text' onkeypress="return isNumberKey(event)" name="begin_row" id="begin_row" class="form-control form-control-sm col-md-6" value="0"/>
                </div>
                <div class="row col-md-12">
                  <label class="col-md-6">End Row Index</label>
                  <input type='text' onkeypress="return isNumberKey(event)" name="end_row" id="end_row" class="form-control form-control-sm col-md-6" value="1000"/>
                </div>
                <div class="col-md-12 mt-1 float-right text-right">
                     <button type="button" class="btn ml-1 btn-primary btn-sm ml-2 float-right submit-form" >Submit</button>
                     <button type="button" class="btn btn-danger btn-sm float-right cancle-import" >Cancle</button>
                </div>
            </div>
        </form>
        </div>
      </div>
    </div>
  </div>
</div>
    <div class="modal fade" id="move-place-modal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title text-info"><b>MOVE PLACE:</b></h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="form-place" action="" method="get" accept-charset="utf-8">
                    <div class="modal-body">
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Move Place:</div>
                            </div>
                            <input type="text" class="form-control text-info"  id="place_name" disabled>
                            <input type="hidden" name="place_id" id="place_id_hidden">
                            <input type="hidden" name="customer_id" id="customer_id_hidden">
                            <input type="hidden" name="current_user" id="current_user">
                        </div>
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">To User:</div>
                            </div>
                            <select name="user_id" id="user_id" class="form-control  text-capitalize">
{{--                                @foreach($user_list as $user)--}}
{{--                                    <option value="{{$user->user_id}}">{{$user->user_nickname}} ( {{$user->getFullname()}} )</option>--}}
{{--                                @endforeach--}}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm cancel-move" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary move-place-submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
<script type="text/javascript">
 $(document).ready(function() {
    $("#created_at").datepicker({});
    var table = $('#dataTableAllCustomer').DataTable({
      // dom: "lifrtp ",
      order:[[7,'desc']],
      responsive:false,
      serverSide: true,
      processing: true,
      buttons: [

           {
               text: '<i class="fas fa-exchange-alt"></i> Move Customer',
               className: "btn-sm an",
               action: function () {
                   document.location.href = "{{route('move-customer-all')}}";
               }
           },
           {
               text: '<i class="fas fa-download"></i> Import',
               className: "btn-sm import-show an",
           },
           {
               text: '<i class="fas fa-upload"></i> Export',
               className: "btn-sm an",
               action: function () {
                  document.location.href = "{{route('export-customer')}}";
              }
           }
       ],
       ajax:{ url:"{{ route('customersDatatable') }}",
        type: 'POST',
       data: function (d) {
          d.start_date = $("#start_date").val();
          d.end_date = $("#end_date").val();
          d.address = $("#address").val();
          d.status_customer = $("#status-customer :selected").val();
          d.team_id = $("#team_id :selected").val();
          d._token = '{{ csrf_token() }}';
            }
        },
        columnDefs: [ {'targets': 0, 'searchable': false} ],
       columns: [

                { data: 'id', name: 'id',class:'w-10' },
                { data: 'ct_salon_name', name: 'ct_salon_name' },
                { data: 'ct_fullname', name: 'ct_fullname'},
                { data: 'ct_business_phone', name: 'ct_business_phone' ,class:'text-center'},
                { data: 'ct_cell_phone', name: 'ct_cell_phone',class:'text-center' },
                { data: 'ct_note', name: 'ct_note',class:'text-center' },
                { data: 'ct_status', name: 'ct_status',class:'text-center' },
                { data: 'created_at', name: 'created_at' ,class:'text-center'},
                { data: 'action' , name:'action' ,orderable: false, searcheble: false ,class:'text-center'}
        ],
    });
    var tableServiceCustomer = $('#servicedCustomer').DataTable({
      // dom: "lifrtp ",
      order:[[7,'desc']],
      responsive:false,
      serverSide: true,
      processing: true,
      buttons: [
       ],
       ajax:{ url:"{{ route('serviceCustomerDatatable') }}",
        type: 'POST',
       data: function (d) {
          d.start_date = $("#start_date").val();
          d.end_date = $("#end_date").val();
          d.address = $("#address").val();
          d.status_customer = $("#status-customer :selected").val();
          d.team_id = $("#team_id :selected").val();
          d._token = '{{ csrf_token() }}';
            }
        },
        columnDefs: [ {'targets': 0, 'searchable': false} ],
       columns: [

                { data: 'id', name: 'id',class:'w-10' },
                { data: 'ct_salon_name', name: 'ct_salon_name' },
                { data: 'ct_fullname', name: 'ct_fullname'},
                { data: 'ct_business_phone', name: 'ct_business_phone' ,class:'text-center'},
                { data: 'ct_cell_phone', name: 'ct_cell_phone',class:'text-center' },
                { data: 'ct_note', name: 'ct_note',class:'text-center' },
                { data: 'ct_status', name: 'ct_status',class:'text-center' },
                { data: 'created_at', name: 'created_at' ,class:'text-center'},
                { data: 'action' , name:'action' ,orderable: false, searcheble: false ,class:'text-center'}
        ],
    });

    // $("#formReset").on('click',function(e){
    //    $(this).parents('form')[0].reset();
    //     table.ajax.reload(null, false);
    // });
     $("#formReset").click(function () {
         $(this).parents('form')[0].reset();
         table.draw();
         tableServiceCustomer.draw();
     });

    $(document).on("click",".view",function(){

      var customer_id = $(this).attr('customer_id');
      var team_id = $("#team_id").val();

      $.ajax({
        url: '{{route('get-customer-detail')}}',
        type: 'GET',
        dataType: 'html',
        data: {
          customer_id: customer_id,
          team_id: team_id
        },
      })
      .done(function(data) {

        if(data == 0){
          toastr.error('Get Detaill Customer Error!');
        }else{
          data = JSON.parse(data);
            var button = ``;
            if(data.count_customer_user === 0)
                button = `<button type="button" id=`+data.customer_list.id+` class="btn btn-primary btn-sm get-customer">Assign</button>`;

            if(data.ct_status === 'Disabled')
                button = '';

            else if(data.customer_list.ct_status != 'New Arrivals' && data.customer_list.ct_status != 'Disabled'){
              if(data.count_serviced_customer === 0){

              }else if(data.count_customer_user === 0){
                data.customer_list.ct_salon_name = '<input type="text" name="business_name" id="business_name" class="form-control form-control-sm col-12" required>';
                data.customer_list.ct_business_phone = '<input type="text" name="business_phone" onkeypress="return isNumberKey(event)" id="business_phone" class="form-control form-control-sm col-12" required>';
              }
            }
          data = data.customer_list;
          if(data.ct_salon_name==null)data.ct_salon_name="";
          if(data.ct_contact_name==null)data.ct_contact_name="";
          if(data.ct_business_phone==null)data.ct_business_phone="";
          if(data.ct_cell_phone==null)data.ct_cell_phone="";
          if(data.ct_email==null)data.ct_email="";
          if(data.ct_address==null)data.ct_address="";
          if(data.ct_website==null)data.ct_website="";
          if(data.ct_note==null)data.ct_note="";
          if(data.ct_status==null)data.ct_status="";


          $(".modal-content-view").html(`
            <form>
            <div class="modal-header">
              <h5 class="modal-title text-center" id="exampleModalLabel"><b>Customer Detail</b></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <div class="modal-body" id="content-customer-detail">
            <div class="row pr-5 pl-5" >
            <div class="col-md-6">
              <div class="row">
                <span class="col-md-4">Business:</span>
                <p class="col-md-8"><b>`+data.ct_salon_name+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Contact Name:</span>
                <p class="col-md-8"><b>`+data.ct_fullname+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Business Phone:</span>
                <p class="col-md-8"><b>`+data.ct_business_phone+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Cell Phone:</span>
                <p class="col-md-8"><b>`+data.ct_cell_phone+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Email:</span>
                <p class="col-md-8"><b>`+data.ct_email+`</b></p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="row">
                <span class="col-md-4">Address:</span>
                <p class="col-md-8"><b>`+data.ct_address+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Website:</span>
                <p class="col-md-8"><b>`+data.ct_website+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Note:</span>
                <p class="col-md-8"><b>`+data.ct_note+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Created:</span>
                <p class="col-md-8"><b>`+data.created_at+` by `+data.user_nickname+`</b></p>
              </div>
              <div class="row">
                <span class="col-md-4">Status:</span>
                <p class="col-md-8"><b>`+data.ct_status+`</b></p>
              </div>
              <div class="row float-right">
                `+button+`
                <button type="button" class="btn btn-danger btn-sm ml-2 close-customer-detail">Close</button>
              </div>
            </div>
          </div>
          </div>
          </form>
            `);
          $("#viewModal").modal('show');
        }
      })
      .fail(function() {
        console.log("error");
      });
    });
    //CLOSE MODAL DETAI CUSTOMER
    $(document).on('click','.close-customer-detail',function(){
      $("#viewModal").modal('hide');
      $(".modal-content-view").html(``);
    });
    //GET CUSTOMER TO MY CUSTOMER
    $(document).on('click','.get-customer',function(){

      var business_name = $("#business_name").val();
      var business_phone = $("#business_phone").val();
      var customer_id = $(this).attr('id');
      if(business_name !== "" || business_phone != ""){
          $.ajax({
              url: '{{route('add-customer-to-my')}}',
              type: 'GET',
              dataType: 'html',
              data: {
                  customer_id: customer_id,
                  business_name: business_name,
                  business_phone: business_phone
              },
          })
              .done(function(data) {
                  data = JSON.parse(data);
                  // console.log(data);
                  // return;
                  if(data.status == 'success'){
                      $("#viewModal").modal('hide');
                      toastr.success('Successfully!');
                  }else
                      if(typeof(data.message) == "string")
                           toastr.error(data.message);
                      else{
                          $.each(data.message,function(ind,val){
                              toastr.error(val);
                          });
                      }
                  table.ajax.reload(null, false);
              })
              .fail(function() {
                  toastr.error('Getting Error! Check again!');
              });
      }else{
          business_phone==""?toastr.error('Enter Business Phone!'):"";
          business_name==""?toastr.error("Enter Business Name"):"";
        }

    });
    $(document).on('click','.edit-customer',function(){

      var customer_id = $(this).attr('customer_id');

      $.ajax({
        url: '{{route('editCustomer')}}',
        type: 'GET',
        dataType: 'html',
        data: {customer_id: customer_id},
      })
      .done(function(data) {
        if(data == 0){
          toastr.error('Getting Error! Check again!');
        }else{
          data = JSON.parse(data);
            if(data.ct_salon_name==null)data.ct_salon_name="";
            if(data.ct_contact_name==null)data.ct_contact_name="";
            if(data.ct_business_phone==null)data.ct_business_phone="";
            if(data.ct_cell_phone==null)data.ct_cell_phone="";
            if(data.ct_email==null)data.ct_email="";
            if(data.ct_address==null)data.ct_address="";
            if(data.ct_website==null)data.ct_website="";
            if(data.ct_note==null)data.ct_note="";
            if(data.ct_status==null)data.ct_status="";

          $(".modal-content").html(`
             <div class="modal-header">
              <h5 class="modal-title text-center" id="exampleModalLabel"><b>Edit Customer</b></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body"">
              <form id="edit-customer-form">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_salon_name">Business Name<i class="text-danger">*</i></label>
                      <input type="text" class="col-md-8 form-control form-control-sm" name="ct_salon_name" id="ct_salon_name" value="`+data.ct_salon_name+`" placeholder="">
                      <input type="hidden" name="" id="customer_id" value="`+data.id+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="first_name">First Name<i class="text-danger">*</i></label>
                      <input type="text" class="col-md-8 form-control form-control-sm" name="first_name" id="first_name" value="`+data.ct_firstname+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="last_name">Last Name<i class="text-danger">*</i></label>
                      <input type="text" class="col-md-8 form-control form-control-sm" name="last_name" id="last_name" value="`+data.ct_lastname+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_business_phone">Business Phone<i class="text-danger">*</i></label>
                      <input type="text" class="col-md-8 form-control form-control-sm" onkeypress="return isNumberKey(event)" name="ct_business_phone" id="ct_business_phone" value="`+data.ct_business_phone+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_cell_phone">Cell Phone<i class="text-danger">*</i></label>
                      <input type="text" onkeypress="return isNumberKey(event)" class="col-md-8 form-control form-control-sm" name="ct_cell_phone" id="ct_cell_phone" value="`+data.ct_cell_phone+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_email">Email</label>
                      <input type="text" class="col-md-8 form-control form-control-sm" name="ct_email" id="ct_email" value="`+data.ct_email+`" placeholder="">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_address">Address</label>
                      <input type="text" class="col-md-8 form-control form-control-sm" name="ct_address" id="ct_address" value="`+data.ct_address+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_website">Website</label>
                      <input type="text" class="col-md-8 form-control form-control-sm" name="ct_website" id="ct_website" value="`+data.ct_website+`" placeholder="">
                    </div>
                    <div class="form-group row">
                      <label class="col-md-4" for="ct_note">Note</label>
                      <textarea class="col-md-8 form-control form-control-sm" name="ct_note" id="ct_note" rows="3" >`+data.ct_note+`</textarea>
                    </div>
                    <div class="form-group float-right">
                      <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancel</button>
                      <button type="button" class="btn btn-primary btn-sm submit-edit" >Submit</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
            `);
          $("#viewModal").modal('show');
        }
      })
      .fail(function() {
        console.log("error");
      });
    });
    //SUBMIT EDIT CUSTOMER
    $(document).on("click",".submit-edit",function(){

      var first_name = $("#first_name").val();
      var last_name = $("#last_name").val();
      var ct_contact_name = $("#ct_contact_name").val();
      var ct_business_phone = $("#ct_business_phone").val();
      var ct_cell_phone = $("#ct_cell_phone").val();
      var ct_email = $("#ct_email").val();
      var ct_address = $("#ct_address").val();
      var ct_website = $("#ct_website").val();
      var ct_note = $("#ct_note").val();
      var customer_id = $("#customer_id").val();

      $.ajax({
        url: '{{route('save-customer')}}',
        type: 'GET',
        dataType: 'html',
        data: {
          first_name: first_name,
          last_name: last_name,
          ct_contact_name: ct_contact_name,
          ct_business_phone: ct_business_phone,
          ct_cell_phone: ct_cell_phone,
          ct_email: ct_email,
          ct_address: ct_address,
          ct_website: ct_website,
          ct_note: ct_note,
          customer_id: customer_id
        },
      })
      .done(function(data) {
        if(data == 0){
          toastr.error('Update Error! Check again!');
        }else{
          toastr.success('Update Success!');
          $("#viewModal").modal('hide');
          table.ajax.reload(null, false);
          $(".modal-content").html("");
        }
      })
      .fail(function() {
        console.log("error");
      });
    });
    $("#search-button").click(function(){
      table.draw();
      tableServiceCustomer.draw();
    });
    $(document).on('click','.delete-customer',function(){

      var customer_id = $(this).attr('customer_id');
      if(confirm('Do you want to be disabled this customer ?')){
         $.ajax({
          url: '{{route('delete-customer')}}',
          type: 'GET',
          dataType: 'html',
          data: {customer_id: customer_id},
        })
        .done(function(data) {
          // console.log(data);
          // return;
          if(data == 1){
            table.ajax.reload(null, false);
            toastr.success('Update Success!');
          }else
            toastr.error('Update Error!');
        })
        .fail(function() {
          toastr.error('Update Error!');
        });
      }else {
        return false;
      }

       
    });
    $(document).on('click','.deleted',function(){
      toastr.error('This Customer Deleted!');
    });
    $(document).on('click','.import-show',function(){
      $("#import-modal").modal("show");
    });
    $(".submit-form").click(function(){

      var begin_row = $("#begin_row").val();
      var end_row = $("#end_row").val();

      var formData = new FormData();
      formData.append('begin_row', begin_row);
      formData.append('end_row', end_row);
      formData.append('_token','{{csrf_token()}}')
      // Attach file
      formData.append('file', $('#file')[0].files[0]);

      $.ajax({
        url: '{{route('import-customer')}}',
        type: 'POST',
        dataType: 'html',
        data: formData,
        contentType: false,
        processData: false
      })
      .done(function(data) {
        data = JSON.parse(data);
        // console.log(data);
        // return;
        if(data.status == 'success'){
          $("#import-modal").modal('hide');
          table.draw();
          tableServiceCustomer.draw();
          toastr.success(data.message);
        }
        else
          toastr.error(data.message);
        // console.log(data);
      })
      .fail(function() {
        console.log("error");
      });
    });
    $(".cancle-import").click(function(){
      $("#import-modal").modal("hide");
    });
     $('#dataTableAllCustomer tbody').on('click', '.details-control', function () {

         var customer_template_id = $(this).attr('id');
         $(this).toggleClass('fa-plus-circle fa-minus-circle');
         var tr = $(this).closest('tr');
         var row = table.row( tr );
         var team_id = $("#team_id :selected").val();

         if ( row.child.isShown() ) {
             // This row is already open - close it
             row.child.hide();
             tr.removeClass('shown');
         }else{
             $.ajax({
                 url: '{{route('get-place-customer')}}',
                 type: 'GET',
                 dataType: 'html',
                 data: {
                     customer_template_id: customer_template_id,
                     team_id: team_id
                 },
             })
                 .done(function(data) {
                     data = JSON.parse(data);
                     console.log(data);
                     var subtask_html = "";
                     $.each(data, function(index,val){

                         if(val.get_user.length  != 0) var user_manage = val.get_user.user_nickname;
                         else var user_manage = "";

                         subtask_html += `
                                <tr>
                                    <td>`+val.get_place.place_name+`</td>
                                    <td>`+val.get_place.place_phone+`</td>
                                    <td>`+val.get_place.place_ip_license+`</td>
                                    <td>`+user_manage+`</td>
                                    <td class="text-center">
                                         <a class="btn btn-sm btn-secondary move-place"
                                            user_id="`+val.get_user.user_id+`"
                                            place_name="`+val.get_place.place_name+`"
                                            place_id="`+val.get_place.place_id+`"
                                            customer_id="`+val.customer_id+`" href="javascript:void(0)" title="Move Place To User">
                                            <i class="fas fa-exchange-alt"></i>
                                         </a>
                                    </td>
                                </tr> `;
                     });
                     row.child(format(row.data()) +subtask_html+"</table>" ).show();
                     tr.addClass('shown');
                 })
                 .fail(function() {
                     toastr.error('Get SubTask Failed!');
                 });
         }
     } );
     function format ( d ) {
         // `d` is the original data object for the row
         return `<table class="border border-info table-striped table table-border bg-white">
            <tr class="bg-info text-white">
                <th scope="col">Name</th>
                <th scope="col">Phone</th>
                <th>Liences</th>
                <th>User Manager</th>
                <th class="text-center">Action</th>
            </tr>`;
     }
     $(document).on('click',".move-place",function(){
         var place_name = $(this).attr('place_name');
         var place_id = $(this).attr('place_id');
         var customer_id = $(this).attr('customer_id');
         var user_id = $(this).attr('user_id');
         $("#place_id_hidden").val(place_id);
         $("#customer_id_hidden").val(customer_id);
         $("#current_user").val(user_id);
         $("#place_name").val(place_name);
         $("#move-place-modal").modal('show');

         //GET USER'S TEAM
         var team_id = $("#team_id :selected").val();
         $.ajax({
             url: '{{route('get_user_form_team')}}',
             type: 'GET',
             dataType: 'html',
             data: {
                 team_id: team_id,
                 user_id: user_id
             },
         })
             .done(function(data) {

                 data = JSON.parse(data);
                 console.log(data);
                 if(data.status == 'error')
                     toastr.error(data.message);
                 else{
                     option_html = '';
                     $.each(data.user_list,function(ind,val){
                         option_html += `<option value="`+val.user_id+`">`+val.user_nickname+`(`+val.user_firstname+val.user_lastname+`)</option>`;
                     });
                     $("#user_id").html(option_html);
                 }
             })
             .fail(function() {
                 console.log("error");
             });
     });
     $(".move-place-submit").click(function(){
         var formData = new FormData($(this).parents('form')[0]);
         formData.append('_token','{{csrf_token()}}');
         formData.append('team_id',$("#team_id :selected").val());

         $.ajax({
             url: '{{route('move_place')}}',
             type: 'POST',
             dataType: 'html',
             processData: false,
             contentType: false,
             data: formData,
         })
             .done(function(data) {
                 data = JSON.parse(data);
                 if(data.status == 'error')
                     toastr.error(data.message);
                 else{
                     toastr.success(data.message);
                     cleanModalPlace();
                 }
             })
             .fail(function() {
                 console.log("error");
             });
     });
     function cleanModalPlace(){
         $("#form-place")[0].reset();
         $("#move-place-modal").modal('hide');
         table.ajax.reload(null, false);
     }
     $(".cancel-move").click(function () {
         cleanModalPlace();
     });
     $(".custom-file-input").on("change", function() {
      var fileName = $(this).val().split("\\").pop();
      $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
     $(document).on("keypress","#business_phone,#ct_cell_phone,#ct_business_phone",function() {
       let number_phone = $(this).val();

       if(number_phone.length >9)
        return false;
     });
});
</script>
@endpush

