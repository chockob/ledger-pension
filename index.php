<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL & ~E_NOTICE);

$logs = array();
$time = time();

echo "<html>".PHP_EOL;
echo "<head>

<script src=\"./jquery-3.6.0.min.js\"></script>
<script>
$(document).ready(function(){
	console.log('id=');
    $('.btn_tx').click(function() {
		var s = $(this).attr('id');
		console.log('id=' + s);
		$('#tx_' + s).toggle();
    });
});
</script>
";

echo '
<style>

textarea {
  width: 100%;
  height: 150px;
  padding: 5px;
  box-sizing: border-box;
  border: 2px solid #ccc;

  
  font-size: 12px;
  resize: none;
}

p {
	font: normal 10pt/12pt Ubuntu Condensed;
}

table { 
    border-spacing: 0px;
    border-collapse: separate;
    border:1px solid black;
    font: normal 8pt/10pt Ubuntu Condensed;
    /*width: 100%;*/
}
table caption {
    font: bold 8pt/10pt Ubuntu Condensed;
    background-color : #000;
    
    color : #fff;
    
    padding: 1px 5px;
    text-align:left;
}

table tr th {
    font: bold 8pt/10pt Ubuntu Condensed;
    background-color : #D3D3D3;
    
    /*
    color : #fff;
    */
    padding: 1px 2px;
    vertical-align:top;
}

table tr th.cool {
	border-top: 1px solid black;
	border-left: 1px solid black;
	/*border-right: 1px solid black;
	*/
}

table tr td {
    padding: 1px 2px;
    vertical-align:top;
}

table tr td.number {
	text-align:right
}

table tr th.number {
	text-align:right
}

tr:nth-child(even){
    background-color: #f5deb3	
}

pre {
    white-space: pre-wrap;       /* Since CSS 2.1 */
    white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
    white-space: -pre-wrap;      /* Opera 4-6 */
    white-space: -o-pre-wrap;    /* Opera 7 */
    word-wrap: break-word;       /* Internet Explorer 5.5+ */
}

.color1 { 
	background: #99ff99; color: #000;
}

.color2 { 
	background: #ffff90; color: #000;
}

.color3 { 
	background: #ff9999; color: #000;
}



</style>'.PHP_EOL;
echo "</head>".PHP_EOL;
echo "<body>".PHP_EOL;

//~ ---------------- 
//~ Получить наименование эмитента по из кода облигации

function get_emitter($emitter_id) {
	
	//~ $emitter_id = '16643-A';
		
	$source_file = 'emitter/'.$emitter_id.'.json';	
	
	$html = '';
	if (file_exists($source_file)  ) {
		
		$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
		$dateEnd = date_create(date('d.m.Y',time()));			
		$dateEnd->setTime(24,0,0);

		$diff = date_diff($dateStart,$dateEnd);
		if ( $diff->format("%a") > 10 ) {
			$html = file_get_contents('https://www.cbr.ru/registries/rcb/ecb/?UniDbQuery.Posted=True&UniDbQuery.SPhrase='.$emitter_id.'&UniDbQuery.SearchType=4');	
			//~ file_put_contents($source_file, $html);
		}
		else
			$result = file_get_contents($source_file);	
		
		
	}	
	else {				
		$html = file_get_contents('https://www.cbr.ru/registries/rcb/ecb/?UniDbQuery.Posted=True&UniDbQuery.SPhrase='.$emitter_id.'&UniDbQuery.SearchType=4');	

		//~ file_put_contents($source_file, $html);
	}
	
	//~ echo '==='.$html;
	
	if (!empty($html)) {
		
		$doc = new DOMDocument();
		$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);

		$elements = $doc->getElementsByTagName('table');
		if (!is_null($elements)) {
			//~ foreach ($elements as $element) {

				//~ $nodes = $element->childNodes;
				//~ foreach ($nodes as $node) {
				  //~ echo $node->nodeValue. "</br>";
				//~ }
			//~ }
		  
			$td = $elements[0]->getElementsByTagName('td');
			
			
		   
			//~ echo $td[2]->nodeValue;  
			//~ echo $td[3]->nodeValue;  
			//~ file_put_contents($source_file, $td[2]->nodeValue);  
			//~ $result = $td[2]->nodeValue;	
			
			$patterns = array('/Общество с ограниченной ответственностью/', "/Публичное акционерное общество/", "/Акционерное общество/", "/Государственное унитарное предприятие/", '/ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО/', '/Открытое акционерное общество/');
			$replacements = array('ООО', "ПАО", "АО", "ГУП", "ПАО", "ОАО");
			
			$name = preg_replace($patterns, $replacements, $td[2]->nodeValue);
			
			file_put_contents($source_file, $name);  
			$result = $name;	
			
			
		}
	}
	else
		$result = file_get_contents($source_file);	
		
	return $result;


}
   


//~ echo get_emitter('16643-A');

//~ exit;
 
 
 

$ver = SQLite3::version();

$db = new SQLite3('/home/chockob/Documents/ledger/ledger-home.sqlite.gnucash', SQLITE3_OPEN_READONLY); //, SQLITE3_OPEN_READWRITE);

//$db_portfolio = new SQLite3('/var/www/html/investment/portfolio.sqlite', SQLITE3_OPEN_READWRITE);





    

/*
 *
 * СО СПИСКОМ ТРАНЗАКЦИИ

SELECT 
transactions.post_date,
accounts.*,
splits.*
--SUM(splits.quantity_num / splits.quantity_denom) AS res_quantity_denom, 
--SUM(splits.value_num / splits.value_denom) AS res_value_num 
 FROM accounts 
 LEFT JOIN splits 
 LEFT JOIN transactions 
	WHERE (
		accounts.parent_guid="59a27d1443e1446eaabf84150d88aa39"
		 AND splits.account_guid=accounts.guid
		AND transactions.guid = splits.tx_guid
		--AND accounts.name = "RU000A102LD1"
	)	
--GROUP BY name 
ORDER BY accounts.name DESC

*/





//exec("/usr/bin/perl /var/www/html/investment/moex.pl moex_bond_tplus SU26218RMFS6",$output);


/*
CREATE TABLE "securities_price_db" (
    "price_db_namespace"	TEXT NOT NULL,
    "price_db_symbol"	TEXT NOT NULL,
    "price_db_currency"	TEXT NOT NULL,
    "price_db_date"	TEXT NOT NULL,
    "price_db_price"	REAL NOT NULL
    )

    
    */


    
