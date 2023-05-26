<?php




class CoreLedgerPension {


	function GetHtmlHead() {
		$cont =	'';
		$cont .= "<!doctype html>".PHP_EOL;
		$cont .= '<html data-theme="dark">'.PHP_EOL;
		$cont .= '<head>

		 <meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			
			
			<link rel="stylesheet" href="style.css">
			
			<script src="./js/jquery-3.6.0.min.js"></script>

			
			<script src="./js/table.js"></script>			
		';

		//~ echo "
		//~ <link rel=\"stylesheet\" href=\"./style.css\">


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


		$cont .= "</head>".PHP_EOL;
		$cont .= "<body>".PHP_EOL;
		
		$cont .= '<p><a href="./index.php?do=shares">Акции</a> | <a href="./index.php?do=bonds">Облигации</a></p>'.PHP_EOL;
		
		return $cont;
	}
	
	function GetHtmlFoot() {	
		$cont = '';
		$cont .= "</body>".PHP_EOL;
		$cont .= "</html>".PHP_EOL;
		return $cont;
	}


	// ПОЛУЧЕНИЕ ПЛАНОВОЙ ДОЛИ В ПОРТФЕЛЕ
	function GetPortfolioShares() {  
		//$bond_secid = 'RU000A104UA4';
		
		$source_file = 'cfg/shares.json';		
		//~ if (file_exists($source_file)) {

			//~ $dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			//~ $dateEnd = date_create(date('d.m.Y',time()));
			//~ $dateEnd->setTime(24,0,0);
			//~ $diff = date_diff($dateStart,$dateEnd);
		
			//~ if ($diff->format("%a") > 1 ) {
				//~ $BOARDID = 'TQBR';		
				//~ $people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/shares/boards/'.$BOARDID.'/securities/'.$shares_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');		
				//~ file_put_contents($source_file, $people_json);
			//~ }		
		//~ }
		//~ else {
			//~ $BOARDID = 'TQBR';		
			//~ $people_json = file_get_contents('https://iss.moex.com/iss/engines/stock/markets/shares/boards/'.$BOARDID.'/securities/'.$shares_secid.'.jsonp?iss.meta=off&iss.only=securities&lang=ru');		
			//~ file_put_contents($source_file, $people_json);
		//~ }
			

		$source_cont = file_get_contents($source_file);	
		
		//~ print_r($source_cont);
			
		$json_cont = json_decode($source_cont, true);		
		//~ print_r($json_cont['colone']);
		
		//~ $res = array();	
		//~ if (!empty($json_cont)) {
			//~ $res['NAME'] 				= $decoded_json['securities']['data'][0][9];  //<th>Полное наименование</th>	
			//~ $res['LOTSIZE'] 			= $decoded_json['securities']['data'][0][4];  //<th>	Количество ценных бумаг в одном стандартном лоте</th>
			//~ $res['DECIMALS'] 			= $decoded_json['securities']['data'][0][8];  //<th>Точность, знаков после запятой</th>
			//~ $res['PREVLEGALCLOSEPRICE'] 			= $decoded_json['securities']['data'][0][22];  //<th>Официальная цена закрытия предыдущего дня, рассчитываемая по методике ФСФР</th>
		//~ }
		
		
		return $json_cont;
		
		
	}

	//~ Получить наименование эмитента по из кода облигации

	function get_emitter_name($emitter_id) {
		
		//~ $emitter_id = '16643-A';
			
		$source_file = 'db/emitter/'.$emitter_id.'.json';	
		
		$url = 'https://www.cbr.ru/registries/rcb/ecb/?UniDbQuery.Posted=True&UniDbQuery.SPhrase='.$emitter_id.'&UniDbQuery.SearchType=4';
		
		$html = '';
		$result = 'EMPTY';
		if (!empty($emitter_id)) {	
			if (file_exists($source_file)  ) {
				
				$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
				$dateEnd = date_create(date('d.m.Y',time()));			
				$dateEnd->setTime(24,0,0);

				$diff = date_diff($dateStart,$dateEnd);
				if ( $diff->format("%a") > 10 ) {
					$html = file_get_contents($url);	
					//~ file_put_contents($source_file, $html);
				}
				else
					$result = file_get_contents($source_file);			
			}	
			else {				
				$html = file_get_contents($url);	
				//~ file_put_contents($source_file, $html);
			}
		}
		//~ echo '==='.$emitter_id.'</br>';
		
		if (!empty($html)) {
			
			$doc = new DOMDocument();
			$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);

			$elements = $doc->getElementsByTagName('table');
			if (!is_null($elements)) {
				$td = $elements[0]->getElementsByTagName('td');
				if (!is_null($td)) {
				
						   
					//~ echo $td[2]->nodeValue;  
					//~ echo $td[3]->nodeValue;  
					//~ file_put_contents($source_file, $td[2]->nodeValue);  
					//~ $result = $td[2]->nodeValue;	
					
					$patterns = array(
						'/ОТКРЫТОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО/',
						'/ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ/', 
						"/ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО/", 
						"/АКЦИОНЕРНОЕ ОБЩЕСТВО/", 
						"/ГОСУДАРСТВЕННОЕ УНИТАРНОЕ ПРЕДПРИЯТИЕ/"						
					);
					$replacements = array(
						"ОАО",
						'ООО', 
						"ПАО", 
						"АО", 
						"ГУП"						
					);
					
					$name = preg_replace($patterns, $replacements, mb_strtoupper($td[2]->nodeValue),1 );
					//~ $name = $td[2]->nodeValue;
					
					if ($td[3]->nodeValue == $emitter_id ) {
						file_put_contents($source_file, $name);  
						$result = $name;	
					}
				}
				
				
			}
		}
		else
			$result = file_get_contents($source_file);	
			
		return $result;


	}



	//~ ПОСЛЕДНИЙ ДЕНЬ ПОКУПКИ АКЦИИ 
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



	// ПОЛУЧЕНО КУПОНОВ ОБЛИГАЦИИ ИЗ GNUCASH
	public function get_gnucash_bondization() {
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








}



?>
