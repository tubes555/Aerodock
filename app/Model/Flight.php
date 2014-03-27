<?php
class Flight extends AppModel {

//page 317 of cakePHPCookbook
	public $validate = array(
	'studentID' => array(
		'rule' => 'notEmpty'
		),
	'instructorID' => array(
		'rule' => 'notEmpty'
		),
	'aircraft' => array(
		'rule' => 'notEmpty'
		),
	'tailNo' => array(
		'rule' => 'notEmpty'
		),
	);


	public function uploadFile( $uploadData, $id ) {
		ClassRegistry::init('Log');

		$log = new Log();

		// Shifts the first item out of the array, equivalent to popping the stack
		// Check to make sure the file has data and there are no errors from upload
		if( $uploadData['size'] == 0 || $uploadData['error'] !== 0) {
			return false;
		}
		if($log->loadCSV($uploadData, $id)){
			return true;
		}

		return false;
	}


	// Returns the flight csv file headers. This method is incomplete and will be 
	// fleshed out as we know what is needed in the application.
	public function getFileAsArray($path = null){
		if(!$path){
			throw new NotFoundException(__('Invalid file'));
		}
		$file = new File('files'. DS . 'flights' . DS . $path, true, 0644);
		if($file->size() == 0){
			throw new NotFoundException(__('No such file'));
		}

		if($handle = fopen('files'. DS . 'flights' . DS . $path,'r')){
			//Discard the first two lines, they are plane information stuff
			fgetcsv($handle);
			fgetcsv($handle);
			// Get the headers from the csv, does not include units. Stored as array.
			$headerArray =fgetcsv($handle);

			return $headerArray;
		}
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
		$pageNum = 1;
		$flightInfo = $log->find('all', array(
			'conditions' => array('Log.flight_id' => $flight_id),
			'fields' => array('Log.Latitude', 'Log.Longitude', 'Log.AltMSL', 'Log.IAS'),
			'limit' => 1));

		$index = 0;
		$latLongArray['lat'][$index] = $flightInfo[0]['Log']['Latitude'];
		$latLongArray['long'][$index] = $flightInfo[0]['Log']['Longitude'];
		$altitude[$index] = $flightInfo[0]['Log']['AltMSL'];
		$airspeed[$index] = $flightInfo[0]['Log']['IAS'];

		$maxLat = $latLongArray['lat'][$index];
		$minLat = $maxLat;
		$maxLong = $latLongArray['long'][$index];
		$minLong = $maxLong;
		$index++;
		// While there is still data to return...
		while(count($flightInfo) != 0){
			for($j=0; $j < count($flightInfo); $j++){
				$latLongArray['lat'][$index]  = $flightInfo[$j]['Log']['Latitude'];
				$latLongArray['long'][$index] = $flightInfo[$j]['Log']['Longitude'];
				$altitude[$index] = $flightInfo[$j]['Log']['AltMSL'];
				$airspeed[$index] = $flightInfo[$j]['Log']['IAS'];
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
				$index++;
			
			}
			$pageNum++;
			$flightInfo = $log->find('all', array(
				'conditions' => array('Log.flight_id' => $flight_id),
				'fields' => array('Log.Latitude', 'Log.Longitude', 'Log.AltMSL', 'Log.IAS'),
				'limit' => 500,
				'page' => $pageNum));
		}

		// Store that data in the array.

		$center = array('lat' => ($maxLat + $minLat)/2,
										'long' => ($maxLong + $minLong)/2);

		$minMax = array('maxLat' => $maxLat, 'minLat' => $minLat,
										'maxLong' => $maxLong, 'minLong' => $minLong);

		$zoomLevel = $this->calculateZoom($minMax);

		$this->makejscript($altitude, $airspeed, $latLongArray, $flight_id);

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

	private function makejscript($altitude, $airspeed, $latLong, $path){
		$altitudeFileName = "al" . $path . ".js";
		$altitudeFile = new File('js' . DS . $altitudeFileName, true, 0644);
		$altitudeFile->create();
		$altitudeFile->write( "var altAirspeed  = [");

		$latLongFile = new File('js' . DS . "latlong" . $path . ".js");
		$latLongFile->create();
		$latLongFile->write( "var flightCoords  = [");


		for($i=0; $i<count($altitude); $i++){
			if(!empty($altitude[$i]) && !empty($airspeed[$i]) && 
				!empty($latLong['lat'][$i]) && !empty($latLong['long'][$i]) &&
				!ctype_space($latLong['lat'][$i]) && !ctype_space($latLong['long'][$i]))
			{
				$altitudeFile->write( "[".$i.",".$altitude[$i].",".$airspeed[$i]."],");
				$latLongFile->write( "new google.maps.LatLng(" . floatval($latLong['lat'][$i]) . "," . floatval($latLong['long'][$i]) . "),\n");
			}
		}
		$altitudeFile->write( "];");
		$latLongFile->write( "];");
	}

}