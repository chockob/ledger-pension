<?php


class CoreExpertRA {

	
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
		return $result;
	}
	
	
	//~ --------------------------
	//~ Форма постановки на котроль рейтинга Эксперт РА
	public function get_raexpert_form_control($content_select_options = '') {
		
		if (!empty($content_select_options)) {
			$content = '<div class="sample4" ><h3>Контроль рейтинга от Эксперт РА</h3>
			<form action="http://127.0.0.1/investment/index.php?do=raexpert-control" method="post">
			<select name="bond-name">';
			$content .= $content_select_options;
			$content .= '
			</select> </br>   
			<input type="radio" name="type-control" value="bond" checked>     <label>Выпуск облигации </label></br>   	
			<input type="radio" name="type-control" value="emitter">  <label>Эмитент облигации</label></br>   
			<label>url</label>			<input name="url-control" value="https://www.raexpert.ru/database/securities/bonds/1000049874/" size="40">
			<input type="submit" value="Ок">
			</form>
			</div>
			';	
		}
		return $content;
	}
}



?>
