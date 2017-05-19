@extends('home.layouts.app')

@section('content')

{!! Form::open(['url' => 'home/test_form', 'method' => 'post']) !!}
{!! Form::label('email', 'E-Mail Address', ['class' => 'email']) !!}
{!! Form::text('email', '', ['class' => 'form-control']) !!}
{!! Form::file('file', ['class' => 'form-control']) !!}
{!! Form::close() !!}
@endsection('content')