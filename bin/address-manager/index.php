<?php

namespace VNShipping\Scripts;

$startTime = microtime( true );
$startMemory = memory_get_usage();

require_once dirname( __DIR__ ) . '/shared.php';
require_once __DIR__ . '/vendor/autoload.php';

global $diff;
$diff = new \cogpowered\FineDiff\Diff();

function show_province_name( $name, $baseName ) {
	global $diff;

	$bsName = trim( $name );

	$replace = [ 'tỉnh', 'thành phố' ];
	$name = str_replace( $replace, '', mb_strtolower( $name ) );
	$baseName = str_replace( $replace, '', mb_strtolower( $baseName ) );

	printf(
		'<span class="diff" title="%s">%s</span>',
		esc_attr( $bsName ),
		$diff->render( trim( $name ), trim( $baseName ) )
	);
}

function show_district_name( $name, $baseName ) {
	global $diff;

	$bsName = trim( $name );

	$name = trim( mb_strtolower( $name ) );
	$baseName = trim( mb_strtolower( $baseName ) );

	printf(
		'<span class="diff" title="%s">%s</span>',
		esc_attr( $bsName ),
		$diff->render( $name, $baseName )
	);
}

function show_wards_name( $name, $baseName ) {
	global $diff;

	$bsName = trim( $name );

	$name = trim( mb_strtolower( $name ) );
	$baseName = trim( mb_strtolower( $baseName ) );

	printf(
		'<span class="diff" title="%s">%s</span>',
		esc_attr( $bsName ),
		$diff->render( $name, $baseName )
	);
}

function show_provinces_tables() {
	$provinces = _query_rows( "
		SELECT provinces.*,
		(SELECT count(*) FROM districts WHERE districts.parent_code = provinces.code AND districts.vtp_code IS NULL) AS districts_missing_vtp,
		(SELECT count(*) FROM districts WHERE districts.parent_code = provinces.code AND districts.ghn_code IS NULL) AS districts_missing_ghn,
		(SELECT count(*) FROM wards INNER JOIN districts ON districts.code = wards.parent_code WHERE districts.parent_code = provinces.code AND wards.vtp_code IS NULL) AS wards_missing_vtp,
		(SELECT count(*) FROM wards INNER JOIN districts ON districts.code = wards.parent_code WHERE districts.parent_code = provinces.code AND wards.ghn_code IS NULL) AS wards_missing_ghn
		FROM provinces"
	);

	$ghn_provinces = json_decode( file_get_contents( dirname( __DIR__ ) . '/data/ghn-json/tinh_tp.json' ), true );
	$ghn_provinces = wp_list_pluck( $ghn_provinces, 'ProvinceName', 'ProvinceID' );

	$vtp_provinces = json_decode( file_get_contents( dirname( __DIR__ ) . '/data/vtp-json/tinh_tp.json' ), true );
	$vtp_provinces = wp_list_pluck( $vtp_provinces, 'PROVINCE_NAME', 'PROVINCE_ID' );
	?>
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>Name</th>
				<th>VTP Code</th>
				<th>GHN Code</th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ( $provinces as $province ): ?>
				<tr id="row_province_<?php echo $province->code; ?>">
					<td style="width: 40%;">
						<a href="?province=<?php echo $province->code; ?>#row_province_<?php echo $province->code; ?>"
						   class="text-decoration-none">
							<?php echo $province->code; ?> -
							<span><?php echo $province->name; ?></span>
						</a>
					</td>

					<td style="width: 20%;">
						<kbd><?php echo $province->vtp_code ?? '-'; ?></kbd> -

						<?php if ( isset( $vtp_provinces[ $province->vtp_code ] ) ) : ?>
							<?php show_province_name( $vtp_provinces[ $province->vtp_code ], $province->name ); ?>
						<?php endif; ?>

						<?php if ( $province->districts_missing_vtp > 0 ) : ?>
							<span class="badge rounded-pill bg-warning">
								<?php echo $province->districts_missing_vtp; ?>
							</span>
						<?php endif; ?>

						<?php if ( $province->wards_missing_vtp > 0 ) : ?>
							<span class="badge rounded-pill bg-danger float-end">
								<?php echo $province->wards_missing_vtp; ?>
							</span>
						<?php endif; ?>
					</td>

					<td style="width: 20%;">
						<kbd><?php echo $province->ghn_code ?? '-'; ?></kbd> -

						<?php if ( isset( $ghn_provinces[ $province->ghn_code ] ) ) : ?>
							<?php show_province_name( $ghn_provinces[ $province->ghn_code ], $province->name ); ?>
						<?php endif; ?>

						<?php if ( $province->districts_missing_ghn > 0 ) : ?>
							<span class="badge rounded-pill bg-warning">
								<?php echo $province->districts_missing_ghn; ?>
							</span>
						<?php endif; ?>

						<?php if ( $province->wards_missing_ghn > 0 ) : ?>
							<span class="badge rounded-pill bg-danger float-end">
								<?php echo $province->wards_missing_ghn; ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>

				<?php if ( isset( $_GET['province'] ) && $_GET['province'] === $province->code ) : ?>
					<tr>
						<td colspan="3">
							<?php show_districts_table( $province->code, $province ); ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php
}

