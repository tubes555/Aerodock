<?php
class Log extends AppModel {
	public function loadCSV($uploadFile, $flightId){

		$handle = fopen($uploadFile['tmp_name'],'r');
		fgetcsv($handle);
		fgetcsv($handle);

		$header = fgetcsv($handle);
		$numItems = count($header);
		$dropColumns = array(0,2,3,6,7,15,16,19,20,21,38,39,
													40,41,42,43,44,45,46,47,48,49,
													50,51,52,53,54,55,57,58,59,60,61,62);
		for ($i=0; $i < count($header); $i++) {
			$header[$i] = trim($header[$i]);
			if(substr($header[$i], 2,1) == " "){
				$header[$i] = substr($header[$i], 3);
			}
			if(substr($header[$i], 3,1) == " "){
				$header[$i] = substr($header[$i], 4);
			}
		}
		foreach ($dropColumns as $dropIndex) {
			unset($header[$dropIndex]);
		}
		$index = 0;
		$firstFlightTime = "";
		$lastFlightTime = "";

		$data = array();
		while($row = fgetcsv($handle)) {

			if(count($row) == $numItems && !(ctype_space($row[4]) || ctype_space($row[5]))){
				foreach ($dropColumns as $dropIndex) {
					unset($row[$dropIndex]);
				}
				$row = array_combine($header, $row);

				if($index == 0)
				{

					
					$firstFlightTime = $row['Time'];

				}

				$lastFlightTime = $row['Time'];



				$row['flight_id'] = $flightId;
				$data[$index] = $row;
				$index++;
			}

			if($index % 750 == 0){
				$this->create();

				$this->saveMany($data);
				$data = array();
			}

			//pr($row);

		}

		$this->create();
		$this->saveMany($data);

		// Time format is HH:MM:SS
		$start = strtotime($firstFlightTime);
		$end = strtotime($lastFlightTime);

		$delta = $end - $start;

		return array("return" => true, "duration" => $delta,);
	}

	public function deleteLog($id)
	{
		$condition = array('Log.flight_id' => $id );
		if($this->deleteAll($condition,false ))
		{
			return true;
		}
		
	}


}
