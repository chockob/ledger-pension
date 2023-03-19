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
			
//~ $content = '<span class="AcraForm_'.$bano_name.'IIIIII">'
//~ .'<input type="checkbox" name="" value="" class="AcraForm_'.$bano_name.'" >'
//~ .'</span>';
//~ $content .= "<script>";




//~ $content .= "$(document).ready(function() {";
//~ $content .= "   $('.AcraForm_$bano_name').hideBalloon();";
//~ $content .= "   console.log('shown =' + shown  );";
//~ $content .= "var shown = true;";
//~ $content .= "$('.AcraForm_$bano_name').balloon({";
//~ $content .= "$('.AcraForm_$bano_name').on(\"click\", function() {";
//~ $content .= "   ;";

//~ $content .= "  $(this).hideBalloon();";
//~ $content .= "  if ($(this).is(':checked')) { $(this).showBalloon() } else { $(this).hideBalloon() }";

//~ $content .= "  shown ? $(this).hideBalloon() : $(this).showBalloon();";
//~ $content .= "  shown = !shown;";
//~ $content .= "  }).showBalloon({";
//~ $content .= "  html: true,";
//~ $content .= "  contents: '<div class=\"sample4\" ><h3>Контроль рейтинга от Эксперт РА</h3>'";
//~ $content .= "			+'<form action=\"http://127.0.0.1/investment/index.php?do=raexpert-control\" method=\"post\">'";
//~ $content .= "			+'<label>Выпуск облигации </label>'";
//~ $content .= "			+'<input type=\"input\" name=\"bond-name\" value=\"$bano_name\" ></br>'";
//~ $content .= "			+'<input type=\"radio\" name=\"type-control\" value=\"bond\" checked>     <label>Рейтинг облигации https://www.raexpert.ru/database/securities/bonds/1000049874/</label></br>'";
//~ $content .= "			+'<input type=\"radio\" name=\"type-control\" value=\"emitter\">  <label>Рейтинг эмитента</label></br>'";
//~ $content .= "			+'<label>Страница</label>			<input name=\"url-control\" value=\"\" size=\"40\">'";
//~ $content .= "			+'<input type=\"submit\" value=\"Добавить\">'";
//~ $content .= "			+'</form>'";
//~ $content .= "			+'</div>'";
//~ $content .= "});			";
//~ $content .= "});";
//~ $content .= "</script>";

//~ $content .= "<!-- Button trigger modal -->";
//~ $content .= '<button type="button" data-toggle="modal" data-target="sample4">Launch modal</button>';
//~ $content .= "  <div class=\"sample4\" ><h3>Контроль рейтинга от Эксперт РА</h3>";
//~ $content .= "			<form action=\"http://127.0.0.1/investment/index.php?do=raexpert-control\" method=\"post\">";
//~ $content .= "			<label>Выпуск облигации </label>";
//~ $content .= "			<input type=\"input\" name=\"bond-name\" value=\"$bano_name\" ></br>";
//~ $content .= "			<input type=\"radio\" name=\"type-control\" value=\"bond\" checked>     <label>Рейтинг облигации https://www.raexpert.ru/database/securities/bonds/1000049874/</label></br>";
//~ $content .= "			<input type=\"radio\" name=\"type-control\" value=\"emitter\">  <label>Рейтинг эмитента</label></br>";
//~ $content .= "			<label>Страница</label>			<input name=\"url-control\" value=\"\" size=\"40\">";
//~ $content .= "			<input type=\"submit\" value=\"Добавить\">";
//~ $content .= "			</form>";
//~ $content .= "			</div>";

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
