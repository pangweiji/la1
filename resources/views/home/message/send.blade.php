@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>发送邮件</h3>
</div>
<form class="form-horizontal" method="post" action="">
	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<div class="form-group">
		<label for="inputEmail1" class="col-md-2 control-label">发送邮箱</label>
		<div class="col-md-9">
			<input type="text" name="sendemail" class="form-control" id="inputEmail1" placeholder="Email">
		</div>
	</div>
	<div class="form-group">
		<label for="inputEmail3" class="col-md-2 control-label">收件邮箱</label>
		<div class="col-md-9">
			<input type="text" name="receiveemail" class="form-control" id="inputEmail3" placeholder="Email">
		</div>
	</div>
	<div class="form-group">
		<label for="inputSubject3" class="col-md-2 control-label">主题</label>
		<div class="col-md-9">
			<input type="text" name="subject" class="form-control" id="inputSubject3" placeholder="Subject">
		</div>
	</div>
	<div class="form-group">
		<label for="inputContent3" class="col-md-2 control-label">内容</label>
		<div class="col-md-9">
			<textarea class="form-control" rows="15" name="content"></textarea>
		</div>
	</div>
	<div class="form-group">
		<div class="col-md-offset-2 col-md-9">
			<button type="submit" name="submit" class="btn btn-default">发送</button>
		</div>
	</div>
</form>
@endsection('content')