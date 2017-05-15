@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>邮箱帐号列表</h3>
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
							<label for="InputAccount1">帐号</label>
							<input type="text" class="form-control" id="InputAccount1" placeholder="Enter account" required>
						</div>
						<div class="form-group">
							<label for="InputEmail1">邮箱</label>
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
			<th>帐号</th>
			<th>Email</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
		@foreach($list as $v)
			<tr>
				<td>{{ $v->id }}</td>
				<td>{{ $v->account }}</td>
				<td>{{ $v->email }}</td>
				<td>
					<button type="button" class="btn btn-primary">修改</button>
					<button type="button" onclick="deleteAccount({{ $v->id }})" class="btn btn-danger">删除</button>
				</td>
			</tr>
		@endforeach
	</tbody>
</table>
{{ $list->links() }}

@endsection('content')

@section('scripts')
<script type="text/javascript">
	$('#btnSubmit').click(function() {
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

		//ajax提交
		$.ajax({
			url: '{{ url(config("laraadmin.homeRoute")."/message_addAccount_ajax") }}',
			type: 'POST',
			dataType: 'json',
			data: {account: account, email: email, password: password},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		})
		.success(function (data) {
			if (data.code != 1001) {
				toastr.warning(data.msg);
			} else {
				toastr.success('操作成功！');
			}
		})
	});

	function deleteAccount(id) {
		$.getJSON('{{ url(config("laraadmin.homeRoute")."/message_deleteAccount_ajax") }}/'+id, function(data) {
			/*optional stuff to do after success */
			if (data.code != 2001) {
				toastr.warning(data.msg);
			} else {
				toastr.success('操作成功！');
			}
		});
	}
</script>
@endsection