<?php
require_once '../vars.inc.php';
require_once $ROOT_FOLDER.'connect_io.php';
require_once $MODULE_FOLDER.'cache/function.inc.php';

$material = htmlspecialchars(stripslashes($_GET['material']), ENT_QUOTES);

?>
<form class="form form_tabletop-material">
	<div class="form__header">Материал <?= htmlspecialchars($_GET['name'], ENT_QUOTES) ?></div>
	<div class="form__row">
		<div class="form__label">Выберите материал из каталога</div>
		<div class="form__select form__select_material">Выберите, пожалуйста</div>
	</div>
	<div class="form__row">
		<div class="form__label">или введите название камня</div>
		<input type="text" class="form__input" id="material_text" value="<?= $material ?>" placeholder="Укажите название камня" required />
	</div>
	<div class="form__row">
		<button type="submit" class="form__button form__button_wide">Выбрать</button>
	</div>
</form>
