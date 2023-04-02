<?php

class CoreMOEX {





	// ПОЛУЧЕНИЕ СВЕДЕНИЙ ОБ АКЦИИ НА МОСБИРЖА
	function get_moex_shares_json($shares_secid) {  
		//$bond_secid = 'RU000A104UA4';
		
		$source_file = 'db/shares/'.$shares_secid.'.json';		
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
		//~ $res['COUPONPERCENT'] 		= $decoded_json['securities']['data'][0][36];   //<th>Ставка купона, %</th>	
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


}

?>
