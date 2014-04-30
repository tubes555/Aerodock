<?php
class Log extends AppModel {
public function loadCSV($uploadFile, $flightId){

		$handle = fopen($uploadFile['tmp_name'],'r');
		$row = fgetcsv($handle);
		$aircraft = substr($row[2],16,-1);
		$tailNumber = substr($row[6],12,-1);
		fgetcsv($handle);
		$header = fgetcsv($handle);
		$numItems = count($header);
		$dropColumns = array(0,2,3,6,7,8,9,12,15,16,19,20,21,37,38,
													39,40,41,42,43,44,45,46,47,48,49,
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
		$setMaintFlag = false;
		$data = array();
		while($row = fgetcsv($handle)) {
			if($index == 0)
			{
				$date = $row[0];
				$firstFlightTime = $row[1];
				$lastFlightTime = $row[1];
			}

			if(count($row) == $numItems && !(ctype_space($row[4]) || ctype_space($row[5]))){
				foreach ($dropColumns as $dropIndex) {
					unset($row[$dropIndex]);
				}
				$row = array_combine($header, $row);

				if((int)substr($row['Time'],0,2) <= (int)substr($lastFlightTime,0,2) &&
					 (int)substr($row['Time'],3,2) <= (int)substr($lastFlightTime,3,2) &&
					 (int)substr($row['Time'],6,2) <= (int)substr($lastFlightTime,6,2)){
					if(substr($row['Time'],6,2) == '00'){
						if(substr($row['Time'],3,2) == '00'){
							$row['Time'] = ((string)((int)substr($row['Time'],0,2)) + 1).
																		substr($row['Time'],2);
						} else {
							$row['Time'] = substr($row['Time'],0,3).
																	((string)((int)substr($row['Time'],3,2)) + 1).
																	substr($row['Time'],5);
						}
					}
				}

				$lastFlightTime = $row['Time'];

				if (!$setMaintFlag)
				{
        				if ($row['CHT1'] > 500 || $row['CHT2'] > 500 || 
        				$row['CHT3'] > 500 || $row['CHT4'] > 500 )
          				{
        					 $setMaintFlag = true;
        				}
        			}
        
        			if (!$setMaintFlag)
        			{
          				if ($row['RPM'] > 2750 || $row['OilT'] > 245 || 
        				($row['MAP'] > 15 && ($row['FPres'] > 35 || $row['FPres'] < 14)))
        					$setMaintFlag = true;
        			}
				$row['flight_id'] = $flightId;
				$data[$index] = $row;
				$index++;
			}

			if($index % 750 == 0){
				$this->create();

				$this->saveMany($data);
				$data = array();
			}

		}
		$flags = 0;
		if ($setMaintFlag)
			$flags += 1;
		$this->create();
		$this->saveMany($data);

		// Time format is HH:MM:SS
		$start = strtotime($firstFlightTime);
		$end = strtotime($lastFlightTime);

		$delta = $end - $start;

		return array("return" => true,
								 "duration" => $delta,
								 "date" => $date, 
								 "tailNo" => $tailNumber, 
								 "aircraft" => $aircraft,
								 "maintenance" => $flags);
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
