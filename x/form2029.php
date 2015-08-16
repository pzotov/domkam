<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'>
	<span class="nc-field-caption">Выберите группу элементов:</span>
	<select name="f_Group_Name">
		<option value=""></option>
		<?= listQuery("SELECT DISTINCT Group_Name FROM Message{$classID} WHERE Checked=1 AND Sub_Class_ID={$cc} ORDER BY Group_Name",
			'<option".($data[Group_Name]=="'.$f_Group_Name.'" ? " selected" : "").">$data[Group_Name]</option>') ?>
	</select>
	<span class="nc-field-caption">или создайте новую группу:</span>
	<input type="text" name="new_Group_Name" maxlength="255" size="50" />
</div>

<div class='nc-field'><?= nc_file_field('Picture', "size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

