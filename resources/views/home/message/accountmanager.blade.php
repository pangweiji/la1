@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>帐号列表</h3>

	<!-- 添加帐号 start -->
	<div id="addAccount" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">添加帐号</h4>
				</div>

				<div class="modal-body">
					<form id="addAccountForm" method="post" action="">
						
						<div class="form-group">
							<label>平台</label>
							<select class="form-control input-sm" id="InputPlatform1">
								<option value="-1">请选择</option>
								<option value="amazon">amazon</option>
								<option value="walmart">walmart</option>
							</select>
						</div>

						<div class="form-group">
							<label for="InputAccount1">帐号</label>
							<input type="text" class="form-control" id="InputAccount1" placeholder="Enter account" required>
						</div>
						<div class="form-group">
							<label for="InputEmail1">Gmail邮箱</label>
							<input type="email" class="form-control" id="InputEmail1" placeholder="Enter email"  required >
						</div>
						<div class="form-group">
							<label for="InputPassword1">密码</label>
							<input type="password" class="form-control" id="InputPassword1" placeholder="Password">
						</div>
					</form>

				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
					<button type="button" class="btn btn-primary" id="btnSubmit">提交</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<div class="bs-example" style="padding-bottom: 5px;">
		<button class="btn btn-primary" data-toggle="modal" data-target="#addAccount">
		添加帐号
		</button>
	</div>
	<!-- 添加帐号 end -->

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

	$('#btnSubmit').click(function() {
		var platform = $('#InputPlatform1').val();
		var account = $('#InputAccount1').val();
		var email = $('#InputEmail1').val();
		var password = $('#InputPassword1').val();
		var _token = $('#addAccountForm input[name=_token]').val();

		if (account.length < 1) {
			toastr.warning('帐号不能为空！');
			return false;
		}
		if (email.length < 1) {
			toastr.warning('邮箱不能为空！');
			return false;
		}
		if (password.length < 1) {
			toastr.warning('密码不能为空！');
			return false;
		}
		if (platform == -1) {
			toastr.warning('请选择平台！');
			return false;
		}

		//ajax提交
		$.ajax({
			url: '{{ url(config("laraadmin.homeRoute")."/messgae/ajax_add_account") }}',
			type: 'POST',
			dataType: 'json',
			data: {platform: platform, account: account, email: email, password: password},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		})
		.success(function (data) {
			console.log(data);
		})
		.done(function() {
			console.log("success");
		})
		.fail(function() {
			console.log("error");
		})
	});
</script>
@endsection