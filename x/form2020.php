
<div class='nc-field'><?= nc_string_field('Name', "maxlength='255' size='50'", ($class_id ? $class_id : $classID), 1) ?></div>

<?= $f_Pictures->settings->preview(460,345,1) ?>
<div class='nc-field'><?= $f_Pictures->form() ?></div>

<div class='nc-field'><?= nc_text_field('Text', "", ($class_id ? $class_id : $classID), 1) ?></div>

