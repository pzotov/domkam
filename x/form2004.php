<?php
/**
 * Created by PhpStorm.
 * User: pavelzotov
 * Date: 26.07.15
 * Time: 10:00
 */
?>

<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Descr', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_file_field('Picture', "size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field image--box'>
	<img class="image--field" src="<?= $nc_core->HTTP_FILES_PATH . explode(":", $cc_settings['Bg'])[3] ?>" />
	<div class="image--marker"></div>
</div>

<div class='nc-field'>
	<span class="nc-field-caption">Координаты:</span>
	<?= nc_float_field('X', 'id="f_X" type="number" style="width:60px;" min="0" max="100"', ( $class_id ? $class_id : $classID)) ?> :
	<?= nc_float_field('Y', 'id="f_Y" type="number" style="width:60px;" min="0" max="100"', ( $class_id ? $class_id : $classID)) ?>
</div>

<style>
	.image--box {
		position: relative;
	}
	.image--field {
		max-width: 100%;
		max-height: 100%;
		padding: 0;
		margin: 0;
		cursor: pointer;
	}
	.image--marker {
		position: absolute;
		z-index: 1;
		width: 10px;
		height: 10px;
		border: 3px solid #fff;
		border-radius: 50%;
		background-color: #f26522;
		margin: -8px 0 0 -8px;
		left: 0;
		top: 0;
	}
</style>
<script type="text/javascript">
	$(".image--field").load(function() {
		var $im = $(".image--field"),
			iw = $im.width(),
			ih = $im.height(),
			$x = $("#f_X"),
			$y = $("#f_Y"),
			$marker = $(".image--marker");
		$im.click(function(e) {
			$x.val(Math.round(e.offsetX / iw * 10000) / 100);
			$y.val(Math.round(e.offsetY / ih * 10000) / 100);
			$marker.css({
				left: e.offsetX,
				top: e.offsetY
			});
		});
		$('#f_X, #f_Y').change(function () {
			$marker.css({
				left: Math.round($x.val().replace(/,/, '.') / 100 * iw),
				top: Math.round($y.val().replace(/,/, '.') / 100 * ih)
			});
		});
		$x.trigger("change");
	});
</script>