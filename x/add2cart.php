<?php

require_once '../vars.inc.php';
require_once $ROOT_FOLDER.'connect_io.php';
require_once $MODULE_FOLDER.'cache/function.inc.php';
require_once $MODULE_FOLDER.'minishop/function.inc.php';

if(preg_match('%^(\d+):(\d+)$%ims', $id, $m)){
	$item = $db->get_row("SELECT Name,Price,PriceUSD,".($m[2]==2035 ? 'PriceAction,PriceUSDAction' : '0 AS PriceAction,0 AS PriceUSDAction')."
			FROM Message{$m[2]}
			WHERE Message_ID={$m[1]}", ARRAY_A);
	if($item['PriceUSD'] || $item['PriceUSDAction']){
		$usd = $db->get_var("SELECT USD FROM Catalogue WHERE Catalogue_ID=1");
	}
	if($item['PriceUSDAction']) $f_Price = $item['PriceUSDAction']*$usd;
	else if($item['PriceAction']) $f_Price = $item['PriceAction'];
	else if($item['PriceUSD']) $f_Price = $item['PriceUSD']*$usd;
	else $f_Price = $item['Price'];

	$qty = 1;
	$hash = $nc_minishop->generate_hash(array(
		'name' => $item['Name'],
		'price' => $f_Price,
		'uri' => $id
	));
?>
<form action="/netcat/modules/minishop/index.php" class="form">
	<input class='nc_msvalues' type='hidden' name='good[0][name]' value='<?= htmlspecialchars($item['Name'], ENT_QUOTES) ?>' />
	<input class='nc_msvalues' type='hidden' name='good[0][price]' value='<?= $f_Price ?>' />
	<input class='nc_msvalues' type='hidden' name='good[0][hash]' value='<?= $hash ?>' />
	<input class='nc_msvalues' type='hidden' name='good[0][uri]' value='<?= $id ?>' />

	<section class="prices">
		<h3 class="prices__header">Добавить в корзину</h3>
		<div class="prices__table-wrap">
			<table class="prices__table">
			<tr>
				<th>Наименование</th>
				<th>Цена</th>
				<th>Количество</th>
			</tr>
			<tr>
				<td class="prices__title"><?= $item['Name'] ?></td>
				<td class="prices__price"><?= $f_Price ?> руб.</td>
				<td class="prices__stock">
					<input type="number" class="form__input form__input_short" name="good[0][quantity]" value="<?= $qty ?>" min="1" required>
				</td>
			</tr>
			</table>
		</div>
		<div class="form__row">
			<button type="submit" class="form__button form__button_submit">Добавить в корзину</button>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<button type="reset" class="form__button" onclick="$.fancybox.close();">Отменить</button>
		</div>
	</section>
</form>
<? } else { ?>
	<script type="text/javascript">
		$.fancybox.close();
	</script>
<? } ?>
