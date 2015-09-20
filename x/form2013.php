<div class='nc-field'><?= nc_related_field('Sub_ID', "") ?></div>

<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('Button_Text', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'>
	<span class="nc-field-caption">Цвета камня для вывода:</span>
	<?
	$selected_colors = explode(",", $f_OnlyColor);
	if($colors = $db->get_results("SELECT a.Message_ID, a.Name, s.Subdivision_Name
				FROM Message2008 a
				LEFT JOIN Subdivision s ON s.Subdivision_ID=a.Subdivision_ID
				GROUP BY a.Message_ID
				ORDER BY s.Priority, s.Subdivision_ID, a.Priority, a.Message_ID
				", ARRAY_A)){
		$prev_sub = null;
		foreach($colors as $c){
			if($prev_sub!=$c['Subdivision_Name']){
				$prev_sub = $c['Subdivision_Name'];
				echo '<br><strong>'.$prev_sub.'</strong><br>';
			}
			echo '<label><input type="checkbox" name="colors[]" value="'.$c['Message_ID'].'"'.(in_array($c['Message_ID'], $selected_colors) ? ' checked' : '').' />
			'.$c['Name'].'
			</label><br>';
		}
	} else dump($db->last_error);
	?>
</div>

<div class='nc-field'>
	<span class="nc-field-caption">Группы камня для вывода:</span>
	<?
	$selected_groups = explode(",", $f_OnlyGroup);
	if($groups = $db->get_results("SELECT a.Message_ID,a.Name, s.Subdivision_Name
				FROM Message2009 a
				LEFT JOIN Subdivision s ON s.Subdivision_ID=a.Subdivision_ID
				GROUP BY a.Message_ID
				ORDER BY s.Priority, s.Subdivision_ID, a.Priority, a.Message_ID
				", ARRAY_A)){
		$prev_sub = null;
		foreach($groups as $c){
			if($prev_sub!=$c['Subdivision_Name']){
				$prev_sub = $c['Subdivision_Name'];
				echo '<br><strong>'.$prev_sub.'</strong><br>';
			}
			echo '<label><input type="checkbox" name="groups[]" value="'.$c['Message_ID'].'"'.(in_array($c['Message_ID'], $selected_groups) ? ' checked' : '').' />
			'.$c['Name'].'
			</label><br>';
		}
	}
	?>
</div>

