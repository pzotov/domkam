<?php
/**
 * Created by PhpStorm.
 * User: pavelzotov
 * Date: 27.07.15
 * Time: 19:31
 */
?>

<div class='nc-field'><?= nc_string_field('Article', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('EnglishName', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_file_field('Picture', "size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'>
	<span class="nc-field-caption">Цвет камня:</span>
	<select name="f_Color_ID">
		<option value="">-- выбрать --</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2008 WHERE Subdivision_ID={$sub} ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Color_ID.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'>
	<span class="nc-field-caption">Группа камня:</span>
	<select name="f_Group_ID">
		<option value="">-- выбрать --</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2009 WHERE Subdivision_ID={$sub} ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Group_ID.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'><?= nc_string_field('H1', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_text_field('Text1', "", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_text_field('Text2', "", ($class_id ? $class_id : $classID), 1) ?></div>

<h3>Характеристики</h3>

<div class="nc-field"><?= nc_float_field('Param1', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_float_field('Param2', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_float_field('Param3', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_float_field('Param4', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_float_field('Param5', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_float_field('Param6', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_float_field('Param7', 'size="10"', ($class_id ? $class_id : $classID), 1) ?></div>
