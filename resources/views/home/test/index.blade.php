@extends('home.layouts.app')

@section('content')

{!! Form::open(['url' => 'home/test_form', 'method' => 'post']) !!}
{!! Form::label('email', 'E-Mail Address', ['class' => 'email']) !!}
{!! Form::text('email', '', ['class' => 'form-control']) !!}
{!! Form::file('file', ['class' => 'form-control']) !!}
{!! Form::close() !!}
<button id='show' class="btn">test_show</button>
@endsection('content')

@section('scripts')
<script>
	$(function () {
		$('#show').click(function () {
			var win = window.open("{{ url('home/test_show') }}", "file", "top=150px,left=350px,width=600px,height=450px,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,z-look=yes");
		});
	});
</script>
@endsection('scripts')