//----------------------------------------------------------------------    
function get_moex_price ($f_symbol, $moex_method) {    
	//$f_symbol = 'SBERP';
	//'moex_bond_tplus'
	//'moex_stock'
	
	$db_portfolio = new SQLite3('/var/www/html/investment/portfolio.sqlite', SQLITE3_OPEN_READWRITE);

	echo "<p>$f_symbol </p>";


	$date_stock = date('Y-m-d');

	echo "<p>date=$date_stock</p>";
	$dayofweek = date('w', strtotime($date_stock));



	if ($dayofweek == 1)
		$date_stock = date('Y-m-d', strtotime("-3 days") );
	elseif ($dayofweek == 0)
		$date_stock = date('Y-m-d', strtotime("-2 days") );
	else
		$date_stock = date('Y-m-d', strtotime("-1 days") );




	$dayofweek = date('w', strtotime($date_stock));


	echo "<p>dayofweek=$dayofweek</p>";
	echo "<p>date_stock=$date_stock</p>";
	//0 вс
	//1 пн
	//2 вт
	//3 ср
	//4 чт
	//5 пт
	//6 сб


	$sql_portfolio = 'SELECT * FROM securities_price_db WHERE price_db_symbol = "'.$f_symbol.'" ORDER BY price_db_date DESC LIMIT 1;';


	//$sql_portfolio = 'SELECT * FROM securities_price_db WHERE price_db_date = "'.$date_stock.'" AND price_db_symbol = "'.$f_symbol.'"';

	$results_portfolio = $db_portfolio->query($sql_portfolio);
	echo "<p>$sql_portfolio</p>";

	$result = '';
	if ($row_portfolio = $results_portfolio->fetchArray()) {
		$result = $row_portfolio['price_db_price'];
		echo "<p>price_db_price=".$row_portfolio['price_db_price']."</p>";
	}
	else {
		//https://iss.moex.com/iss/engines/stock/markets/bonds/boardgroups/58/securities.json
		//exec("/usr/bin/perl /var/www/html/investment/moex.pl $moex_method $f_symbol",$output);
		//exec("/usr/bin/perl /var/www/html/investment/moex.pl moex_stock $f_symbol",$output);
	//    echo print_r ($output);
		
		$res = explode (';', $output[0] );
		
		
		$sql_insert = "INSERT INTO securities_price_db (price_db_namespace,price_db_symbol,price_db_currency,price_db_date,price_db_price) 
		VALUES ('MICEX', '$f_symbol', '$res[2]', '$res[0]', '$res[1]');";
		echo "<p>$sql_insert</p>";
		//$db_portfolio->query($sql_insert);
		$result = $res[1];
		
		
		
	//    echo $res[0]."\n</br>";
	//    echo $res[1]."\n</br>";
    
		
	}
	return $result;
}
//----------------------------------------------------------------------
function get_moex_bond_price () {    



	$db_portfolio = new SQLite3('/var/www/html/investment/portfolio.sqlite', SQLITE3_OPEN_READWRITE);

	$results_portfolio = $db_portfolio->query('SELECT SECID FROM securities_bonds;');
	$row_secid = array();
	while ($row = $results_portfolio->fetchArray()) {
		$row_secid[] = $row['SECID'];
	}
	//echo var_export($row_secid);

	
	$people_json = file_get_contents('securities.json');
	//$people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/bonds/boardgroups/58/securities.json');
	 
	$decoded_json = json_decode($people_json, true);
	
	echo '<pre>';
	
	$res = array();
	
	//echo var_export($decoded_json['securities']['data']);
	
	$tt=array();
	foreach($decoded_json['securities']['data'] as $key_ar1=>$val_ar1) {
		
			//if (in_array( trim($val_ar1[0]) , array('RU000A1009A1','RU000A1009Z8','RU000A100VY0'))) {
			if (in_array( trim($val_ar1[0]) , $row_secid)) {
			echo "~~~\t".$key_ar1."\t"
			.trim($val_ar1[0])."\t"
			.trim($val_ar1[10])."\t" //FACEVALUE
			.trim($val_ar1[3])."\t" //PREVWAPRICE
			.trim($val_ar1[7])."\t" //ACCRUEDINT
			.trim($val_ar1[6])."\t" //NEXTCOUPON
			.trim($val_ar1[5])."\t" //COUPONVALUE
			.trim($val_ar1[36])."\t" //COUPONPERCENT
			.trim($val_ar1[19])."\t" //PREVDATE
			.number_format((($val_ar1[3] * $val_ar1[10] / 100) + $val_ar1[7]), 2, ',', ' ')."\t" 
			.'<br>';
			//echo var_export($key_ar1).'~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~<br>';
			$sql = 'SELECT * FROM securities_bonds_price WHERE (SECID = "'.trim($val_ar1[0]).'" AND PREVDATE = "'.trim($val_ar1[19]).'");';
			//echo $sql;
			$results_portfolio = $db_portfolio->query($sql);
			
			if ($row_portfolio = $results_portfolio->fetchArray()) {		
				$portfolio = $row_portfolio['SECID'];
				
			} else {
				$insert_portfolio = 'INSERT INTO securities_bonds_price 
				("SECID","PREVDATE","FACEVALUE","PREVWAPRICE","ACCRUEDINT","NEXTCOUPON","COUPONVALUE","COUPONPERCENT") 
				VALUES (
				"'.trim($val_ar1[0]).'",
				"'.trim($val_ar1[19]).'",
				"'.trim($val_ar1[10]).'",
				"'.trim($val_ar1[3]).'",
				"'.trim($val_ar1[7]).'",
				"'.trim($val_ar1[6]).'",
				"'.trim($val_ar1[5]).'",
				"'.trim($val_ar1[36]).'");';

				//echo "<br>".$insert_portfolio;
				$db_portfolio->query($insert_portfolio);
			}
	
				
			}
	
	
		
			
		
		
	}
	//var_export($res);
	//var_export($tt);
	//$res .= var_export($decoded_json['securities']['data'][0]);
	echo '</pre>'; 
	//echo $decoded_json['data'];
	// Monty
	 
	//echo $decoded_json['email'];
	// monty@something.com
	 
	//echo $decoded_json['age'];
	// 77
	$db_portfolio->close();
	return $res;
}

// ИНФОРМАЦИЯ ПО ОБЛИГАЦИИ
// https://iss.moex.com/iss/securities/RU000A104UA4.jsonp?shortname=1&iss.only=description&iss.meta=off&iss.json=extended&lang=ru