function show_districts_table( $provinceCode, $provinceData = null ) {
	if ( ! $provinceData ) {
		$provinceData = _query_row( "SELECT * FROM provinces where code = ?", [ $provinceCode ] );
	}

	$districts = _query_rows(
		"SELECT districts.*,
       (SELECT count(*) FROM wards WHERE wards.parent_code = districts.code AND wards.vtp_code IS NULL) AS wards_missing_vtp,
       (SELECT count(*) FROM wards WHERE wards.parent_code = districts.code AND wards.ghn_code IS NULL) AS wards_missing_ghn
		FROM districts where parent_code = ?",
		[ $provinceCode ]
	);

	if ( $provinceData->ghn_code ) {
		$ghn_data = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/ghn-json/quan-huyen/' . $provinceData->ghn_code . '.json' ),
			true
		);

		$ghn_data = wp_list_pluck( $ghn_data, 'DistrictName', 'DistrictID' );
	} else {
		$ghn_data = [];
	}

	if ( $provinceData->vtp_code ) {
		$vtp_data = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/vtp-json/quan-huyen/' . $provinceData->vtp_code . '.json' ),
			true
		);

		$vtp_data = wp_list_pluck( $vtp_data, 'DISTRICT_NAME', 'DISTRICT_ID' );
	} else {
		$vtp_data = [];
	}

	?>
	<table class="table table-bordered" style="padding: 0;margin: 0; border: solid 1px #000; background: #fff;">
		<tbody>
			<?php foreach ( $districts as $district ): ?>
				<tr id="row_district_<?php echo $district->code; ?>">
					<td style="width: 40%;">
						<a href="?province=<?php echo $provinceCode; ?>&amp;district=<?php echo $district->code; ?>#row_district_<?php echo $district->code; ?>"
						   class="text-decoration-none">
							<?php echo $district->code; ?> -
							<span><?php echo $district->name; ?></span>
						</a>
					</td>

					<td style="width: 20%;">
						<button
							type="button"
							class="btn btn-sm btn-success"
							data-bs-toggle="modal"
							data-bs-target="#assignDistrictModal"
							data-name="vtp"
							data-district="<?php echo $district->code; ?>">
							<?php echo $district->vtp_code ?? '-'; ?>
						</button>

						<?php if ( isset( $vtp_data[ $district->vtp_code ] ) ) : ?>
							<?php show_district_name( $vtp_data[ $district->vtp_code ], $district->name ); ?>
						<?php endif; ?>

						<?php if ( $district->wards_missing_vtp > 0 ) : ?>
							<span class="badge rounded-pill bg-warning">
								<?php echo $district->wards_missing_vtp; ?>
							</span>
						<?php endif; ?>
					</td>

					<td style="width: 20%;">
						<button
							type="button"
							class="btn btn-sm btn-success"
							data-bs-toggle="modal"
							data-bs-target="#assignDistrictModal"
							data-name="ghn"
							data-district="<?php echo $district->code; ?>">
							<?php echo $district->ghn_code ?? '-'; ?>
						</button>

						<?php if ( isset( $ghn_data[ $district->ghn_code ] ) ) : ?>
							<?php show_district_name( $ghn_data[ $district->ghn_code ], $district->name ); ?>
						<?php endif; ?>

						<?php if ( $district->wards_missing_ghn > 0 ) : ?>
							<span class="badge rounded-pill bg-warning">
								<?php echo $district->wards_missing_ghn; ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>

				<?php if ( isset( $_GET['province'], $_GET['district'] ) && $_GET['district'] === $district->code ) : ?>
					<tr>
						<td colspan="3">
							<?php show_wars_table( $provinceCode, $district->code, $district ); ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php
}

