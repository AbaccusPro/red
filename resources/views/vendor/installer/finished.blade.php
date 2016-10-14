@extends('vendor.installer.layouts.master')

@section('title', trans('messages.final.title'))
@section('container')
    <p class="paragraph">{{ session('message')['message'] }}</p>
    <div class="btn-installer">
    	<a href="{!! url('/') !!}" class="btn btn-primary">{{ trans('messages.final.exit') }}</a>
    </div>
@stop