function get_moex_bond_json($bond_secid) {    		
	//$bond_secid = 'RU000A104UA4';
	
	$source_file = 'bond/'.$bond_secid.'.json';	
	
	$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
	$dateEnd = date_create(date('d.m.Y',time()));
			
	$dateEnd->setTime(24,0,0);

	$diff = date_diff($dateStart,$dateEnd);
	
	
	if (file_exists($source_file) && $diff->format("%a") <= 1 ) {
		$people_json = file_get_contents($source_file);	
		//echo "file<br>";
	}
	else {				
		$BOARDID = 'TQCB';
		if (preg_match("/SU[a-zA-Z0-9]{10}/", $bond_secid) )
			$BOARDID = 'TQOB';
		
			
		
		$people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/bonds/boards/'.$BOARDID.'/securities/'.$bond_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');	
		//$people_json = file_get_contents('https://iss.moex.com/iss/securities/'.$bond_secid.'.jsonp?shortname=1&iss.only=description&iss.meta=off&iss.json=extended&lang=ru');	
		file_put_contents($source_file, $people_json);
	}
	$decoded_json = json_decode($people_json, true);		
	//print_r($decoded_json);
	$res = array();	
	
	$res['NAME'] 				= $decoded_json['securities']['data'][0][20];  //<th>Полное наименование</th>	
	$res['ACCRUEDINT'] 			= $decoded_json['securities']['data'][0][7];  //<th>НКД на дату расчетов</th>
	$res['MATDATE'] 			= $decoded_json['securities']['data'][0][13];  //<th>Дата погашения</th>
	$res['PREVLEGALCLOSEPRICE'] 			= $decoded_json['securities']['data'][0][3];  //<th>Цена пред. дня, % к номиналу</th>
	$res['STATUS'] 			= $decoded_json['securities']['data'][0][12];  //<th>Статус</th>	
	$res['LISTLEVEL'] 			= $decoded_json['securities']['data'][0][34];  //<th>Уровень листинга</th>	
	$res['FACEVALUE'] 			= $decoded_json['securities']['data'][0][10];   //<th>Номинальная стоимость</th>
	$res['COUPONPERIOD'] 	= $decoded_json['securities']['data'][0][15];  //<th>Перио-дичность выплаты купона в год</th>
	$res['COUPONPERCENT'] 		= $decoded_json['securities']['data'][0][36];   //<th>Ставка купона, %</th>	
	$res['COUPONVALUE'] 		= $decoded_json['securities']['data'][0][5];   //<th>Размер купона</th>	
	
	
	
	//~ 4B02-04-87154-H-002P
	
	$matches = null;
	$returnValue = preg_match('/^[A-Z0-9]*-[A-Z0-9]*-([A-Z0-9]*-[A-Z0-9]*)/', $decoded_json['securities']['data'][0][31], $matches);
	
	$res['REGNUMBER'] 		= $matches[1];   //<th>Размер купона</th>	
	
	//~ $res['REGNUMBER'] 		= $decoded_json['securities']['data'][0][31];   //<th>Размер купона</th>	
	
	
	
	
	return $res;
}


// ИНФОРМАЦИЯ ПО АКЦИИ
// https://iss.moex.com/iss/engines/stock/markets/shares/boards/TQBR/securities/MTSS.jsonp?iss.meta=off&iss.only=securities&lang=ru

//{
//"securities": {
	//"columns": [
	//"SECID", "BOARDID", "SHORTNAME", "PREVPRICE", "LOTSIZE", "FACEVALUE", "STATUS", "BOARDNAME", "DECIMALS", "SECNAME", 
	//"REMARKS", "MARKETCODE", "INSTRID", "SECTORID", "MINSTEP", "PREVWAPRICE", "FACEUNIT", "PREVDATE", "ISSUESIZE", "ISIN", 
	//"LATNAME", "REGNUMBER", "PREVLEGALCLOSEPRICE", "PREVADMITTEDQUOTE", "CURRENCYID", "SECTYPE", "LISTLEVEL", "SETTLEDATE"], 
	//"data": [
		//["MTSS", "TQBR", "МТС-ао", 236.15, 10, 0.1, "A", "Т+: Акции и ДР - безадрес.", 2, "Мобильные ТелеСистемы ПАО ао", 
		//null, "FNDT", "EQIN", null, 0.05, 236.2, "SUR", "2023-01-05", 1998381575, "RU0007775219", 
		//"MTS", "1-01-04715-A", 236.4, 236.4, "SUR", "1", 1, "2023-01-10"]
	//]
//}}
//-------------------------------------------
// ПОЛУЧЕНИЕ СВЕДЕНИЙ ОБ АКЦИИ НА МОСБИРЖА
//-------------------------------------------
function get_moex_shares_json($shares_secid, $update_force = false) {  
	//$bond_secid = 'RU000A104UA4';
	
	$source_file = 'shares/'.$shares_secid.'.json';		

	if ($update_force == true) {				
		$BOARDID = 'TQBR';		
		$people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/shares/boards/'.$BOARDID.'/securities/'.$shares_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');		
		file_put_contents($source_file, $people_json);
		
	}
	
	if (file_exists($source_file)) {

		$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
		$dateEnd = date_create(date('d.m.Y',time()));
		$dateEnd->setTime(24,0,0);
		$diff = date_diff($dateStart,$dateEnd);
	
		if ($diff->format("%a") > 1 ) {
			$BOARDID = 'TQBR';		
			$people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/shares/boards/'.$BOARDID.'/securities/'.$shares_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');		
			file_put_contents($source_file, $people_json);
		}
		
	}
	else {
		$BOARDID = 'TQBR';		
		$people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/shares/boards/'.$BOARDID.'/securities/'.$shares_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');		
		file_put_contents($source_file, $people_json);
	}
		

	$people_json = file_get_contents($source_file);	
		
	$decoded_json = json_decode($people_json, true);		
	//print_r($decoded_json);
	$res = array();	
	$res['NAME'] 				= $decoded_json['securities']['data'][0][9];  //<th>Полное наименование</th>	
	$res['LOTSIZE'] 			= $decoded_json['securities']['data'][0][4];  //<th>	Количество ценных бумаг в одном стандартном лоте</th>
	$res['DECIMALS'] 			= $decoded_json['securities']['data'][0][8];  //<th>Точность, знаков после запятой</th>
	$res['PREVLEGALCLOSEPRICE'] 			= $decoded_json['securities']['data'][0][22];  //<th>Официальная цена закрытия предыдущего дня, рассчитываемая по методике ФСФР</th>
	//$res['STATUS'] 			= $decoded_json['securities']['data'][0][12];  //<th>Статус</th>	
	//$res['LISTLEVEL'] 			= $decoded_json['securities']['data'][0][34];  //<th>Уровень листинга</th>	
	//$res['FACEVALUE'] 			= $decoded_json['securities']['data'][0][10];   //<th>Номинальная стоимость</th>
	//$res['COUPONPERIOD'] 	= $decoded_json['securities']['data'][0][15];  //<th>Перио-дичность выплаты купона в год</th>
	//$res['COUPONPERCENT'] 		= $decoded_json['securities']['data'][0][36];   //<th>Ставка купона, %</th>	
	//$res['COUPONVALUE'] 		= $decoded_json['securities']['data'][0][5];   //<th>Размер купона</th>	
	return $res;
	
	
}

