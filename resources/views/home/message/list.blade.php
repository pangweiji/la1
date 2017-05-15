@extends('home.layouts.app')

@section('content')
<div class="col-md-12 column">
	<h3>收件箱</h3>
</div>
<table class="table table-hover">
	<thead>
		<tr>
			<th>邮件编号</th>
			<th>发件人</th>
			<th>收件人</th>
			<th>邮件状态</th>
			<th width='20%'>主题</th>
			<th>邮件发送时间</th>
			<th>邮件进入系统时间</th>
			<th>回复时间</th>
		</tr>
	</thead>
	<tbody>
		@foreach($list as $v)
			<tr>
				<td>{{ $v->id }}</td>
				<td>{{ $v->receiveid }}</td>
				<td>{{ $v->sendid }}</td>
				<td>{{ $v->status }}</td>
				<td><a href="{{ url('home/message_detail').'/'.$v->id }}">{{ $v->subject }}</a></td>
				<td>{{ $v->sendtime }}</td>
				<td>{{ $v->receivetimestamp}}</td>
				<td>{{ $v->replytime }}</td>
			</tr>
		@endforeach
	</tbody>
</table>
{{ $list->links() }}

@endsection('content')