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
			//~ $result = '<span title="'.$a_csv[0].'" >'.$a_csv[1].'</span>';
			$result = $a_csv[1];
		}
		else
			$result = $this->get_raexpert_form_control($bond);
		return $result;
	}
	
	
	//~ Форма постановки на котроль рейтинга Эксперт РА
	function get_raexpert_form_control($bano_name) {
		

$content ='<!-- Button to trigger the modal -->
<a class="contrast"
  data-target="modal-example-'.$bano_name.'"
  onClick="toggleModal(event)">
  +
</a>

<!-- Modal -->
<dialog id="modal-example-'.$bano_name.'">
  <article>
    <a href="#close"
      aria-label="Close"
      class="close"
      data-target="modal-example-'.$bano_name.'"
      onClick="toggleModal(event)">
    </a>
    <h3>Контроль рейтинга от Эксперт РА</h3>
    
<form action="http://127.0.0.1/investment/index.php" method="post" id="form-'.$bano_name.'">


<input type="radio" name="type-control" value="bond" checked>     <label>Рейтинг облигации https://www.raexpert.ru/database/securities/bonds/1000049874/</label></br>
<input type="radio" name="type-control" value="emitter">  <label>Рейтинг эмитента</label></br>
<label>Страница</label>			<input type="input" name="do" value="raexpert-control" ></br>
<label>Страница</label>			<input type="input" name="url-control" value="" size="40"></br>
<label>Выпуск облигации </label> <input type="input" name="bond-name" value="'.$bano_name.'" ></br>
</form>    
    
    
    <footer>
      <a href="#cancel"
        role="button"
        class="secondary"
        data-target="modal-example-'.$bano_name.'"
        onClick="toggleModal(event)">
        Cancel
      </a>
      <a href="#confirm"
        role="button"
        data-target="modal-example-'.$bano_name.'"
        
        onClick="$( '."'#form-".$bano_name."'".' ).submit(); toggleModal(event);">
        Confirm
      </a>
    </footer>
  </article>
</dialog>';
		return $content;
	}
}



?>