function show_wars_table( $provinceCode, $districtCode, $districtData = null ) {
	$wards = _query_rows(
		"SELECT wards.* FROM wards INNER JOIN districts ON districts.code = wards.parent_code WHERE wards.parent_code = ? AND districts.parent_code = ?;",
		[ $districtCode, $provinceCode ]
	);

	if ( ! $districtData ) {
		$districtData = _query_row( "SELECT * FROM districts where code = ?", [ $districtCode ] );
	}

	if ( $districtData->ghn_code ) {
		$ghn_data = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/ghn-json/xa-phuong/' . $districtData->ghn_code . '.json' ),
			true
		);

		$ghn_data = wp_list_pluck( $ghn_data, 'WardName', 'WardCode' );
	} else {
		$ghn_data = [];
	}

	if ( $districtData->vtp_code ) {
		$vtp_data = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/vtp-json/xa-phuong/' . $districtData->vtp_code . '.json' ),
			true
		);

		$vtp_data = wp_list_pluck( $vtp_data, 'WARDS_NAME', 'WARDS_ID' );
	} else {
		$vtp_data = [];
	}

	?>
	<table class="table table-bordered" style="padding: 0;margin: 0; border: solid 2px #396767; background: #f8ffff;">
		<tbody>
			<?php foreach ( $wards as $ward ): ?>
				<tr>
					<td style="width: 40%;">
						<?php echo $ward->code; ?> -
						<span><?php echo $ward->name; ?></span>
					</td>

					<td style="width: 20%;">
						<button type="button"
								class="btn btn-sm btn-outline-success"
								data-bs-toggle="modal"
								data-bs-target="#assignWardModal"
								data-name="vtp"
								data-ward-code="<?php echo $ward->code ?>"
								data-district="<?php echo $districtData->vtp_code; ?>">
							<?php echo $ward->vtp_code ?? '-'; ?>
						</button>

						<?php if ( isset( $vtp_data[ $ward->vtp_code ] ) ) : ?>
							<?php show_wards_name( $vtp_data[ $ward->vtp_code ], $ward->name ); ?>
						<?php endif; ?>
					</td>

					<td style="width: 20%;">
						<button type="button"
								class="btn btn-sm btn-outline-success"
								data-bs-toggle="modal"
								data-bs-target="#assignWardModal"
								data-name="ghn"
								data-ward-code="<?php echo $ward->code ?>"
								data-district="<?php echo $districtData->ghn_code; ?>">
							<?php echo $ward->ghn_code ?? '-'; ?>
						</button>

						<?php if ( isset( $ghn_data[ $ward->ghn_code ] ) ) : ?>
							<?php show_wards_name( $ghn_data[ $ward->ghn_code ], $ward->name ); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php
}

function get_wards_list( $name, $districtCode ) {
	if ( $name === 'vtp' ) {
		$wards = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/vtp-json/xa-phuong/' . $districtCode . '.json' ),
			true
		);

		$wards = wp_list_pluck( $wards, 'WARDS_NAME', 'WARDS_ID' );
	} else {
		$wards = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/ghn-json/xa-phuong/' . $districtCode . '.json' ),
			true
		);

		$wards = wp_list_pluck( $wards, 'WardName', 'WardCode' );
	}

	asort( $wards );

	return $wards;
}

function get_districts_list( $name, $provinceCode ) {
	if ( $name === 'vtp' ) {
		$districts = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/vtp-json/quan-huyen/' . $provinceCode . '.json' ),
			true
		);

		$districts = wp_list_pluck( $districts, 'DISTRICT_NAME', 'DISTRICT_ID' );
	} else {
		$districts = json_decode(
			file_get_contents( dirname( __DIR__ ) . '/data/ghn-json/quan-huyen/' . $provinceCode . '.json' ),
			true
		);

		$districts = wp_list_pluck( $districts, 'DistrictName', 'DistrictID' );
	}

	asort( $districts );

	return $districts;
}

