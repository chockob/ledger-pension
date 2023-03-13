<?php

//~ ini_set('display_errors', 1); 
//~ ini_set('display_startup_errors', 1); 
//~ error_reporting(E_ALL & ~E_NOTICE);

include "./core/core.class.php";
include "./core/raexpert.class.php";
include "./core/acra.class.php";
include "./core/moex.class.php";

$Core = new CoreLedgerPension();

$CoreAcra = new CoreAcra();
$CoreExpertRA = new CoreExpertRA();
$CoreMOEX = new CoreMOEX();



$logs = array();
$time = time();

echo "<!doctype html>".PHP_EOL;
echo '<html data-theme="dark">'.PHP_EOL;
echo '<head>

 <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/pico.css">
';

//~ echo "
//~ <link rel=\"stylesheet\" href=\"./style.css\">

//~ <script src=\"./js/jquery-3.6.0.min.js\"></script>
//~ <script src=\"./js/jquery.balloon.min.js\"></script>

//~ <script>
//~ $(document).ready(function(){
	//~ console.log('id=');
    //~ $('.btn_tx').click(function() {
		//~ var s = $(this).attr('id');
		//~ console.log('id=' + s);
		//~ $('#tx_' + s).toggle();
    //~ });
    
       
//~ });
//~ </script>


//~ ";


echo "</head>".PHP_EOL;
echo "<body>".PHP_EOL;



   



 
 
 

$ver = SQLite3::version();

$db = new SQLite3('/home/chockob/Documents/ledger/ledger-home.sqlite.gnucash', SQLITE3_OPEN_READONLY); //, SQLITE3_OPEN_READWRITE);


    

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
	
	//~ echo $people_json;
		
	$decoded_json = json_decode($people_json, true);		
	//~ print_r($decoded_json);
	
	$res = array();	
	if (!empty($decoded_json['securities']['data'])) {
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
	}
	//~ exit;
	
	return $res;
	
	
}

