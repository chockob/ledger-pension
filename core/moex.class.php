<?php

class CoreMOEX {



	// ПОЛУЧЕНИЕ состава индекса MOEXBC (голубые фишки)
	function get_moex_moexbc() {  
		//$bond_secid = 'RU000A104UA4';
		
		$result = '';
		$source_file = 'db/moex_moexbc.json';	
		
		$url = 'https://iss.moex.com/iss/statistics/engines/stock/markets/index/analytics/MOEXBC.jsonp?iss.meta=off&iss.json=extended&start=0&limit=100&lang=ru&iss.meta=off&iss.json=extended&lang=ru';
		
		

		
		//~ print_r($portfolio_bond_isin);
			
		if (file_exists($source_file)) {

			$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			$dateEnd = date_create(date('d.m.Y',time()));
			$dateEnd->setTime(24,0,0);
			$diff = date_diff($dateStart,$dateEnd);
		
			$hours = $diff->h;
			$hours = $hours + ($diff->days*24);
			//~ echo $hours;
			
			//~ if ($diff->format("%h") > 12 ) {
			if ($hours > 24 ) {
					
				$source_json = file_get_contents($url);		
				file_put_contents($source_file, $source_json);
			}		
		}
		else {
			
			$source_json = file_get_contents($url);
			
			file_put_contents($source_file, $source_json);
		}
		
		//~ echo $source_file;
			
		$content_json = file_get_contents($source_file);

		//~ echo $content_json;

		$decoded_json = json_decode($content_json, true);
		
		//~ echo "<pre>";
		//~ echo $decoded_json[1]['analytics'][1]['shortnames'] ;
		//~ foreach ($decoded_json['analytics'] as $item) {
			//~ print_r($item['shortnames'] );
		//~ }
		//~ var_export($decoded_json);
		//~ echo "</pre>";
		

		//~ echo $decoded_json['payload']['news'][0]['announce'];   //<th>Размер купона</th>	
		
		$result .= '<table style="width:10%">';
		$result .= '<caption><a href="https://www.moex.com/ru/index/MOEXBC/constituents/">MOEXBC</a><caption>';
		$result .= '<tr><th>Инструмент</th><th>Вес (%)</th></tr>';
		
		$result_a = array();
		
		usort( $decoded_json[1]['analytics'] , function($a, $b) { //Sort the array using a user defined function
			return $a['weight'] > $b['weight'] ? -1 : 1; //Compare the scores
			
			//~ return $a['weight'] <=> $b['weight'];
		});		
		
		foreach ($decoded_json[1]['analytics'] as $item) {
			//~ if (!empty($item['trade_groups'])) {
				
				
				$result .= '<tr><td>';
				$result .= '<a hre="https://www.moex.com/ru/issue.aspx?code='.$item['ticker'].'">'.$item['shortnames'].'</a>';
				$result .= '</td><td>';
				$result .= $item['weight'];
				$result .= '</td></tr>';
				
				$result_a[$item['weight']] = $result_tmp;
				
				
				
				//~ foreach ( $item['trade_groups'] as $item_trade) {
					
					//~ $css_background = '';
					
					//~ if (in_array(trim($item_trade['ticker']['ticker']), $portfolio_bond_isin))
					
						//~ $css_background = 'color1';
					
					//~ $result .= '<tr><td class="'.$css_background.'">'.$item_trade['ticker']['ticker'];
					//~ $result .= '</td><td class="'.$css_background.'" ><a href="https://www.moex.com/ru/issue.aspx?code='.trim($item_trade['ticker']['ticker']).'" >'.$item_trade['ticker']['name'].'</a>';
					
					//~ $result .= '</td><td class="number '.$css_background.'">';
					//~ if ($item_trade['action'] == 'buy')
						//~ $result .= "&plus;";
					//~ elseif ($item_trade['action'] == 'sell')
						//~ $result .= "&minus;";
					//~ $result .= $item_trade['quantity'];
					//~ $result .= '</td><td class="number '.$css_background.'">'.$item_trade['ticker']['price'];
					//~ $result .= '</td><td class="'.$css_background.'"> <s>'.$item_trade['part_before'].'</s> &roarr; '.$item_trade['part_after'];
					//~ $result .= '</td><td class="number '.$css_background.'">'.number_format(($item_trade['sum'] ), 2, ',', '&nbsp;').'';
					//~ $result .= '</td>';
					//~ $result .= '<td>';

					//~ $bond_yieldscalculator = $this->get_bond_yieldscalculator($item_trade['ticker']['ticker']);
					//~ $result .=  $bond_yieldscalculator['calculated']['DURATIONYEAR'];
					$result .= '</td></tr>';
				//~ }
			//~ }
		}
		
		//~ krsort($result_a);
		
		//~ $result .= implode($result_a);
		$result .= '</table>';
		return $result;
	}	

	

