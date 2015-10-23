
<div class='nc-field'><?= nc_string_field('Article', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'>
	<span class="nc-field-caption">Размер плитки:</span>
	<select name="f_Size">
		<option value="">Выберите</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2036 ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Size.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'>
	<span class="nc-field-caption">Вид обработки:</span>
	<select name="f_Manufacturing">
		<option value="">Выберите</option>
		<?= listQuery("SELECT Message_ID,Name FROM Message2037 ORDER BY Priority",
			'<option value=\"$data[Message_ID]\"".($data[Message_ID]=="'.$f_Manufacturing.'" ? " selected" : "").">$data[Name]</option>') ?>
	</select>
</div>

<div class='nc-field'>
	<span class="nc-field-caption">Камень, из которого изготовлена плитка:</span>
	<select name="f_Stone_ID">
		<option value="">Выберите</option>
		<?
		if($stones = $db->get_results("SELECT s.Subdivision_Name,a.Message_ID,a.Name,a.Article
				FROM Message2006 a
				LEFT JOIN Subdivision s ON a.Subdivision_ID=s.Subdivision_ID
				".($cc_settings['Stone_Sub_ID'] ? 'WHERE a.Subdivision_ID='.$cc_settings['Stone_Sub_ID'] : '')."
				ORDER BY s.Priority,s.Subdivision_ID,a.Priority,a.Message_ID", ARRAY_A)){
			$prev_sub = null;
			foreach($stones as $data){
				if($prev_sub!=$data['Subdivision_Name']){
					if($prev_sub) echo '</optgroup>';
					echo '<optgroup label="'.$data['Subdivision_Name'].'">';
					$prev_sub = $data['Subdivision_Name'];
				}
				echo '<option value="'.$data['Message_ID'].'"'.($data['Message_ID']==$f_Stone_ID ? " selected" : "").'>'.$data['Article'].': '.$data['Name'].'</option>';
			}
			if($prev_sub) echo '</optgroup>';
		}
		?>
	</select>
</div>

<div class='nc-field'><?= nc_float_field('InStock', "size='12'", ($class_id ? $class_id : $classID ), 1) ?></div>

<div class='nc-field'><?= nc_float_field('Price', "maxlength='12' size='12'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_float_field('PriceUSD', "maxlength='12' size='12'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_float_field('PriceOpt', "maxlength='12' size='12'", ( $class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_float_field('PriceAction', "maxlength='12' size='12'", ( $class_id ? $class_id : $classID), 1) ?></div>
