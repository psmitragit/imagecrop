<?php
include('./constants.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Image Crop</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
	<script src='./assets/jquery-3.6.4.min.js'></script>
	<script src='./assets/jquery-ui.js'></script>
</head>
<style>
	body {
		margin: 0;
		padding: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100vh;
		background-color: #f0f0f0;
		/* Set a background color for the page */
	}

	#transparent-box {
		background-color: rgba(255, 255, 255, 0.7);
		/* Use rgba for transparency */
		padding: 20px;
		border-radius: 10px;
		text-align: center;
	}

	#edit-icon {
		padding: 10px 20px;
		font-size: 16px;
		cursor: pointer;
	}

	#file-upload {
		display: none;
	}

	#crop_wrapper {

		position: relative;

		margin: 50px auto auto auto;

		overflow: hidden;

	}



	#crop_div {

		border: 1px solid red;

		position: absolute;

		top: 0px;

		box-sizing: border-box;

		box-shadow: 0 0 0 99999px rgba(255, 255, 255, 0.5);

	}

	.modal-xl {
		max-width: 80%;
		/* Adjust the percentage based on your design */
	}

	#crop_wrapper {
		overflow: auto;
		/* or overflow: hidden; depending on your design */
	}

	.modal-body {
		max-height: 80vh;
		/* Adjust the percentage based on your design */
		overflow: auto;
	}

	#edit-icon {
		padding: 10px 20px;
		font-size: 16px;
		cursor: pointer;
		border: 2px solid #3498db;
		/* Border color */
		color: #3498db;
		/* Text color */
		background-color: #ffffff;
		/* Background color */
		border-radius: 5px;
		/* Border radius */
		transition: all 0.3s ease;
		/* Add smooth transition */
	}

	#edit-icon:hover {
		background-color: #3498db;
		/* Change background color on hover */
		color: #ffffff;
		/* Change text color on hover */
	}

	#file-upload {
		display: none;
	}
</style>

<body>
	<div id="transparent-box">
		<button type="button" id="edit-icon">Add Image</button>
		<input type="file" id="file-upload" multiple>
	</div>

	<!-- feature image of the product cutting functions -->

	<!-- Modal -->

	<div class="modal fade" id="crop-modal" tabindex="-1" role="dialog" aria-labelledby="jobinvoiceLabel" aria-hidden="true">

		<div class="modal-dialog modal-xl" role="document">

			<div class="modal-content">

				<form id="crop_image_form" method="post" action="ajax.php">
					<input type="hidden" name='action' value='manual_crop'>
					<script type="text/javascript">
						$(function() {

							$("#crop_div").draggable({

								containment: "parent"

							});

						});



						function crop() {

							var posi = document.getElementById('crop_div');

							document.getElementById("top").value = posi.offsetTop;

							document.getElementById("left").value = posi.offsetLeft;

							document.getElementById("right").value = posi.offsetWidth;

							document.getElementById("bottom").value = posi.offsetHeight;

							return true;

						}
					</script>


					<div class="modal-body">

						<div id="crop_wrapper">

							<img src="" id="resized_image">

							<div id="crop_div">

							</div>



							<input type="hidden" value="" id="top" name="top">

							<input type="hidden" value="" id="left" name="left">

							<input type="hidden" value="" id="right" name="right">

							<input type="hidden" value="" id="bottom" name="bottom">

							<input type="hidden" id='fv1' name="image">

							<input type="hidden" id='fv2' name="maxWidth">

							<input type="hidden" id='fv3' name="maxHeight">

							<input type="hidden" id='fv4' name="dirPath">

							<input type="hidden" id='fv5' name="thumbFileName">

							<input type="hidden" id='fv6' name="ext">

							<input type="hidden" name="crop_image">



						</div>

					</div>

					<div class="modal-footer">

						<button type='submit' class="btn btn-primary">Crop & Save</a>

					</div>

				</form>

			</div>

		</div>

	</div>

	<script>
		$('#crop_image_form').submit(function(event) {

			// Prevent default form submission

			event.preventDefault();

			crop();

			// Serialize form data

			var formData = $(this).serialize();



			// Send AJAX request

			$.ajax({

				type: 'POST',

				url: $(this).attr('action'),

				data: formData,

				dataType: 'json',

				success: function(res) {

					// Handle successful response

					if (res.status == 1) {

						var image_name = res.img_name;

						$('input[name="image"]').val(image_name);

						$('#crop-modal').modal('hide');

					}

				},

				error: function(xhr, status, error) {

					// Handle error

				}

			});

		});
	</script>

	<script>
		$(document).ready(function() {

			$('#edit-icon').on('click', function() {

				$('#file-upload').click();

			});



			$('#file-upload').on('change', function() {

				var img_height = <?= MANUAL_CROP_HEIGHT ?>;
				var img_width = <?= MANUAL_CROP_WIDTH ?>;

				var form_data = new FormData();
				form_data.append('img_height', img_height);
				form_data.append('img_width', img_width);

				var files = $('#file-upload').prop('files');
				if (files.length > 1) {
					form_data.append('action', 'multi_image_crop');

					for (var i = 0; i < files.length; i++) {
						form_data.append('files[]', files[i]);
					}

					var url = 'ajax.php';
					$.ajax({
						url: url,
						cache: false,
						contentType: false,
						processData: false,
						data: form_data,
						type: 'post',
						dataType: 'json',
						success: function(res) {

						}
					});
				} else {
					var file_data = files[0];
					form_data.append('action', 'tmp_crop_image');

					if (file_data) {
						var img = new Image();
						img.onload = function() {
							var width = parseInt(img.width);
							var height = parseInt(img.height);
							if (width < img_width || height < img_height) {
								alert('Minimum Image Width: ' + img_width + 'px, Minimum Image Height: ' + img_height + 'px.');
							} else {
								form_data.append('image', file_data);

								var url = 'ajax.php';

								$.ajax({

									url: url,

									cache: false,

									contentType: false,

									processData: false,

									data: form_data,

									type: 'post',

									dataType: 'json',

									success: function(res) {
										if (res.status !== 0) {

											$('#fv1').val(res.resizedimg);

											$('#fv2').val(res.maxWidth);

											$('#fv3').val(res.maxHeight);

											$('#fv4').val(res.dir_path);

											$('#fv5').val(res.thumbFileName);

											$('#fv6').val(res.ext);

											if (res.status == 1) {

												var path = 'images/' + res.image_name;

												$('#crop-modal').modal('show');

												$('#resized_image').attr('src', path);

												$('#crop_wrapper').css('width', res.resize_width);

												$('#crop_wrapper').css('height', res.resize_height);

												$('#crop_wrapper img').css('width', res.resize_width);

												$('#crop_wrapper img').css('height', res.resize_height);

												$('#crop_div').css('width', res.selector_width);

												$('#crop_div').css('height', res.selector_height);

											} else if (res.status == 2) {
												$('#crop_image_form').submit();
											}

										}
									}

								});
							}

						};
						img.src = URL.createObjectURL(file_data);
					}

				}
			});

		});
	</script>

	<!-- feature image of the product cutting functions -->
</body>

</html>