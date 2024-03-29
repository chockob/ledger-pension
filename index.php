<?php

//~ ini_set('display_errors', 1); 
//~ ini_set('display_startup_errors', 1); 
//~ error_reporting(E_ALL & ~E_NOTICE);

include "./core/core.class.php";
include "./core/raexpert.class.php";
include "./core/acra.class.php";
include "./core/moex.class.php";
include "./core/tinkoff.class.php";


$_CFG['COLON_MAX'] = 0.024;
$_CFG['COLON_MIN'] = 0.021;

$Core = new CoreLedgerPension();

$CoreAcra = new CoreAcra();
$CoreExpertRA = new CoreExpertRA();
$CoreMOEX = new CoreMOEX();
$CoreTinkoff = new CoreTinkoff();



$logs = array();
$time = time();








//~ echo $CoreMOEX->get_moex_moexbc(); 

//~ exit;   



 
 
 

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




//~ var_export($_POST);


//var_export(get_gnucash_bondization());

//var_export(get_moex_bond_bondization_json('RU000A0ZYJT2'));
//exit;






echo $Core->GetHtmlHead();


if ($_POST['sudo'] == 'check_bondization') {

	//~ echo 1111;
	if (!empty($_POST['bond'])) {
		$bond = filter_var ($_POST['bond']);
		echo "<h3>$bond</h3>";
		$CoreMOEX->get_moex_bond_bondization_json($bond, 1);
	}

}


