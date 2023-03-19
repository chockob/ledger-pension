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
		$source_file = 'raexpert/'.$bond.'.csv';
		$a_csv = '';
		if (file_exists($source_file)) {
			//~ echo $source_file;
			$a_csv = str_getcsv( file_get_contents($source_file),';' );	
			//~ print_r($result);
			$result = '<span title="'.$a_csv[0].'" >'.$a_csv[1].'</span>';
		}
		else
			$result = $this->get_raexpert_form_control($bond);
		return $result;
	}
	
	//~ public function get_baloon_form() {

			//~ ."$(function() {"
			//~ ."	$('.sample4').balloon({"
			//~ ."	  html: true,"
			//~ ."	  contents: '$get_raexpert_form_control'"
			//~ ."	});"
			//~ ."});"
		
		
		//~ return $result;
	//~ }
	
	//~ class="AcraForm"
	//~ --------------------------
	//~ Форма постановки на котроль рейтинга Эксперт РА
	function get_raexpert_form_control($bano_name) {
			//~ $content = '<div class="sample4" ><h3>Контроль рейтинга от Эксперт РА</h3>';
			//~ $content = '<form action="http://127.0.0.1/investment/index.php?do=raexpert-control" method="post">';
			//~ $content = '<select name="bond-name">';
			//~ $content .= $content_select_options;
			//~ $content = '</select> </br>   ';
			//~ $content = '
			//~ <input type="input" name="name_bond" value="bond" >     <label>Выпуск облигации </label></br>   				
			//~ <input type="radio" name="type-control" value="bond" checked>     <label>Рейтинг облигации </label></br>   	
			//~ <input type="radio" name="type-control" value="emitter">  <label>Рейтинг эмитента</label></br>   
			//~ <label>url</label>			<input name="url-control" value="https://www.raexpert.ru/database/securities/bonds/1000049874/" size="40">
			//~ <input type="submit" value="Ок">
			//~ </form>
			//~ </div>
			//~ ';	
			
$content = '<span class="AcraForm_'.$bano_name.'">+</span>';
$content .= "<script>";
$content .= "$(document).ready(function() {";
$content .= "$('.AcraForm_$bano_name').balloon({";
$content .= "  html: true,";
$content .= "  contents: '<div class=\"sample4\" ><h3>Контроль рейтинга от Эксперт РА</h3>'";
$content .= "			+'<form action=\"http://127.0.0.1/investment/index.php?do=raexpert-control\" method=\"post\">'";
$content .= "			+'<label>Выпуск облигации </label>'";
$content .= "			+'<input type=\"input\" name=\"bond-name\" value=\"$bano_name\" ></br>'";
$content .= "			+'<input type=\"radio\" name=\"type-control\" value=\"bond\" checked>     <label>Рейтинг облигации https://www.raexpert.ru/database/securities/bonds/1000049874/</label></br>'";
$content .= "			+'<input type=\"radio\" name=\"type-control\" value=\"emitter\">  <label>Рейтинг эмитента</label></br>'";
$content .= "			+'<label>Страница</label>			<input name=\"url-control\" value=\"\" size=\"40\">'";
$content .= "			+'<input type=\"submit\" value=\"Добавить\">'";
$content .= "			+'</form>'";
$content .= "			+'</div>'";
$content .= "});			";
$content .= "});";
$content .= "</script>";
		return $content;
	}
}



?>
