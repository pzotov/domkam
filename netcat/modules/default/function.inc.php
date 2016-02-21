<?php
global $ru_monthes, $day_of_week;
$ru_monthes = array(
	'01' => 'января',
	'02' => 'февраля',
	'03' => 'марта',
	'04' => 'апреля',
	'05' => 'мая',
	'06' => 'июня',
	'07' => 'июля',
	'08' => 'августа',
	'09' => 'сентября',
	'10' => 'октября',
	'11' => 'ноября',
	'12' => 'декабря'
);
$day_of_week = array( "воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота" );

/**
 * @param $goal_id - номер цели
 * @param string $type - тип вызова кода цели
 * @param null $param - дополнительный параметр
 * @return mixed
 */
function goal($goal_id, $type="script", $param=NULL){
	return nc_objects_list(380, 687, "id={$goal_id}&type={$type}&param={$param}");
}

/**
 * @param $toEmail - куда отправлять письмо из настроек инфоблока
 * @param $fromEmail - e-mail, который указал посетитель сайта
 * @return string - возвращает e-mail на который нужно отправить письмо
 */
function selectToEmail($toEmail, $fromEmail){
	global $db, $current_catalogue;
	$result = $current_catalogue['Email'];
	//если этот e-mail уже был в заявках, то нужно отправлять на тот же e-mail, что и в прошлый раз
	if($existEmail = $db->get_var("SELECT toEmail FROM Message2010 WHERE Email='".$db->escape($fromEmail)."' ORDER BY Cretaed DESC LIMIT 1")) $result = $existEmail;
	//если в настройках инфоблока четко указан адресат, который отвечает за эту форму заявки,
	// то отправляем ему
	else if($toEmail) $result = $toEmail;
	//если нет, то поочередно выбираем отправку на prod1@domkam.ru и на prod2domkam.ru
	else if(rand(0,1)) $result = "prod1@domkam.ru, webmaster@a-r-b.ru";
	else $result = "prod2@domkam.ru, 136@a-r-b.ru";

	return $result;
}

function userRegion(){
	$gb = new IPGeoBase();
	$region = "";
	if(($data = $gb->getRecord(getenv('REMOTE_ADDR'))) && ($data['city'] || $data['region'])){
		$region .= "Регион: ".iconv('windows-1251', 'utf-8', $data['city']);
		if($data['city'] && $data['region'] ) $region .= ', ';
		if($data['region']) $region .= iconv('windows-1251', 'utf-8', $data['region']);
		$region .= ' (ip-адрес '.getenv('REMOTE_ADDR').')';
	}
	return $region;
}

function __log($text){
	global $__log, $nc_core;
	if(!$__log) $__log = fopen($nc_core->DOCUMENT_ROOT.'/x/log.txt', "a");
	if($__log) fwrite($__log, date("[Y-m-d H:i:s] ").$text."\n");
}

function listValue($id){
	global $db;
	return $db->get_var("SELECT Name FROM Message2032 WHERE Message_ID=".intval($id));
}

function updateUSDRate(){
	global $nc_core;

	$localfile = $nc_core->DOCUMENT_ROOT."/x/cbr.xml";
	if(!file_exists($localfile) || filemtime($localfile)<time()-43200){
		if($rates_xml = file_get_contents(strftime("http://www.cbr.ru/scripts/XML_daily.asp?date_req=%d%%2F%m%%2F%Y"))){
			file_put_contents($localfile, $rates_xml);
			$rates = json_decode(json_encode(simplexml_load_string($rates_xml)),true);
			foreach($rates['Valute'] as $v){
				if($v['NumCode']==840) {
					$val = trim(str_replace(',', '.', $v['Value']));
					update_row("Catalogue", array(
						"USD" => trim(str_replace(',', '.', round($val*1.01,2)))
					), "Catalogue_ID=1");
					return;
				}
			}
		}
	}
}