if ($_GET['do'] == 'shares') {

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
	
	//~ ПЛАНОВАЯ ДОЛЯ АКЦИИ В ПОРТФЕЛЕ
	$PortfolioShares = $Core->GetPortfolioShares();
	
	var_export($PortfolioShares);
	
	
	//~ ПОДСЧЕТ ЗНАЧЕНИЯ ИТОГО
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

	$SharesColoneSum = 0;
	$total_prevlegalcloseprice = 0;
	$moex_shares = array();


	$results = $db->query($sql_q);
	while ($row = $results->fetchArray()) {
		
		$moex_shares[$row['name']] = $CoreMOEX->get_moex_shares_json($row['name']);
		//~ var_export($moex_shares);
	
		if (!empty($PortfolioShares['colone'][$row['name']]))
			$SharesColoneSum += $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom'];
		
		if (!empty($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE']))	
			$total_prevlegalcloseprice += $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom'];				
	}

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
	echo '<table style="width:70%;" id="table_editable">';
	echo "<thead>";    
	echo '
	<tr>
	<th rowspan="2">#</th>
	<th rowspan="2">Наименование</th>
	<th colspan="5">Базис</th>
	<th colspan="4">Значение</th>
	<th colspan="6">Результат</th>
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


	<th><a title="Разница (&sum; Базис - &sum;Значение) ">&Delta;&nbsp;(&sum;)&nbsp;(₽)</a></th>

	<th><a title="Разница (&#956;-цена - Цена)">&Delta;&nbsp;(₽)</a></th>
	<th><a title="Разница (&#956;-цена - Цена)">&Delta;&nbsp;(пп)</a></th>

	<th><a title="Сумма полученных дивидендов">&sum;&nbsp;Див.&nbsp;(₽)</a></th>
	<th><a title="Возвратность инвестиционных вложений. Сумма дивидендов / Базис * 100">ROI&nbsp;(%)</a></th>';

	echo '<th><a title="Плановое значение пропорции акции в портфеле">'.array_sum($PortfolioShares['colone']).' &Colon;&nbsp;(%)</a>';
	//~ echo ( array_sum($PortfolioShares['colone']) > 100) ? array_sum($PortfolioShares['colone']) : '';
	echo '</th>';
	echo '<th><a title="Разница &Colon;(План - Значение)">&Delta;&nbsp;(пп)</a></th>


	</tr>';
	echo "</thead>";    
	echo "<tbody>";

	$gnucash_dividendization = $Core->get_gnucash_dividendization();

	$total_gnucash_dividendization =0;



	$results = $db->query($sql_q);
	while ($row = $results->fetchArray()) {
		if ($row['res_quantity_denom'] > 0) {
		
		//~ $moex_shares = get_moex_shares_json($row['name']);
		
		//~ if (!in_array($row['name'], array('TBRU'))) {
		
			echo '<tr>';

			$css_background_portfolio = '';
			if (!empty($PortfolioShares['colone'][$row['name']]))
				$css_background_portfolio = 'color1';
			
			
			echo '<td class="'.$css_background_portfolio.'">'.$row['name'].'</td>';
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
				$daybuy = $Core->get_gnucash_last_daybuy_shares($row['name']);
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
				

				if (!empty($PortfolioShares['colone'][$row['name']]))						
					$portfolio_shares_colone = ($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom']*100/$SharesColoneSum);
				else
					$portfolio_shares_colone = ($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom']*100/$total_prevlegalcloseprice);
				
				$colone_pp = $portfolio_shares_colone-$PortfolioShares['colone'][$row['name']];
				$css_background_colone = '';
				if (!empty($PortfolioShares['colone'][$row['name']]))
					if (  $colone_pp < 0.69 && $colone_pp > -0.69)
						$css_background_colone = 'color1';
					elseif (  $colone_pp < 0.99 && $colone_pp > -0.99)
						$css_background_colone = 'color2';
					else
						$css_background_colone = 'color3';
						
				
				echo '<td class="number '.$css_background_colone.'">';
				echo ($row['res_quantity_denom'] > 0) 
				? number_format($portfolio_shares_colone, 2, ',', '&nbsp;') 
				: '';
				echo '</td>';
			}
			else
				echo '<td class="number"></td><td class="number"></td>';
			
				
			
				
				//цена пред.дня			
				echo '<td class="number">'
				.number_format($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], $moex_shares[$row['name']]['DECIMALS'], ',', '&nbsp;')
				.'</td>';
				
				//стоимость лота	
				echo '<td class="number">'
				.number_format($moex_shares[$row['name']]['LOTSIZE'] * $moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', '&nbsp;')
				.'</td>';
				//~ //лотность	
				//~ echo '<td class="number">';			
				//~ if ($moex_shares[$row['name']]['LOTSIZE'] > 1000)
					//~ echo number_format($moex_shares[$row['name']]['LOTSIZE']/1000, 0, ',', '&nbsp;').'k';
				//~ else
					//~ echo number_format($moex_shares[$row['name']]['LOTSIZE'], 0, ',', '&nbsp;');
				//~ echo '</td>';

			if ($row['res_quantity_denom'] > 0) {
				
				
				$shares_profit = ($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] - $shares_avg);
				$shares_profit_pp = $shares_profit *100 / $shares_avg;
				$avg_pm = '';
				$css_background = 'color3';
				if ( $shares_profit_pp > -10 && $shares_profit_pp < 0 ) {
					$css_background = 'color2';
				}
				elseif ( $shares_profit_pp >= 0  ) {
					$css_background = 'color1';
				}				
				//~ elseif (  ) {
					//~ $avg_pm = '+';
					
				//~ }
				
				//~ разница Базис - Значение
				echo '<td class="number '.$css_background.'">';
				echo number_format( ($moex_shares[$row['name']]['PREVLEGALCLOSEPRICE'] * $row['res_quantity_denom']) - $row['res_value_num'], 2, ',', '&nbsp;');
				//~ echo number_format($shares_profit, $moex_shares[$row['name']]['DECIMALS'], ',', '&nbsp;')
				echo '</td>';
				
				
				// Портфель Δ-цена акции, ₽		
				echo '<td class="number '.$css_background.'">'
				.$avg_pm.number_format($shares_profit, $moex_shares[$row['name']]['DECIMALS'], ',', '&nbsp;')
				.'</td>'
				// Портфель Δ-цена акции, %	
				.'<td class="number '.$css_background.'">'
				.$avg_pm.number_format($shares_profit_pp, 1, ',', ' ')
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
			if ($row['res_quantity_denom'] > 0) {
				if (!empty($gnucash_dividendization[$row['name']]) & !empty($row['res_value_num'])) {
					echo number_format($gnucash_dividendization[$row['name']] /  $row['res_value_num'] *100, 2, ',', ' ');
				}
			}
			echo '</td>';
			
			//~ плановая доля
		
			echo '<td class="number">';
			echo $PortfolioShares['colone'][$row['name']];
			echo '</td>';
			
			//~ плановая доля (разница пп)
			echo '<td class="number '.$css_background_colone.'">';
			if (!empty($PortfolioShares['colone'][$row['name']])) {
				if ($colone_pp > 0)
					echo '&plus;';
				echo number_format($colone_pp,2,',','&nbspl');
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
	}
	echo "</tbody>";
	echo "</table>";  

	echo '<table style="width:15%;">';  
	echo "<caption>Результаты</caption>";
	//echo "<tr>";
	//echo '<th></th><th>&sum;</th>';
	//echo "</tr>";
	echo "<tr>";
	echo '<td>Базис инвестиций</td><td class="number">&sum;</td><td class="number">'.number_format($total_investment, 2, ',', ' ').'</td>';
	echo "</tr>";

	echo "<tr>";
	echo '<td>Значение инвестиций</td><td class="number">&sum;</td><td class="number"> '.number_format($total_prevlegalcloseprice, 2, ',', ' ').'</td>';
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
	
	
	echo "<tr>";
	echo '<td>Значение портфеля</td><td class="number">&sum;</td><td class="number"> '.number_format($SharesColoneSum, 2, ',', ' ').'</td>';
	echo "</tr>";	
	
	
	echo '<td>ROI</td><td class="number">%</td><td class="number">'.number_format($total_gnucash_dividendization / $total_investment * 100, 2, ',', ' ').'</td>';
	//echo '<th></th><th></th>';
	//echo '<th></th><th></th>';
	//echo '<th></th><th></th>';
	//echo '<th class="number">'.number_format( (array_sum($avg_couponpercent) / count($avg_couponpercent)), 2, ',', ' ').'</th>';

	echo "</tr>";
	echo "</table>";   
	

	echo $CoreMOEX->get_moex_moexbc(); 	
	
	
}





if ($_GET['do'] == 'bonds') {
	/*-------------------------------------
	ОБЛИГАЦИИ
	-------------------------------------*/


	//~ новости покупки/продажи активов в БПИФ TBRU
	$tbru = $CoreTinkoff->get_tbru_news($portfolio_bond_isin);
	


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
	
	$portfolio_bond_isin = array();
	
	$bond = array();
	$results = $db->query($sql_q);
	while ($row = $results->fetchArray()) {
		//~ $bond[$row['name']] = $CoreMOEX->get_moex_bond_json($row['name']);	
		$bond[$row['name']] = $CoreMOEX->get_moex2_bond_default($row['name']);	
		
		
		
		
		//~ echo $bond[$row['name']]['REGNUMBER'];
		if (!empty($row['res_quantity_denom']))
			array_push($portfolio_bond_isin, $row['name']);
			
		$total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']] += $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
		$total_prevlegalcloseprice += $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
	}

//~ print_r($portfolio_bond_isin);





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

	$body_cont  = '';
	$body_cont .= "<h1>Облигации</h1>";		

	$body_cont .= '<table>';    
	$body_cont .= "<thead>";    
	$body_cont .= '

	<tr>
	<th rowspan="2">#</th>
	<th rowspan="2">Наименование</th>
	<th colspan="3" title="Базис. Сумма инвестированных средств в облигации, (руб / пропорция от итоговой суммы)" >Базис</th>

<!--
	<th colspan="2" title="Доля эмитента в портфеле">Эмитент</th>
-->	
	<th colspan="6" title="Рыночная стоимость облигации">Значение</th>
	<th colspan="5">Результаты</th>
	<th colspan="4" title="Размер купона 
	/ Ставка купона 
	/ Период выплаты (количество в год) купонов">Купоны</th>

	<th rowspan="2">Погашение
	(Оферта)</th>
	<th rowspan="2" title="AAA(RU)

	AA+(RU)
	AA(RU)
	AA-(RU)

	A+(RU)
	A(RU)
	A-(RU)
	">АКРА
	Экперт&nbsp;РА</th>
	
	<th colspan="2">Дюрация</th>
	';


		$year_today = date("Y");
		$body_cont .= '<th colspan="'.(13-date("n")).'" class="color1" style="font-size:80%" >'.$year_today."</th>";
		$body_cont .= '<th colspan="12" class="color2" style="font-size:80%"  >'.($year_today+1)."</th>";
		$body_cont .= '<th colspan="4" class="color3" style="font-size:80%" >'.($year_today+2)."</th>";





	$body_cont .= '</tr>';

	$body_cont .= '
	<tr>
	<th class="btn_tx1" value="&sum;&nbsp;(₽)" content="b1" >&sum;&nbsp;(₽)</th>   
<!--	<th class="btn_tx1" value="&Colon;&nbsp;(₽)" content="b2" >&Colon;&nbsp;(%)</th>
-->
	<th class="btn_tx1" value="шт" content="b3" title="Портфель количество облигации">шт</th>
	<th class="btn_tx1" value="&#956;&nbsp;(%)" content="b4" title="Средняя цена (Базис / Кол.)">&#956;&nbsp;(%)</th>

<!--
	<th class="btn_tx1" value="&sum;&nbsp;Эм.&nbsp;(₽)" content="z1" title="Значение (сумма) эмитента в портфеля">&sum;&nbsp;&nbsp;(₽)</th>   
	<th class="btn_tx1" value="&Colon;&nbsp;(%)" content="z2"  title="Значение (доля) эмитента в портфеля">&Colon;&nbsp;(%)</th>
-->
	<th class="btn_tx1" value="&sum;&nbsp;Об.&nbsp;(₽)" content="z3" title="Значение (сумма) облигации в портфеля">&sum;&nbsp;&nbsp;(₽)</th>   
	<th class="btn_tx1" value="&Colon;&nbsp;(%)" content="z4" title="Значение (доля) облигации в портфеля">&Colon;&nbsp;(%)</th>
	<th class="btn_tx1" value="Ном.&nbsp;(₽)" content="z5"  title="Номинальная стоимость облигации (в тысячах)">Н.&nbsp;(₽)</th>
	<th class="btn_tx1" value="Рын.&nbsp;(%)" content="z6"  title="Рыночная стоимость">Рын.&nbsp;(%)</th>
	<th class="btn_tx1" value=""   title="Доходность по средневзвешенной цене, % годовых">Дох.&nbsp;(%/г)</th>
	<th class="btn_tx1" value=""   title="">G/Z-spread</th>

	<th class="btn_tx1" value="&Delta;&nbsp;(пп)" content="r1" title="Разница цен (&#956; (%) - Рын. (%))">&Delta;&nbsp;(пп)</th>
	<th class="btn_tx1" value="&Delta;&nbsp;(пп)" content="r1" title="Разница: Знач.Рын (₽) - Базис (₽)">&Delta;&nbsp;(₽)</th>
	<th class="btn_tx1" value="&sum;&nbsp;Куп.&nbsp;(₽)" content="r2" title="Сумма полученных купонов">&sum;&nbsp;К.&nbsp;(₽)</th>
	<th class="btn_tx1" value="ROI&nbsp;(%)" content="r3" title="возвратность инвестиционных вложений.Сумма купонного дохода / (Инвестировано*Цена пред.дня)">ROI&nbsp;(%)</th>
	
	<th class="btn_tx1" value="" content="r3" title="Профит">Profit(₽)</th>	

	<th><a title="Накомпленный купонный доход">НКД&nbsp;(₽)</a></th>
	<th><a title="Размер">₽</a></th>
	<th><a title="Ставка">%</a></th>
	<th><a title="Количество">шт</a></th>
	<th>г.</th>
	<th>дата</th>
	
';

	$starttime = microtime(true); // Top of page

	$bondization_period = 24;
	$css_background = '';
	$y_toggle ='';
	$m_toggle ='';
	$year = date("Y");
	for ($i = date("n"); $i < ($bondization_period + date("n")); $i++)	{	
		$css_background = 'color1';	
		if ( $i > 24) {
			$month = $i - 24;
			$css_background = 'color3';
		}
		elseif ( $i > 12 ) {
			$month = $i - 12;
			$css_background = 'color2';
		}
		else
			$month = $i;
				
		$body_cont .= '<th class="'.$css_background.'" style="'.$th_style.'">';	
		//~ $body_cont .= $i.'</br>'.$month.'</br>'.$year;
		$body_cont .= $month;
		$body_cont .= "</th>";
	}
		
	//получить сведения о полученных сумме купонов по облигациям из gnucash
	$gnucash_bondization = $Core->get_gnucash_bondization();	
		
		


	$body_cont .= "</tr>";	
	$body_cont .= "</thead>";
	$body_cont .= "<tbody>";

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
				

				
				
				$body_cont .= '<tr>';		
				//~ код инструмента ISIN
				
				
				$css_background = '';				
				if ($tbru['CONTENT_ARRAY'][$row['name']] == 'buy')
					$css_background = 'color1';
				elseif ($tbru['CONTENT_ARRAY'][$row['name']] == 'sell')
					$css_background = 'color3';				
					
				
				$body_cont .= '<td class="'.$css_background.'"">'.$row['name'].'</td>';
				
				//$bond = '';
				//$bond = get_moex_bond_json($row['name']);
				//if ('RU000A104UA4' == $row['name']) 
				
				//~ $bond = get_moex_bond_json($row['name']);
				
				
				
				//~ наименование инструмента
				$body_cont .= '<td style="white-space: nowrap;" class="'.'"">';			
				$body_cont .= '<a href="https://www.moex.com/ru/issue.aspx?code='.$row['name'].'">';
				//~ if (preg_match("/RU[a-zA-Z0-9]{10}/", $row['name']) ) {
					//~ $body_cont .= ''.$bond[$row['name']]['BOARDNAME'];	
				//~ }
				//~ else
					$body_cont .= $bond[$row['name']]['NAME'];
				$body_cont .= '</a>';			
				$body_cont .= '</td>';
				

				//~ доля портфеля
				$portfolio_bond_colone = ($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom']*100/$total_prevlegalcloseprice);				
				$css_background = '';				
				if ( $portfolio_bond_colone > ($_CFG['COLON_MAX'] * 100))
					$css_background = 'color3';
				
				
				//Базис, сумма ₽ / ∷			
				$body_cont .= '<td class="number '.$css_background.'"" >'.'<span class="b1">';
				$body_cont .= number_format($row['res_value_num'], 2, ',', '&nbsp;');	
				$body_cont .= '</span></td>';

				// Базис доля портфеля
				//~ $body_cont .= '<td class="number" >'			;
				//~ .'<span class="b2">';
				//~ $body_cont .= ( $row['res_value_num'] > 0 )
				//~ ? number_format(($row['res_value_num']*100/$total_investment), 2, ',', '&nbsp;')
				//~ : '&nbsp;';
				//~ $body_cont .= '</span></td>';
				
				//количество
				$body_cont .= '<td class="number '.$css_background.'">'
				.'<span class="b3">';
				$body_cont .= ( $row['res_value_num'] > 0 )
				? number_format($row['res_quantity_denom'], 0, ',', '&nbsp;')
				: '&nbsp;';
				$body_cont .= '</span></td>';
				
				//средняя μ-Цена,₽
				$bond_avg =  ($row['res_value_num']/$row['res_quantity_denom']*100/$bond[$row['name']]['FACEVALUE']);
				$body_cont .= '<td class="number '.$css_background.'">'
				.'<span class="b4">';
				$body_cont .= ( $row['res_value_num'] > 0 )
				? number_format($bond_avg, 2, ',', '&nbsp;')			
				: '&nbsp;';
				$body_cont .= '</span></td>';

				
				//~ в отдельный субинфо
				//~ сумма по эмитенту
				//~ $emitter_colon = number_format($total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']] * 100 / $sum_prevlegalcloseprice_emitter, 2, ',','&nbsp;');
				//~ $body_cont .= '<td class="number">'
				//~ .'<span class="z1">';				
				//~ $body_cont .= ($togle_name != $bond[$row['name']]['REGNUMBER']) 
				//~ ? number_format($total_prevlegalcloseprice_emitter[$bond[$row['name']]['REGNUMBER']], 2, ',', '&nbsp;')
				//~ : '&#12291;';
				//~ $body_cont .= '</span>';
				//~ $body_cont .= '</td>';
				
				//~ доля (суммы) по эмитенту
				//~ $body_cont .= '<td class="number">'
				//~ .'<span class="z2">';
				//~ $body_cont .= ($togle_name != $bond[$row['name']]['REGNUMBER']) 
				//~ ? $emitter_colon
				//~ : '&#12291;';
				//~ $body_cont .= '</span>';
				//~ $body_cont .= '</td>';
				
				$togle_name = $bond[$row['name']]['REGNUMBER'];
				
				
				


				//Значение ∑  (₽)
				
				$Value_SumRub = $bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom'];
				
				$body_cont .= '<td class="number '.$css_background.'">'
				.'<span class="z3">'
				.number_format($Value_SumRub, 2, ',', '&nbsp;')
				.'</span>'
				.'</td>';
				
								
				//~ доля портфеля
				
				//~ $portfolio_bond_colone = ($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom']*100/$total_prevlegalcloseprice);
				
				//~ $css_background = '';				
				//~ if ( $portfolio_bond_colone > ($_CFG['COLON_MAX'] * 100))
					//~ $css_background = 'color3';
				
				$body_cont .= '<td class="number '.$css_background.'">'
				.'<span class="z4">'
				.number_format($portfolio_bond_colone, 2, ',', '&nbsp;')
				.'</span>'
				.'</td>';
	


				
				
				
				//~ номинальная стоимость
				$body_cont .= '<td class="number">'
				.'<span class="z5">';
				
				if ($bond[$row['name']]['FACEVALUE'] == 1000) {
					$body_cont .= '1k';
				}
				else {
					$body_cont .= '~';
					$body_cont .= number_format($bond[$row['name']]['FACEVALUE'], 0, ',', '&nbsp;');
				}
				
				$body_cont .= '</span>'
				.'</td>'
				
				//Рыночная Цена,₽
				.'<td class="number">'
				.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'], 2, ',', ' ')
				.'</td>';
				
				//~ Дох. (%/г)
				$body_cont .= '<td class="number">'				
				.number_format($bond[$row['name']]['EFFECTIVEYIELDWAPRICE'], 2, ',', ' ')
				.'</td>';
				
				
				//~ Дох. (%/г)
				$body_cont .= '<td class="number">'				
				.number_format($bond[$row['name']]['GSPREADBP'], 0, ',', ' ').'/'
				.number_format($bond[$row['name']]['ZSPREADBP'], 0, ',', ' ')
				.'</td>';
				
				


				//Δ (пп)
				$css_background = '';
				if ($row['res_value_num'] > 0 ) { 
					$plus_minus = '+';
					$css_background = 'color1';
					if (($bond[$row['name']]['PREVLEGALCLOSEPRICE'] -$bond_avg) < 0) {
						$plus_minus = '';
						$css_background = 'color3';						
					}
				}
				
				$body_cont .= '<td class="number '.$css_background.'">';
				$body_cont .= $plus_minus.number_format($bond[$row['name']]['PREVLEGALCLOSEPRICE'] - $bond_avg, 2, ',', '&nbsp;');				
				$body_cont .= '</td> ';
				
				//~ Δ (₽)
				$body_cont .= '<td class="number '.$css_background.'">'.'<span class="r1">';				
				$body_cont .= $plus_minus.number_format(($bond[$row['name']]['PREVLEGALCLOSEPRICE'] * $bond[$row['name']]['FACEVALUE'] /100 * $row['res_quantity_denom']) - $row['res_value_num'], 2, ',', '&nbsp;');

				$body_cont .= '</td> ';
				
				
				
				//∑ купонов,₽
				$Value_BondizationSumRub = '';
				
				 
				
				$body_cont .= '<td class="number">';
				if ($Value_BondizationSumRub = $gnucash_bondization[$row['name']] ) {
					$body_cont .= number_format($Value_BondizationSumRub, 2, ',', ' ');
					$sum_gnucash_bondization += $Value_BondizationSumRub;
				}
				$body_cont .= '</td>';
				
				
			
				//if (($bond['PREVLEGALCLOSEPRICE'] -$bond_avg) < 0)
					//$bond_avg_css = 'style="color: red; "';			
				
				//echo '<td  class="number" '.$bond_avg_css.'>'.number_format($bond['PREVLEGALCLOSEPRICE'] - $bond_avg, 2, ',', ' ').'';
				//echo '</td> ';
				
				// ROI
				$body_cont .= '<td class="number">'
				.'<span class="r3">';
				if (!empty($Value_BondizationSumRub)) {
					$body_cont .= number_format(
					($Value_BondizationSumRub / ($row['res_value_num']* ($bond[$row['name']]['PREVLEGALCLOSEPRICE']/100) )  *100 ) , 2, ',', '&nbsp;'
					);
				}
				$body_cont .= '</span>';
				$body_cont .= '</td>';
				
				//~ Profit(₽)
				$body_cont .= '<td class="number">'
				.number_format($Value_SumRub + $Value_BondizationSumRub + $bond[$row['name']]['ACCRUEDINT'] - $row['res_value_num'] , 2, ',', '&nbsp;')
				.'</td>';
		
				//~ НКД (₽)
				$body_cont .= '<td class="number">'
				.number_format($bond[$row['name']]['ACCRUEDINT'],2,',',' ')
				.'</td>';		
				
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
				$body_cont .= '<td class="number">'
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
				$css_background = 'color3';
				if (strtotime("+1 year", $time) > strtotime($bond[$row['name']]['MATDATE']) )
					$css_background = 'color1';
				elseif (strtotime("+2 year", $time) > strtotime($bond[$row['name']]['MATDATE']) )
					$css_background = 'color2';					

				$css_background_cupon = $css_background;
				$body_cont .= '<td class="'.$css_background.'" >'.date('d.m.Y', strtotime($bond[$row['name']]['MATDATE'])).'</td>';

				//~ рейтинг АКРА				
				$acra = $CoreAcra->get_acra_rate_emission($row['name']);
				$body_cont .= '<td>'.$acra;
				//~ Рейтинг Эксперт РА
				$raexport = $CoreExpertRA->get_raexpert_rate_bond($row['name']);
				$body_cont .= $raexport.'</td>';
				
				
				//~ дюрация
				$bond_yieldscalculator = $CoreMOEX->get_bond_yieldscalculator($row['name']);
				
				$portfolio_bond_duration[] = $bond_yieldscalculator['calculated']['DURATIONYEAR'];
				
				$body_cont .= '<td class="'.$css_background.'">';
				
				$body_cont .= number_format( $bond_yieldscalculator['calculated']['DURATIONYEAR'] , 1, ',', '&nbsp;');
				$body_cont .= '</td>';
				$body_cont .= '<td>';
				$body_cont .= date('d.m.Y', strtotime('+'.$bond_yieldscalculator['calculated']['DURATION'].' day') );
				$body_cont .= '</td>';

				
				
				//~ календарь выплаты купонов
				for ($i=0;  $i < $row['res_quantity_denom']; $i++)
					$avg_couponpercent[] = $bond[$row['name']]['COUPONPERCENT'];
		
				$bondization = $CoreMOEX->get_moex_bond_bondization_json($row['name']);

				if (is_array($bondization)) {		
					$matdate = date('y.n', strtotime($bond[$row['name']]['MATDATE']));
					$css_fin = '';

					$year = date("Y") ;
					for ($i = date("n"); $i < ($bondization_period + date("n")); $i++)	{	
					
					//~ for ($i=0;$i<$bondization_period;$i++) {
						
						//~ $yn = date("y.n",strtotime("+$i month", $time) );
						//~ $n = date("n",strtotime("+$i month", $time) );

						if ( $i > 24) {
							$month = $i - 24;
							$yn = date("y.n",strtotime(($year+2-2000)."-$month-1") );
							$n = "$month";
						} elseif ( $i > 12 ) {
							$month = $i - 12;
							$yn = date("y.n",strtotime(($year+1-2000)."-$month-1") );
							$n = "$month";
						} else {
							$month = $i;		
							$yn = ($year-2000).".$month";
							$n = "$month";
						}
						
						$bond_month = $bondization[$yn]['value_rub'];
						
						if (strcmp($matdate,$yn) == 0) {
							$css_fin = 'text-align:center;';
							$a_bond_month[$yn]['bond'] += $bond[$row['name']]['FACEVALUE'];	
						}
						
						if (!is_null( $bond_month)) {
							$body_cont .= '<td class="number '.$css_background_cupon.'" >';
							if ($bond_month*$row['res_quantity_denom'] > 1) {
								$body_cont .= number_format( $bond_month*$row['res_quantity_denom'], 2, ',', '&nbsp;');
							}
							
							//~ $body_cont .= '</br>'.$yn;
							$body_cont .= '</td>';
							$a_bond_month[$yn]['coupon'] += $bond_month*$row['res_quantity_denom'];	
						}
						else {
							
							$body_cont .= '<td style="'.$css_fin.'">';
							if (!empty($css_fin))
								$body_cont .= '&bull;';
							//~ $body_cont .= '</br>'.$yn;	
							$body_cont .= '</td>';
						}
					}
				}
				$body_cont .= '</tr>';
			}
	}
	$db->close();



	//~ echo "<tfoot>";


	$body_cont .= '<tr><td colspan="24" style="text-align:right;">Погашение</br>Купоны</td>';

	$year = date("Y");
	for ($i = date("n"); $i < ($bondization_period + date("n")); $i++)	{	


	//~ for ($i=0;$i<$bondization_period;$i++) {
		
		//~ $portfolio_redemption = $a_bond_month[date("y.n",strtotime("+$i month", $time) )]['bond'];
		//~ $portfolio_coupon = $a_bond_month[date("y.n",strtotime("+$i month", $time) )]['coupon'];

		if ( $i > 24) {
			$month = $i - 24;
			$yn = date("y.n",strtotime(($year+2-2000)."-$month-1") );
			$n = "$month";
		} elseif ( $i > 12 ) {
			$month = $i - 12;
			$yn = date("y.n",strtotime(($year+1-2000)."-$month-1") );
			$n = "$month";
		} else {
			$month = $i;		
			$yn = ($year-2000).".$month";
			$n = "$month";			
		}
		$portfolio_redemption = $a_bond_month[$yn]['bond'];
		$portfolio_coupon = $a_bond_month[$yn]['coupon'];
		


		
		$css_background = '';
		if ($portfolio_coupon >= 1000) 
			$css_background = 'color1';
		elseif ($portfolio_coupon >= 500) 
			$css_background = 'color2';
		//~ elseif ($portfolio_coupon >= 500) 
			//~ $css_background = 'color2';
		
		
		$body_cont .= '<td class="number '.$css_background.'" style="border-top:1px solid black;">';	
		//~ $body_cont .= $yn.'</br>';
		$body_cont .= $portfolio_redemption ? number_format($portfolio_redemption, 2, ',', '&nbsp;') : '';	
		$body_cont .= $portfolio_coupon ? '</br>'.number_format($portfolio_coupon, 2, ',', '&nbsp;') : '';	
		$body_cont .= '</td>';
	}
	$body_cont .= '</tr>';
	$body_cont .= "</tbody>";
	//~ echo "</tfoot>";
	$body_cont .= "</table>";

	echo '<table style="width:15%;">';
	echo '<tr><td colspan="2" style="text-align:right;">Базис &sum; (₽)</td><td class="number">'.number_format($total_investment, 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">Значение &sum; (₽)</td><td class="number">'.number_format($total_prevlegalcloseprice, 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">Результаты &Delta; (₽)</td><td class="number">'.number_format($total_prevlegalcloseprice-$total_investment, 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">Купоны &sum; (₽)</td><td class="number">'.number_format($sum_gnucash_bondization, 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">ROI (%)</td><td class="number">'.number_format($sum_gnucash_bondization / $total_investment * 100, 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">Ставка купона &#956; (%)</td><td class="number">'.number_format( (array_sum($avg_couponpercent) / count($avg_couponpercent)), 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">'.($_CFG['COLON_MAX']*100).'% &Colon; (₽)</td><td class="number">'.number_format( $total_prevlegalcloseprice * ($_CFG['COLON_MAX']+1) * $_CFG['COLON_MAX'] , 2, ',', '&nbsp;').'</td></tr>';
	echo '<tr><td colspan="2" style="text-align:right;">Дюрация &#956; (г)</td><td class="number">'.number_format( array_sum($portfolio_bond_duration)/count($portfolio_bond_duration) , 4, ',', '&nbsp;').'</td></tr>';
	echo "</table>";
	
	
	
	
	echo '<form action="./index.php?do=bonds" method="POST">';
	echo '<p>График выплаты купонов</p>';
	echo '<input type="text" name="bond" value="">';
	echo '<input type="hidden" name="sudo" value="check_bondization">';
	echo '<input type="submit" value="Обновить">';
	echo '</form>';
	
	
	
	
	echo $body_cont;



	
	//~ print_r($portfolio_bond_isin);
	
	//~ новости покупки/продажи активов в БПИФ TBRU
	$tbru = $CoreTinkoff->get_tbru_news($portfolio_bond_isin);
	echo $tbru['CONTENT'];

}




//~ echo print_r($bond);



//var_export( $avg_couponvalue );



	


	echo $Core->GetHtmlFoot();
   
			


//~ ======================


//get_moex_bond_price();


// echo "<textarea>";
// foreach ($logs as $line) 
// 	echo $line.PHP_EOL;

// $endtime = microtime(true); // Bottom of page

// printf("Page loaded in %f seconds", $endtime - $starttime );	
// echo "</textarea>";
