<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>Bucket Form</title>
	<!-- bootstrap css-->
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
	<!-- bootstrap js-->
	<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
	<!-- custome css -->
	<link href="{{ asset('css/custome.css') }}" rel="stylesheet">	
</head>

<body>
	<div class="container mt-5">
		<div class="row justify-content-center">
			<div class="col-sm-5">
				<h3>Bucket Form</h3>
				<form id="bucket" method="post">
					@csrf

					<div class="form-group row">
						<label class="col-sm-4 col-form-label ">Bucket Name: </label>
						<div class="col-sm-6">
							<input type="text" class="form-control form-control-sm" id="bucket_name" name="bucket_name"
								placeholder="bucket name" value="" />
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-4 col-form-label">Volume(in Inches): </label>
						<div class="col-sm-6">
							<input type="text" class="form-control form-control-sm" id="bucket_volume" name="volume"
								placeholder="volume" value="" />
						</div>
					</div>
					<input type="hidden" name="ftype" value="fb" />
					<div class="text-center mt-4">
						<button type="button" fi="bucket"
							class="formact btn btn-primary text-center rounded-pill btn-warning font-weight-bold">Save</button>
					</div>
				</form>
			</div>
			<div class="col-sm-5 ">
				<h3>Ball Form</h3>
				<form id="ball" method="post">
					@csrf
					<div class="form-group row">
						<label class="col-sm-4 col-form-label ">Ball Name: </label>
						<div class="col-sm-6">
							<input type="text" class="form-control form-control-sm" id="ball_name" name="ball_name"
								placeholder="ball name" value="" />
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-4 col-form-label">Volume(in Inches): </label>
						<div class="col-sm-6">
							<input type="text" class="form-control form-control-sm" id="ball_volume" name="volume"
								placeholder="volume" value="" />
						</div>
					</div>
					<input type="hidden" name="ftype" value="bf" />
					<div class="text-center mt-4">
						<button type="button" fi="ball"
							class="formact btn font-weight-bold  text-center rounded-pill btn-warning ">Save</button>
					</div>
				</form>
			</div>
		</div>
		<div class="row justify-content-center mt-4">
			<div class="col-sm-5 ">
				<h3>Bucket Suggetion</h3>
			</div>
			<div class="col-sm-5 "></div>
		</div>
		<div class="row justify-content-center mt-1 mb-4">

			<div class="col-sm-5 cb">


				<form id="bsf" class="ti" name="bsf" method="post">
					@csrf
					@if(count($ballData) > 0)
					<table class="table" align="center" colpending="10" style="width:80%">
						@foreach($ballData as $key => $val)
						<input type="hidden" class="form-control form-control-sm" id="ball_{{$val['id']}}"
							name="ball_id[]" value="{{$val['id']}}" />
						<input type="hidden" class="form-control form-control-sm" id="ball_size_{{$val['id']}}"
							name="ball_size[]" value="{{$val['volume']}}" />
						<input type="hidden" class="form-control form-control-sm" id="ball_nm{{$val['id']}}"
							name="ball_nm[]" value="{{$val['ball_name']}}" />	
						<tr>
							<th style="border: none;">{{$val['ball_name']}}</th>
							<td><input type="text" class="form-control form-control-sm " id="total_ball_{{$val['id']}}"
									name="total_ball[]" placeholder="Total Ball" /></td>
						</tr>

						@endforeach
					</table>

					@else
					<table class="table" align="center" colpending="10" style="width:80%">
						<tbody>
							<div class="text-center pnh">
								<h7 class="error">Balls not found.please Add new balls</h7>
							</div>
						</tbody>
					</table>
					@endif
					<input type="hidden" name="ftype" value="bucketSuggest" />
					<div class="text-center mt-4 pbib"
						style="display: <?php if(count($ballData) > 0){ echo 'block'; }else { echo 'none'; } ?>">
						<button type="button" fi="bsf"
							class="formact  btn font-weight-bold  text-center rounded-pill btn-warning ">PLACE BALLS IN
							BUCKET</button>
					</div>
				</form>

			</div>
			<div class="col-sm-5 cb">
				<h5>Result:</h5>
				<h7>Following are the suggested buckets</h7>
				<div>
					<ul class="dataRes">

					</ul>
				</div>

			</div>
		</div>
	</div>
	<!-- jquery 3.6.4 -->
	<script src="{{ asset('js/jquery-3.6.4.min.js') }}"></script>
	<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
	<script src="{{ asset('js/additional-methods.js') }}"></script>




	<script>
		jQuery.validator.addMethod("lettersonly", function (value, element) {
			return this.optional(element) || /^[a-z\s]+$/i.test(value);
		}, "Only alphabetical characters");

		$(document).ready(function () {
			getResult();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			$("#bucket").validate({
				rules: {
					bucket_name: {
						required: true,
						lettersonly: true
					},
					volume: {
						required: true,
						number: true,

					}

				},
			})
			$("#ball").validate({
				rules: {
					ball_name: {
						required: true,
						lettersonly: true
					},
					volume: {
						required: true,
						number: true
					}
				},
			})
			$("form[name='bsf']").validate({
				rules: {
					"total_ball[]": {
						required: true,
						number: true
					},
				},
			});

			$(".formact").click(function (e) {
				e.preventDefault();
				const form = $(this).attr('fi');
				console.log(form);
				const vcheck = $('#' + form).valid();
				if(vcheck){
					$.ajax({
						type: "post",
						url: "{{ url('store') }}",
						dataType: "json",
						data: $('#' + form).serialize(),
						success: function (data) {
							console.log(data);
							// alert("Data Save: " + data.status);
							if (data.actype == "add") {
								if (form == "ball") {
									if (data.actype == "add") {
										const appendtext = '<tr><input type="hidden" class="form-control form-control-sm" id="ball_' + data.data.id + '" name ="ball_id[]" value="' + data.data.id + '" /><input type="hidden" class="form-control form-control-sm" id="ball_nm' + data.data.id + '" name ="ball_nm[]" value="' + data.data.ball_name + '" /><input type="hidden" class="form-control form-control-sm" id="ball_size_' + data.data.id + '" name ="ball_size[]" value="' + data.data.volume + '" /><tr><th style="border: none;">' + data.data.ball_name + '</th><td><input type="text" class="form-control form-control-sm " id="total_ball_' + data.data.id + '" name ="total_ball[]" placeholder="Total Ball" /></td></tr>';
										@if (count($ballData) <= 0)
											$('.pbib').css({ display: 'block' });
										$('.pnh').css({ display: 'none' });
										@endif
										$('table tbody').prepend(appendtext);
									}
								}
								alert(data.msg);
							} else if (data.actype == "update") {
								$('#ball_size_' + data.data.id).val(data.data.volume);
								alert(data.msg);
							} else {
								alert(data.msg);
							}
							getResult();
							$('#' + form).trigger("reset");
						},
						error: function (data) {
							console.log(data);
							alert("Something Wrong.Please tray later.")
						}
					});
				}
			})


			function getResult() {
				// get result
				$.ajax({
					type: "GET",
					url: "{{ url('result') }}",
					dataType: "json",
					success: function (data) {
						console.log(data);
						// alert("Data Save: " + data.status);
						if (data.actype == "found") {
							$('.dataRes').html(data.data);
						}
					},
					error: function (data) {
						console.log(data);
						alert("Something Wrong.Please tray later.")
					}
				});
			}

		})
	</script>
</body>

</html>