@extends('layouts.app')
@section('content-title')
    ALL ORDERS
@endsection
@section('content')
    <div class="table-responsive">
    <form>
        <div class="form-group col-md-12 row">
            <div class="col-md-4">
                <label for="">Created date</label>
                <div class="input-daterange input-group" id="created_at">
                  <input type="text" class="input-sm form-control form-control-sm" id="start_date" value="{{ today()->subMonth(1)->format('m/d/Y') }}" name="start" />
                  <span class="input-group-addon">to</span>
                  <input type="text" class="input-sm form-control form-control-sm" value="{{ today()->format('m/d/Y') }}" id="end_date" name="end" />
                </div>
            </div>
            <div class="col-md-2">
                <label for="">Last 4 card Number</label>
                <input type="text" name="csb_card_number" id="csb_card_number" onkeypress="return isNumberKey(event)" class="form-control form-control-sm">
            </div>

           {{--  <div class="col-md-2">
                <label for="">Status</label>
                <select id="status-customer" name="status_customer" class="form-control form-control-sm">
                    <option value="">-- ALL --</option>
                    @foreach ($status as $key =>  $element)
                        <option value="{{$key}}">{{$element}}</option>
                    @endforeach
                </select>
            </div> --}}
            <div class="col-2 " style="position: relative;">
                <div style="position: absolute;top: 50%;" class="">
                <input type="button" class="btn btn-primary btn-sm" id="search-button" value="Search">
                <input type="button" class="btn btn-secondary btn-sm" id="reset-btn" value="Reset">
                </div>
            </div>
        </div>
    </form>
    <hr>
    <table class="table table-sm table-bordered table-hover" id="dataTableAllCustomer" width="100%" cellspacing="0">
        <thead>
            <tr class="text-center thead-light">
                <th>Id</th>
                <th>Order Date</th>
                <th>Customer</th>
                <th>Services</th>
                <th>Subtotal($)</th>
                <th>Discount($)</th>
                <th>Total Charged($)</th>
                <th>Payment Date</th>
                <th>Info</th>
            </tr>
        </thead>
    </table>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
 $(document).ready(function() {
    $("#created_at").datepicker({});
    var table = $('#dataTableAllCustomer').DataTable({
         // dom: "lBfrtip",
        order: [[1,'desc']],
            buttons: [
            ],
            processing: true,
            serverSide: true,
        ajax:{ url:"{{ route('my-order-datatable') }}",

        data: function (d) {
            d.start_date = $("#start_date").val();
            d.end_date = $("#end_date").val();
            d.csb_card_number = $('#csb_card_number').val();
            }
        },
        columns: [

            { data: 'id', name: 'id',class:'text-center' },
            { data: 'order_date', name: 'order_date', class:'text-center' },
            { data: 'customer', name: 'customer'},
            { data: 'servivce', name: 'servivce' },
            { data: 'subtotal', name: 'subtotal',class:'text-right' },
            { data: 'discount', name: 'discount',class:'text-right' },
            { data: 'total_charge', name: 'total_charge',class:'text-right' },
            { data: 'updated_at', name: 'updated_at',class:'text-center' },
            { data: 'information', name: 'information'},
                  // { data: 'action' , name:'action' ,orderable: false, searcheble: false ,class:'text-center'}
        ],
    });
    $("#search-button").click(function(){
        table.draw();
    });
    $("#csb_card_number").keypress(function(event) {
        let csb_card_number = $(this).val();
        if(csb_card_number.length > 3 ){
            return false;
        }
    });
    $("#reset-btn").click(function(){
        $(this).parents('form')[0].reset();
        table.draw();
    })
});
</script>
@endpush
