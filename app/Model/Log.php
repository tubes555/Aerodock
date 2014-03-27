<?php
class Log extends AppModel {
	public function loadCSV($uploadFile, $flightId){
		$handle = fopen($uploadFile['tmp_name'],'r');
		
		fgetcsv($handle);
		fgetcsv($handle);
		$header = fgetcsv($handle);
		for ($i=0; $i < count($header); $i++) { 
				$header[$i] = trim($header[$i]);
		}
		$numItems = count($header);
		while($row = fgetcsv($handle)) {
			if(count($row) == $numItems && !(ctype_space($row[4]) || ctype_space($row[5]))){
				$row = array_combine($header, $row);
				$row['flight_id'] = $flightId;
				$this->create();
				$this->set($row);
				$this->save();
			}
		}
		return true;
	}

}
