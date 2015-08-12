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

function __log($text){
	global $__log, $nc_core;
	if(!$__log) $__log = fopen($nc_core->DOCUMENT_ROOT.'/x/log.txt', "a");
	if($__log) fwrite($__log, date("[Y-m-d H:i:s] ").$text."\n");
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

function quickSubscribe($email){

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
	global $nc_core, $classID, $message, $db;
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
	else $dateString = $day." ".$ru_monthes[$month]." ".$year."";
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
