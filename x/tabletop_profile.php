<?php
require_once '../vars.inc.php';
require_once $ROOT_FOLDER.'connect_io.php';
require_once $MODULE_FOLDER.'cache/function.inc.php';

$profile = stripslashes($_GET['profile']);

if($_GET['name']=="ступени") {
	$list = nc_objects_list(78, 557, "nc_ctpl=2034&profile=".urlencode($profile));
	$torets = "торца";
} else if($_GET['name']=="балясины") {
	$list = nc_objects_list(77, 580, "nc_ctpl=2034&profile=".urlencode($profile));
	$torets = "формы";
} else if($_GET['name']=="памятника") {
	$list = nc_objects_list(110, 386, "nc_ctpl=2034&profile=".urlencode($profile));
	$torets = "эскиза";
} else {
	$list = nc_objects_list(30, 93, "nc_ctpl=2034&profile=".urlencode($profile));
	$torets = "торца";
}

?>
<form class="form form_tabletop-profile">
	<div class="form__header">Выбор <?= $torets.' '.htmlspecialchars($_GET['name'], ENT_QUOTES) ?></div>
	<div class="form__row">
		<?= $list ?>
	</div>
	<div class="form__row">
		<button type="submit" class="form__button form__button_wide">Применить выбранные</button>
	</div>
</form>
