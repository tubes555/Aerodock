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
		$latLongArray = array('lat' => array(),
													'long'=> array());
		$altitude = array();
		$airspeed = array();
		$engineRPM = array();
		$engineTemp = array();
		$tracking = array();
		$timestamp = array();
		$pageNum = 1;
		$flightInfo = $log->find('all', array(
			'conditions' => array('Log.flight_id' => $flight_id),
			'fields' => array('Latitude', 'Longitude', 'IAS', 
												'CHT1', 'RPM','TRK','Time','AltGPS',
												'Pitch', 'Roll', 'FFlow', 'FPres','GndSpd',
												'OilT', 'OilP', 'MAP','CHT1','CHT2','CHT3', 'CHT4',
												'EGT1', 'EGT2', 'EGT3', 'EGT4','VSpdG'),
			'limit' => 1));

		$index = 0;
		$maxLat = $flightInfo[0]['Log']['Latitude'];
		$minLat = $maxLat;
		$maxLong = $flightInfo[0]['Log']['Longitude'];
		$minLong = $maxLong;

		$lastTime = $flightInfo[0]['Log']['Time'];
		$flightAggregate = array();

		// While there is still data to return...
		while(count($flightInfo) != 0){
			for($j=0; $j < count($flightInfo); $j++){
				if($flightInfo[$j]['Log']['Latitude'] < $minLat){
					$minLat = $flightInfo[$j]['Log']['Latitude'];
				}
				if($flightInfo[$j]['Log']['Latitude'] > $maxLat){
					$maxLat = $flightInfo[$j]['Log']['Latitude'];
				}				
				if($flightInfo[$j]['Log']['Longitude'] < $minLong){
					$minLong = $flightInfo[$j]['Log']['Longitude'];
				}				
				if($flightInfo[$j]['Log']['Longitude'] > $maxLong){
					$maxLong = $flightInfo[$j]['Log']['Longitude'];
				}

				// Fixes issue where minute does not increment after seconds reset to 00 in CSV.
				if((int)substr($flightInfo[$j]['Log']['Time'],0,2) <= (int)substr($lastTime,0,2) &&
					 (int)substr($flightInfo[$j]['Log']['Time'],3,2) <= (int)substr($lastTime,3,2) &&
					 (int)substr($flightInfo[$j]['Log']['Time'],6,2) <= (int)substr($lastTime,6,2)){
					if(substr($flightInfo[$j]['Log']['Time'],6,2) == '00'){
						if(substr($flightInfo[$j]['Log']['Time'],3,2) == '00'){
							$flightInfo[$j]['Log']['Time'] = ((string)((int)substr($flightInfo[$j]['Log']['Time'],0,2)) + 1).
																		substr($flightInfo[$j]['Log']['Time'],2);
						} else {
							$flightInfo[$j]['Log']['Time'] = substr($flightInfo[$j]['Log']['Time'],0,3).
																	((string)((int)substr($flightInfo[$j]['Log']['Time'],3,2)) + 1).
																	substr($flightInfo[$j]['Log']['Time'],5);
						}
					}
				}
				$lastTime = $flightInfo[$j]['Log']['Time'];
				$flightAggregate[$index] = $flightInfo[$j];
				unset($flightInfo[$j]);
				$index++;
			
			}
			$flightInfo = $log->find('all', array(
				'conditions' => array('Log.flight_id' => $flight_id),
				'fields' => array('Latitude', 'Longitude', 'IAS', 
												'CHT1', 'RPM','TRK','Time','AltGPS',
												'Pitch', 'Roll', 'FFlow', 'FPres','GndSpd',
												'OilT', 'OilP', 'MAP','CHT1','CHT2','CHT3', 'CHT4',
												'EGT1', 'EGT2', 'EGT3', 'EGT4','VSpdG'),
				'limit' => 500,
				'page' => $pageNum));
			$pageNum++;
		}

		$center = array('lat' => ($maxLat + $minLat)/2,
										'long' => ($maxLong + $minLong)/2);

		$minMax = array('maxLat' => $maxLat, 'minLat' => $minLat,
										'maxLong' => $maxLong, 'minLong' => $minLong);

		$zoomLevel = $this->calculateZoom($minMax);
		$this->makejscript($flightAggregate,$flight_id);
		return array('center' => $center,
								 'zoomLevel' => $zoomLevel);

	}

	private function calculateZoom($minMax){
		$latZoom =  (int)(12 - log(($minMax['maxLat'] - $minMax['minLat'])/0.15,2));
		$longZoom = (int)(12 - log(($minMax['maxLong'] - $minMax['minLong'])/0.22,2));
		if($latZoom < $longZoom)
			return $latZoom;
		return $longZoom;
	}

	private function makejscript($flightAggregate, $flight_id){

		$altitudeFileName = "al" . $flight_id . ".js";
		$altitudeFile = new File('js' . DS . 'Flightjs'. DS. $altitudeFileName, true, 0644);
		$altitudeFile->create();
		$altitudeFile->write( "var altAirspeed  = [");

		$latLongFile = new File('js' . DS .'Flightjs'. DS. "latlong" . $flight_id . ".js");
		$latLongFile->create();
		$latLongFile->write( "var flightCoords  = [");

		$engineString = "var engine = [";
		$trackingString = "var tracking = [";
		for($i=0; $i<count($flightAggregate); $i++){
			$altitudeFile->write( "[\"".$i."\",".
														$flightAggregate[$i]['Log']['AltGPS'].",".
														$flightAggregate[$i]['Log']['IAS']."],");
			$latLongFile->write( "new google.maps.LatLng(" . 
														$flightAggregate[$i]['Log']['Latitude'] . "," . 
														$flightAggregate[$i]['Log']['Longitude'] . "),");
			$engineString .= "[\"".$flightAggregate[$i]['Log']['Time']."\",".
														$flightAggregate[$i]['Log']['CHT1'].",".$flightAggregate[$i]['Log']['RPM']."],";
			$trackingString .= "[\"" .$flightAggregate[$i]['Log']['Time']."\",".
														$flightAggregate[$i]['Log']['TRK']."],\n";
		}
		$altitudeFile->write( "];");
		$latLongFile->write( "];");
		$engineString .= "];";
		$trackingString .= "];";
		$altitudeFile->write($engineString);
		$altitudeFile->write($trackingString);
	}
}