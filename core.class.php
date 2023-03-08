<?php


class CoreLedgerPension {


	//~ ---------------- 
	//~ Получить наименование эмитента по из кода облигации

	function get_emitter_name($emitter_id) {
		
		//~ $emitter_id = '16643-A';
			
		$source_file = 'emitter/'.$emitter_id.'.json';	
		
		$html = '';
		$result = 'EMPTY';
		if (!empty($emitter_id)) {	
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
						'/Группа компаний/',
						'/ГРУППА КОМПАНИЙ/',
						'/Холдинговая компания/',
						'/Центральная пригородная пассажирская компания/',
						'/Государственная транспортная лизинговая компания/',
						'/Горно - металлургическая компания/', 
						'/Жилищно-коммунальное хозяйство/',
						'/Общество с ограниченной ответственностью/', 
						"/Публичное акционерное общество/", 
						"/Акционерное общество/", 
						"/АКЦИОНЕРНОЕ ОБЩЕСТВО/", 
						"/Государственное унитарное предприятие/", 
						'/ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО/', 
						'/Открытое акционерное общество/'
					);
					$replacements = array(
						'ГК',
						'ХК',
						'ЦППК',
						'ГТЛК',
						'ГМК', 
						'ЖКХ', 
						'ООО', 
						"ПАО", 
						"АО", 
						"АО", 
						"ГУП", 
						"ПАО", 
						"ОАО"
					);
					
					$name = preg_replace($patterns, $replacements, $td[2]->nodeValue);
					
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


	//~ ------------------------
	//~ ПОЛУЧИТЬ АКРА РЕЙТИНГ ПО ЭМИССИИ
	public function get_acra_rate_emission($emitter_id) {
		//~ $emitter_id = 'RU000A104ZC9';
		$source_file = 'acra-ratings/'.$emitter_id.'.json';	
		
		$html = '';
		$result = 'EMPTY';
		//~ if (!empty($emitter_id)) {	
		if (!file_exists($source_file)  ) {
			
			$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			$dateEnd = date_create(date('d.m.Y',time()));			
			$dateEnd->setTime(24,0,0);

			$diff = date_diff($dateStart,$dateEnd);
			if ( $diff->format("%a") > 10 ) {
				$post_data = '{"text":"'.$emitter_id.'","sectors":[],"activities":[],"countries":[],"forecasts":[],"on_revision":0,"rating_scale":0,"rate_from":0,"rate_to":0,"vexel_types":[],"debt_types":[],"page":1,"sort":"","count":10}';
				$stream_options=array(
					"ssl"=>array(
						"verify_peer"=>false,
						"verify_peer_name"=>false,
					),
				   'http'=>array(
						'method'  => 'POST',
						'content' => ($post_data),
						'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
							."Referer: https://www.acra-ratings.ru/ratings/emissions/?text=$emitter_id&sectors[]=&activities[]=&countries[]=&forecasts[]=&on_revision=0&rating_scale=0&rate_from=0&rate_to=0&vexel_types[]=&debt_types[]=&page=1&sort=&count=10&\r\n"
					)    
				); 
				$context  = stream_context_create($stream_options);			
				$html = file_get_contents('https://www.acra-ratings.ru/local/ajax/get_rate_emission.php', null, $context);	
				//~ echo '===LOAD URL '.$emitter_id.'</br>';

				file_put_contents($source_file, $html);
				//~ echo "<pre>";		
				//~ var_dump(json_decode($html, true));		
				//~ echo "</pre>";	
			}		
		}	
			
		$json = file_get_contents($source_file);				
		$res = json_decode($json, true);
			//~ echo "<h1>";		
		$result = str_replace(' ','&nbsp;', $res['data']['items'][0]['info']['rate']['value']['title']);
			//~ echo "</h1>";		
			//~ echo "<pre>";		
			//~ print_r(json_decode($json, true));		
			//~ echo "</pre>";						

		return $result;
	}
	
	
	//~ Рейтинг Эксперт РА по выпуску облигации
	public function get_raexpert_rate_bond($bond) {
		$result = '';
		$source_file = 'raexpert/'.$bond.'.csv';
		
		if (file_exists($source_file)) {
			//~ echo $source_file;
			$result = str_getcsv( file_get_contents($source_file),';' );	
			//~ print_r($result);
			
		}
		return '<span title="'.$result[0].'" >'.$result[1].'</span>';
	}
	
}



?>
