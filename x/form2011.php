
<div class='nc-field'><?= nc_bool_field('Zayavka', "", ($class_id ? $class_id : $classID ), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_text_field('Phone', "", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Address', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Phone800', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_text_field('Mobile', "", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Email', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_file_field('QR', "size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field" id="map-box" style="height:300px;"></div>


<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<script type="text/javascript">
	var map = null;
	ymaps.ready(function(){
		map = new ymaps.Map('map-box', {
			center: [55.76, 37.64], // Москва
			zoom: 10
		}, {
			controls: ['zoomControl']
		});
	});
</script>

<div class='nc-field'><?= nc_float_field('Lat', "maxlength='12' size='12'", ( $class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_float_field('Lon', "maxlength='12' size='12'", ( $class_id ? $class_id : $classID), 1) ?></div>
