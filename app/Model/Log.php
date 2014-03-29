<?php
class Log extends AppModel {
	public function loadCSV($uploadFile, $flightId){

		$handle = fopen($uploadFile['tmp_name'],'r');
		fgetcsv($handle);
		fgetcsv($handle);

		$header = fgetcsv($handle);
		for ($i=0; $i < count($header); $i++) { 
				$header[$i] = trim($header[$i]);
				if(substr($header[$i], 2,1) == " "){
					$header[$i] = substr($header[$i], 3);
				};
		}
		$numItems = count($header);
		$index = 0;
		$data = array();
		while($row = fgetcsv($handle)) {

			if(count($row) == $numItems && !(ctype_space($row[4]) || ctype_space($row[5]))){
				$row = array_combine($header, $row);
				$row['flight_id'] = $flightId;
				$data[$index] = $row;
				$index++;
			}


			if($index == 750){
				$this->create();
				$this->saveMany($data, array(
																'validate' => false,
																));
				$data = array();
				$index = 0;
			}

		}
		$this->create();
		$this->saveMany($data);
		return true;
	}

}
