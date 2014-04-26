<?php
class Flight extends AppModel {

//page 317 of cakePHPCookbook
	public $validate = array(
	'studentID' => array(
		'rule' => 'notEmpty'
		)
	);

	public function deleteFlight($id)
	{
		ClassRegistry::init('Log');
		$log = new Log();

		if($this->delete($id) && $log->deleteLog($id))
		{
			return true;
		}
		else
		{
			return false;
		}


	}

	public function uploadFile( $uploadData, $id ) {
		ClassRegistry::init('Log');
		$log = new Log();
		// Shifts the first item out of the array, equivalent to popping the stack
		// Check to make sure the file has data and there are no errors from upload
		if( $uploadData['size'] == 0 || $uploadData['error'] !== 0) {
			return false;
		}
		
		$loadCSVArray = $log->loadCSV($uploadData, $id);

		if($loadCSVArray['return']){
			unset($loadCSVArray['return']);
			return $loadCSVArray;
		}

		return false;
	}

	// Takes the file path as a parameter and returns the latitude and longitude from the
	// csv. This method may get folded into a more efficient method that returns more values
	// at one time. This if for front end mock up purposes.
	public function getLatLong($flight_id = null) {

		if(!$flight_id){
			throw new NotFoundException(__('Invalid flight'));
		}
		ClassRegistry::init('Log');
		$log = new Log();

		// Initialize the array that we will return as an array of arrays.
		$minMaxes = $log->find('first',array('conditions' => array('Log.flight_id' => $flight_id),
														 'fields' => array('MAX(Latitude) as maxLat, MIN(Latitude) as minLat,
														 	MAX(Longitude) as maxLong, MIN(Longitude) as minLong'),
														 'recursive' => 1));
		$minMaxes = $minMaxes[0];
		$temp = $minMaxes['minLong'];
		$minMaxes['minLong'] = $minMaxes['maxLong'];
		$minMaxes['maxLong'] = $temp;
		// While there is still data to return...
		$endSlice = $log->find('count',array('conditions' => array('Log.flight_id' => $flight_id)));

		$center = array('lat' => ($minMaxes['maxLat'] + $minMaxes['minLat'])/2,
										'long' => ($minMaxes['maxLong'] + $minMaxes['minLong'])/2);
		
		$zoomLevel = $this->calculateZoom($minMaxes);
		return array('center' => $center,
								 'zoomLevel' => $zoomLevel,
								 'endSlice' => $endSlice);

	}

	private function calculateZoom($minMax){
		$latZoom =  (int)(12 - log(($minMax['maxLat'] - $minMax['minLat'])/0.15,2));
		$longZoom = (int)(12 - log(($minMax['minLong'] - $minMax['maxLong'])/0.22,2));
		if($latZoom < $longZoom)
			return $latZoom;
		return $longZoom;
	}

	public function generateCoords($data){
		ClassRegistry::init('Log');
		$log = new Log();
		$outputString = "[";

		// Initialize the array that we will return as an array of arrays.
		$pageNum = 1;
		$flightInfo = $log->find('all', array(
			'conditions' => array('Log.flight_id' => $data['flightid']),
			'fields' => array('Latitude', 'Longitude', 'AltGPS', 'TRK', 'Roll'),
			'limit' => 500,
			'pageNum' => $pageNum));
		$index = 1;
		$flightAggregate = array();

		// While there is still data to return...
		while(count($flightInfo) != 0){
			for($j=0; $j < count($flightInfo); $j++){
				array_push($flightAggregate, $flightInfo);
				$outputString.= "[";
				$outputString.= $flightInfo[$j]['Log']['Latitude'];
				$outputString.= ",";
				$outputString.= $flightInfo[$j]['Log']['Longitude'];
				$outputString.= "],";
			}
			$pageNum++;
			$flightInfo = $log->find('all', array(
				'conditions' => array('Log.flight_id' => $data['flightid']),
				'fields' => array('Latitude', 'Longitude'),
				'limit' => 500,
				'page' => $pageNum));
			
		}
		$outputString = rtrim($outputString, ",");
		$outputString.= "]";

		return $outputString;
	}
	function generateJsArray($data){

		$columns = array('Time');
		$numGraphs = 0;
		if(!($data['altOn']    == 'true' || $data['engineOn'] == 'true' || $data['oilOn']    == 'true' ||
				$data['rpmOn']    == 'true' || $data['fuelOn']   == 'true')){
			return 'empty';
		}

		if($data['altOn']    == 'true'){ 
			$numGraphs += 2;
			array_push($columns, 'AltGPS', 'IAS');
		} 
		if($data['engineOn'] == 'true'){
			$numGraphs += 2;
			array_push($columns, 'CHT1','CHT2','CHT3', 'CHT4',
												'EGT1', 'EGT2', 'EGT3', 'EGT4');
		}
		if($data['oilOn']    == 'true'){ 
			$numGraphs += 2;
			array_push($columns, 'OilT', 'OilP');
		}
		if($data['rpmOn']    == 'true'){
			$numGraphs += 2;
			array_push($columns, 'RPM', 'MAP');
		}
		if($data['fuelOn']   == 'true'){ 
			$numGraphs += 2;
			array_push($columns, 'FFlow', 'FPres');
		}
		ClassRegistry::init('Log');
		$log = new Log();

		$queryString = "";
		foreach ($columns as $key => $value) {
			$queryString .= 'MAX('.$value.") as " . $value .",";
		}
		$queryString = rtrim($queryString, ',');

		$maxValues = $log->find('first',array('conditions' => array('Log.flight_id' => $data['flightid']),
												 'fields' => array($queryString),
												 'recursive' => 1));

		$span = $data['endSlice'] - $data['beginSlice'];
		$interval = (int)($span * $numGraphs / 1000);
		if($interval == 0){
			$interval = 1;
		}
		$maxValues = $maxValues[0];
		if($data['engineOn'] == 'true'){
			$maxValues['CHT'] = ($maxValues['CHT1'] + $maxValues['CHT2'] +
														 $maxValues['CHT3'] +$maxValues['CHT4'])/4;
			$maxValues['EGT'] = ($maxValues['EGT1'] + $maxValues['EGT2'] +
														 $maxValues['EGT3'] +$maxValues['EGT4'])/4;
		}
		//pr($maxValues);
		$outputString = "[";
		// Initialize the array that we will return as an array of arrays.
		if($data['beginSlice'] + 500 - (500%$interval) > $data['endSlice']){
			$limit = $data['endSlice']- $data['beginSlice'];
		} else {
			$limit = 500 - (500%$interval);
		}
		$offset = $data['beginSlice'];
		$flightInfo = $log->find('all', array(
			'conditions' => array('Log.flight_id' => $data['flightid']),
			'fields' => $columns,
			'limit' => $limit,
			'offset' => $offset));
		// While there is still data to return...
		while(count($flightInfo) != 0){
			for($j=0; $j < count($flightInfo); $j++){
				if($j % $interval == 0) {
					$outputString.= "[";
					$CHTSum = 0;
					$EGTSum = 0;
					foreach ($flightInfo[$j]['Log'] as $key => $value) {
						if($key == 'Time'){
							$outputString.= "\"";
						}
						
						else if(substr($key, 0,3) == "CHT"){
							$CHTSum += $value;
						}
						else if(substr($key, 0,3) == "EGT"){
							$EGTSum += $value;
						}
						if(substr($key, 0,3) != "EGT" && substr($key, 0,3) != "CHT" && $key != "Time"){
							//pr($value);
							$outputString .= ($value)/($maxValues[$key]) . ',"' .$value.'",';
						}
						else if($key == "EGT4"){
							$outputString .= ($EGTSum/4)/$maxValues['EGT'] . ",\"". ($EGTSum/4)."\",";
						}
						else if($key	== "CHT4"){
							$outputString .= ($CHTSum/4)/$maxValues['CHT'] . ",\"". ($CHTSum/4)."\",";
						}
						if($key == 'Time'){
							$outputString.= $value . "\",";
						}
					}
					$outputString = rtrim($outputString, ",");
					$outputString.= "],";
				}		
			}
			$flightInfo = array();
			$offset += $limit;
			if($offset + 500 - (500%$interval) > $data['endSlice']){
				$limit = $data['endSlice']- $data['beginSlice'];
			} else {
				$limit = 500 - (500%$interval);
			}
			if($offset < $data['endSlice']){
				//pr($offset."<".$data['endSlice']);
				$flightInfo = $log->find('all', array(
					'conditions' => array('Log.flight_id' => $data['flightid']),
					'fields' => $columns,
					'limit' => $limit,
					'offset' => $offset));
			}
		}
		$outputString = rtrim($outputString, ",");
		$outputString.= "]";
		return $outputString;
	}

	function events($flightid)
	{
		ClassRegistry::init('Log');
		$log = new Log();
		$pageNum = 1;
		$data = $log->find('all', array(
			'conditions' => array('Log.flight_id' => $flightid),
			'fields' => array('Latitude', 'Longitude', 'AltGPS', 'TRK', 'Roll', 'IAS')));
	
	    $gAlt = 0.0;

	    $SIZE = count($data);
	    // evan shouldnt have to change anything below here

	    for($i = 1; $i < $SIZE; $i++){

	        if($gAlt == 0.0 && $data[$i]['Log']['IAS'] > 0.0) $gAlt = $data[$i]['Log']['AltGPS'];

	        if($data[$i]['Log']['IAS'] > 55) $speed = true;
	        else $speed = false;

	        $a = $data[$i-1]['Log']['TRK'];
	        $b = $data[$i]['Log']['TRK'];

	        if ($a<300) $a+=360;
	        if ($b<300) $b+=360;

	        if (abs($b-$a)>2.5) $turn = true;
	        else $turn = false;

	        if ($b < $a) $lTurn = true;
	        else $lTurn = false;

	        if(abs($data[$i]['Log']['AltGPS'] - $data[$i-1]['Log']['AltGPS']) > 5) $notLevel = true;
	        else $notLevel = false;

	        if($data[$i]['Log']['AltGPS'] > $data[$i-1]['Log']['AltGPS']) $climb = true;
	        else $climb = false;

	        if($turn && $speed && $lTurn && $notLevel && $climb) $event[$i] = "+L";
	        else if($turn && $speed && $lTurn && $notLevel && !$climb) $event[$i] = "-L";
	        else if($turn && $speed && !$lTurn && $notLevel && $climb) $event[$i] = "+R";
	        else if($turn && $speed && !$lTurn && $notLevel && !$climb) $event[$i] = "-R";
	        else if($turn && $speed && $lTurn) $event[$i] = "L";
	        else if($turn && $speed && !$lTurn) $event[$i] = "R";
	        else if($notLevel && $speed && $climb) $event[$i] = "+";
	        else if($notLevel && $speed && !$climb) $event[$i] = "-";
	        else if(!$turn && $speed) $event[$i] = "S";
	        else $event[$i] = "U";
	    }

	    $minLat = 100.0;
	    $maxLat = 0.0;
	    $minLong = -100.0;
	    $maxLong = 0.0;
	    $event[0] = "U";
	    $c = $event[0];
	    $start = true;
	    $next = false;
	    $end = false;
	    $eventCtr = 0;
	    $sizeEvent = count($event);

	    $events[$eventCtr]['name'] = $c;
	    $events[$eventCtr]['rowBegin'] = 0;
	    $events[$eventCtr]['minLat'] = $data[0]['Log']['Latitude'];
	    $events[$eventCtr]['maxLat'] = $data[0]['Log']['Latitude'];
	    $events[$eventCtr]['minLong'] = $data[0]['Log']['Longitude'];
	    $events[$eventCtr]['maxLong'] = $data[0]['Log']['Longitude'];
	    $events[$eventCtr]['ctrLat'] = $data[0]['Log']['Latitude'];
	    $events[$eventCtr]['ctrLong'] = $data[0]['Log']['Longitude'];

	    for($i = 0; $i < $SIZE - 5; $i++){
	        if($next == false){
	            if($data[$i]['Log']['Latitude'] < $minLat) $minLat = $data[$i]['Log']['Latitude'];
	            if($data[$i]['Log']['Latitude'] > $maxLat) $maxLat = $data[$i]['Log']['Latitude'];
	            if($data[$i]['Log']['Longitude'] > $minLong) $minLong = $data[$i]['Log']['Longitude'];
	            if($data[$i]['Log']['Longitude'] < $maxLong) $maxLong = $data[$i]['Log']['Longitude'];
	        }

	        if($start && !$end && $c != $event[$i] && $c != $event[$i + 1] && $c != $event[$i + 2]){
	            $end = true;
	            $events[$eventCtr]['rowEnd'] = $i - 1;
	            $start = false;
	            $eventCtr++;
	            $next = true;

	            $events[$eventCtr]['minLat'] = $minLat;
	            $events[$eventCtr]['maxLat'] = $maxLat;
	            $events[$eventCtr]['minLong'] = $minLong;
	            $events[$eventCtr]['maxLong'] = $maxLong;
	            $events[$eventCtr]['ctrLat'] = ($minLat + $maxLat) / 2.0;
	            $events[$eventCtr]['ctrLong'] = ($minLong + $maxLong) / 2.0;
	        }

	        else if($start && !$end && $c != $event[$i] && $c != $event[$i + 2] && ($event[$i - 1] != $event[$i + 1]) && ($event[$i - 2] != $event[$i + 2])){
	            $end = true;
	            $events[$eventCtr]['rowEnd'] = $i;
	            $start = false;
	            $eventCtr++;
	            $next = true;
	            $events[$eventCtr]['minLat'] = $minLat;
	            $events[$eventCtr]['maxLat'] = $maxLat;
	            $events[$eventCtr]['minLong'] = $minLong;
	            $events[$eventCtr]['maxLong'] = $maxLong;
	            $events[$eventCtr]['ctrLat'] = ($minLat + $maxLat) / 2.0;
	            $events[$eventCtr]['ctrLong'] = ($minLong + $maxLong) / 2.0;
	        }

	        if($next){
	            $minLat = 100.0;
	            $maxLat = 0.0;
	            $minLong = -100.0;
	            $maxLong = 0.0;
	            $next = false;
	        }

	        if($start == false && $event[$i] == $event[$i + 1]){
	            $c = $event[$i];
	            $events[$eventCtr]['name'] = $c;
	            $events[$eventCtr]['rowBegin'] = $i;
	            $start = true;
	            $end = false;
	        }
	    }

	    $events[$eventCtr]['rowEnd'] = $SIZE - 1;
	    $events[$eventCtr]['minLat'] = $minLat;
	    $events[$eventCtr]['maxLat'] = $maxLat;
	    $events[$eventCtr]['minLong'] = $minLong;
	    $events[$eventCtr]['maxLong'] = $maxLong;
	    $events[$eventCtr]['ctrLat'] = ($minLat + $maxLat) / 2.0;
	    $events[$eventCtr]['ctrLong'] = ($minLong + $maxLong) / 2.0;

	    $eventCtr = count($events);



	    $cmprsdEvnts = array(array());
	    $ceCtr = 0;

	    for($i = 0; $i < $eventCtr ; $i++)
	    {
	        if($events[$i]['name'] == "U" || $events[$i]['name'] == "S" || $events[$i]['name'] == "+" || $events[$i]['name'] == "-")
	        {
	            //$cmprsdEvents[$ceCtr] = $events[$i];
	            $cmprsdEvnts[$ceCtr]['name'] = $events[$i]['name'];
	            $cmprsdEvnts[$ceCtr]['rowBegin'] = $events[$i]['rowBegin'];
	            $cmprsdEvnts[$ceCtr]['rowEnd'] = $events[$i]['rowEnd'];
	            $cmprsdEvnts[$ceCtr]['minLat'] = $events[$i]['minLat'];
	            $cmprsdEvnts[$ceCtr]['minLong'] = $events[$i]['minLong'];
	            $cmprsdEvnts[$ceCtr]['maxLat'] = $events[$i]['maxLat'];
	            $cmprsdEvnts[$ceCtr]['maxLong'] = $events[$i]['maxLong'];
	            $cmprsdEvnts[$ceCtr]['ctrLat'] = $events[$i]['ctrLat'];
	            $cmprsdEvnts[$ceCtr]['ctrLong'] = $events[$i]['ctrLong'];

	            $ceCtr++;
	        }

	       else
	       {
	            if($events[$i]['name'] == "R" || $events[$i]['name'] == "+R" || $events[$i]['name'] == "-R")
	            {
	                $r = $i;
	                $cmprsdEvnts[$ceCtr]['name'] = "R";
	                $cmprsdEvnts[$ceCtr]['rowBegin'] = $events[$i]['rowBegin'];
	                $cmprsdEvnts[$ceCtr]['minLat'] = $events[$i]['minLat'];
	                $cmprsdEvnts[$ceCtr]['minLong'] = $events[$i]['minLong'];
	                $cmprsdEvnts[$ceCtr]['maxLat'] = $events[$i]['maxLat'];
	                $cmprsdEvnts[$ceCtr]['maxLong'] = $events[$i]['maxLong'];
	                $cmprsdEvnts[$ceCtr]['ctrLat'] = $events[$i]['ctrLat'];
	                $cmprsdEvnts[$ceCtr]['ctrLong'] = $events[$i]['ctrLong'];
	                for ($j=$i; $events[$j]['name'] == "R" || $events[$j]['name'] == "+R" || $events[$j]['name'] == "-R"; $j++ )
	                {
	                    if ($events[$j]['maxLat'] > $cmprsdEvnts[$ceCtr]['maxLat'])
	                        $cmprsdEvnts[$ceCtr]['maxLat'] = $events[$j]['maxLat'];
	                    if ($events[$j]['minLat'] < $cmprsdEvnts[$ceCtr]['minLat'])
	                        $cmprsdEvnts[$ceCtr]['minLat'] = $events[$j]['minLat'];
	                    if ($events[$j]['maxLong'] < $cmprsdEvnts[$ceCtr]['maxLong'])
	                        $cmprsdEvnts[$ceCtr]['maxLong'] = $events[$j]['maxLong'];
	                    if ($events[$j]['minLong'] > $cmprsdEvnts[$ceCtr]['minLong'])
	                        $cmprsdEvnts[$ceCtr]['minLong'] = $events[$j]['minLong'];
	                    $r++;
	                }
	                $cmprsdEvnts[$ceCtr]['rowEnd'] = $events[$r-1]['rowEnd'];
	                $cmprsdEvnts[$ceCtr]['ctrLat'] = ($cmprsdEvnts[$ceCtr]['maxLat'] + $cmprsdEvnts[$ceCtr]['minLat'])/2.0;
	                $cmprsdEvnts[$ceCtr]['ctrLong'] = ($cmprsdEvnts[$ceCtr]['maxLong'] + $cmprsdEvnts[$ceCtr]['minLong'])/2.0;
	                $ceCtr++;
	                $i=$r-1;
	        }

	        // ********************** compress any successive events that have L in the name **************************
	        if ($events[$i]['name'] == "L" || $events[$i]['name'] == "+L" || $events[$i]['name'] == "-L") // left turns
	        {
	                $l = $i;
	                $cmprsdEvnts[$ceCtr]['name'] = "L";
	                $cmprsdEvnts[$ceCtr]['rowBegin'] = $events[$i]['rowBegin'];
	                $cmprsdEvnts[$ceCtr]['minLat'] = $events[$i]['minLat'];
	                $cmprsdEvnts[$ceCtr]['minLong'] = $events[$i]['minLong'];
	                $cmprsdEvnts[$ceCtr]['maxLat'] = $events[$i]['maxLat'];
	                $cmprsdEvnts[$ceCtr]['maxLong'] = $events[$i]['maxLong'];
	                $cmprsdEvnts[$ceCtr]['ctrLat'] = $events[$i]['ctrLat'];
	                $cmprsdEvnts[$ceCtr]['ctrLong'] = $events[$i]['ctrLong'];
	                for ($j=$i; $events[$j]['name'] == "L" || $events[$j]['name'] == "+L" || $events[$j]['name'] == "-L"; $j++ )
	                {
	                    if ($events[$j]['maxLat'] > $cmprsdEvnts[$ceCtr]['maxLat'])
	                        $cmprsdEvnts[$ceCtr]['maxLat'] = $events[$j]['maxLat'];
	                    if ($events[$j]['minLat'] < $cmprsdEvnts[$ceCtr]['minLat'])
	                        $cmprsdEvnts[$ceCtr]['minLat'] = $events[$j]['minLat'];
	                    if ($events[$j]['maxLong'] < $cmprsdEvnts[$ceCtr]['maxLong'])
	                        $cmprsdEvnts[$ceCtr]['maxLong'] = $events[$j]['maxLong'];
	                    if ($events[$j]['minLong'] > $cmprsdEvnts[$ceCtr]['minLong'])
	                        $cmprsdEvnts[$ceCtr]['minLong'] = $events[$j]['minLong'];
	                   $l++;
	                }
	                $cmprsdEvnts[$ceCtr]['rowEnd'] = $events[$l-1]['rowEnd'];
	                $cmprsdEvnts[$ceCtr]['ctrLat'] = ($cmprsdEvnts[$ceCtr]['maxLat'] + $cmprsdEvnts[$ceCtr]['minLat'])/2.0;
	                $cmprsdEvnts[$ceCtr]['ctrLong'] = ($cmprsdEvnts[$ceCtr]['maxLong'] + $cmprsdEvnts[$ceCtr]['minLong'])/2.0;
	                $ceCtr++;
	                $i=$l-1;
		        }
		    }
		}
		$ceCtr--;


		// ***************************  maneuvers parse  *********************************
		$maneuvers = array();
		$manCtr=0;
		for ($i=0; $i<$ceCtr; $i++)
		{
		    // *************************** begin steep turn  *********************************
		    if (($cmprsdEvnts[$i]['rowEnd'] - $cmprsdEvnts[$i]['rowBegin'] > 20) &&
		        ($cmprsdEvnts[$i]['name'] == "L" || $cmprsdEvnts[$i]['name'] == "R") &&
		        ($data[$cmprsdEvnts[$i]['rowEnd']+2]['Log']['TRK'] - $data[$cmprsdEvnts[$i]['rowBegin']-2]['Log']['TRK'] < 10) &&
		        (abs(($data[$cmprsdEvnts[$i]['rowEnd']+5]['Log']['AltGPS'])-($data[$cmprsdEvnts[$i]['rowBegin']-5]['Log']['AltGPS'])) < 100)) // fix heading for 0-360, trk should be hdg
		    {
		        $sum =0;
		        for ($j = $cmprsdEvnts[$i]['rowBegin']; $j < $cmprsdEvnts[$i]['rowEnd']; $j++){
		            $sum=$sum + abs($data[$j]['Log']['Roll']);}

		        //$abs =0;
		        if(abs( 45 - ($sum / ($cmprsdEvnts[$i]['rowEnd'] - $cmprsdEvnts[$i]['rowBegin'])) < 5)){
		            $maneuvers[$manCtr] = $cmprsdEvnts[$i];
		            $maneuvers[$manCtr]['name'] = "Steep Turn";
		            $manCtr++;
		        }
		    // *************************** end steep turn  *********************************
		    }
		}
		$outputString = "[";
		for ($i=0; $i < count($maneuvers); $i++) { 
			$outputString .= "[";
			foreach ($maneuvers[$i] as $key => $value) {
				if($key == 'name'){
					$outputString .= '"';
				}
				$outputString .= $value .",";
				if($key == 'name'){
					$outputString = rtrim($outputString,",");
					$outputString .= '",';
				}
			}
			$outputString .= $this->calculateZoom(array('maxLat' =>  $maneuvers[$i]['maxLat'],
																									'minLat' =>  $maneuvers[$i]['minLat'],
																									'minLong' => $maneuvers[$i]['minLong'],
																									'maxLong' => $maneuvers[$i]['maxLong']));
			$outputString .= "],";
		}
		$outputString = rtrim($outputString,",");
		$outputString .= "]";
		return array($maneuvers,$outputString);

	}
}