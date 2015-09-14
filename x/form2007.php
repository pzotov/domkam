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

<?
	$color2 = explode(",", $f_Color2_ID);
	$colors = $db->get_results("SELECT Message_ID,Name FROM Message2008 WHERE Subdivision_ID={$sub} ORDER BY Priority", ARRAY_A);
?>
<div class='nc-field'>
	<span class="nc-field-caption">Дополнительные цвета камня:</span>
	<? foreach($colors as $c){ ?>
		<label><input type="checkbox" name="color2[]" value="<?= $c['Message_ID'] ?>" <?= @in_array($c['Message_ID'], $color2) ? ' checked' : '' ?>> <?= $c['Name'] ?></label><br>
	<? } ?>
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

<br>
<h3>Блок &laquo;Применение камня&raquo;</h3>

<div class='nc-field'><?= nc_string_field('Application_Header', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<div class='nc-field'><?= nc_text_field('Application_Text', "", ($class_id ? $class_id : $classID), 1) ?></div>

<style>
	.app-block {
		position: relative;
		border: 1px solid #ccc;
		padding: 10px;
		margin-bottom: 10px;
	}
	a.app-remove-link {
		position: absolute;
		right: 10px;
		top: 10px;
		font-size: 36px;
		color: #f00;
		text-decoration: none;
	}
</style>
<script type="text/x-tpl" id="app-tpl">
	<div class="app-block" id="app_fieldset_%index">
		<input type="checkbox" name="apps[%index][Kill]" value="1" class="app-kill hidden" />
		<a href="" title="Удалить этот вариант" class="app-remove-link" id="apps_remove_%index">&times;</a>
		<div class="nc-field">
			<span class="nc-field-caption">Название:</span>
			<input type="text" name="apps[%index][Name]" size="50" id="apps_%index_Name" />
		</div>
		<div class="nc-field">
			<span class="nc-field-caption">Фото 100&times;100px:</span>
			<input type="file" name="apps[%index][Picture]" />
			<div class="nc-field-old" id="apps_%index_Picture">
				<input type="hidden" name="apps[%index][Picture_old]" id="apps_%index_Picture_old" />
				<span></span>
			</div>
		</div>
		<div class="nc-field">
			<span class="nc-field-caption">Описание:</span>
			<textarea name="apps[%index][Text]" id="apps_%index_Text" cols="30" rows="5"></textarea>
		</div>
	</div>
</script>

<h4>Варианты применения камня</h4>
<div id="app-box"></div>
<script type="text/javascript">
	var app_tpl = $("#app-tpl").html(),
		app_box = $("#app-box"),
		app_index = 0;

	function addApp(app){
		app_box.append(app_tpl.replace(/%index/img, app_index));

		$("#apps_" + app_index + "_Name").val(app.Name);
		$("#apps_" + app_index + "_Text").val(app.Text);
		if(app.Picture){
			$("#apps_" + app_index + "_Picture_old").val(app.Picture);
			$("#apps_" + app_index + "_Picture span").html('Уже загружено <a href="'+app.Picture+'" target="_blank">фото</a>');
		}

		$("#apps_remove_" + app_index).click(function(e){
			e.preventDefault();
			if(confirm("Действительно хотите удалить этот вариант?")){
				$(this)
					.closest(".app-block").hide()
					.find(".app-kill").attr("checked", "checked");
				;
			}
		});
		app_index++;
	}
<?
	if($_POST['posting']) $f_Application = stripslashes($f_Application);
	$apps = json_decode($f_Application, true);
	if($apps && count($apps)){
		foreach($apps as $app){
			echo 'addApp('.json_encode($app).');';
		}
	}
?>
</script>
<div class="nc-field">
	<button type="button" class="nc-btn" onclick="addApp({Name: '', Text: '', Picture: ''});">Добавить вариант применения</button>
</div>

<br>
<h3>Характеристики</h3>

<div class="nc-field"><?= nc_string_field('Param1', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_string_field('Param2', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_string_field('Param3', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_string_field('Param4', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_string_field('Param5', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_string_field('Param6', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_string_field('Param7', 'maxlength="255" size="50"', ($class_id ? $class_id : $classID), 1) ?></div>

<br>
<h3>Аналоги камня</h3>
<ul>
<?
	if(isset($_POST['posting'])){
		dump(123);
		$analog_values = isset($_POST['analogs']) ? $_POST['analogs'] : array();
	} else if(!($analog_values = $db->get_col("SELECT Stone2_ID FROM Stone_Analogs WHERE Stone1_ID={$message}"))) $analog_values = array();

	if($stones = $db->get_results("SELECT Message_ID,Name,EnglishName FROM Message{$classID} WHERE Subdivision_ID={$sub} AND Message_ID!='{$message}' ORDER BY Priority", ARRAY_A)){
		foreach($stones as $stone){
			echo '<li><label><input type="checkbox" name="analogs[]" value="'.$stone['Message_ID'].'"'.(in_array($stone['Message_ID'], $analog_values) ? ' checked' : '').' /> ';
			echo $stone['Name'];
			if($stone['EnglishName']) echo ' ('.$stone['EnglishName'].')';
			echo '</label></li>';
		}
	}
?>
</ul>

<div class="nc-field"><?= nc_date_field('News_Date', '', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_file_field('News_Picture', '', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_date_field('Action_Date', '', ($class_id ? $class_id : $classID), 1) ?></div>

<div class="nc-field"><?= nc_file_field('Action_Picture', '', ($class_id ? $class_id : $classID), 1) ?></div>