//------------------------------------------
// КУПОННЫЙ КАЛЕНДАРЬ
// https://iss.moex.com/iss/securities/RU000A104UA4/bondization.json?iss.json=extended&iss.meta=off&iss.only=coupons&lang=ru&limit=unlimited
// bondization.json
function get_moex_bond_bondization_json ($bond_secid) {    	
	
	//$bond_secid = 'RU000A104UA4';
	
	$bondization_file = 'bondization/'.$bond_secid.'.json';	
	if (file_exists($bondization_file)) {
		$people_json = file_get_contents($bondization_file);	
		//echo "file<br>";
	}
	else {
		$people_json = file_get_contents('https://iss.moex.com/iss/securities/'.$bond_secid.'/bondization.json?iss.json=extended&iss.meta=on&iss.only=coupons&lang=ru&limit=unlimited');	
		file_put_contents($bondization_file, $people_json);
	}
	$decoded_json = json_decode($people_json, true);	
	
	//print_r($decoded_json);
	
	$res = array();
	
	//$i=0;
	foreach($decoded_json[1]['coupons'][1] as $key=>$val) {
		//echo '<hr>';
		
		//echo 'coupondate => '.$val['coupondate'].'<br>';
		//echo 'recorddate => '.$val['recorddate'].'<br>';
		//echo 'startdate => '.$val['startdate'].'<br>';
		//echo 'value_rub => '.$val['value_rub'].'<br>';
		//echo 'valueprc => '.$val['valueprc'].'<br>';
	
		$i = date('y.n',strtotime($val['coupondate']));
		
		
		$res[$i]['coupondate'] = $val['coupondate'];
		$res[$i]['recorddate'] = $val['recorddate'];
		$res[$i]['startdate'] = $val['startdate'];
		$res[$i]['value_rub'] = $val['value_rub'];
		$res[$i]['valueprc'] = $val['valueprc'];
		
		//$i++;
	
	}
	
	return $res;
}

//------------------------------------------
// ПОЛУЧЕНО КУПОНОВ ОБЛИГАЦИИ ИЗ GNUCASH
function get_gnucash_bondization() {
	$db = new SQLite3('/home/chockob/Documents/ledger/ledger-home.sqlite.gnucash'); //, SQLITE3_OPEN_READWRITE);
	$sql = 'SELECT 
	accounts.name,
	SUM(splits.value_num * -1) AS sum_splits_value_num,
	splits.value_denom
	 FROM accounts 
	 LEFT JOIN splits 
		WHERE (
			accounts.account_type="INCOME"
			 AND parent_guid="40d63499a70445c1bdd2900e90fba7a7"
			 AND splits.account_guid=accounts.guid	
		)
	GROUP BY accounts.name 
	ORDER BY accounts.name DESC';
	$res = array();
	$results = $db->query($sql);
	while ($row = $results->fetchArray()) {
		$res[$row['name']] = $row['sum_splits_value_num'] / $row['value_denom'];
	}	
	$db->close();
	return $res;
}


//------------------------------------------
// ПОЛУЧЕНО ДИВИДЕНДОВ ИЗ GNUCASH
function get_gnucash_dividendization() {
	$db = new SQLite3('/home/chockob/Documents/ledger/ledger-home.sqlite.gnucash'); //, SQLITE3_OPEN_READWRITE);
	$sql = 'SELECT 
---transactions.post_date,
accounts.name,
accounts.account_type,
--splits.*,
SUM((splits.quantity_num * -1) / splits.quantity_denom) AS res_quantity_denom 
 FROM accounts 
 LEFT JOIN splits 
 LEFT JOIN transactions 
	WHERE (
		accounts.parent_guid="09a92a76bc444418b9ab7d0d57e6cc18"
		 AND splits.account_guid=accounts.guid
		AND transactions.guid = splits.tx_guid
---		AND accounts.name = "GAZP"
	)	
GROUP BY name 
ORDER BY accounts.name DESC
';
	$res = array();
	$results = $db->query($sql);
	while ($row = $results->fetchArray()) {
		$res[$row['name']] = $row['res_quantity_denom'];
	}	
	$db->close();
	return $res;
}

//var_export(get_gnucash_bondization());

//var_export(get_moex_bond_bondization_json('RU000A0ZYJT2'));
//exit;

/*
 ПОСЛЕДНИЙ ДЕНЬ ПОКУПКИ АКЦИИ 
 */
 
function get_gnucash_last_daybuy_shares($shares_secid) {
	$db = new SQLite3('/home/chockob/Documents/ledger/ledger-home.sqlite.gnucash'); //, SQLITE3_OPEN_READWRITE);
	$sql = 'SELECT 
accounts.name,
transactions.post_date
 FROM transactions 
 LEFT JOIN splits 
 LEFT JOIN accounts  
	WHERE (
		accounts.parent_guid="59a27d1443e1446eaabf84150d88aa39"
		AND splits.account_guid=accounts.guid
		AND transactions.guid = splits.tx_guid
		AND splits.action = "Покупка"
		AND accounts.name = "'.$shares_secid.'"		
	)	

ORDER BY transactions.post_date DESC
';

	$res = '';
	$results = $db->query($sql);
	if ($row = $results->fetchArray()) {
		$res = $row['post_date'];
	}	
	$db->close();
	return $res;
}



/*-------------------------------------*/
if ($_GET['do'] == 'update_share') {
	
	$share = $_GET['share'];
	
	get_moex_shares_json($share, true);
	
}



/*-------------------------------------*/

/*-------------------------------------
АКЦИИ
-------------------------------------*/

$sql_total_investment= 
"SELECT 
sum(splits.value_num / splits.value_denom) AS res_value_num , 
splits.tx_guid

 FROM accounts 
 
 LEFT JOIN splits 
	WHERE (
		parent_guid='59a27d1443e1446eaabf84150d88aa39'
		 AND splits.account_guid=accounts.guid
		 AND (splits.action='Покупка' OR splits.action='Продажа') 		 
	)";
$total_investment = 0;
$res_total_investment = $db->query($sql_total_investment);
if ($row_total_investment = $res_total_investment->fetchArray()) {
	$total_investment = $row_total_investment['res_value_num'];
}
//~ ---------------------
//~ подсчет Значения итого
$sql_q = '
SELECT accounts.name,
	SUM(splits.quantity_num / splits.quantity_denom) AS res_quantity_denom  
