@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>帐号列表</h3>
</div>

	<table class="table table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>Gamil邮箱</th>
			<th>状态</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>1</td>
			<td>pang@gmail.com</td>
			<td>未授权</td>
			<td>
				<button gmail="pang@gmail.com" platform="amazon" id="auth" class="btn btn-primary">Gmail授权</button>
			</td>
		</tr>
	</tbody>
</table>

@endsection('content')

@section('scripts')
<script type="text/javascript">
	var subWindow;
	$('#auth').click(function () {
		if (subWindow == null || subWindow.closed) {
			var gmail = $(this).attr('gmail');
			var platform = $(this).attr('platform');
			subWindow = window.open("{{ url('home/message/auth') }}?gmail="+gmail+"&platform="+platform, "", "top=150px,left=350px,width=600px,height=450px,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,z-look=yes")
		} else {
			toastr.warning('同时智能打开一个授权窗口！');
		}
	});
</script>
@endsection