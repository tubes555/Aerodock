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

}