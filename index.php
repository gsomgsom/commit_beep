<?
// Забъём на время выполнения
set_time_limit(0);

// Подключим всякие функции и конфиги
require 'functions.php';
require 'config.php';

// Готовим CURL
$s = curl_init();
curl_setopt($s, CURLOPT_URL, $svnURL); 
curl_setopt($s, CURLOPT_USERPWD, $authLogin.':'.$authPass);
curl_setopt($s, CURLOPT_USERAGENT, 'CommitBeep 1.0');
curl_setopt($s, CURLOPT_REFERER, $svnURL);
curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

// Получаем список проектов
$page = curl_exec($s);
$httpCode = curl_getinfo($s, CURLINFO_HTTP_CODE);
curl_close($s);

// Получим ссылки на проекты
$doc = new DOMDocument();
$doc->loadHTML($page);
$links = $doc->getElementsByTagName('a');
foreach ($links as $link) {
	$link = dom2array($link);
	if (strpos($link['@attributes']['href'], 'listing.php?repname') !== false) {
		$projectName = $link['#text'];
		checkSVNCommit($projectName);
	}
}