function ajax_show_assign_wards() {
	$name = $_REQUEST['name'] ?? null;
	$wardCode = $_REQUEST['ward'] ?? null;
	$districtCode = $_REQUEST['district'] ?? null;

	if ( empty( $name ) || empty( $wardCode ) || empty( $districtCode ) ) {
		return;
	}

	$mapColumn = $name === 'ghn' ? 'ghn_code' : 'vtp_code';

	$wardInfo = _query_row(
		"SELECT
			wards.code,
       		wards.{$mapColumn} as ward_map,
			CONCAT_WS(', ', wards.name, districts.name, provinces.name) as name
		FROM wards
		INNER JOIN districts ON districts.code = wards.parent_code
		INNER JOIN provinces ON provinces.code = districts.parent_code
		WHERE wards.code = ?
		LIMIT 1;",
		$wardCode
	);

	if ( empty( $wardInfo ) ) {
		return;
	}

	$wards = get_wards_list( $name, $districtCode );

	?>
	<input type="hidden" name="name" value="<?php echo $name; ?>">
	<input type="hidden" name="ward" value="<?php echo $wardCode; ?>">
	<input type="hidden" name="district" value="<?php echo $districtCode; ?>">

	<h4 class="mb-4"><?php echo sprintf( '%s - %s', $wardInfo->code, $wardInfo->name ); ?></h4>

	<div class="mb-3">
		<label for="to_ward" class="form-label">Select Ward Code</label>

		<select id="to_ward" name="to_ward" class="form-control" required>
			<option value="">---</option>
			<?php foreach ( $wards as $id => $name ): ?>
				<option value="<?php echo $id; ?>" <?php selected( $id, $wardInfo->ward_map ); ?>>
					<?php echo $name; ?>
				</option>
			<?php endforeach ?>
		</select>
	</div>

	<div class="form-check">
		<input class="form-check-input" type="checkbox" name="is-force" value="1" id="forceUpdate">
		<label class="form-check-label" for="forceUpdate">Force assign?</label>
	</div>
	<?php
}

function ajax_handle_assign_wards() {
	$name = $_REQUEST['name'];
	$wardCode = $_REQUEST['ward'];
	$toWardCode = $_REQUEST['to_ward'];
	$districtCode = $_REQUEST['district'];

	if ( empty( $name ) || empty( $wardCode ) || empty( $districtCode ) || empty( $toWardCode ) ) {
		return;
	}

	$isForce = (bool) ( $_REQUEST['is-force'] ?? false );

	$wards = get_wards_list( $name, $districtCode );

	if ( array_key_exists( $toWardCode, $wards ) ) {
		$column = $name === 'vtp' ? 'vtp_code' : 'ghn_code';

		$toWardInfo = _query_row( "SELECT * FROM wards WHERE " . $column . " = ? LIMIT 1;", [ $toWardCode ] );

		if ( $toWardInfo && ! $isForce ) {
			$assignedName = $wards[ $toWardInfo->{$column} ] ?? '';

			echo sprintf( '"%s" đã gán cho: "%s - %s"', $toWardInfo->name, $toWardInfo->{$column}, $assignedName );
			exit( 1 );
		}

		if ( $toWardInfo && $isForce ) {
			_get_pdo()->prepare( "UPDATE `wards` SET " . $column . " = NULL WHERE code = ?" )
				->execute( [ $toWardInfo->code ] );
		}

		_get_pdo()->prepare( "UPDATE `wards` SET " . $column . " = ? WHERE code = ?" )
			->execute( [ $toWardCode, $wardCode ] );
	}

	exit( 'OK' );
}

