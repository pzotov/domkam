
<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_string_field('H1', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<?= $f_Pictures->settings->preview(460,345,1) ?>
<div class='nc-field'><?= $f_Pictures->form() ?></div>

<div class='nc-field'><?= nc_text_field('Text', "", ($class_id ? $class_id : $classID), 1) ?></div>

<h3>Географические координаты для карты проектов</h3>

<div class="nc-field">
	<span class="nc-field-caption">Широта</span>
	<?= nc_float_field('Lat', 'size="12"', ($class_id ? $class_id : $classID)) ?>
</div>
<div class="nc-field">
	<span class="nc-field-caption">Долгота</span>
	<?= nc_float_field('Lon', 'size="12"', ($class_id ? $class_id : $classID)) ?>
</div>

<h3>Использованные материалы</h3>
<?
if(isset($_POST['stones'])) $stones = $_POST['stones'];
else if($tmp = $db->get_col("SELECT Stone_ID FROM Project_Stone_Rel WHERE Project_ID=".$message)) $stones = $tmp;
else $stones = array();

if($_stones = $db->get_results("SELECT a.Message_ID,a.Name, s.Subdivision_Name
					FROM Message2006 a
					LEFT JOIN Subdivision s ON s.Subdivision_ID=a.Subdivision_ID
					GROUP BY a.Message_ID
					ORDER BY s.Priority, s.Subdivision_ID, a.Priority, a.Message_ID
					", ARRAY_A)){
	$prev_sub = null;
	foreach($_stones as $s){
		if($prev_sub!=$s['Subdivision_Name']){
			echo '<br><strong>'.$s['Subdivision_Name'].'</strong><br>';
			$prev_sub = $s['Subdivision_Name'];
		}
		echo '<label><input type="checkbox" name="stones[]" value="'.$s['Message_ID'].'"'.(in_array($s['Message_ID'], $stones) ? ' checked' : '').' /> '.$s['Name'].'</label><br>';
	}
}

