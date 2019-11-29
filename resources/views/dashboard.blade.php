@extends('layouts.app')
@section('title','DTI - Dashboard')
@section('content-title')
    Dashboard
@endsection
@section('content')
	<div class="col-12">
          <!-- Content Row -->
          <div class="row">
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Earnings (Monthly)</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">$40,000?</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Earnings (Annual)</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">${{number_format($earnings)}}</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Tasks</div>
                      <div class="row no-gutters align-items-center">
                        <div class="col-auto">
                          <div class="h5 mb-0 font-weight-bold text-gray-800">{{$pendingTasks}}</div>
                        </div>
                      </div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total customers nearly expired</div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">{{$nearlyExpired}}</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Content Row -->

          <div class="row">
            <!-- Area Chart -->
            <div class="col-xl-6 col-lg-6">
              <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary"><a href="{{ route('statisticsCustomer') }}">Show Details</a></h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  <div id="new-customer-chart" style="height: 300px; width: 100%;"></div>
                </div>
              </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-6 col-lg-6">
              <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary"><a href="{{ route('statisticsService') }}">Show Details</a></h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                  	<div id="top-10-services-chart" style="height: 300px; width: 100%;"></div>
                </div>
              </div>
            </div>
          </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <span class="m-0 font-weight-bold text-primary">Processing Task</span> <a href="{{route('my-task')}}">View More >>></a>
                    </div>
                    <table class="table table-striped table-hover" id="datatable-task-dashboard" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Task#</th>
                            <th>Subject</th>
                            {{--                        <th class="text-center">Priority</th>--}}
                            <th class="text-center">Status</th>
                            {{--                        <th class="text-center">Date Start</th>--}}
                            {{--                        <th class="text-center">Date end</th>--}}
                            {{--                        <th class="text-center">%Complete</th>--}}
                            <th class="text-center">Category</th>
                            <th class="text-center">Order#</th>
                            <th class="text-center">Last Updated</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            @if(\Gate::allows('permission','dashboard-customer'))
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <span class="m-0 font-weight-bold text-primary">Customer is about to expire</span> <a href="{{route('myCustomers')}}">View More >>></a>
                    </div>
                    <table class="table table-striped table-hover" id="datatable-customer-service" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Customer Phone</th>
                            <th>Service</th>
                            <th>Expired Date</th>
                            <th>Seller</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