function ajax_show_assign_district() {
	$name = $_REQUEST['name'] ?? null;
	$districtCode = $_REQUEST['district'] ?? null;

	if ( empty( $name ) || empty( $districtCode ) ) {
		return;
	}

	$mapColumn = $name === 'ghn' ? 'ghn_code' : 'vtp_code';
	$districtInfo = _query_row(
		"SELECT districts.code, districts.name, districts.{$mapColumn} as district_map, provinces.{$mapColumn} as province_map
		FROM districts
		INNER JOIN provinces ON provinces.code = districts.parent_code
		WHERE districts.code = ?
		LIMIT 1;",
		$districtCode
	);

	if ( empty( $districtInfo ) ) {
		return;
	}

	$districts = get_districts_list( $name, $districtInfo->province_map );

	?>
	<input type="hidden" name="name" value="<?php echo $name; ?>">
	<input type="hidden" name="district" value="<?php echo $districtCode; ?>">

	<h4 class="mb-4"><?php echo sprintf( '%s - %s', $districtInfo->code, $districtInfo->name ); ?></h4>

	<div class="mb-3">
		<label for="to_district" class="form-label">Select District Code</label>

		<select id="to_district" name="to_district" class="form-control" required>
			<option value="">---</option>
			<?php foreach ( $districts as $id => $name ): ?>
				<option value="<?php echo $id; ?>" <?php selected( $id, $districtInfo->district_map ); ?>>
					<?php echo $name; ?>
				</option>
			<?php endforeach ?>
		</select>
	</div>

	<div class="form-check">
		<input class="form-check-input" type="checkbox" name="is-force" value="1" id="forceUpdate">
		<label class="form-check-label" for="forceUpdate">Force assign?</label>
	</div>
	<?php
}

function ajax_handle_assign_district() {
	$name = $_REQUEST['name'];
	$districtCode = $_REQUEST['district'];
	$toDistrictCode = $_REQUEST['to_district'];

	if ( empty( $name ) || empty( $districtCode ) || empty( $toDistrictCode ) ) {
		return;
	}

	$isForce = (bool) ( $_REQUEST['is-force'] ?? false );

	$mapColumn = $name === 'ghn' ? 'ghn_code' : 'vtp_code';
	$districtInfo = _query_row(
		"SELECT districts.code, districts.name, districts.{$mapColumn} as district_map, provinces.{$mapColumn} as province_map
		FROM districts
		INNER JOIN provinces ON provinces.code = districts.parent_code
		WHERE districts.code = ?
		LIMIT 1;",
		$districtCode
	);

	if ( empty( $districtInfo ) ) {
		return;
	}

	$districts = get_districts_list( $name, $districtInfo->province_map );

	if ( array_key_exists( $toDistrictCode, $districts ) ) {
		if ( ! empty( $districtInfo->district_map ) && ! $isForce ) {
			$assignedName = $districts[ $districtInfo->district_map ] ?? '';

			echo sprintf(
				'"%s" đã gán cho: "%s - %s"',
				$districtInfo->name,
				$districtInfo->district_map,
				$assignedName
			);
			exit( 1 );
		}

		if ( $districtInfo->district_map && $isForce ) {
			_get_pdo()->prepare( "UPDATE `districts` SET " . $mapColumn . " = NULL WHERE code = ?" )
				->execute( [ $districtInfo->district_map ] );
		}

		_get_pdo()->prepare( "UPDATE `districts` SET " . $mapColumn . " = ? WHERE code = ?" )
			->execute( [ $toDistrictCode, $districtCode ] );

		exit( 'OK' );
	}

	exit( 'Nothing to update' );
}

status_header( 200 );
if ( isset( $_REQUEST['ajaxAction'] ) && $_REQUEST['ajaxAction'] === 'showAssignDistrict' ) {
	ajax_show_assign_district();
	exit( 0 );
}

if ( isset( $_REQUEST['ajaxAction'] ) && $_REQUEST['ajaxAction'] === 'assignDistrict' ) {
	ajax_handle_assign_district();
	exit( 0 );
}

if ( isset( $_REQUEST['ajaxAction'] ) && $_REQUEST['ajaxAction'] === 'showAssignWard' ) {
	ajax_show_assign_wards();
	exit( 0 );
}

if ( isset( $_REQUEST['ajaxAction'] ) && $_REQUEST['ajaxAction'] === 'assignWard' ) {
	ajax_handle_assign_wards();
	exit( 0 );
}

