@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>邮件详情</h3>
</div>
<div class="container">
	@foreach($msg as $m)
	<div class="row">
		<div class="col-md-12 column" style="height:40px;background-color: #e7e7e7;line-height: 40px;margin-bottom: 4px;border-radius: 4px;">
			发送人：{{ $m->receiveid }}&nbsp;&nbsp; 收件人：{{ $m->sendid }}&nbsp;&nbsp;进入系统时间：{{ date('Y-m-d H:i:s', $m->receivetimestamp) }}
		</div>
		<div class="col-md-4 column">
			<div style="font-size: 16px;">邮件主题：{{ $m->subject }}</div>
			<textarea class="form-control" style="resize:none;overflow: scroll;" rows="24" >{{ $m->plaincontent }}</textarea>
		</div>
		<div class="col-md-8 column">
			<form role="form">
				<div class="form-group">
					<textarea id="replycontent" name="replycontent" placeholder="这里输入内容" autofocus></textarea>
				</div>
				<div>
					上传附件：
				</div>
				<input type="hidden" name="msgid" value="{{ $m->id }}" />
				@if($m->status == 2)
					<button id="reply" type="button" class="btn btn-primary">已回复</button>
				@elseif($m->status == 1)
					<button id="reply" type="button" class="btn btn-primary">正在回复</button>
				@elseif($m->status == 4)
					<button id="reply" type="button" class="btn btn-primary">回复失败</button>
				@endif
				
			</form>
		</div>	
	</div>
	@endforeach
</div>


@endsection('content')

@section('styles')
<link href="{{ asset('/la-assets/plugins/simditor/styles/simditor.css') }}" rel="stylesheet">
@endsection

@section('scripts')
<script src="{{ asset('/la-assets/plugins/simditor/scripts/module.js') }}"></script>
<script src="{{ asset('/la-assets/plugins/simditor/scripts/hotkeys.js') }}"></script>
<script src="{{ asset('/la-assets/plugins/simditor/scripts/uploader.js') }}"></script>
<script src="{{ asset('/la-assets/plugins/simditor/scripts/simditor.js') }}"></script>

<script>
var editor = new Simditor({
  textarea: $('#replycontent')
  //optional options
});

$(function () {
	$('#reply').click(function() {
		var replycontent = $('#replycontent').val();
		if (replycontent.length < 1) {
			toastr.warning('内容不能为空！');
			return false;
		}

		var url = '/home/message_reply';
		var msgid = $('form input[name=msgid]').val();
		$('#reply').html('正在回复');
		$.ajax({
			url: url,
			type: 'POST',
			dataType: 'json',
			data: {msgid: msgid, replycontent: replycontent},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		})
		.success(function (data) {
			if (data.code != 3001) {
				toastr.error(data.msg);
				return false;
			} else {
				$('#reply').html('已回复');
				toastr.success(data.msg);
				return false;
			}
		})

	});
});

</script>
@endsection