//------------------------------------------
// КУПОННЫЙ КАЛЕНДАРЬ
// https://iss.moex.com/iss/securities/RU000A104UA4/bondization.json?iss.json=extended&iss.meta=off&iss.only=coupons&lang=ru&limit=unlimited
// bondization.json
function get_moex_bond_bondization_json ($bond_secid) {    	
	
	//$bond_secid = 'RU000A104UA4';
	
	$source_file = 'bondization/'.$bond_secid.'.json';	
	if (file_exists($source_file)) {
		//~ $people_json = file_get_contents($source_file);	
		//echo "file<br>";

		$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
		$dateEnd = date_create(date('d.m.Y',time()));
		$dateEnd->setTime(24,0,0);
		$diff = date_diff($dateStart,$dateEnd);
	
		if ($diff->format("%a") > 100 ) {					
			$people_json = file_get_contents('https://iss.moex.com/iss/securities/'.$bond_secid.'/bondization.json?iss.json=extended&iss.meta=on&iss.only=coupons&lang=ru&limit=unlimited');	
			file_put_contents($source_file, $people_json);
		}
	}
	else {
		$people_json = file_get_contents('https://iss.moex.com/iss/securities/'.$bond_secid.'/bondization.json?iss.json=extended&iss.meta=on&iss.only=coupons&lang=ru&limit=unlimited');	
		file_put_contents($source_file, $people_json);
	}
	
	$people_json = file_get_contents($source_file);	
	$decoded_json = json_decode($people_json, true);	
	
	//print_r($decoded_json);
	
	$res = array();
	
	//$i=0;
	foreach($decoded_json[1]['coupons'][1] as $key=>$val) {

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
	//~ var_export($moex_shares);
	
	if (!empty($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE']))	
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



echo "<h1>Акции</h1>";
echo "<table>";
echo "<thead>";    
echo '
<tr>
<th rowspan="2">#</th>
<th rowspan="2">Наименование</th>
<th colspan="5">Базис</th>
<th colspan="4">Значение</th>
<th colspan="4">Результат</th>
</tr> 

<tr>  			
<th><a title="Сумма инвестированных средств в акции, (руб / пропорция от итоговой суммы)">&sum;&nbsp;(₽)</a></th>
<th><a title="Сумма инвестированных средств в акции, (руб / пропорция от итоговой суммы)">&Colon;&nbsp;(%)</a></th>
<th>шт</th>
<th><a title="Средняя цена (Базис / Количество)">&#956;&nbsp;(₽)</a></th>
<th><a title="Последняя покупка акций">Сут.</a></th>


<th><a title="Сумма Рыночная стоимость акций">&sum;&nbsp;(₽)</a></th>
<th><a title="Доля Рыночная стоимость акций">&Colon;&nbsp;(%)</a></th>
<th><a title="Официальная цена закрытия предыдущего дня, рассчитываемая по методике ФСФР ">Рын.&nbsp;(₽)</a></th>
<th><a title="Количество ценных бумаг в одном лоте и его стоимость">Лот&nbsp;(₽)</a></th>


<th><a title="Разница (&#956;-цена - Цена)">&Delta;&nbsp;(₽)</a></th>
<th><a title="Разница (&#956;-цена - Цена)">&Delta;&nbsp;(пп)</a></th>


<th><a title="Сумма полученных дивидендов">&sum;&nbsp;Див.&nbsp;(₽)</a></th>
<th><a title="Возвратность инвестиционных вложений. Сумма дивидендов / Базис * 100">ROI&nbsp;(%)</a></th>
</tr>';
echo "</thead>";    
echo "<tbody>";

$gnucash_dividendization = get_gnucash_dividendization();

$total_gnucash_dividendization =0;



$results = $db->query($sql_q);
while ($row = $results->fetchArray()) {
	
	//~ $moex_shares = get_moex_shares_json($row['name']);
	
	echo '<tr>';
	echo '<td><a href="/investment/index.php?do=update_share&share='.$row['name'].'">'.$row['name'].'</a></td>';
	echo '<td>'.str_replace(' ', '&nbsp;', $row['description']).'</td>';
	
			//echo '<td class="number">'.number_format($row['res_quantity_denom'], 0, ',', ' ').'</td>';

			
			// базис (инвестировано)			
			if ($row['res_quantity_denom'] > 0) {				
				$total_gnucash_dividendization += $gnucash_dividendization[$row['name']];
				echo '<td class="number">';
				echo number_format($row['res_value_num'], 2, ',', '&nbsp;');
				echo '</td>';
				// доля портфеля
				echo '<td class="number">';
				echo ($row['res_quantity_denom'] > 0) ? number_format(($row['res_value_num']*100/$total_investment), 2, ',', '&nbsp;') : '';				
				echo '</td>';
			}
			else
				echo '<td class="number"></td><td class="number"></td>';

			// количество
			echo '<td class="number">';			
			if ($row['res_quantity_denom'] > 0) {
				if ($row['res_quantity_denom'] <1000)
					echo number_format($row['res_quantity_denom'], 0, ',', '&nbsp;');
				else
					echo number_format($row['res_quantity_denom']/1000, 0, ',', '&nbsp;').'k';
			}
			echo '</td>';

			//Портфель μ-цена акции, ₽				
			echo '<td class="number">';
			if ($row['res_quantity_denom'] > 0) {
				$shares_avg = $row['res_value_num'] / $row['res_quantity_denom'];
				
				echo number_format($shares_avg  , $moex_shares[$row['name']]['DECIMALS'], ',', ' ');
				
			}
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
					echo $diff->format("%a");
					//~ echo $diff->format("%m/%d");
				}
			}			
			echo '</td>';			
			
			//Значение (Портфель рыночная стоимость, ₽)
			if ($row['res_quantity_denom'] > 0) {							
				echo '<td class="number">';
				echo number_format( $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom'] , 2, ',', '&nbsp;');
				echo '</td>';
				// доля портфеля
				echo '<td class="number">';		
				echo ($row['res_quantity_denom'] > 0) ? number_format(($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom']*100/$total_prevlegalcloseprice), 2, ',', '&nbsp;') : '';
				echo '</td>';
			}
			else
				echo '<td class="number"></td><td class="number"></td>';
			
				
			
				
				//цена пред.дня			
				echo '<td class="number">'
				.number_format($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], $moex_shares[$row['name']]['DECIMALS'], ',', ' ')
				.'</td>';
				
				//стоимость лота	
				echo '<td class="number">'
				.number_format($moex_shares[$row['name']]['LOTSIZE'] * $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', ' ')
				.'</td>';
				//~ //лотность	
				//~ echo '<td class="number">';			
				//~ if ($moex_shares[$row['name']]['LOTSIZE'] > 1000)
					//~ echo number_format($moex_shares[$row['name']]['LOTSIZE']/1000, 0, ',', '&nbsp;').'k';
				//~ else
					//~ echo number_format($moex_shares[$row['name']]['LOTSIZE'], 0, ',', '&nbsp;');
				//~ echo '</td>';

			if ($row['res_quantity_denom'] > 0) {
				
				$avg_pm = '+';
				$bond_avg_css = '#99ff99';
				if (($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg) < 0) {
					$bond_avg_css = '#ff9999';
					$avg_pm = '';
				}
				
				// Портфель Δ-цена акции, ₽		
				echo '<td class="number">'
				.$avg_pm.number_format($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg, $moex_shares[$row['name']]['DECIMALS'], ',', '&nbsp;')
				.'</td>'
				// Портфель Δ-цена акции, %	
				.'<td class="number">'
				.$avg_pm.number_format(($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg ) *100 / $shares_avg, 1, ',', ' ')
				.'</td>';
			}
			else {				
				echo '<td>-</td>';
				echo '<td>-</td>';
			}									
			//Портфель сумма дивидендов, ₽
			echo '<td class="number" >';			
			echo ($gnucash_dividendization[$row['name']] > 0) ? number_format($gnucash_dividendization[$row['name']], 2, ',', ' ') : '-';						
			echo '</td>';			
			
			// ROI
			echo '<td class="number">';
			if ($row['res_quantity_denom'] > 0) {
				if (!empty($gnucash_dividendization[$row['name']]) & !empty($row['res_value_num'])) {
					echo number_format($gnucash_dividendization[$row['name']] /  $row['res_value_num'] *100, 2, ',', ' ');
				}
			}
			echo '</td>';
			
			

	
			
			//~ //стоимость лота	
			//~ echo '<td class="number">'
			//~ .number_format($moex_shares[$row['name']]['LOTSIZE'] * $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', ' ')
			//~ .'</td>';
			//~ //лотность	
			//~ echo '<td class="number">'
			//~ .number_format($moex_shares[$row['name']]['LOTSIZE'], 0, ',', '&nbsp;').'</span>'
			//~ .'</td>';
	
	
	echo '</tr>';	
}
echo "</tbody>";
echo "</table>";  

echo "<table>";  
echo "<caption>Результаты</caption>";
//echo "<tr>";
//echo '<th></th><th>&sum;</th>';
//echo "</tr>";
echo "<tr>";
echo '<td>Базис</td><td class="number">&sum;</td><td class="number">'.number_format($total_investment, 2, ',', ' ').'</td>';
echo "</tr>";

echo "<tr>";
echo '<td>Значение</td><td class="number">&sum;</td><td class="number"> '.number_format($total_prevlegalcloseprice, 2, ',', ' ').'</td>';
echo "</tr>";
echo "<tr>";
echo '<td></td><td class="number">&Delta;</td><td class="number"> '.number_format($total_prevlegalcloseprice-$total_investment, 2, ',', ' ').'</td>';
echo "</tr>";
echo "<tr>";
//echo '<th></th><th></th><th></th>';
//echo '<th></th>';
echo '<td>Дивиденды</td><td class="number"> &sum; </td><td class="number"> '.number_format($total_gnucash_dividendization, 2, ',', ' ').'</td>';
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
$content_option = '';
while ($row = $results->fetchArray()) {
	
	$bond[$row['name']] = $CoreMOEX->get_moex_bond_json($row['name']);	
	$total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']] += $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
	$total_prevlegalcloseprice += $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
	$selected = '';
	//~ для формы постановки на контроль рейтинга Экперт РА
	$content_option .= '<option '.$selected.' value="'.$row['name'].'" >'.$row['name'].'</option>';
}
//~ форма постановки на контроль рейтинга Эксперт РА
$get_raexpert_form_control = $CoreExpertRA->get_raexpert_form_control($content_option);

//~ Обработка команды на постановку контроля
if ($_GET['do'] == 'raexpert-control') {
	if ($_POST['type-control'] == 'bond') {
		$bond_name = $_POST['bond-name'];
		$url_control = $_POST['url-control'];
		$source_file = 'raexpert/'.$bond_name.'.csv';
		$content_control = file_get_contents($url_control);	
		//~ echo $content_control;
		$doc = new DOMDocument();
		$doc->loadHTML(mb_convert_encoding($content_control, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
//~ var_export($doc);
		$elements = $doc->getElementsByTagName('table');
		$tr = $elements[0]->getElementsByTagName('tr');
		//~ echo $tr[0]->nodeValue.'</br>';		  
		//~ echo $tr[1]->nodeValue.'</br>';    
		$span = $tr[1]->getElementsByTagName('span');
		echo $span[0]->nodeValue.'</br>';    
		$raexport_bond_val = trim($span[0]->nodeValue);
		$td = $tr[1]->getElementsByTagName('td');
		echo $td[1]->nodeValue.'</br>';    
		$raexport_bond_d = trim($td[1]->nodeValue);
		file_put_contents($source_file, "$raexport_bond_d;$raexport_bond_val;$url_control");  
	}
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

echo "<h1>Облигации</h1>";		
echo '<figure>';

echo '<table>';    
echo "<thead>";    
echo '

<tr>
<th rowspan="2">#</th>
<th rowspan="2">Наименование</th>
<th colspan="4"><a title="Сумма инвестированных средств в облигации, (руб / пропорция от итоговой суммы)">Базис по выпуску</a></th>


<th colspan="6"><a title="Рыночная стоимость облигации">Значение выпуска</a></th>
<th colspan="3">Результаты</th>
<th colspan="4"><a title="Размер купона 
/ Ставка купона 
/ Период выплаты (количество в год) купонов">Купоны</a></th>

<th rowspan="2">Дата погашения</th>
<th colspan="2">Рейтинг</th>';


	$year_today = date("Y");
	echo '<th colspan="'.(13-date("n")).'" class="color1"  >'.$year_today."</th>";
	echo '<th colspan="12" class="color2"  >'.($year_today+1)."</th>";
	echo '<th colspan="2" class="color3"  >'.($year_today+2)."</th>";





echo '</tr>';

echo '
<tr>
<th>&sum;&nbsp;(₽)</th>   
<th>&Colon;&nbsp;(%)</th>
<th><a title="Портфель количество облигации">шт</a></th>
<th><a title="Средняя цена (Базис / Кол.)">&#956;&nbsp;(%)</th>

<th><a title="Значение (сумма) эмитента в портфеля">&sum;&nbsp;Эм.&nbsp;(₽)</a></th>   
<th><a title="Значение (доля) эмитента в портфеля">&Colon;&nbsp;(%)</a></th>

<th><a title="Значение (сумма) облигации в портфеля">&sum;&nbsp;Об.&nbsp;(₽)</a></th>   
<th><a title="Значение (доля) облигации в портфеля">&Colon;&nbsp;(%)</a></th>

<th><a title="Номинальная стоимость облигации">Ном.&nbsp;(₽)</a></th>
<th><a title="Рыночная стоимость">Рын.&nbsp;(%)</a></th>
<th><a title="Разница цен (&#956; (%) - Рын. (%))">&Delta;&nbsp;(пп)</a></th>





<th><a title="Сумма полученных купонов">&sum;&nbsp;Куп.&nbsp;(₽)</a></th>
<th><a title="возвратность инвестиционных вложений.Сумма купонного дохода / (Инвестировано*Цена пред.дня)">ROI&nbsp;(%)</a></th>

<th><a title="Накомпленный купонный доход">НКД&nbsp;(₽)</a></th>
<th><a title="Размер">₽</a></th>
<th><a title="Ставка">%</a></th>
<th><a title="Количество">шт</a></th>

<th>&nbsp;&nbsp;АКРА&nbsp;&nbsp;</th>
<th>Экперт&nbsp;РА</th>
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
	//~ .date("y.n", strtotime("+$i month", $time))
	$year = date("Y", strtotime("+$i month", $time));
	$month = date("n", strtotime("+$i month", $time));
	$th_content = '';
	$th_style = '';
	//~ if ($y_toggle != $year) {
		//~ $th_content .= $year;
		//~ $y_toggle = $year;
	//~ }
	if ($m_toggle != $month) {		
		$th_content .= ''.$month;
		$m_toggle = $month;
		if ($month == 12)
			$th_style = 'border-right:1px dotted black;';
	}	
	echo '<th class="'.$css_background.'" style="'.$th_style.'" >';	
	echo $th_content;
	echo "</th>";
}
	
//получить сведения о полученных сумме купонов по облигациям из gnucash
$gnucash_bondization = get_gnucash_bondization();	
	
	


echo "</tr>";	
echo "</thead>";
echo "<tbody>";

//~ Сумма по месячно. купоны и пограшение номинала облигации по 
$a_bond_month = array();

$sum_gnucash_bondization = 0;
//~ $sum_portfolio_price = 0;
$avg_couponpercent = array();

$sum_prevlegalcloseprice_emitter =  array_sum($total_prevlegalcloseprice_emitter);
$togle_name = '';
$i_bond=0;
while ($row = $results->fetchArray()) {
		
		if ($row['res_quantity_denom'] > 0) {
			$i_bond++;
			echo '<tr>';
			//~ echo '<td>'.$i_bond.' <a href="https://www.moex.com/ru/issue.aspx?code='.$row['name'].'">'.$row['name'].'</a></td>';
			echo '<td>'.$i_bond.'</td>';
			
			//$bond = '';
			//$bond = get_moex_bond_json($row['name']);
			//if ('RU000A104UA4' == $row['name']) 
			
			//~ $bond = get_moex_bond_json($row['name']);
			
			
			
			
			echo '<td style="white-space: nowrap;">';			
			echo '<a href="https://www.moex.com/ru/issue.aspx?code='.$row['name'].'">';
			if (preg_match("/RU[a-zA-Z0-9]{10}/", $row['name']) ) {
				if ($togle_name != $bond[$row['name']]['REGNUMBER'])
					echo ''.$Core->get_emitter_name($bond[$row['name']]['REGNUMBER'] );
				else
					echo '&#12291;';
			}
			else
				echo $bond[$row['name']]['NAME'];
			echo '</a>';
			echo '</td>';
			
			//Базис,₽ / ∷			
			echo '<td class="number">'			
			.number_format($row['res_value_num'], 2, ',', '&nbsp;')
			.'</td>';

			// Базис доля портфеля
			echo '<td class="number">'			
			.number_format(($row['res_value_num']*100/$total_investment), 2, ',', '&nbsp;')
			.'</td>';
			
			//количество
			echo '<td class="number">'.number_format($row['res_quantity_denom'], 0, ',', '&nbsp;').'</td>';
			
			//средняя μ-Цена,₽
			$bond_avg =  ($row['res_value_num']/$row['res_quantity_denom']*100/$bond[$row['name']]['FACEVALUE']);
			echo '<td class="number">'
			.number_format($bond_avg, 2, ',', '&nbsp;')			
			.'</td>';

			
			//~ сумма по эмитенту
			$emitter_colon = number_format($total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']] * 100 / $sum_prevlegalcloseprice_emitter, 2, ',','&nbsp;');

			if ( $emitter_colon >= 5)
				$css_background = 'color3';
			elseif ( $emitter_colon <= 4 && $emitter_colon >= 3)
				$css_background = 'color1';
			else
				$css_background = 'color2';
				
							
			echo '<td class="number">';
			
			echo ($togle_name != $bond[$row['name']]['REGNUMBER']) 
			? number_format($total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']], 2, ',', '&nbsp;')
			: '&#12291;';

			echo '</td>';
			echo '<td class="number '.$css_background.'">';
			echo ($togle_name != $bond[$row['name']]['REGNUMBER']) 
			? $emitter_colon
			: '&#12291;';
			echo '</td>';
			
			$togle_name = $bond[$row['name']]['REGNUMBER'];
			
			
			


			//Значение (Портфель рыночная стоимость, ₽)
				
			if ($row['res_quantity_denom'] > 0) {				
				//~ $sum_portfolio_price += $bond['PREVLEGALCLOSEPRICE'] * $bond['FACEVALUE'] /100 * $row['res_quantity_denom'];
				echo '<td class="number">'
				.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'], 2, ',', '&nbsp;')
				.'</td>';
				// доля портфеля
				echo '<td class="number">'
				.number_format(($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom']*100/$total_prevlegalcloseprice), 2, ',', '&nbsp;')
				.'</td>';
			}


			
			
			
			//~ номинальная стоимость
			echo '<td class="number">'
			.number_format($bond[$row['name']]['FACEVALUE'], 2, ',', '&nbsp;')
			.'</td>'
			//Рыночная Цена,₽
			.'<td class="number">'
			.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', ' ')
			.'</td>';
			


			//Δ-Цены,₽/п.п
			$plus_minus = '+';
			$css_background = 'color1';
			if (($bond[$row['name']]['PREVLEGALCLOSEPRICE'] -$bond_avg) < 0) {
				$plus_minus = '';
				$css_background = 'color3';						
			}
			echo '<td class="number '.$css_background.'">'
			//~ echo '<span  style="background-color:'.$bond_avg_css.' min-width:25px; display: table-cell; " >'
			.$plus_minus
			.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'] - $bond_avg, 2, ',', '&nbsp;')
			.'</td> ';
			
			
			
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
			.number_format($bond[$row['name']]['COUPONVALUE'], 2, ',', ' ')
			.'</td>'
			//~ ставка купона
			.'<td class="number '.$css_background.'">'
			.$bond[$row['name']]['COUPONPERCENT']
			.'</td>'
			.'<td class="number">'
			.number_format(364/$bond[$row['name']]['COUPONPERIOD'],0,'','')
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

			// рейтинги
			echo '<td>'
			.$CoreAcra->get_acra_rate_emission($row['name'])
			.'</td>';
			echo '<td>';
			echo ( $raexport = $CoreExpertRA->get_raexpert_rate_bond($row['name']) )
			? $raexport
			: '-';
			//~ '<div class="sample4">+</div>'
			//~ ."<script>"
			//~ ."$(function() {"
			//~ ."	$('.sample4').balloon({"
			//~ ."	  html: true,"
			//~ ."	  contents: '$get_raexpert_form_control'"
			//~ ."	});"
			//~ ."});"
			//~ ."</script>";			
			
			echo '</td>';
			
			//~ календарь выплаты купонов
			for ($i=0;  $i < $row['res_quantity_denom']; $i++)
				$avg_couponpercent[] = $bond[$row['name']]['COUPONPERCENT'];
	
			$bondization = get_moex_bond_bondization_json($row['name']);

			if (is_array($bondization)) {		
				$matdate = date('y.n', strtotime($bond[$row['name']]['MATDATE']));
				$css_fin = '';
				for ($i=0;$i<$bondization_period;$i++) {
					
					$yn = date("y.n",strtotime("+$i month", $time) );
					$n = date("n",strtotime("+$i month", $time) );
					$bond_month = $bondization[$yn]['value_rub'];
					
					if (strcmp($matdate,$yn) == 0) {
						$css_fin = 'text-align:center;';
						$a_bond_month[$yn]['bond'] += $bond[$row['name']]['FACEVALUE'];	
					}
					
					if (!is_null( $bond_month)) {
						echo '<td class="number '.$css_background.'" >';
						if ($bond_month*$row['res_quantity_denom'] > 1) {
							echo number_format( $bond_month*$row['res_quantity_denom'], 2, ',', '&nbsp;');
						}
						//~ echo "</br>$n";
						echo '</td>';
						$a_bond_month[$yn]['coupon'] += $bond_month*$row['res_quantity_denom'];	
					}
					else {
						
						echo '<td style="'.$css_fin.'">';
						if (!empty($css_fin))
							echo '&bull;';
							
						echo '</td>';
					}
				}
			}
			echo '</tr>';
		}
}
$db->close();



//~ echo "<tfoot>";


echo '<tr><td colspan="22" style="text-align:right;">Погашение</br>Купоны</td>';

for ($i=0;$i<$bondization_period;$i++) {
	if ($css_background == 'color1') 
		$css_background = 'color2';
	else
		$css_background = 'color1';
	echo '<td class="number '.$css_background.'" style="border-top:1px solid black;">';	
	echo ($ff = $a_bond_month[date("y.n",strtotime("+$i month", $time) )]['bond']) ? number_format($ff, 2, ',', '&nbsp;') : '';	
	echo ($ff = $a_bond_month[date("y.n",strtotime("+$i month", $time) )]['coupon']) ? '</br>'.number_format($ff, 2, ',', '&nbsp;') : '';	
	echo '</td>';
}
echo '</tr>';


echo '<tr><td colspan="2" style="text-align:right;">Базис &sum; (₽)</td><td class="number">'.number_format($total_investment, 2, ',', '&nbsp;').'</td><td colspan="19" ></td></tr>';
echo '<tr><td colspan="2" style="text-align:right;">Значение &sum; (₽)</td><td colspan="6"></td><td class="number">'.number_format($total_prevlegalcloseprice, 2, ',', '&nbsp;').'</td><td colspan="13" ></td></tr>';
echo '<tr><td colspan="2" style="text-align:right;">Результаты &Delta; (₽)</td><td colspan="10"></td><td class="number">'.number_format($total_prevlegalcloseprice-$total_investment, 2, ',', '&nbsp;').'</td><td colspan="9" ></td></tr>';
echo '<tr><td colspan="2" style="text-align:right;">Купоны &sum; (₽)</td><td colspan="11"></td><td class="number">'.number_format($sum_gnucash_bondization, 2, ',', '&nbsp;').'</td><td colspan="8" ></td></tr>';
echo '<tr><td colspan="2" style="text-align:right;">ROI (%)</td><td colspan="12"></td><td class="number">'.number_format($sum_gnucash_bondization / $total_investment * 100, 2, ',', '&nbsp;').'</td><td colspan="7" ></td></tr>';
echo '<tr><td colspan="2" style="text-align:right;">Ставка купона &#956; (%)</td><td colspan="15"></td><td class="number">'.number_format( (array_sum($avg_couponpercent) / count($avg_couponpercent)), 2, ',', '&nbsp;').'</td><td colspan="4" ></td></tr>';

echo "</tbody>";
//~ echo "</tfoot>";
echo "</table>";
echo '</figure>';






//~ echo print_r($bond);



//var_export( $avg_couponvalue );




echo "</body>".PHP_EOL;
echo "</html>".PHP_EOL;


   
			


//~ ======================


//get_moex_bond_price();


// echo "<textarea>";
// foreach ($logs as $line) 
// 	echo $line.PHP_EOL;

// $endtime = microtime(true); // Bottom of page

// printf("Page loaded in %f seconds", $endtime - $starttime );	
// echo "</textarea>";
