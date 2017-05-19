<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>@yield('title', config('app.name'))</title>

	<link href="{{ asset('/la-assets/css/bootstrap.css') }}" rel="stylesheet">
	<link href="{{ asset('/la-assets/plugins/toastr/build/toastr.css') }}" rel="stylesheet">
	<script src="{{ asset('/la-assets/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
	<script src="{{ asset('/la-assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('/la-assets/plugins/toastr/build/toastr.min.js') }}"></script>
	

	<!-- Scripts -->
	<script>
		window.Language = '{{ config('app.locale') }}';

		window.Laravel = <?php echo json_encode([
			'csrfToken' => csrf_token(),
		]); ?>

		toastr.options.positionClass = 'toast-bottom-right';
	</script>

	@yield('styles')
</head>
<body>
	<div class="container">
		<div class="row clearfix">
			<!-- 导航 start -->
			<div class="col-md-14 column">
				<nav class="navbar navbar-default" role="navigation">
					<div class="container-fluid">
						<div class="navbar-header">
							<a class="navbar-brand" href="#">系统</a>
						</div>
						<div>
							<ul class="nav navbar-nav">
								<li class="active"><a href="#">主页</a></li>
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">
										邮件管理 <b class="caret"></b>
									</a>
									<ul class="dropdown-menu">
										<li><a href="{{ url('home/message_list') }}">收件箱</a></li>
										<li><a href="{{ url('home/sendmessage') }}">发送邮件</a></li>
										<li><a href="{{ url('home/message_accountList') }}">帐户管理</a></li>
										<li class="divider"></li>
										<li><a href="{{ url('home/gmail_list') }}">Gmail管理</a></li>
										<li class="divider"></li>	
										<li><a href="#">另一个分离的链接</a></li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</nav>
			</div>
			<!-- 导航 end -->

			<!-- 内容 start -->
				<div id="app">
					<div class="main">
						@yield('content')
					</div>
				</div>
			<!-- 内容 end -->
		</div>
	</div>


	<!-- Scripts -->
	@yield('scripts')
</body>
</html>
