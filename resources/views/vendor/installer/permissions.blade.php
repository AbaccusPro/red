@extends('vendor.installer.layouts.master')

@section('title', trans('messages.permissions.title'))
@section('container')

<ul class="list-group">
    @foreach($permissions['permissions'] as $permission)
    	<li class="list-group-item">
        	{{ $permission['folder'] }}<span>
        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        	{{ $permission['permission'] }}</span>
        	<div class="pull-right">
        		@if($permission['isSet'])
		    		<i class="fa success fa-check-circle-o"></i>
	        	@else
		    		<i class="fa error fa-times-circle-o"></i>
	        	@endif
        	</div>
        </li>
    @endforeach
</ul>

<div class="text-center">
	<i class="fa fa-spin fa-spinner hidden"></i>
</div>


@if(!isset($permissions['errors']))
	<div class="btn-installer">
		<a class="btn btn-primary" href="{{ route('LaravelInstaller::database') }}">
	    {{ trans('messages.install') }}
	</a>
	</div>
@endif

@stop


<script type="text/javascript">
	$('.btn-primary').on('click',function(e){
		e.preventDefault();
		$('.fa-spin').removeClass('hidden');
	});
</script>