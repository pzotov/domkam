
<div class='nc-field'><?= nc_string_field('Article', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'>
	<select name="f_Size">
		<option value="">Выберите размер</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2036 ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Size.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'>
	<select name="f_Manufacturing">
		<option value="">Выберите обработку</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2037 ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Manufacturing.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'>
	<select name="f_Manufacturing">
		<option value="">Выберите обработку</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2037 ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Manufacturing.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'><?= nc_bool_field('InStock', "", ($class_id ? $class_id : $classID ), 1) ?></div>

<div class='nc-field'><?= nc_float_field('Price', "maxlength='12' size='12'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_float_field('PriceOpt', "maxlength='12' size='12'", ( $class_id ? $class_id : $classID), 1) ?></div>
