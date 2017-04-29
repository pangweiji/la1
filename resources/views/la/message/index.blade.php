<!DOCTYPE html>
<html>
<head>
	<title>Message</title>
</head>
<body>
	<form>
		{{ csrf_field() }}
	</form>
	三元：
	{{ $name or 'Default' }}<br>
	
	原生数据：{!! $html !!}<br>
	处理后数据:{{ $html }}<br>
	输出原始格式：@{{ $name }}<br>
	循环：
		@foreach ($users as $user)
			@if ($loop->first)
				{{ $user }}
			@endif
			
			@if ($loop->last)
				{{ $user }}
			@endif
		@endforeach
</body>
</html>