?>
<!doctype html>
<html lang="en">
<head>
	<title>Address Manager</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
	      integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

	<style>
		:root {
			scroll-behavior: auto;
		}

		.diff del {
			background: rgba(244, 67, 54, .25);
			text-decoration: line-through;
		}

		.diff ins {
			background: rgba(71, 220, 76, 0.25);
		}

		a:focus {
			outline: solid 3px #000;
		}
	</style>
</head>
<body>
	<div class="container pt-5 pb-5">
		<h1><a href="index.php" class="text-decoration-none text-dark">Address</a></h1>
		<hr class="mb-4">

		<?php show_provinces_tables(); ?>

		<code>
			<?php printf(
				"Total time: %s\r\nMemory Used: %s",
				round( microtime( true ) - $startTime, 4 ),
				size_format( memory_get_usage() - $startMemory, 2 )
			); ?>
		</code>
	</div>

	<div class="modal fade" id="assignWardModal" tabindex="-1" aria-hidden="true">
		<form method="POST" action="" id="assignWardForm">
			<input type="hidden" name="ajaxAction" value="assignWard">

			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Assign Ward</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="modal-body">
						<div id="assignWardModalAJAX"></div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</div>
			</div>
		</form>
	</div>

	<div class="modal fade" id="assignDistrictModal" tabindex="-1" aria-hidden="true">
		<form method="POST" action="" id="assignDistrictForm">
			<input type="hidden" name="ajaxAction" value="assignDistrict">

			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Assign District</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="modal-body">
						<div id="assignDistrictModalAJAX"></div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</div>
			</div>
		</form>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
	        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
	        crossorigin="anonymous"></script>

	<script>
		(function() {
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('.diff[title]'));
			tooltipTriggerList.map(function(tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl);
			});

			var assignWardModal = document.getElementById('assignWardModal');
			var assignWardModalRoot = document.getElementById('assignWardModalAJAX');
			assignWardModal.addEventListener('show.bs.modal', function(event) {
				var button = event.relatedTarget;

				var name = button.getAttribute('data-name');
				var district = button.getAttribute('data-district');
				var wardCode = button.getAttribute('data-ward-code');

				var showUrl = 'index.php?ajaxAction=showAssignWard&district=' + district + '&name=' + name + '&ward=' +
					wardCode;

				assignWardModalRoot.innerHTML = '';
				window.fetch(showUrl).then(function(res) {
					res.text().then(function(text) {
						assignWardModalRoot.innerHTML = text;
					});
				});
			});

			var assignDistrictModal = document.getElementById('assignDistrictModal');
			var assignDistrictModalRoot = document.getElementById('assignDistrictModalAJAX');
			assignDistrictModal.addEventListener('show.bs.modal', function(event) {
				var button = event.relatedTarget;

				var name = button.getAttribute('data-name');
				var district = button.getAttribute('data-district');

				assignDistrictModalRoot.innerHTML = '';

				var showUrl = 'index.php?ajaxAction=showAssignDistrict&district=' + district + '&name=' + name;
				window.fetch(showUrl).then(function(res) {
					res.text().then(function(text) {
						assignDistrictModalRoot.innerHTML = text;
					});
				});
			});

			// AJAX submit.
			var assignWardForm = document.getElementById('assignWardForm');
			assignWardForm.addEventListener('submit', function(event) {
				event.preventDefault();

				var form = event.currentTarget;

				window.fetch('index.php', {
					method: 'POST',
					body: new FormData(form)
				}).then(function(response) {
					response.text().then(function(text) {
						if (text && text === 'OK') {
							window.location.reload();
						} else if (text) {
							alert(text);
						}
					});
				});
			});

			var assignDistrictForm = document.getElementById('assignDistrictForm');
			assignDistrictForm.addEventListener('submit', function(event) {
				event.preventDefault();

				var form = event.currentTarget;

				window.fetch('index.php', {
					method: 'POST',
					body: new FormData(form)
				}).then(function(response) {
					response.text().then(function(text) {
						if (text && text === 'OK') {
							window.location.reload();
						} else if (text) {
							alert(text);
						}
					});
				});
			});
		})();
	</script>
</body>
</html>
