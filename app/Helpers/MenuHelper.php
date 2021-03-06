<?php
namespace App\Helpers;

class MenuHelper{

	public static function getMenuList(){
		return [
		    ['text' => 'Dashboard', 'icon'=>'fas fa-tachometer-alt', 'link' => 'dashboard'],
		    ['text' => 'Task', 'icon'=>'fas fa-users', 'link' => 'task','childrens' => [
                ['text' => 'All Task', 'link'=> 'task/all-task'],
		        ['text' => 'My Task', 'link'=> 'task'],
		        ['text' => 'Create New Task', 'link'=> 'task/add'],
		    ]],
		    ['text' => 'Customers', 'icon'=>'fas fa-users', 'link' => 'customer','childrens' => [
		        ['text' => 'All Customers', 'link'=> 'customer/customers'],
		        ['text' => 'My Customer', 'link'=> 'customer/my-customers'],
		        ['text' => 'Create New Customer', 'link'=> 'customer/add'],
		    ]],
		    ['text' => 'Marketing', 'icon'=>'fas fa-lightbulb', 'link' => 'marketing','childrens' => [
		        ['text' => 'Send SMS', 'link'=> 'marketing/sendsms'],
		        ['text' => 'Tracking History', 'link'=> 'marketing/tracking-history'],
		        ['text' => 'News', 'link'=> 'marketing/news'],
		    ]],
		    /*['text' => 'DataSetup', 'icon'=>'fas fa-database', 'link' => 'datasetup','childrens' => [
		        ['text' => 'Combo', 'link'=> 'datasetup/combos'],
		        ['text' => 'Services', 'link'=> 'datasetup/services'],
		        ['text' => 'Service Details', 'link'=> 'datasetup/servicedetails'],
		        ['text' => 'Themes', 'link'=> 'datasetup/themes'],
		        ['text' => 'Licenses', 'link'=> 'datasetup/licenses'],
		    ]],*/
		    ['text' => 'Statistic', 'icon'=>'fas fa-chart-bar', 'link' => 'statistic','childrens' => [
		        // ['text' => 'Seller', 'link'=> 'statistic/seller'],
		        // ['text' => 'POS', 'link'=> 'statistic/pos'],
		        // ['text' => 'Website', 'link'=> 'statistic/website'],
		        ['text' => 'Customers', 'link'=> 'statistic/customers'],
		        ['text' => 'Services', 'link'=> 'statistic/services'],
		    ]],
		     ['text' => 'IT Tools', 'icon'=>'fas fa-toolbox', 'link' => 'tools','childrens' => [
		        // ['text' => 'Clone Website', 'link'=> 'tools/clonewebsite'],
		        // ['text' => 'Update Website', 'link'=> 'tools/updatewebsite'],
		        ['text' => 'Website theme', 'link'=> 'tools/website-themes'],
		        ['text' => 'App banners', 'link'=> 'tools/app-banners'],
		        ['text' => 'Places', 'link'=> 'tools/places'],
		        ['text' => 'Coupon/Promotion Template', 'link'=> 'tools/template'],
		    ]],
		    ['text' => 'Orders', 'icon'=>'fas fa-shopping-cart', 'link' => 'orders','childrens' => [
		        ['text' => 'My Orders', 'link'=> 'orders/my-orders'],
		        ['text' => 'All Orders', 'link'=> 'orders/all'],
		        ['text' => "Seller's Orders", 'link'=> 'orders/sellers'],
		        ['text' => "New Order", 'link'=> 'orders/add'],
		    ]],
		    ['text' => 'Users', 'icon'=>'fas fa-user-cog', 'link' => 'user','childrens' => [
		        ['text' => 'Users', 'link'=> 'user/list'],
		        ['text' => 'Roles', 'link'=> 'user/roles'],
                ['text' => 'User Permission', 'link'=> 'user/user-permission'],

                ['text' => 'Service Permission', 'link'=> 'user/service-permission'],
		    ]],
		    ['text' => 'Settings', 'icon'=>'fas fa-cog', 'link' => 'setting','childrens' => [
		        ['text' => 'Setup Team', 'link'=> 'setting/setup-team'],
		        ['text' => 'Setup Team Type', 'link'=> 'setting/setup-team-type'],
		        ['text' => 'Setup Service', 'link'=> 'setting/setup-service'],
                ['text' => 'Setup Service Type', 'link'=> 'setting/setup-service-type'],
                ['text' => 'Setup Template SMS', 'link'=> 'setting/setup-template-sms'],
		        ['text' => 'Setup Login Background', 'link'=> 'setting/login-background'],
                ['text' => 'Setup Event Holiday', 'link'=> 'setting/setup-event-holiday'],
                ['text' => 'Setup Menu', 'link'=> 'setting/menu'],
                ['text' => 'Setup Permission', 'link'=> 'setting/setup-permission'],

                ['text' => 'Setup Type Template', 'link'=> 'setting/setup-type-template'],
		    ]],
            ['text' => 'Notification', 'icon'=>'fas fa-sms', 'link' => 'notification'],
		    ['text' => 'Recent Logs', 'icon'=>'fas fa-list-alt', 'link' => 'recentlog'],
		];
	}
}

 ?>