	// КУПОННЫЙ КАЛЕНДАРЬ
	// https://iss.moex.com/iss/securities/RU000A104UA4/bondization.json?iss.json=extended&iss.meta=off&iss.only=coupons&lang=ru&limit=unlimited
	// bondization.json
	function get_moex_bond_bondization_json ($bond_secid, $force = 100) {    	
		
		//$bond_secid = 'RU000A104UA4';
		
		$source_file = 'db/bondization/'.$bond_secid.'.json';	
		if (file_exists($source_file)) {
			//~ $people_json = file_get_contents($source_file);	
			//echo "file<br>";

			$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			$dateEnd = date_create(date('d.m.Y',time()));
			$dateEnd->setTime(24,0,0);
			$diff = date_diff($dateStart,$dateEnd);
		
			if ($diff->format("%a") > $force ) {					
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



	// ПОЛУЧЕНИЕ СВЕДЕНИЙ ОБ АКЦИИ НА МОСБИРЖА
	function get_moex_shares_json($shares_secid) {  
		//$bond_secid = 'RU000A104UA4';

		$BOARDID = 'TQBR';		
		if ($shares_secid == 'TBRU')
			$BOARDID = 'TQTF';		
		
		$source_file = 'db/shares/'.$shares_secid.'.json';		
		if (file_exists($source_file)) {

			$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			$dateEnd = date_create(date('d.m.Y',time()));
			$dateEnd->setTime(24,0,0);
			$diff = date_diff($dateStart,$dateEnd);
		
			if ($diff->format("%a") > 1 ) {
				
				$people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/shares/boards/'.$BOARDID.'/securities/'.$shares_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');		
				file_put_contents($source_file, $people_json);
			}		
		}
		else {
			
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


	//----------------------------------------------------------------------

	// ИНФОРМАЦИЯ ПО ОБЛИГАЦИИ
	// https://iss.moex.com/iss/securities/RU000A104UA4.jsonp?shortname=1&iss.only=description&iss.meta=off&iss.json=extended&lang=ru

	function get_moex_bond_json($bond_secid) {    		
		//$bond_secid = 'RU000A104UA4';
		
		$source_file = 'db/bond/'.$bond_secid.'.json';	
		
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
		
		
		if (preg_match("/SU[a-zA-Z0-9]{10}/", $bond_secid) )
			$res['NAME'] 				= $decoded_json['securities']['data'][0][19];  //<th>Полное наименование</th>	
		else
			$res['NAME'] 				= $decoded_json['securities']['data'][0][20];  //<th>Полное наименование</th>	
		$res['ACCRUEDINT'] 			= $decoded_json['securities']['data'][0][7];  //<th>НКД на дату расчетов</th>
		$res['MATDATE'] 			= $decoded_json['securities']['data'][0][13];  //<th>Дата погашения</th>
		$res['PREVLEGALCLOSEPRICE'] 			= $decoded_json['securities']['data'][0][3];  //<th>Цена пред. дня, % к номиналу</th>
		$res['STATUS'] 			= $decoded_json['securities']['data'][0][12];  //<th>Статус</th>	
		$res['LISTLEVEL'] 			= $decoded_json['securities']['data'][0][34];  //<th>Уровень листинга</th>	
		$res['FACEVALUE'] 			= $decoded_json['securities']['data'][0][10];   //<th>Номинальная стоимость</th>
		$res['COUPONPERIOD'] 	= $decoded_json['securities']['data'][0][15];  //<th>Перио-дичность выплаты купона в год</th>
		
		$res['BOARDNAME'] 		= $decoded_json['securities']['data'][0][19];   //<th>Ставка купона, %</th>	
		
		$res['COUPONPERCENT'] 		= $decoded_json['securities']['data'][0][35];   //<th>Ставка купона, %</th>	
		$res['COUPONVALUE'] 		= $decoded_json['securities']['data'][0][5];   //<th>Размер купона</th>	
		
		
		
		//~ 4B02-04-87154-H-002P
		
		$matches = null;
		//~ $returnValue = preg_match('/^[A-Z0-9]*-[A-Z0-9]*-([A-Z0-9]*-[A-Z0-9]*)/', $decoded_json['securities']['data'][0][31], $matches);
		$returnValue = preg_match('/^[A-Z0-9]*-[A-Z0-9]*-([A-Z0-9]*-[A-Z0-9]*)/', $decoded_json['securities']['data'][0][30], $matches);
		
		$res['REGNUMBER'] 		= $matches[1];   //<th>Размер купона</th>	
		
		//~ $res['REGNUMBER'] 		= $decoded_json['securities']['data'][0][31];   //<th>Размер купона</th>	
		
		
		
		
		return $res;
	}
	
	
	public function get_bond_yieldscalculator($bond_isin) {
		
		$result = '';
		$source_file = 'db/yieldscalculator/'.$bond_isin.'.json';	
		
		
		//~ print_r($portfolio_bond_isin);
		
		$url = 'https://iss.moex.com/iss/apps/bondization/yieldscalculator?accint_source=t0&calc_method=by_price_to_maturity&calc_value=100&secid='.$bond_isin.'&sell_date='.date('Y-m-d', strtotime("+1 day") ).'&sell_value=100&tradedate='.date('Y-m-d');
		        
			
		if (file_exists($source_file)) {

			$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			$dateEnd = date_create(date('d.m.Y',time()));
			$dateEnd->setTime(24,0,0);
			$diff = date_diff($dateStart,$dateEnd);
		
			$hours = $diff->h;
			$hours = $hours + ($diff->days*24);
			//~ echo $hours;
			
			//~ if ($diff->format("%h") > 12 ) {
			if ($hours > 48 ) {
					
				$source_json = file_get_contents($url);		
				file_put_contents($source_file, $source_json);
			}		
		}
		else {
			
			$source_json = file_get_contents($url);		
			
			file_put_contents($source_file, $source_json);
		}
		
		//~ echo $source_file;
			
		$content_json = file_get_contents($source_file);

		//~ echo $content_json;

		$decoded_json = json_decode($content_json, true);
		
		$result = $decoded_json;
		
		//~ print_r($portfolio_bond_isin);
		

		//~ echo $decoded_json['payload']['news'][0]['announce'];   //<th>Размер купона</th>	
		//~ $result .= '<h1>TBRU - Тинькофф Bonds RUB<h1>';
		//~ $result .= '<table style="width:45%">';
		//~ foreach ($decoded_json['payload']['news'] as $item) {
			//~ if (!empty($item['trade_groups'])) {
				//~ $result .= '<tr><th colspan="6">';
				//~ $result .= date("Y.m.d", strtotime($item['date']));
				//~ echo ' '.$item['announce'];
				//~ $result .= '</th></tr>';
				
				
				//~ foreach ( $item['trade_groups'] as $item_trade) {
					
					//~ $css_background = '';
					
					//~ if (in_array(trim($item_trade['ticker']['ticker']), $portfolio_bond_isin))
					
						//~ $css_background = 'color1';
					
					//~ $result .= '<tr><td class="'.$css_background.'">'.$item_trade['ticker']['ticker'];
					//~ $result .= '</td><td class="'.$css_background.'" ><a href="https://www.moex.com/ru/issue.aspx?code='.trim($item_trade['ticker']['ticker']).'" >'.$item_trade['ticker']['name'].'</a>';
					
					//~ $result .= '</td><td class="number '.$css_background.'">';
					//~ if ($item_trade['action'] == 'buy')
						//~ $result .= "&plus;";
					//~ elseif ($item_trade['action'] == 'sell')
						//~ $result .= "&minus;";
					//~ $result .= $item_trade['quantity'];
					//~ $result .= '</td><td class="number '.$css_background.'">'.$item_trade['ticker']['price'];
					//~ $result .= '</td><td class="'.$css_background.'"> <s>'.$item_trade['part_before'].'</s> &roarr; '.$item_trade['part_after'];
					//~ $result .= '</td><td class="number '.$css_background.'">'.number_format(($item_trade['sum'] ), 2, ',', '&nbsp;').'';
					//~ $result .= '</td></tr>';
				//~ }
				
			//~ }
				
		//~ }
		//~ $result .= '</table>';
		
	
		
		return $result;		
		
	}


}

?>