@push('scripts')
<script type="text/javascript">
		window.onload = function () {
			var chartCustomer = new CanvasJS.Chart("new-customer-chart", {
				title:{
					text: "New customers in 12 months"
				},
				data: [
				{
					type: "column",
					dataPoints: [
						{ label: "Jan",  y: {{$newCustomer['1'] ?? '0'}}  },
						{ label: "Feb",  y: {{$newCustomer['2'] ?? '0'}}  },
						{ label: "Mar",  y: {{$newCustomer['3'] ?? '0'}}  },
						{ label: "Apr",  y: {{$newCustomer['4'] ?? '0'}}  },
						{ label: "May",  y: {{$newCustomer['5'] ?? '0'}}  },
						{ label: "Jun",  y: {{$newCustomer['6'] ?? '0'}}  },
						{ label: "Jul",  y: {{$newCustomer['7'] ?? '0'}}  },
						{ label: "Aug",  y: {{$newCustomer['8'] ?? '0'}}  },
						{ label: "Sep",  y: {{$newCustomer['9'] ?? '0'}}  },
						{ label: "Oct",  y: {{$newCustomer['10'] ?? '0'}}  },
						{ label: "Nov",  y: {{$newCustomer['11'] ?? '0'}}  },
						{ label: "Dec",  y: {{$newCustomer['12'] ?? '0'}}  }
					]
				}
				]
			});
			chartCustomer.render();

			var chartServices = new CanvasJS.Chart("top-10-services-chart", {
				animationEnabled: true,
				axisX:{
			    gridThickness: 0,
			    tickLength: 0,
			    lineThickness: 0,
			    labelFormatter: function(){
			      return " ";
			    	}
			  	},
				title:{
					text: "Top 10 most popular services in month"
				},
				data: [
				{
					type: "column",
					dataPoints: [
						{ label: "{{$popularServices[0]['nameService'] ?? ' '}}",  y: {{$popularServices[0]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[1]['nameService'] ?? ' '}}",  y: {{$popularServices[1]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[2]['nameService'] ?? ' '}}",  y: {{$popularServices[2]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[3]['nameService'] ?? ' '}}",  y: {{$popularServices[3]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[4]['nameService'] ?? ' '}}",  y: {{$popularServices[4]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[5]['nameService'] ?? ' '}}",  y: {{$popularServices[5]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[6]['nameService'] ?? ' '}}",  y: {{$popularServices[6]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[7]['nameService'] ?? ' '}}",  y: {{$popularServices[7]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[8]['nameService'] ?? ' '}}",  y: {{$popularServices[8]['count'] ?? '0'}}  },
						{ label: "{{$popularServices[9]['nameService'] ?? ' '}}",  y: {{$popularServices[9]['count'] ?? '0'}}  },

					]
				}
				]
			});
			chartServices.render();
			$("a.canvasjs-chart-credit").remove();
            var table = $('#datatable-customer-service').DataTable({
                // dom: "lBfrtip",
                buttons: [
                ],
                processing: true,
                serverSide: true,
                paging:false,
                searching: false,
                info:false,
                responsive: false,
                ajax:{ url:"{{ route('customer-service-datatable') }}",
                    data: function (d) {
                    }
                },
                columns: [
                    { data: 'cs_id', name: 'cs_id',class:'text-center' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'customer_phone', name: 'customer_phone' },
                    { data: 'service_info', name: 'service_info'},
                    { data: 'expired_date', name: 'expired_date',class: 'text-center'},
                    { data: 'seller_name', name: 'seller_name'},
                    { data: 'action' , name:'action' ,orderable: false, searcheble: false ,class:'text-center'}
                ],
            });
		}
		$(document).ready(function () {
            var table = $('#datatable-task-dashboard').DataTable({
                // dom: "lBfrtip",
                responsive: false,
                order:[[5,'desc']],
                info: false,
                paging:false,
                searching: false,
                buttons: [
                ],
                processing: true,
                serverSide: true,
                ajax:{ url:"{{route('my-task-datatable')}}",
                    data: function (d) {
                        d.task_dashboard = 'task-dashboard';
                    }
                },
                columns: [
                    { data: 'task', name: 'task',class:'text-center' },
                    { data: 'subject', name: 'subject',class:'text-center' },
                    // { data: 'priority', name: 'priority',class:'text-center' },
                    { data: 'status', name: 'status',class:'text-center' },
                    // { data: 'date_start', name: 'date_start',class:'text-center' },
                    // { data: 'date_end', name: 'date_end',class:'text-center' },
                    // { data: 'complete_percent', name: 'complete_percent',class: 'text-center' },
                    { data: 'category', name: 'category',class: 'text-center' },
                    { data: 'order_id', name: 'order_id',class: 'text-center' },
                    { data: 'updated_at', name: 'updated_at',class: 'text-center'},
                ],
            });
            $('#datatable-task-dashboard tbody').on('click', '.details-control', function () {

                var task_id = $(this).attr('id');
                $(this).toggleClass('fa-plus-circle fa-minus-circle');
                var tr = $(this).closest('tr');
                var row = table.row( tr );

                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }else{
                    $.ajax({
                        url: '{{route('get-subtask')}}',
                        type: 'GET',
                        dataType: 'html',
                        data: {
                            task_id: task_id,
                        },
                    })
                        .done(function(data) {
                            data = JSON.parse(data);
                            var subtask_html = "";
                            $.each(data.data, function(index,val){

                                var complete_percent = "";
                                if(val.complete_percent == null)  complete_percent = "";
                                else complete_percent = val.complete_percent;

                                subtask_html += `
                                <tr>
                                    <td>`+val.task+`</td>
                                    <td>`+val.subject+`</td>
                                    <td>`+val.priority+`</td>
                                    <td>`+val.status+`</td>
                                    <td>`+val.date_start+`</td>
                                    <td>`+val.date_end+`</td>
                                    <td>`+complete_percent+`</td>
                                    <td>`+val.category+`</td>
                                    <td>`+val.assign_to+`</td>
                                    <td>`+val.updated_at+`</td>
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
        });
        function format ( d ) {
            // `d` is the original data object for the row
            return `<table class="border border-info table-striped table table-border bg-white">
            <tr class="bg-info text-white">
                <th scope="col">SubTask</th>
                <th scope="col">Subject</th>
                <th class="text-center">Priority</th>
                <th class="text-center">Status</th>
                <th class="text-center">Date Start</th>
                <th class="text-center">Date end</th>
                <th class="text-center">%Complete</th>
                <th class="text-center">Category</th>
                <th class="text-center">Assign To</th>
                <th class="text-center">Last Updated</th>
            </tr>`;
        }
</script>
@endpush

