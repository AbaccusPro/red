@extends('vendor.installer.layouts.master')

@section('title', trans('messages.requirements.title'))
@section('container')

<ul class="list-group">
    @foreach($requirements['requirements'] as $extention => $enabled)
    <li class="list-group-item">
    	{{ $extention }}
    	<div class="pull-right">
    		@if($enabled) 
	    		<i class="fa success fa-check-circle-o"></i>
	    	@else 
	    		<i class="fa error fa-times-circle-o"></i>
	    	@endif
    	</div>
    </li>
    @endforeach
</ul>

@if(!isset($requirements['errors']))
    <div class="btn-installer">
    	<a class="btn btn-primary" href="{{ route('LaravelInstaller::permissions') }}">
		    {{ trans('messages.next') }}
	    </a>
    </div>
@endif

@stop