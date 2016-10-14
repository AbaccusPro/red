@extends('vendor.installer.layouts.master')

@section('title', trans('messages.environment.title'))
@section('container')
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    <form method="post" class="installer-form" action="{{ route('LaravelInstaller::environmentSave') }}">
        <textarea class="form-control" rows="12" name="envConfig">{{ $envConfig }}</textarea>
        {!! csrf_field() !!}
             <button class="btn btn-success" type="submit">{{ trans('messages.environment.save') }}</button>
    </form>
    @if(!isset($environment['errors']))
    <div class="btn-installer">
        <a class="btn btn-primary" href="{{ route('LaravelInstaller::requirements') }}">
        {{ trans('messages.next') }}
        </a>
    </div>
    @endif
@stop