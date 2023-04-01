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
			<!--
			<link rel="stylesheet" href="css/pico.css">
			
			!-->
			
			<script src="./js/jquery-3.6.0.min.js"></script>
			
			<script src="./js/table.js"></script>
			
			<!--
			<script src="./js/jquery.balloon.min.js"></script>    
			
			<script src="./js/modal.min.js"></script>
			
			!-->

		<link href="./jquery-editable/css/jquery-editable.css" rel="stylesheet"/>
		<script src="./jquery-editable/js/jquery-editable-poshytip.min.js"></script>
			
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

	//~ ---------------- 
	//~ Получить наименование эмитента по из кода облигации

	function get_emitter_name($emitter_id) {
		
		//~ $emitter_id = '16643-A';
			
		$source_file = 'db/emitter/'.$emitter_id.'.json';	
		
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


	

}



?>
