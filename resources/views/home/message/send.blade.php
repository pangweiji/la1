@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>发送邮件</h3>
</div>
<form class="form-horizontal" action="">
	<div class="form-group">
		<label for="inputEmail3" class="col-md-2 control-label">Sendid</label>
		<div class="col-md-9">
			<input type="email" class="form-control" id="inputEmail3" placeholder="Email">
		</div>
	</div>
	<div class="form-group">
		<label for="inputSubject3" class="col-md-2 control-label">Subject</label>
		<div class="col-md-9">
			<input type="text" class="form-control" id="inputSubject3" placeholder="Subject">
		</div>
	</div>
	<div class="form-group">
		<label for="inputContent3" class="col-md-2 control-label">Content</label>
		<div class="col-md-9">
			<textarea class="form-control" rows="15"></textarea>
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-offset-2 col-md-9">
			<button type="submit" class="btn btn-default">提交</button>
		</div>
	</div>
</form>
@endsection('content')