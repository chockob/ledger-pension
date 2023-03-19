<?php


class CoreAcra {

	//~ ПОЛУЧИТЬ МАССИВ ШКАЛЫ 
	public function getAcraScala(string $level): array
    {
		if ($level == 'AAA')
			return array('AAA(RU)');
		elseif ($level == 'AA')
			return array('AA+(RU)','AA(RU)','AA-(RU)');
		elseif ($level == 'A')
			return array('A+(RU)','A(RU)','A-(RU)');
		else
			array('AAA(RU)','AA+(RU)','AA(RU)','AA-(RU)','A+(RU)','A(RU)','A-(RU)');
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
		$result = str_replace(array(' ','+'),array('&nbsp;','&#43;'), $res['data']['items'][0]['info']['rate']['value']['title']);
		
		
		
		$raiting = strtoupper( $res['data']['items'][0]['info']['rate']['value']['title'] );
		
		$result = $raiting;
		
			//~ echo "</h1>";		
			//~ echo "<pre>";		
			//~ print_r(json_decode($json, true));		
			//~ echo "</pre>";						

		return $result;
	}

	
}



?>
