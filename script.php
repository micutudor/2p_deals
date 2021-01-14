<!DOCTYPE html>
<html>
<head>
	<!-- Page responsive -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Bootstrap 4 CDN -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

	<?php

	/**
	 *
	 *	(c) Tudor Micu 2020
	 *  A simple script to get deals from 2Performant using their API
	 *
	 */

	# 2Performant credentials
	define('EMAIL', "mail@example.com");
	define('PASS', "password");

	# Constants for the positions of auth headers in the session array
	define('AUTH_KEY', 6);
	define('CLIENT', 8);
	define('UID', 12);

	# Get headers from http request
	function getHeaders($http_response, $curl_stream, &$headers_array = [])
	{
		$header_size = curl_getinfo($curl_stream, CURLINFO_HEADER_SIZE);
		$headers = substr($http_response, 0, $header_size);
		$body = substr($http_response, $header_size);

		$headers = explode("\r\n", $headers);
		$headers = array_filter($headers);

		$i = 1;
		$headers_array = [];

		foreach ($headers as &$value) {
		    $headers_array[$i ++] = $value;
		}
	}

	# 2P API Login
	$cURLConnection = curl_init();

	curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.2performant.com/users/sign_in.json');
	curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, '{"user":{"email":"'.EMAIL.'","password":"'.PASS.'"}}');
	curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
 	curl_setopt($cURLConnection, CURLOPT_HEADER, 1);
 	curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json'
	));

	$response = curl_exec($cURLConnection);

	# Get auth credentials
	$headers = [];
	getHeaders($response, $cURLConnection, $headers);

	curl_close($cURLConnection);

	# Get deals from accepted advertisers
	$cURLConnection = curl_init();

	curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.2performant.com/affiliate/advertiser_promotions?filter[affrequest_status]=accepted');
	curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
 	curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		$headers[AUTH_KEY],
		$headers[CLIENT],
		$headers[UID]
	));

	$response = curl_exec($cURLConnection);

	curl_close($cURLConnection);
?>
</head>
<body>
	<div class="container">
		<br>
		<?php
			# Get .json data to array
			$promotionsData = json_decode($response, true);

			$i = 0;

			# View deals
			foreach ($promotionsData['advertiser_promotions'] as $promotionData)
			{	
				if ($i == 0) echo '<div class="row">'; # new row
				?>	
					<div class="col-lg-4 d-flex align-self-stretch">
						<div class="card mb-3 text-center d-flex align-items-stretch" style="width: 18rem;">
						  <img style="width: 120px; height: 120px" src="<?= $promotionData['campaign_logo'] ?>" class="card-img-top mx-auto d-block" alt="<?= $promotionData['name'] ?>">
						  <div class="card-body d-flex flex-column">
						  	<span class="badge badge-danger mb-2">DISCOUNT</span>
						    <p class="card-text"><?= $promotionData['name'] ?></p>
						    <a href="<?= $promotionData['landing_page_link'] ?>" class="btn btn-warning btn-block text-uppercase mt-auto" target="_blank"><b>Prinde oferta</b></a>
						  </div>
						</div>
					</div>
				<?php

				$i ++;
				if ($i == 3) { $i = 0; echo '</div>'; } # end row
			}
		?>
		</div>
	</div>
</body>
</html>
