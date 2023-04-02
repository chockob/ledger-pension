<?php


class CoreExpertRA {


	
	//~ ПОЛУЧИТЬ МАССИВ ШКАЛЫ 
	public function getExpertScala(string $level): array
    {
		if ($level == 'AAA')
			return array('ruAAA');
		elseif ($level == 'AA')
			return array('ruAA+','ruAA','ruAA-');
		elseif ($level == 'A')
			return array('ruA+','ruA','ruA-');

		elseif ($level == 'BBB')
			return array('ruBBB+','ruBBB','ruBBB-');
		elseif ($level == 'BB')
			return array('ruBB+','ruBB','ruBB-');
		elseif ($level == 'B')
			return array('ruB+','ruB','ruB-');			
		else
			return array('ruAAA','ruAA+','ruAA','ruAA-','ruA+','ruA','ruA-','ruBBB+','ruBBB','ruBBB-','ruBB+','ruBB','ruBB-','ruB+','ruB','ruB-');
	
	
    }
    
    	
	//~ Рейтинг Эксперт РА по выпуску облигации
	public function get_raexpert_rate_bond($bond) {
		$result = '';
		$source_file = 'db/raexpert/'.$bond.'.csv';
		if (file_exists($source_file)) {
			//~ echo $source_file;
			$source_cont = str_getcsv( file_get_contents($source_file),';' );	
			
			//~ $result = '<span title="'.$a_csv[0].'" >'.$a_csv[1].'</span>';
			//~ $result = $source_cont[1];
			$result = '<a href="'.$source_cont[2].'" title="'.$source_cont[0].'">'.$source_cont[1].'</a>';
		
		
			$dateStart = date_create(date ("d.m.Y", filemtime($source_file)));
			$dateEnd = date_create(date('d.m.Y',time()));
			$dateEnd->setTime(24,0,0);
			$diff = date_diff($dateStart,$dateEnd);
		
			if ($diff->format("%a") > 30 ) {
				
				//~ print_r($source_cont);
				
				$doc = new DOMDocument();
				$doc->loadHTML(mb_convert_encoding(file_get_contents($source_cont[2]), 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
				
				$elements = $doc->getElementsByTagName('table');
				
				//~ echo $elements[0]->nodeValue.'</br>';		
				
				
				$tr = $elements[0]->getElementsByTagName('tr');
				
				
				//~ --echo $tr[0]->nodeValue.'</br>';		  
				//~ --echo $tr[1]->nodeValue.'</br>';    
				$span = $tr[1]->getElementsByTagName('span');
				//~ echo $span[0]->nodeValue.'</br>';    
				$raexport_bond_val = trim($span[0]->nodeValue);
				$td = $tr[1]->getElementsByTagName('td');
				//~ echo $td[1]->nodeValue.'</br>';    
				$raexport_bond_d = trim($td[1]->nodeValue);
				//~ echo "</br>$raexport_bond_d;$raexport_bond_val;".$source_cont[2];  
				file_put_contents($source_file, "$raexport_bond_d;$raexport_bond_val;".$source_cont[2]);  
				
				$result = '<a href="'.$source_cont[2].'" title="'.$raexport_bond_d.'">'.$raexport_bond_val.'</a>';
			}	
		}
			
			
					
		
		return $result;
	}
	
	
}



?>
