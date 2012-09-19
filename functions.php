<?
/**
 * Проверяем ревизию и вызываем бибикало, если что
 * @param string $projectName название проекта
 */
function checkSVNCommit($projectName) {
	require 'config.php'; // Конфиг у нас тут подключается локально

  $old_rev = @file_get_contents('revisions/'.$projectName.'.txt');
	$rev = 0;
	$user = 'unknown';

	// Готовим CURL
	$s = curl_init();
	curl_setopt($s, CURLOPT_URL, $svnURL.'/listing.php?repname='.urlencode($projectName).'&path=%2F&sc=0'); 
	curl_setopt($s, CURLOPT_USERPWD, $authLogin.':'.$authPass);
	curl_setopt($s, CURLOPT_USERAGENT, 'CommitBeep 1.0');
	curl_setopt($s, CURLOPT_REFERER, $svnURL);
	curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

	// Получаем список проектов
	$page = curl_exec($s);
	$httpCode = curl_getinfo($s, CURLINFO_HTTP_CODE);
	curl_close($s);

	// Весьма топорная реализация, но зато наглядно и быстро
	$page = explode("\n", strip_tags($page));
	foreach($page as $line) {
		if (strpos($line, 'Last modification:') !== false) {
			$line = explode(' ', $line);
			$rev = $line[3];
			$user = $line[5];
		}
	}

	echo "$projectName $rev $user<br/>\n";

	// Ревизия поменялась? Бибикаем!
	if ($rev != $old_rev) {
		beep($comNumber, getMelodyByUser($user));
		file_put_contents('revisions/'.$projectName.'.txt', $rev);
	}
}

/**
 * Парсит DOM узел в массив
 * @param object $node узел
 */
function dom2array($node) {
  $res = array();
  if($node->nodeType == XML_TEXT_NODE){
      $res = $node->nodeValue;
  }
  else{
      if($node->hasAttributes()){
          $attributes = $node->attributes;
          if(!is_null($attributes)){
              $res['@attributes'] = array();
              foreach ($attributes as $index=>$attr) {
                  $res['@attributes'][$attr->name] = $attr->value;
              }
          }
      }
      if($node->hasChildNodes()){
          $children = $node->childNodes;
          for($i=0;$i<$children->length;$i++){
              $child = $children->item($i);
              $res[$child->nodeName] = dom2array($child);
          }
      }
  }
  return $res;
}

/**
 * Номер мелодии для юзера
 * @param string $username имя юзера
 * @return integer $melody номер мелодии
 */
function getMelodyByUser($username) {
	switch ($username) {
		case 'user1': $num = 2; break;
		case 'user2': $num = 3; break;
		case 'user3': $num = 4; break;
		case 'user4': $num = 5; break;
		case 'user5': $num = 6; break;
		case 'user6': $num = 7; break;
		case 'user7': $num = 8; break;
		case 'user8': $num = 9; break;
		default: $num = 1;
	}

	return $num;
}

/**
 * Посылает сигнал бибикалу
 * @param integer $com номер COM-порта
 * @param integer $melody номер мелодии
 */
function beep($com = 6, $melody = 1) {
	// Чтобы всё заработало, надо php библиотечку php_ser
	// http://www.thebyteworks.com/
	// Для Линуксов всё проще, действуем через fopen()
	ser_open("COM".$com, 9600, 8, "None", "1", "None");
	ser_write("$melody");
	ser_close();
}
