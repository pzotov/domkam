<?php
require_once '../vars.inc.php';
require_once $ROOT_FOLDER.'connect_io.php';

$profile = stripslashes($_GET['profile']);

?>
<form class="form form_tabletop-profile">
	<div class="form__header">Выбор торца столешницы</div>
	<div class="form__row">
		<?= nc_objects_list(30,93, "nc_ctpl=2034&profile=".urlencode($profile)) ?>
	</div>
	<div class="form__row">
		<button type="submit" class="form__button form__button_wide">Применить выбранные</button>
	</div>
</form>
