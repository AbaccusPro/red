@extends('vendor.installer.layouts.master')

@section('title', trans('messages.welcome.title'))
@section('container')
    <p class="paragraph">{{ trans('messages.welcome.message') }}</p>
    <div class="btn-installer"><a href="{{ route('LaravelInstaller::environment') }}" class="btn btn-primary">{{ trans('messages.next') }}</a></div>

@stop