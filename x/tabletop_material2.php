<?php
require_once '../vars.inc.php';
require_once $ROOT_FOLDER.'connect_io.php';
require_once $MODULE_FOLDER.'cache/function.inc.php';

$material = stripslashes($_GET['material']);

?>
<form class="form form_tabletop-material2">
	<div class="form__header">Выбор материала из каталога</div>
	<div class="form__row">
		<?= nc_objects_list(9,26, "nc_ctpl=2033&material=".urlencode($material)) ?>
	</div>
	<div class="form__row">
		<button type="submit" class="form__button form__button_wide">Применить выбранные</button>
	</div>
</form>