function importCatalog(){
	if(!$_FILES['xls'] || $_FILES['xls']['error'])
		return array('error' => 'Файл не загружен');

	$result = array(
		'ok' => true,
		'error' => false
	);
	global $nc_core, $db;
	require_once $nc_core->INCLUDE_FOLDER.'lib/excel/PHPExcel.php';

	try {
		$file_type = PHPExcel_IOFactory::identify($_FILES['xls']['tmp_name']);
		$reader = PHPExcel_IOFactory::createReader($file_type);
		$ea = $reader->load($_FILES['xls']['tmp_name']);

		$ews = $ea->getSheet(0);
		$max_row = $ews->getHighestRow();
		$max_col = $ews->getHighestColumn();

		$sub_id = null;
		$cc_id = null;
		for($row=1; $row<=$max_row; $row++){
			$_data = $ews->rangeToArray('a'.$row.':'.$max_col.$row, NULL, TRUE, FALSE);
			$data = $_data[0];
			$colors = array();
			$groups = array();

			if(!$data[0] && trim($data[1]) && !$data[2]){
				//Строка с названием раздела
				$sub_name = trim($data[1]);
				list($sub_id, $cc_id) = $db->get_row("SELECT s.Subdivision_ID,cc.Sub_Class_ID
										FROM Subdivision s
										LEFT JOIN Sub_Class cc ON cc.Subdivision_ID=s.Subdivision_ID
										WHERE s.Subdivision_Name='".mysql_real_escape_string(trim($data[1]))."' AND cc.Class_ID=2006
										GROUP BY cc.Sub_Class_ID
										ORDER BY cc.Checked DESC, cc.Priority
										", ARRAY_N);
				$colors = array();
				if($_colors = $db->get_results("SELECT Message_ID,ShortName FROM Message2008 WHERE Subdivision_ID={$sub_id}", ARRAY_A)){
					foreach($_colors as $c){
						$colors[mb_strtolower($c['ShortName'])] = $c['Message_ID'];
					}
				}
				$groups = array();
				if($_groups = $db->get_results("SELECT Message_ID,ShortName FROM Message2009 WHERE Subdivision_ID={$sub_id}", ARRAY_A)){
					foreach($_groups as $c){
						$groups[mb_strtolower($c['ShortName'])] = $c['Message_ID'];
					}
				}
				//Пропускаем строку с заголовками колонок
				$row++;
			} else if($data[0] && $data[1] && $sub_id) {
				//строка с камнем
				$a = array(
					'Article' => $data[0],
					'Name' => $data[1],
					'EnglishName' => $data[2],
					'Color_ID' => $colors[$data[3]],
					'Group_ID' => $groups[$data[4]],
					'H1' => $data[5],
					'Text1' => $data[6],
					'Text2' => $data[7],
					'Param1' => $data[8],
					'Param2' => $data[9],
					'Param3' => $data[10],
					'Param4' => $data[11],
					'Param5' => $data[12],
					'Param6' => $data[13],
					'Param7' => $data[14]
				);
				if($stone_id = $db->get_var("SELECT Message_ID FROM Message2006 WHERE Article='".mysql_real_escape_string(trim($data[0]))."' LIMIT 1")){
					update_row("Message2006", $a, "Message_ID=".$stone_id);
				} else {
					$a['Subdivision_ID'] = $sub_id;
					$a['Sub_Class_ID'] = $cc_id;
					$a['Checked'] = 1;
					$a['Keyword'] = translit($a['Name']);
					insert_row("Message2006", $a);
				}
			}
		}
	} catch (Exception $e) {
		$result['error'] = $e->getMessage();
	}

	return $result;
}

function exportCatalog(){
	global $nc_core, $db;
	require_once $nc_core->INCLUDE_FOLDER.'lib/excel/PHPExcel.php';

	$items = $db->get_results("SELECT a.*,s.Subdivision_Name, c.ShortName Color_Name, g.ShortName Group_Name
								FROM Message2006 a
								LEFT JOIN Subdivision s ON s.Subdivision_ID=a.Subdivision_ID
								LEFT JOIN Message2008 c ON c.Message_ID=a.Color_ID
								LEFT JOIN Message2009 g ON g.Message_ID=a.Group_ID
								GROUP BY a.Message_ID
								ORDER BY s.Priority,s.Subdivision_ID,a.Priority,a.Message_ID
								", ARRAY_A);

	$ea = new \PHPExcel();
	$ea->getProperties()
		->setTitle('Каталог')
	;
	$ews = $ea->getSheet(0);
	$ews->setTitle('Каталог');

	$ews->getColumnDimension('A')->setWidth(10);
	$ews->getColumnDimension('B')->setWidth(20);
	$ews->getColumnDimension('C')->setWidth(20);
	$ews->getColumnDimension('D')->setWidth(15);
	$ews->getColumnDimension('E')->setWidth(15);
	$ews->getColumnDimension('F')->setWidth(30);
	$ews->getColumnDimension('G')->setWidth(50);
	$ews->getColumnDimension('H')->setWidth(50);

	$row = 1;
	$prev_sub = null;
	foreach($items as $item){
		if($prev_sub!=$item['Subdivision_Name']){
			if($prev_sub) $row +=3;

			$ews->setCellValue('b'.$row, $item['Subdivision_Name']);
			$ews
				->getStyle('a'.$row.':bb'.$row)
				->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()
				->setARGB('00ffff00');
			$ews
				->getStyle('a'.$row.':bb'.$row)
				->applyFromArray(array(
					'font' => array(
						'bold' => true,
						'size' => 15
					)
				));
			$prev_sub = $item['Subdivision_Name'];
			$row++;

			$ews->setCellValue('a'.$row, 'Артикул');
			$ews->setCellValue('b'.$row, 'Русское название');
			$ews->setCellValue('c'.$row, 'Английское название');
			$ews->setCellValue('d'.$row, 'Цвет');
			$ews->setCellValue('e'.$row, 'Группа');
			$ews->setCellValue('f'.$row, 'Заголовок H1');
			$ews->setCellValue('g'.$row, 'Краткое описание камня');
			$ews->setCellValue('h'.$row, 'Описание камня под фото и параметрами');
			$ews->setCellValue('i'.$row, 'объемный вес, кг/м3');
			$ews->setCellValue('j'.$row, 'удельная плотность, г/см3');
			$ews->setCellValue('k'.$row, 'водопоглощение, %');
			$ews->setCellValue('l'.$row, 'пористость, %');
			$ews->setCellValue('m'.$row, 'истираемость, г/см2');
			$ews->setCellValue('n'.$row, 'морозостойкость, циклов');
			$ews->setCellValue('o'.$row, 'предел прочности при сжатии, кг/см2 (МПа)');

			$ews
				->getStyle('a'.$row.':bb'.$row)
				->applyFromArray(array(
					'font' => array(
						'bold' => true
					)
				));
			$row++;
		}

		$ews->setCellValue('a'.$row, $item['Article']);
		$ews->setCellValue('b'.$row, $item['Name']);
		$ews->setCellValue('c'.$row, $item['EnglishName']);
		$ews->setCellValue('d'.$row, $item['Color_Name']);
		$ews->setCellValue('e'.$row, $item['Group_Name']);
		$ews->setCellValue('f'.$row, $item['H1']);
		$ews->setCellValue('g'.$row, $item['Text1']);
		$ews->setCellValue('h'.$row, $item['Text2']);
		$ews->setCellValue('i'.$row, $item['Param1']);
		$ews->setCellValue('j'.$row, $item['Param2']);
		$ews->setCellValue('k'.$row, $item['Param3']);
		$ews->setCellValue('l'.$row, $item['Param4']);
		$ews->setCellValue('m'.$row, $item['Param5']);
		$ews->setCellValue('n'.$row, $item['Param6']);
		$ews->setCellValue('o'.$row, $item['Param7']);

		$ews->getStyle('g'.$row)->getAlignment()->setWrapText(true);
		$ews->getStyle('h'.$row)->getAlignment()->setWrapText(true);

		$row++;
	}

	$ews->getStyle('a1:bb'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$writer = \PHPExcel_IOFactory::createWriter($ea, 'Excel2007');

	//$writer->setIncludeCharts(true);
	$writer->save($nc_core->DOCUMENT_ROOT.'/x/catalog.xlsx');

	//$writer->save('php://output');
	return '/x/catalog.xlsx';
}

function importPlitka(){
	if(!$_FILES['xls'] || $_FILES['xls']['error'])
		return array('error' => 'Файл не загружен');

	$result = array(
		'ok' => true,
		'error' => ''
	);
	global $nc_core, $db, $sub, $cc, $classID;
	$cc_id = $db->get_var("SELECT Sub_Class_ID FROM Sub_Class WHERE Subdivision_ID={$sub} AND Sub_Class_ID!={$cc} AND Class_ID={$classID} ORDER BY Priority LIMIT 1");

	$db->query("DELETE FROM Message{$classID} WHERE Sub_Class_ID={$cc_id}");

	$stones = array();
	if($_stones = $db->get_results("SELECT Message_ID,Article FROM Message2006", ARRAY_A)){
		foreach($_stones as $s){
			$stones[mb_strtolower(trim($s['Article']))] = $s['Message_ID'];
		}
	} else return array('error' => 'нет камней');

	$sizes = array();
	if($_sizes = $db->get_results("SELECT Message_ID,Name FROM Message2036", ARRAY_A)){
		foreach($_sizes as $s){
			$sizes[trim($s['Name'])] = $s['Message_ID'];
		}
	}
	//return array('error' => print_r($sizes, true));
	$mans = array();
	if($_mans = $db->get_results("SELECT Message_ID,Name FROM Message2037", ARRAY_A)){
		foreach($_mans as $s){
			$mans[trim($s['Name'])] = $s['Message_ID'];
		}
	}

	require_once $nc_core->INCLUDE_FOLDER.'lib/excel/PHPExcel.php';

	//return $result;

	try {
		$file_type = PHPExcel_IOFactory::identify($_FILES['xls']['tmp_name']);
		$reader = PHPExcel_IOFactory::createReader($file_type);
		$ea = $reader->load($_FILES['xls']['tmp_name']);

		$ews = $ea->getSheet(0);
		$max_row = $ews->getHighestRow();
		$max_col = $ews->getHighestColumn();

		for($row=1; $row<=$max_row; $row++){
			$_data = $ews->rangeToArray('a'.$row.':'.$max_col.$row, NULL, TRUE, FALSE);
			$data = $_data[0];

			if(!$data[0] && trim($data[1]) && !$data[2]){
				//Строка с названием раздела

				//Пропускаем строку с заголовками колонок
				$row++;
				continue;
			} else if($data[0] && $data[1]) {
				//строка с плиткой
				$data[2] = mb_strtolower(trim($data[2]));
				if(!isset($stones[$data[2]])){
					$result['error'] .= 'Камень &laquo;'.$data[2].'&raquo; не найден в базе<br>';
					continue;
				}
				if(!isset($sizes[trim($data[4])])){
					$sizes[trim($data[4])] = insert_row("Message2036", array(
						"Subdivision_ID" => 6,
						"Sub_Class_ID" => 96,
						"Priority" => intval($db->get_var("SELECT MAX(Priority) FROM Message2036"))+1,
						"Checked" => 1,
						"Name" => trim($data[4])
					));
					//$result['error'] .= 'Размер плитки &laquo;'.$data[4].'&raquo; не найден в базе<br>';
					//continue;
				}
				if(!isset($mans[trim($data[5])])){
					$mans[trim($data[5])] = insert_row("Message2037", array(
						"Subdivision_ID" => 6,
						"Sub_Class_ID" => 97,
						"Priority" => intval($db->get_var("SELECT MAX(Priority) FROM Message2037"))+1,
						"Checked" => 1,
						"Name" => trim($data[5])
					));
//					$result['error'] .= 'Вид обработки &laquo;'.$data[5].'&raquo; не найден в базе<br>';
//					continue;
				}
				$a = array(
					'Article' => $data[0],
					'Name' => $data[1],
					'Stone_ID' => $stones[$data[2]],
					'Size' => $sizes[trim($data[4])],
					'SizeStr' => $data[4],
					'Manufacturing' => $mans[trim($data[5])],
					'ManufacturingStr' => $data[5],
					'InStock' => str_replace(",", ".", $data[6]),
					'Price' => str_replace(",", ".", $data[7]),
					'PriceAction' => str_replace(",", ".", $data[8]),
					'PriceUSD' => str_replace(",", ".", $data[9]),
					'PriceUSDAction' => str_replace(",", ".", $data[10])
				);
				if($id = $db->get_var("SELECT Message_ID FROM Message2035 WHERE Sub_Class_ID={$cc_id} AND Article='".mysql_real_escape_string(trim($data[0]))."' LIMIT 1")){
					update_row("Message2035", $a, "Message_ID=".$id);
				} else {
					$a['Subdivision_ID'] = $sub;
					$a['Sub_Class_ID'] = $cc_id;
					$a['Checked'] = 1;
					$a['Keyword'] = translit($a['Article'].'-'.$a['Name']);
					$a['Priority'] = intval($db->get_var("SELECT MAX(Priority) FROM Message2035 WHERE Sub_Class_ID={$cc_id}"))+1;
					insert_row("Message2035", $a);
				}
//				$result['error'] .= $db->last_query."<br>";
//				$result['error'] .= $db->last_error."<br>";
			}
		}
	} catch (Exception $e) {
		$result['error'] = $e->getMessage();
	}

	return $result;
}

function exportPlitka(){
	global $nc_core, $db, $sub;
	require_once $nc_core->INCLUDE_FOLDER.'lib/excel/PHPExcel.php';

	$items = $db->get_results("SELECT a.*,
								s.Subdivision_Name,
								stone.Article Stone_Article, stone.Name Stone_Name, stone.EnglishName Stone_EnglishName
								FROM Message2035 a
								LEFT JOIN Subdivision s ON s.Subdivision_ID=a.Subdivision_ID
								LEFT JOIN Message2006 stone ON stone.Message_ID=a.Stone_ID
								WHERE a.Subdivision_ID={$sub}
								GROUP BY a.Message_ID
								ORDER BY s.Priority,s.Subdivision_ID,stone.Priority,stone.Message_ID,a.Priority,a.Message_ID
								", ARRAY_A);

	$ea = new \PHPExcel();
	$ea->getProperties()
		->setTitle('Остатки')
	;
	$ews = $ea->getSheet(0);
	$ews->setTitle('Остатки');

	$ews->getColumnDimension('a')->setWidth(10);
	$ews->getColumnDimension('b')->setWidth(25);
	$ews->getColumnDimension('c')->setWidth(15);
	$ews->getColumnDimension('d')->setWidth(25);
	$ews->getColumnDimension('e')->setWidth(20);
	$ews->getColumnDimension('f')->setWidth(15);
	$ews->getColumnDimension('g')->setWidth(10);
	$ews->getColumnDimension('h')->setWidth(15);
	$ews->getColumnDimension('i')->setWidth(15);
	$ews->getColumnDimension('j')->setWidth(15);
	$ews->getColumnDimension('k')->setWidth(15);

	$row = 1;
	$prev_sub = null;
	foreach($items as $item){
		if($prev_sub!=$item['Subdivision_Name']){
			if($prev_sub) $row +=3;

			$ews->setCellValue('b'.$row, $item['Subdivision_Name']);
			$ews
				->getStyle('a'.$row.':bb'.$row)
				->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()
				->setARGB('00ffff00');
			$ews
				->getStyle('a'.$row.':bb'.$row)
				->applyFromArray(array(
					'font' => array(
						'bold' => true,
						'size' => 15
					)
				));
			$prev_sub = $item['Subdivision_Name'];
			$row++;

			$ews->setCellValue('a'.$row, 'Артикул');
			$ews->setCellValue('b'.$row, 'Название плитки');
			$ews->setCellValue('c'.$row, 'Номер камня');
			$ews->setCellValue('d'.$row, 'Название камня');
			$ews->setCellValue('e'.$row, 'Размер');
			$ews->setCellValue('f'.$row, 'Вид обработки');
			$ews->setCellValue('g'.$row, 'Наличие');
			$ews->setCellValue('h'.$row, 'Розничная цена');
			$ews->setCellValue('i'.$row, 'Цена по акции');
			$ews->setCellValue('j'.$row, 'Цена в у.е.');
			$ews->setCellValue('k'.$row, 'Цена по акции в у.е.');

			$ews
				->getStyle('a'.$row.':bb'.$row)
				->applyFromArray(array(
					'font' => array(
						'bold' => true
					)
				));
			$row++;
		}

		$ews->setCellValue('a'.$row, $item['Article']);
		$ews->setCellValue('b'.$row, $item['Name']);
		$ews->setCellValue('c'.$row, $item['Stone_Article']);
		$ews->setCellValue('d'.$row, $item['Stone_Name'].($item['Stone_EnglishName'] ? ' ('.$item['Stone_EnglishName'].')' : ''));
		$ews->setCellValue('e'.$row, $item['SizeStr']);
		$ews->setCellValue('f'.$row, $item['ManufacturingStr']);
		$ews->setCellValue('g'.$row, $item['InStock']);
		$ews->setCellValue('h'.$row, $item['Price']);
		$ews->setCellValue('i'.$row, $item['PriceAction']);
		$ews->setCellValue('j'.$row, $item['PriceUSD']);
		$ews->setCellValue('k'.$row, $item['PriceUSDAction']);

		$row++;
	}

	$ews->getStyle('a1:bb'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$writer = \PHPExcel_IOFactory::createWriter($ea, 'Excel2007');

	//$writer->setIncludeCharts(true);
	$writer->save($nc_core->DOCUMENT_ROOT.'/x/ostatki.xlsx');

	//$writer->save('php://output');
	return '/x/ostatki.xlsx';
}

function importTovary(){
	if(!$_FILES['xls'] || $_FILES['xls']['error'])
		return array('error' => 'Файл не загружен');

	$result = array(
		'ok' => true,
		'error' => ''
	);
	global $nc_core, $db;

	require_once $nc_core->INCLUDE_FOLDER.'lib/excel/PHPExcel.php';

	//return $result;

	$sub_id = null;
	$cc_id = null;

	try {
		$file_type = PHPExcel_IOFactory::identify($_FILES['xls']['tmp_name']);
		$reader = PHPExcel_IOFactory::createReader($file_type);
		$ea = $reader->load($_FILES['xls']['tmp_name']);

		$ews = $ea->getSheet(0);
		$max_row = $ews->getHighestRow();
		$max_col = $ews->getHighestColumn();

		for($row=1; $row<=$max_row; $row++){
			$_data = $ews->rangeToArray('a'.$row.':'.$max_col.$row, NULL, TRUE, FALSE);
			$data = $_data[0];

			if(!$data[0] && trim($data[1]) && !$data[2]){
				//Строка с названием раздела
				$sub_name = trim($data[1]);
				list($sub_id, $cc_id) = $db->get_row("SELECT s.Subdivision_ID,cc.Sub_Class_ID
										FROM Subdivision s
										LEFT JOIN Sub_Class cc ON cc.Subdivision_ID=s.Subdivision_ID
										WHERE s.Subdivision_Name='".mysql_real_escape_string(trim($data[1]))."' AND cc.Class_ID=2056 AND cc.Class_Template_ID=0
										GROUP BY cc.Sub_Class_ID
										ORDER BY cc.Checked DESC, cc.Priority
										", ARRAY_N);
				//Пропускаем строку с заголовками колонок
				$row++;
				continue;
			} else if($data[0] && $data[1] && $sub_id) {
				//строка с товаром
				$a = array(
					'Article' => $data[0],
					'Name' => $data[1],
					'Description' => $data[2],
					'InStock' => str_replace(",", ".", trim($data[3])),
					'Price' => str_replace('от ', '', str_replace(",", ".", trim($data[4]))),
					'PriceUSD' => str_replace('от ', '', str_replace(",", ".", trim($data[5]))),
					'PricePrefix' => (mb_substr(trim($data[4]), 0, 2)=="от" || mb_substr(trim($data[5]), 0, 2)=="от") ? 1 : 0
				);

				if($id = $db->get_var("SELECT Message_ID FROM Message2056 WHERE Sub_Class_ID={$cc_id} AND Article='".mysql_real_escape_string(trim($data[0]))."' LIMIT 1")){
					update_row("Message2056", $a, "Message_ID=".$id);
				} else {
					$a['Subdivision_ID'] = $sub_id;
					$a['Sub_Class_ID'] = $cc_id;
					$a['Checked'] = 1;
					$a['Keyword'] = translit($a['Name']);
					insert_row("Message2056", $a);
				}
				//$result['error'] .= $db->last_query."\n";
			}
		}
	} catch (Exception $e) {
		$result['error'] = $e->getMessage();
	}

	return $result;
}

function exportTovary(){
	global $nc_core, $db, $sub;
	require_once $nc_core->INCLUDE_FOLDER.'lib/excel/PHPExcel.php';

	$items = $db->get_results("SELECT a.*,
								s.Subdivision_Name
								FROM Message2056 a
								LEFT JOIN Subdivision s ON s.Subdivision_ID=a.Subdivision_ID
								GROUP BY a.Message_ID
								ORDER BY s.Priority,s.Subdivision_ID,a.Priority,a.Message_ID
								", ARRAY_A);

	$ea = new \PHPExcel();
	$ea->getProperties()
		->setTitle('Товары')
	;
	$ews = $ea->getSheet(0);
	$ews->setTitle('Товары');

	$ews->getColumnDimension('a')->setWidth(10);
	$ews->getColumnDimension('b')->setWidth(20);
	$ews->getColumnDimension('c')->setWidth(20);
	$ews->getColumnDimension('d')->setWidth(20);
	$ews->getColumnDimension('e')->setWidth(15);
	$ews->getColumnDimension('f')->setWidth(15);
	$ews->getColumnDimension('g')->setWidth(15);
	$ews->getColumnDimension('h')->setWidth(15);

	$row = 1;
	$prev_sub = null;
	foreach($items as $item){
		if($prev_sub!=$item['Subdivision_Name']){
			if($prev_sub) $row +=3;

			$ews->setCellValue('b'.$row, $item['Subdivision_Name']);
			$ews
				->getStyle('a'.$row.':bb'.$row)
				->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()
				->setARGB('00ffff00');
			$ews
				->getStyle('a'.$row.':bb'.$row)
				->applyFromArray(array(
					'font' => array(
						'bold' => true,
						'size' => 15
					)
				));
			$prev_sub = $item['Subdivision_Name'];
			$row++;

			$ews->setCellValue('a'.$row, 'Артикул');
			$ews->setCellValue('b'.$row, 'Наименование');
			$ews->setCellValue('c'.$row, 'Описание');
			$ews->setCellValue('d'.$row, 'Наличие');
			$ews->setCellValue('e'.$row, 'Цена');
			$ews->setCellValue('f'.$row, 'Цена в у.е.');

			$ews
				->getStyle('a'.$row.':bb'.$row)
				->applyFromArray(array(
					'font' => array(
						'bold' => true
					)
				));
			$row++;
		}

		$ews->setCellValue('a'.$row, $item['Article']);
		$ews->setCellValue('b'.$row, $item['Name']);
		$ews->setCellValue('c'.$row, $item['Description']);
		$ews->setCellValue('d'.$row, $item['InStock']);
		if($item['Price']) $ews->setCellValue('e'.$row, ($item['PricePrefix'] ? 'от ' : '' ).$item['Price']);
		if($item['PriceUSD']) $ews->setCellValue('f'.$row, ($item['PricePrefix'] ? 'от ' : '' ).$item['PriceUSD']);

		$row++;
	}

	$ews->getStyle('a1:bb'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

	$writer = \PHPExcel_IOFactory::createWriter($ea, 'Excel2007');

	//$writer->setIncludeCharts(true);
	$writer->save($nc_core->DOCUMENT_ROOT.'/x/tovary.xlsx');

	//$writer->save('php://output');
	return '/x/tovary.xlsx';
}


function saveStonesInProject($message){
	global $db;
	$db->query("DELETE FROM Project_Stone_Rel WHERE Project_ID=".$message);
	foreach($_POST['stones'] as $s){
		insert_row("Project_Stone_Rel", array(
			"Project_ID" => $message,
			"Stone_ID" => $s
		));
	}
}

function makeStoneApplications(){
	global $nc_core, $sub, $cc;
	require_once $nc_core->INCLUDE_FOLDER.'classes/nc_imagetransform.class.php';

	if(is_array($_POST['apps'])){
		$apps = $_POST['apps'];

		foreach($apps as $index => $app){
			if($app['Kill']){
				if($app['Picture_old'])	@unlink($nc_core->DOCUMENT_ROOT.$app['Picture_old']);
				unset($apps[$index]);
				continue;
			}
			if(isset($_FILES['apps']['error'][$index]['Picture']) && !$_FILES['apps']['error'][$index]['Picture']){
				if($app['Picture_old'])	@unlink($nc_core->DOCUMENT_ROOT.$app['Picture_old']);
				$filename = $nc_core->HTTP_FILES_PATH.$sub.'/'.$cc.'/'.translit($app['Name']).'.jpg';
				$fi = 0;
				while(file_exists($nc_core->DOCUMENT_ROOT.$filename)){
					$fi++;
					$filename = $nc_core->HTTP_FILES_PATH.$sub.'/'.$cc.'/'.translit($app['Name']).'_'.$fi.'.jpg';
				}
				nc_ImageTransform::imgResize($_FILES['apps']['tmp_name'][$index]['Picture'], $nc_core->DOCUMENT_ROOT.$filename, 100, 100, 1, 'jpeg');
				$apps[$index]['Picture'] = $filename;
			} else if($app['Picture_old']){
				$apps[$index]['Picture'] = $app['Picture_old'];
			}
		}

		return addslashes(json_encode($apps));
	}
	return NULL;
}

function quickSubscribe($email, $name = '', $memorials = false){
	try {
		$subscriber = nc_subscriber::get_object();
		$subscriber->subscription_add(1, 0, 0, false, array(
			"Email" => $email,
			'FIO' => $name
		));
		if($memorials){
			update_row("User", array(
				"Memorial_Subscriber" => 1
			), "Email='" . mysql_real_escape_string($email) . "'");
		}
	}catch(\Exception $e){
		dump($e->GetMessage());
	}
}

function processTextColumns($text){
	$columns = explode('<div style="page-break-after: always"><span style="display: none;">&nbsp;</span></div>', $text);
	$n = count($columns);
	if($n>1){
		if($n>4) $n=4;
		$result = '<div class="text__columns clearfix">';
		foreach ($columns as $column){
			$result .= '<div class="text__column text__column_'.$n.'">'.$column.'</div>';
		}
		$result .= '</div>';
		return $result;
	} else return $text;
}

function saveAnalogs(){
	global $message, $db;
	$db->query("DELETE FROM Stone_Analogs WHERE Stone1_ID={$message} OR Stone2_ID={$message}");
	if(is_array($_POST['analogs'])){
		foreach($_POST['analogs'] as $a){
			insert_row("Stone_Analogs", array(
				"Stone1_ID" => $message,
				"Stone2_ID" => $a
			));
			insert_row("Stone_Analogs", array(
				"Stone2_ID" => $message,
				"Stone1_ID" => $a
			));
		}
	}
}

function imageWatermarked($f_Article){
	global $nc_core, $classID, $message;
	require_once $nc_core->INCLUDE_FOLDER.'classes/nc_imagetransform.class.php';
	nc_ImageTransform::createThumb("Picture", "Watermarked", 500, 345, 1, NULL, 100);

	$src_file = $nc_core->DOCUMENT_ROOT.nc_file_path($classID, $message, "Watermarked");
	$src = imagecreatefromstring(file_get_contents($src_file));
	$dst = imagecreatetruecolor(500, 345);
	imagealphablending($dst, true);
	$white = imagecolorallocatealpha($dst, 255, 255, 255, 40);
	$black = imagecolorallocatealpha($dst, 0, 0, 0, 0);

	imagecopyresampled($dst, $src, 0, 0, 0, 0, 500, 345, 500, 345);
	$size = imagettfbbox(36, 0, $nc_core->DOCUMENT_ROOT.'/x/OpenSans-Semibold.ttf', $f_Article);
	imagefilledrectangle($dst, 15, 15, 15+$size[2] + 10, 15-$size[7] + 10, $white);
	imagettftext($dst, 36, 0, 20, 20-$size[7], $black, $nc_core->DOCUMENT_ROOT.'/x/OpenSans-Semibold.ttf', $f_Article);

	$wm = imagecreatefrompng($nc_core->DOCUMENT_ROOT.'/assets/images/watermark.png');
	imagecopyresampled($dst, $wm, 0, 0, 0, 0, 500, 345, 500, 345);

	imagejpeg($dst, $src_file);
}

function imageResize( $field, $width, $height, $mode=0, $sys_table=NULL ){
	global $nc_core, $classID, $message, $db;
	$field_id = $db->get_var( "SELECT Field_ID FROM Field WHERE Class_ID='{$classID}' AND Field_Name='".mysql_real_escape_string( $field )."'");
	if( $_FILES['f_'.$field] && !$_FILES['f_'.$field]['error'] ){
		require_once $nc_core->INCLUDE_FOLDER.'classes/nc_imagetransform.class.php';
		$pic = $nc_core->DOCUMENT_ROOT.nc_file_path( $sys_table ? $sys_table : $classID, $message, $field );
		$im = getimagesize( $pic );
		if($im[0]<=$width && $im[1]<=$height && !$mode || $im[0]==$width && $im[1]==$height && $mode) return;
		nc_ImageTransform::imgResize( $pic, $pic, $width, $height, $mode, NULL, 90, $message, $field_id );
	}
}

function imageThumb( $src_field, $dst_field, $width, $height, $mode=0 ){
	global $nc_core;
	if( $_FILES['f_'.$src_field] && !$_FILES['f_'.$src_field]['error'] ){
		require_once $nc_core->INCLUDE_FOLDER.'classes/nc_imagetransform.class.php';
		nc_ImageTransform::createThumb($src_field, $dst_field, $width, $height, $mode, NULL, 90);
	}
}

function inflect( $text ){
		$text = trim( $text );
		$inflectxml = file_get_contents( "http://export.yandex.ru/inflect.xml?name=".urlencode( $text ) );
		$inflects = array();
		if( preg_match_all( '%<inflection case="(\d)">(.*?)</inflection>%ims', $inflectxml, $m ) ){
			for( $i=0; $i<count($m[0]); $i++ ){
				$inflects[$m[1][$i]] = trim( $m[2][$i] );
			}
		}
		return $inflects;
}

function formatPrice( $price, $separator=' ' ){
	return preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', $separator, $price );
}

function humanDate( $dateField, $showTime = false, $timeSeparator = ", " ){
	global $ru_monthes;
	if( preg_match( '%^(\d{4})-(\d\d)-(\d\d)(\s+(\d\d):(\d\d):(\d\d))?$%ims', trim( $dateField ), $m ) ){
		//проверяем, получили ли мы действительно неткатовское значение даты
		$year = $m[1];
		$month = $m[2];
		$day = $m[3];
		if( !trim($m[4]) ) $showTime = false;
		$hours = $m[5];
		$minutes = $m[6];
		$seconds = $m[7];
	} else return $dateField;
	if( date("Ymd")==$year.$month.$day ) $dateString = "сегодня";
	else if( date("Ymd", time()-86400)==$year.$month.$day ) $dateString = "вчера";
	else $dateString = $day." ".$ru_monthes[$month]." ".$year."г.";
	if( $showTime ) $dateString .= $timeSeparator."{$hours}:{$minutes}";
	return $dateString;
}

function firstSentence( $text, $maxlen = 100, $tobecon = '...' ){
	$text = trim( preg_replace( '/\s+/ims', ' ', $text ) );
	if( preg_match( '/^(.{1,'.$maxlen.'}\.)\s+[“”"«&А-ЯA-Z].*?$/msu', $text, $m ) ) $result = trim( $m[1] );
	else if( preg_match( '/^(.{1,'.$maxlen.'})(\s.*?)?$/imsu', $text, $m ) ){
		$result = trim( $m[1] );
		if( strlen( $text ) > strlen( $result ) ) $result .= $tobecon;
	}
	else $result = NULL;
	return $result;
}

define( _ANTICAPTCHA_NOTVALID, '<p>Система антиспама заподозрила спам в Вашем сообщении, если это не так, то просто нажмите еще раз кнопку &laquo;Отправить&raquo;</p>' );
function anticaptcha( $uri, $check = false, $ac_id = "ac" ){
	//session_start();
	if( $check ){
		$result = $_SESSION['anticaptcha'][$uri] && $_POST['ac']==$_SESSION['anticaptcha'][$uri];
		$_SESSION['anticaptcha'][$uri] = NULL;
		return $result;
	} else {
		$_SESSION['anticaptcha'][$uri] = md5( time() );
		if( $_POST['a'] ){
			ob_end_clean();
			echo preg_replace('/(.)(.)/sim', '$2$1', $_SESSION['anticaptcha'][$uri] );
			exit;
		} 
		return '<input type="hidden" name="ac" id="'.$ac_id.'" value="0"><script type="text/javascript">$.post("'.$uri.'",{"a":1},function(d){$("#'.$ac_id.'").val(d.replace(/(.)(.)/img, "$2$1"));});</script>';
	}
}

function update_row( $table, $row, $where ){
	global $db;
	if( !$row || !is_array( $row ) || !count( $row ) ) return NULL;
	$query = "UPDATE `{$table}` SET ";
	$comma = false;
	foreach( $row as $k=>$v ){
		if( $comma ) $query .= ",";
		$comma = true;
		if( $v===NULL ) $query .= "`{$k}`=NULL";
		else $query .= "`{$k}`='".mysql_real_escape_string( $v )."'";
	}
	$query .= " WHERE {$where}";
	$db->query( $query );
	return $db->insert_id;
}

function insert_row( $table, $row ){
	global $db;
	$db->insert_id = NULL;
	$query = "INSERT INTO `{$table}` SET ";
	$comma = false;
	foreach( $row as $k=>$v ){
		if( $comma ) $query .= ",";
		$comma = true;
		if( $v===NULL ) $query .= "`{$k}`=NULL";
		else $query .= "`{$k}`='".mysql_real_escape_string( $v )."'";
	}
	$db->query( $query );
	return $db->insert_id;
}

function translit( $string, $url = true ) {
	$russians = array("а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я","А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я");
	$latinians = array("a","b","v","g","d","e","jo","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f","kh","ts","ch","sh","sch","","y","","je","ju","ja","a","b","v","g","d","e","jo","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f","kh","ts","ch","sh","sch","","y","","je","ju","ja");
	$translited = str_replace( $russians, $latinians, strtolower( trim( $string ) ) );
	if( $url ) $translited = preg_replace('#[^\d\w]+#i', '-', $translited);
	return $translited;
}

function maildump($var){
	mail("pavel.v.zotov@gmail.com", "maildump", print_r($var, true), "Content-type: text/plain; charset=utf-8\nFrom: domkam@zotov.info");
}

function subdivisionChecks($selected, $parent_id=0){
	global $db;
	$result = '';
	if(!$parent_id){
		$result .= '
		<style>
			.sub-checks {
				max-height: 300px;
				overflow-x: hidden;
				overflow-y: scroll;
				bordeR: 1px solid #eee;
			}
			.sub-checks__items {
				list-style: outside none;
				padding: 5px 0;
			}
			.sub-checks__item {
				display: block;
				list-style: outside none;
				padding-left: 1em;
			}
		</style>
		<div class="sub-checks">
			<ul class="sub-checks__items"><li class="sub-checks__item"><label><input type="checkbox" class="sub-checks__checkbox" onclick="$(\'.sub-checks__checkbox\').removeAttr(\'checked\').removeProp(\'checked\');"> Все разделы</label></li></ul>
		';
	}
	if($subs = $db->get_results("SELECT Subdivision_ID,Subdivision_Name FROM Subdivision WHERE Parent_Sub_ID={$parent_id} ORDER BY Priority, Subdivision_ID", ARRAY_A)){
		$result .= '<ul class="sub-checks__items">';
		foreach($subs as $s){
			$result .= '<li class="sub-checks__item"><label class="sub-checks__label"><input type="checkbox" class="sub-checks__checkbox" name="sub_checks[]" value="'.$s['Subdivision_ID'].'"'.(in_array($s['Subdivision_ID'], $selected) ? ' checked' : '').'> '.$s['Subdivision_Name'].'</label>';
			$result .= subdivisionChecks($selected, $s['Subdivision_ID']);
			$result .= '</li>';
		}
		$result .= '</ul>';
	}
	if(!$parent_id){
		$result .= '
		</div>
		';
	}
	return $result;
}


class EventListener {
	public function __construct () {
		$nc_core = nc_Core::get_object();
		$nc_core->event->bind($this, array('addSubClass' => 'updateSubClass') );
		$nc_core->event->bind($this, array('updateSubClass' => 'updateSubClass') );
		$nc_core->event->bind($this, array('addSubdivision' => 'updateSub') );
		$nc_core->event->bind($this, array('updateSubdivision' => 'updateSub') );
	}

	public function updateSub($catalogue, $sub){
		imageResize("img", 170, 170, 1, "Subdivision");
	}

	public function updateSubClass($catalogue, $sub, $cc){
		global $db;

		list($classID, $cc_settings) = $db->get_row("SELECT Class_ID,CustomSettings FROM Sub_Class WHERE Sub_Class_ID=".$cc, ARRAY_N);
		if($classID==2006){
			eval($cc_settings);
//			maildump(array($cc_settings, $CustomSettings));
			//склоняем название камня
			if($CustomSettings['Name']){
				$inflects = inflect(mb_strtolower($CustomSettings['Name']));
				foreach($inflects as $k=>$v){
					if($k>1) $CustomSettings['Name'.$k] = $v;
				}

				$cc_settings = "\$CustomSettings = array(";
				$comma = false;
				foreach($CustomSettings as $k=>$v){
					if($comma) $cc_settings .= ',';
					$comma = true;
					$cc_settings .= "'{$k}' => '".addslashes($v)."'";
				}
				$cc_settings .= ');';
				update_row("Sub_Class", array(
					'CustomSettings' => $cc_settings
				), "Sub_Class_ID=".$cc);
			}
		}
	}
}

$listener = new  EventListener();

/*
	Class for working with ipgeobase.ru geo database.

	Copyright (C) 2013, Vladislav Ross

	This library is free software; you can redistribute it and/or
	modify it under the terms of the GNU Lesser General Public
	License as published by the Free Software Foundation; either
	version 2.1 of the License, or (at your option) any later version.

	This library is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public
	License along with this library; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA

    E-mail: vladislav.ross@gmail.com
	URL: https://github.com/rossvs/ipgeobase.php

*/
/*
 * @class IPGeoBase
 * @brief Класс для работы с текстовыми базами ipgeobase.ru
 * @see example.php
 *
 * Определяет страну, регион и город по IP для России и Украины
 */

class IPGeoBase {
	private $fhandleCIDR, $fhandleCities, $fSizeCIDR, $fsizeCities;

	/*
	 * @brief Конструктор
	 *
	 * @param CIDRFile файл базы диапазонов IP (cidr_optim.txt)
	 * @param CitiesFile файл базы городов (cities.txt)
	 */
	function __construct($CIDRFile = false, $CitiesFile = false){
		global $nc_core;
		if(!$CIDRFile){
			$CIDRFile = $nc_core->DOCUMENT_ROOT . '/x/geo_files/cidr_optim.txt';
		}
		if(!$CitiesFile){
			$CitiesFile = $nc_core->DOCUMENT_ROOT . '/x/geo_files/cities.txt';
		}
		$this->fhandleCIDR = fopen($CIDRFile, 'r') or die("Cannot open $CIDRFile");
		$this->fhandleCities = fopen($CitiesFile, 'r') or die("Cannot open $CitiesFile");
		$this->fSizeCIDR = filesize($CIDRFile);
		$this->fsizeCities = filesize($CitiesFile);
	}

	/*
	 * @brief Получение информации о городе по индексу
	 * @param idx индекс города
	 * @return массив или false, если не найдено
	 */
	private function getCityByIdx($idx){
		rewind($this->fhandleCities);
		while (!feof($this->fhandleCities)) {
			$str = fgets($this->fhandleCities);
			$arRecord = explode("\t", trim($str));
			if($arRecord[0] == $idx){
				return array('city' => $arRecord[1],
					'region' => $arRecord[2],
					'district' => $arRecord[3],
					'lat' => $arRecord[4],
					'lng' => $arRecord[5]);
			}
		}
		return false;
	}

	/*
	 * @brief Получение гео-информации по IP
	 * @param ip IPv4-адрес
	 * @return массив или false, если не найдено
	 */
	function getRecord($ip){
		$ip = sprintf('%u', ip2long($ip));

		rewind($this->fhandleCIDR);
		$rad = floor($this->fSizeCIDR / 2);
		$pos = $rad;
		while (fseek($this->fhandleCIDR, $pos, SEEK_SET) != -1) {
			if($rad){
				$str = fgets($this->fhandleCIDR);
			} else {
				rewind($this->fhandleCIDR);
			}

			$str = fgets($this->fhandleCIDR);

			if(!$str){
				return false;
			}

			$arRecord = explode("\t", trim($str));

			$rad = floor($rad / 2);
			if(!$rad && ($ip < $arRecord[0] || $ip > $arRecord[1])){
				return false;
			}

			if($ip < $arRecord[0]){
				$pos -= $rad;
			} elseif($ip > $arRecord[1]) {
				$pos += $rad;
			} else {
				$result = array('range' => $arRecord[2], 'cc' => $arRecord[3]);

				if($arRecord[4] != '-' && $cityResult = $this->getCityByIdx($arRecord[4])){
					$result += $cityResult;
				}

				return $result;
			}
		}
		return false;
	}
}