FROM accounts 
LEFT JOIN splits 
	WHERE (
		parent_guid="59a27d1443e1446eaabf84150d88aa39"
		 AND splits.account_guid=accounts.guid
	)
GROUP BY name ';

$total_prevlegalcloseprice = 0;
$moex_shares = array();


$results = $db->query($sql_q);
while ($row = $results->fetchArray()) {

	$moex_shares[$row['name']] = get_moex_shares_json($row['name']);
	
	$total_prevlegalcloseprice += $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom'];				
}



//~ ------------------
//~ Вывод акции

$sql_q = '
SELECT accounts.*,
splits.action, 
SUM(splits.quantity_num / splits.quantity_denom) AS res_quantity_denom, 
SUM(splits.value_num / splits.value_denom) AS res_value_num , 
splits.tx_guid
 FROM accounts 
 LEFT JOIN splits 
	WHERE (
		parent_guid="59a27d1443e1446eaabf84150d88aa39"
		 AND splits.account_guid=accounts.guid
	)
GROUP BY name 
ORDER BY res_value_num DESC';




echo "<table>";    
echo "<caption>Акции</caption>";
echo '<tr>  			
<th>ISIN</th>
<th>Полное наименование</th>			
<th>Акций,</br>шт</th>
<th><a title="Сумма инвестированных средств в акции, (руб / пропорция от итоговой суммы)">Базис / &Colon;</br>₽ / %</a></th>
<th><a title="Рыночная стоимость акций">Значение / &Colon; </br>₽ / %</a></th>
<th><a title="Официальная цена закрытия предыдущего дня, рассчитываемая по методике ФСФР 
/ Средняя цена (Базис / Количество)
/ Разница (&#956;-цена - Цена)">Цена <big>/ &#956; / &Delta;</big></br>
₽ / ₽ / ₽ - %
</a></th>
<!-- <th><a title="Средняя цена акции (Базис / Акции)"><big>&#956;</big>-Цена,</br>₽</a></th>
<th><a title="Официальная цена закрытия предыдущего дня, рассчитываемая по методике ФСФР">Цена,</br>₽</a></th>
<th><a title="Разница (&#956;-цена - Цена)"><big>&Delta;</big>-Цены,</br>₽/%</a></th>
-->
<th><a title="Сумма полученных дивидендов"><big>&sum;</big> дивидендов,</br>₽</a></th>
<th><a title="Возвратность инвестиционных вложений. Сумма дивидендов / Базис * 100">ROI,</br>%</a></th>
<th><a title="Последняя покупка акций">Прошло,</br>мес/дн</a></th>
<th><a title="Количество ценных бумаг в одном лоте и его стоимость">Цена лота,</br>₽/шт</a></th>

</tr>
';

$gnucash_dividendization = get_gnucash_dividendization();

$total_gnucash_dividendization =0;



$results = $db->query($sql_q);
while ($row = $results->fetchArray()) {
	
	//~ $moex_shares = get_moex_shares_json($row['name']);
	
	echo '<tr>';
	echo '<td><a href="/investment/index.php?do=update_share&share='.$row['name'].'">'.$row['name'].'</a></td>';
	echo '<td>'.str_replace(' ', '&nbsp;', $row['description']).'</td>';
	
			//echo '<td class="number">'.number_format($row['res_quantity_denom'], 0, ',', ' ').'</td>';

			// количество
			echo '<td class="number">';			
			echo ($row['res_quantity_denom'] > 0) ? number_format($row['res_quantity_denom'], 0, ',', '&nbsp;') : '-';
			echo '</td>';
			
			// базис (инвестировано)
			echo '<td class="number">';			
			if ($row['res_quantity_denom'] > 0) {				
				$total_gnucash_dividendization += $gnucash_dividendization[$row['name']];
				echo number_format($row['res_value_num'], 2, ',', '&nbsp;');				
				// доля портфеля
				echo ($row['res_quantity_denom'] > 0) ? '&nbsp;<span style="color:gray; display:inline-block; width:20px;">/&nbsp;'.number_format(($row['res_value_num']*100/$total_investment), 2, ',', '&nbsp;').'</span>' : '';				
			}
			else
				echo '-';
			echo '</td>';
			
			//Значение (Портфель рыночная стоимость, ₽)
			echo '<td class="number">';			
			if ($row['res_quantity_denom'] > 0) {							
				echo number_format( $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom'] , 2, ',', '&nbsp;');
				// доля портфеля
				echo ($row['res_quantity_denom'] > 0) ? '&nbsp;<span style="color:gray; display:inline-block; width:20px;">/&nbsp;'.number_format(($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom']*100/$total_prevlegalcloseprice), 2, ',', '&nbsp;').'</span>' : '';			
			}
			else
				echo '-';
			echo '</td>';
				
			if ($row['res_quantity_denom'] > 0) {
				
				//цена пред.дня			
				echo '<td class="number">'					
				.'<div style="min-width:42px; display: table-cell;">'
				.number_format($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], $moex_shares[$row['name']]['DECIMALS'], ',', ' ')
				.'</div>';
				
				//Портфель μ-цена акции, ₽
				//echo '<td class="number" >';	
				$shares_avg = $row['res_value_num'] / $row['res_quantity_denom'];
				echo '<div style="min-width:42px; color: gray; display: table-cell;">'
				.number_format($shares_avg  , $moex_shares[$row['name']]['DECIMALS'], ',', ' ')
				.'</div>';
				
				
				$avg_pm = '+';
				$bond_avg_css = '#99ff99';
				if (($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg) < 0) {
					$bond_avg_css = '#ff9999';
					$avg_pm = '';
				}
				//echo '</td>';
				//цена пред.дня			
				//echo '<td class="number">';						
				//echo number_format($moex_shares['PREVLEGALCLOSEPRICE'], $moex_shares['DECIMALS'], ',', ' ');
				//echo '</td>';			
				
				//style="width:30px; color: gray; display: inline-block;
				
				// Портфель Δ-цена акции, %	
				echo '<div style="background-color:'.$bond_avg_css.'; display: table-cell; min-width:42px;" >'
				.$avg_pm.number_format($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg, $moex_shares[$row['name']]['DECIMALS'], ',', '&nbsp;')
				.'</div><div style="background-color:'.$bond_avg_css.'; display:table-cell; min-width:30px;">&nbsp;'
				.$avg_pm.number_format(($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg ) *100 / $shares_avg, 1, ',', ' ')
				.'</div>'
				.'</td>';
			}
			else {				
				echo '<td>-</td>';
				echo '<td>-</td>';
				echo '<td>-</td>';
			}									
			//Портфель сумма дивидендов, ₽
			echo '<td class="number" >';			
			echo ($gnucash_dividendization[$row['name']] > 0) ? number_format($gnucash_dividendization[$row['name']], 2, ',', ' ') : '-';						
			echo '</td>';			
			
			// ROI
			echo '<td class="number">';
			if (!empty($gnucash_dividendization[$row['name']]) & !empty($row['res_value_num'])) {
				echo number_format($gnucash_dividendization[$row['name']] /  $row['res_value_num'] *100, 2, ',', ' ');
			}
			else
				echo '-';
			echo '</td>';
			
			
			//последний день покупки
			echo '<td class="number">';		
			
			if ($row['res_quantity_denom'] > 0) {
			
				$daybuy = get_gnucash_last_daybuy_shares($row['name']);
				
				if (!empty($daybuy)) {
				
					$dateStart = date_create($daybuy);
					$dateEnd = date_create(date('d.m.Y',$time));
				
					$dateEnd->setTime(24,0,0);

					$diff = date_diff($dateStart,$dateEnd);
					//echo $diff->format("%a");
					echo $diff->format("%m/%d");
				}
					
			}			
			echo '</td>';
	
			echo '<td class="number">';						
			//стоимость лота	
			echo number_format($moex_shares[$row['name']]['LOTSIZE'] * $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', ' ');
			//лотность	
			echo '&nbsp;<span style="color:gray; display:inline-block; width:5px;">/&nbsp;'.number_format($moex_shares[$row['name']]['LOTSIZE'], 0, ',', '&nbsp;').'</span>';
			echo '</td>';					
	
	
	echo '</tr>';	
}
echo "</table>";  

echo "<table>";  
echo "<caption>Результаты</caption>";
//echo "<tr>";
//echo '<th></th><th><big>&sum;</big></th>';
//echo "</tr>";
echo "<tr>";
echo '<td>Базис</td><td class="number"><big>&sum;</big></td><td class="number">'.number_format($total_investment, 2, ',', ' ').'</td>';
echo "</tr>";

echo "<tr>";
echo '<td>Значение</td><td class="number"><big>&sum;</big></td><td class="number"> '.number_format($total_prevlegalcloseprice, 2, ',', ' ').'</td>';
echo "</tr>";
echo "<tr>";
echo '<td></td><td class="number"><big>&Delta;</big></td><td class="number"> '.number_format($total_prevlegalcloseprice-$total_investment, 2, ',', ' ').'</td>';
echo "</tr>";
echo "<tr>";
//echo '<th></th><th></th><th></th>';
//echo '<th></th>';
echo '<td>Дивиденды</td><td class="number"> <big>&sum;</big> </td><td class="number"> '.number_format($total_gnucash_dividendization, 2, ',', ' ').'</td>';
echo "</tr>";
echo "<tr>";
echo '<td>ROI</td><td class="number">%</td><td class="number">'.number_format($total_gnucash_dividendization / $total_investment * 100, 2, ',', ' ').'</td>';
//echo '<th></th><th></th>';
//echo '<th></th><th></th>';
//echo '<th></th><th></th>';
//echo '<th class="number">'.number_format( (array_sum($avg_couponpercent) / count($avg_couponpercent)), 2, ',', ' ').'</th>';

echo "</tr>";
echo "</table>";    


/*-------------------------------------
ОБЛИГАЦИИ
-------------------------------------*/

//~ ---------------
//~ Итоговый Базис
$sql_total_investment= 
"SELECT 
sum(splits.value_num / splits.value_denom) AS res_value_num , 
splits.tx_guid

 FROM accounts 
 
 LEFT JOIN splits 
	WHERE (
		parent_guid='dea8fee127474021a42c9502aa3e76cb'
		 AND splits.account_guid=accounts.guid
		 AND (splits.action='Покупка' OR splits.action='Продажа') 		 
	)";
	
$total_investment = 0;
$res_total_investment = $db->query($sql_total_investment);
if ($row_total_investment = $res_total_investment->fetchArray()) {
	$total_investment = $row_total_investment['res_value_num'];
}
//~ ----------------------
//~ Итоговый Значение

$sql_q = '
SELECT accounts.name,
SUM(splits.quantity_num / splits.quantity_denom) AS res_quantity_denom
 FROM accounts 
 LEFT JOIN splits 
	WHERE (
		parent_guid="dea8fee127474021a42c9502aa3e76cb"
		 AND splits.account_guid=accounts.guid
	)
GROUP BY name ';
$total_prevlegalcloseprice = 0;
//~ сумма по эмиттеру
$total_prevlegalcloseprice_emitter = array();
$bond = array();
$results = $db->query($sql_q);
while ($row = $results->fetchArray()) {
	$bond[$row['name']] = get_moex_bond_json($row['name']);	
	
	
	$total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']] += $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
	
	
	
	$total_prevlegalcloseprice += $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
}






//~ --------------------
//~ Группировка с суммой инвестиций и количество бумаг
$sql_q = '
SELECT accounts.*,
splits.action, 
SUM(splits.quantity_num / splits.quantity_denom) AS res_quantity_denom, 
SUM(splits.value_num / splits.value_denom) AS res_value_num , 
splits.tx_guid
 FROM accounts 
 LEFT JOIN splits 
	WHERE (
		parent_guid="dea8fee127474021a42c9502aa3e76cb"
		 AND splits.account_guid=accounts.guid
	)
GROUP BY name 

	';
	
$logs[] = $sql_q;	



$results = $db->query($sql_q);
$sql_rr= '';
$invest_summ = 0;
$a_lines = array();
$a_lines_sum = array();
echo "<table>";    
echo "<caption>Облигации</caption>";		
echo '
<tr>  			
<th>ISIN</th>
<th>Полное наименование</th>			
<th><a title="Для эмитента в портфеля">Эмитент&nbsp;/&nbsp;&Colon; </br>₽ / % </a></th>			
<th><a title="Портфель количество облигации">Обл.</br>шт</a></th>

<th><a title="Сумма инвестированных средств в облигации, (руб / пропорция от итоговой суммы)">Базис / &Colon;</br>₽ / %</a></th>

<th><a title="Рыночная стоимость акций">Значение / &Colon;</br>₽ / %</a></th>
<th><a title="Номинальная стоимость облигации
/ Официальная цена закрытия предыдущего дня, рассчитываемая по методике ФСФР 
/ Средняя цена (Базис / Количество)
/ Разница (&#956;-цена - Цена)">Номинал. / Цена <big>/ &#956; / &Delta;</big></br>₽ / % / % / пп</a></th>

<th><a title="Сумма полученных купонов"><big>&sum;</big> куп.
</br>₽</a></th>
<th><a title="возвратность инвестиционных вложений.
Сумма купонного дохода / (Инвестировано*Цена пред.дня)">ROI</a></br>%</th>
<th>НКД</th>

<th><a title="Размер купона 
/ Ставка купона 
/ Период выплаты (количество в год) купонов">Купон,
</br>₽ / % / шт</a></th>



<th>Дата погашения</th>
<th>Ст. Ур.</th>


';


$starttime = microtime(true); // Top of page

$bondization_period = 24;
$css_background = '';
$y_toggle ='';
$m_toggle ='';
for ($i=0;$i<$bondization_period;$i++)	{
	
	
	if ( $i > 11)
		$css_background = 'color3';
	elseif ( $i > 5)
		$css_background = 'color2';
	else
		$css_background = 'color1';
	
	echo '<th class="'.$css_background.'">';
	//~ .date("y.n", strtotime("+$i month", $time))
	$year = date("Y", strtotime("+$i month", $time));
	$month = date("n", strtotime("+$i month", $time));
	
	if ($y_toggle != $year) {
		echo $year;
		$y_toggle = $year;
	}
	if ($m_toggle != $month) {
		echo '</br>'.$month;
		$m_toggle = $month;
	}	
	
	echo "</th>";
	
}
	
//получить сведения о полученных сумме купонов по облигациям из gnucash
$gnucash_bondization = get_gnucash_bondization();	
	
	


echo "</tr>			";	

$a_bond_month = array();
$sum_gnucash_bondization = 0;
//~ $sum_portfolio_price = 0;
$avg_couponpercent = array();

$sum_prevlegalcloseprice_emitter =  array_sum($total_prevlegalcloseprice_emitter);
$togle_name = '';
while ($row = $results->fetchArray()) {
		
		if ($row['res_quantity_denom'] > 0) {
			echo '<tr>';
			echo '<td>'.$row['name'].'</td>';
			
			//$bond = '';
			//$bond = get_moex_bond_json($row['name']);
			//if ('RU000A104UA4' == $row['name']) 
			
			//~ $bond = get_moex_bond_json($row['name']);
			
			
			
			
			echo '<td style="white-space: nowrap;">';
			
			
			if (preg_match("/RU[a-zA-Z0-9]{10}/", $row['name']) ) {
				if ($togle_name != $bond[$row['name']]['REGNUMBER'])
					echo''.get_emitter($bond[$row['name']]['REGNUMBER'] );
				else
					echo '&#12291;';
			}
			else
				echo $bond[$row['name']]['NAME'];
			//~ echo $bond[$row['name']]['REGNUMBER'];
			
			
			echo '</td>';	
			
			//~ сумма по эмитенту
			echo '<td class="number">'
			.'<span style="display:table-cell; min-width:40px;">';
			
			echo ($togle_name != $bond[$row['name']]['REGNUMBER']) 
			? number_format($total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']], 2, ',', '&nbsp;')
			: '&#12291;';

			echo '</span>'
			.'<span style="color:gray; display:table-cell; min-width:20px;">&nbsp;';
			echo ($togle_name != $bond[$row['name']]['REGNUMBER']) 
			? number_format($total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']] * 100 / $sum_prevlegalcloseprice_emitter, 2, ',','&nbsp;')
			: '&#12291;';
			echo '</span>'
			.'</td>';
			
			$togle_name = $bond[$row['name']]['REGNUMBER'];
			
			
			//количество
			echo '<td class="number">'.number_format($row['res_quantity_denom'], 0, ',', '&nbsp;').'</td>';
			
			//Базис,₽ / ∷			
			echo '<td class="number">'
			.'<span style="display:table-cell; min-width:40px;">'
			.number_format($row['res_value_num'], 2, ',', '&nbsp;')
			.'</span>';

			// доля портфеля
			echo '<span style="color:gray; display:table-cell; min-width:20px;">&nbsp;'
			.number_format(($row['res_value_num']*100/$total_investment), 2, ',', '&nbsp;')
			.'</span>';
			echo '</td>';


			//Значение (Портфель рыночная стоимость, ₽)
			echo '<td class="number">';			
			if ($row['res_quantity_denom'] > 0) {				
				
				//~ $sum_portfolio_price += $bond['PREVLEGALCLOSEPRICE'] * $bond['FACEVALUE'] /100 * $row['res_quantity_denom'];
				
				echo '<span style="display:table-cell; min-width:40px;">&nbsp;'
				.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'], 2, ',', '&nbsp;')
				.'</span>';
				// доля портфеля
				echo '<span style="color:gray; display:table-cell; min-width:20px;">&nbsp;'
				.number_format(($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom']*100/$total_prevlegalcloseprice), 2, ',', '&nbsp;')
				.'</span>';
				
				
				
			}
			else
				echo '-';
			echo '</td>';


			
			
			echo '<td class="number">'
			//~ номинальная стоимость
			.'<span style="min-width:30px; display: table-cell;   ">'
			.number_format($bond[$row['name']]['FACEVALUE'], 2, ',', '&nbsp;')
			.'</span>'
			//Цена,₽
			.'<span style="min-width:30px; display: table-cell;   ">'
			.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', ' ')
			.'&nbsp;'
			.'</span>';
			

			//μ-Цена,₽
			$bond_avg =  ($row['res_value_num']/$row['res_quantity_denom']*100/$bond[$row['name']]['FACEVALUE']);
			
			echo '<span style="min-width:30px; color: gray; display: table-cell; ">'.number_format($bond_avg, 2, ',', '&nbsp;')			
			.'&nbsp;'
			.'</span>';

			//Δ-Цены,₽/п.п
			$plus_minus = '+';
			$bond_avg_css = '#99ff99;';
			if (($bond[$row['name']]['PREVLEGALCLOSEPRICE'] -$bond_avg) < 0) {
				$plus_minus = '';
				$bond_avg_css = '#ff9999;';						
			}
			echo '<span  style="background-color:'.$bond_avg_css.' min-width:25px; display: table-cell; " >'
			.$plus_minus
			.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'] - $bond_avg, 2, ',', '&nbsp;')
			.'</span>';
			echo '</td> ';
			
			
			
			//∑ купонов,₽
			$result_bondization = '';
			echo ($result_bondization = $gnucash_bondization[$row['name']]) ? '<td class="number">'.number_format($result_bondization, 2, ',', ' ').'</td>' : '<td class="number"></td>';
			$sum_gnucash_bondization += $result_bondization;
		
			//if (($bond['PREVLEGALCLOSEPRICE'] -$bond_avg) < 0)
				//$bond_avg_css = 'style="color: red; "';			
			
			//echo '<td  class="number" '.$bond_avg_css.'>'.number_format($bond['PREVLEGALCLOSEPRICE'] - $bond_avg, 2, ',', ' ').'';
			//echo '</td> ';
			
			// ROI
			echo '<td class="number">';
			if (!empty($result_bondization)) {
				echo number_format(
				($result_bondization / ($row['res_value_num']* ($bond[$row['name']]['PREVLEGALCLOSEPRICE']/100) )  *100 ) , 2, ',', ' '
				);
			}
			echo '</td>';
			
			
			//echo '<td class="number">'.number_format($bond['PREVLEGALCLOSEPRICE'], 2, ',', ' ').'</td>';
	

			echo '<td class="number">'.number_format($bond[$row['name']]['ACCRUEDINT'],2,',',' ').'</td>';		
			
			$css_background = '';
			if ($bond[$row['name']]['COUPONPERCENT'] >=11 )
				$css_background = 'color1';
			elseif ($bond[$row['name']]['COUPONPERCENT'] >=9 )
				$css_background = 'color1';
			elseif ($bond[$row['name']]['COUPONPERCENT'] >=7 )
				$css_background = 'color3';
			elseif ($bond[$row['name']]['COUPONPERCENT'] >=6 )
				$css_background = 'color3';
				
				

			
			//~ размер купона
			echo '<td class="number">'
			.'<span style="display:table-cell; min-width:25px; ">'
			.number_format($bond[$row['name']]['COUPONVALUE'], 2, ',', ' ')
			.'</span>'
			//~ .'</td>'
			//~ ставка купона
			//~ echo '<td class="number">'
			.'<span style="display:table-cell; min-width:25px;" class="'.$css_background.'">'
			
			.$bond[$row['name']]['COUPONPERCENT']
			.'</span>'
			.'<span style="color:gray; display:table-cell; min-width:10px; border:0px solid red;">'
			.number_format(364/$bond[$row['name']]['COUPONPERIOD'],0,'','')
			.'</span>'
			.'</td>';	


			// дата погашения
			$css_background = '';
			$matdate_n = date('Y', strtotime($bond[$row['name']]['MATDATE']));
			$matdate_y0 = date("Y",$time);
			$matdate_y1 = date("Y",strtotime("+1 year", $time) );
			$matdate_y2 = date("Y",strtotime("+2 year", $time) );
			$matdate_y3 = date("Y",strtotime("+3 year", $time) );
			$matdate_y4 = date("Y",strtotime("+4 year", $time) );
			$matdate_y5 = date("Y",strtotime("+5 year", $time) );
			$matdate_y6 = date("Y",strtotime("+6 year", $time) );
			
			
			if (			
				strcmp($matdate_n,$matdate_y0) == 0
				)
				$css_background = 'color1';
			elseif (
				strcmp($matdate_n,$matdate_y1) == 0 ||
				strcmp($matdate_n,$matdate_y2) == 0 ||
				strcmp($matdate_n,$matdate_y3) == 0 
				)
				$css_background = 'color2';
			elseif (
				strcmp($matdate_n,$matdate_y4) == 0 ||
				strcmp($matdate_n,$matdate_y5) == 0 ||
				strcmp($matdate_n,$matdate_y6) == 0 
			)
				$css_background = 'color3';
			else
				$css_background = 'color3';
				
				
				
				
			echo '<td class="'.$css_background.'" >'.date('d.m.Y', strtotime($bond[$row['name']]['MATDATE'])).'</td>';
			//--------------
			
			
			echo '<td class="number">'.$bond[$row['name']]['STATUS'].' '.$bond[$row['name']]['LISTLEVEL'].'</td>';			
			
			
			
			
			
			for ($i=0;  $i < $row['res_quantity_denom']; $i++)
				$avg_couponpercent[] = $bond[$row['name']]['COUPONPERCENT'];
	
			$bondization = get_moex_bond_bondization_json($row['name']);

			if (is_array($bondization)) {		
				$matdate = date('y.n', strtotime($bond[$row['name']]['MATDATE']));
				
				for ($i=0;$i<$bondization_period;$i++) {
					
					$yn = date("y.n",strtotime("+$i month", $time) );
					
					$bond_month = $bondization[$yn]['value_rub'];
					
					$_css = '';
					if (strcmp($matdate,$yn) == 0)
						$_css = 'border-right:2px solid black;';
					
					
					if (!is_null( $bond_month)) {
						echo '<td class="number '.$css_background.'" style="'.$_css.'">';
						if ($bond_month*$row['res_quantity_denom'] > 1) {
							echo number_format( $bond_month*$row['res_quantity_denom'], 2, ',', ' ');
						}
						echo '</td>';
						$a_bond_month[$yn] += $bond_month*$row['res_quantity_denom'];	
					}
					else
						echo '<td></td>';
				}
			}
			echo '</tr>';
		}
}
$db->close();

echo '<tr><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th> <th></th><th></th>';
for ($i=0;$i<$bondization_period;$i++) {
	echo '<th class="number">';
	
	echo ($ff = $a_bond_month[date("y.n",strtotime("+$i month", $time) )]) ? number_format($ff, 2, ',', ' ') : '';
	
	echo '</th>';
}
echo '</tr>';

echo "</table>";

echo "<table>";
echo "<caption>Портфель</caption>";
echo '<tr>';
echo '<td>Базис</td><td class="number"><big>&sum;</big></td><td class="number">'.number_format($total_investment, 2, ',', ' ').'</td>';
echo '</tr><tr>';
echo '<td>Значение</td><td class="number"><big>&sum;</big></td><td class="number">'.number_format($total_prevlegalcloseprice, 2, ',', ' ').'</td>';



echo '</tr><tr>';
echo '<td></td><td class="number"><big>&Delta;</big></td><td class="number"> '.number_format($total_prevlegalcloseprice-$total_investment, 2, ',', ' ').'</td>';

echo '</tr><tr>';
echo '<td>Купоны</td><td class="number"><big>&sum;</big> </td><td class="number">'.number_format($sum_gnucash_bondization, 2, ',', ' ').'</td>';
echo '</tr><tr>';
echo '<td>ROI</td><td class="number">%</td><td class="number">'.number_format($sum_gnucash_bondization / $total_investment * 100, 2, ',', ' ').'</td>';
echo '</tr><tr>';
echo '<td>Ставка купона</td><td class="number"><big>&#956;</big> </td><td class="number">'.number_format( (array_sum($avg_couponpercent) / count($avg_couponpercent)), 2, ',', ' ').'%</td>';
echo '</tr>';
echo "</table>";




//var_export( $avg_couponvalue );

//exit;



echo "</body>".PHP_EOL;
echo "</html>".PHP_EOL;

//get_moex_bond_price();


echo "<textarea>";
foreach ($logs as $line) 
	echo $line.PHP_EOL;

$endtime = microtime(true); // Bottom of page

printf("Page loaded in %f seconds", $endtime - $starttime );	
echo "</textarea>";
