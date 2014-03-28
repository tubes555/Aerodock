<?php
class Log extends AppModel {
	public function loadCSV($uploadFile, $flightId){
		$latLongFile = new File("times");
		$latLongFile->create();
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

		while($row = fgetcsv($handle)) {
					$time_pre = microtime(true);
			if(count($row) == $numItems && !(ctype_space($row[4]) || ctype_space($row[5]))){
				$row = array_combine($header, $row);
				$row['flight_id'] = $flightId;
				$this->create();
				$this->set($row);
				$this->save();
			}
					$time_post = microtime(true);
							$latLongFile->write($time_post - $time_pre);
							$latLongFile->write("\n");
		}

		return true;
	}

}
