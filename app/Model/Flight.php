
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
	'csvPath' => array(
		'extension' => array(
			'rule'=> array('extension', array('csv')),
			'message' => 'Only csv files',
			),
		'upload-file' => array(
			'rule' => array('uploadFile'),
			'message' => 'Error uploading file')
		)
	);


	public function uploadFile( $check ) {
		// Shifts the first item out of the array, equivalent to popping the stack
		$uploadData = array_shift($check);
		// Check to make sure the file has data and there are no errors from upload
		if( $uploadData['size'] == 0 || $uploadData['error'] !== 0) {
			return false;
		}
		// This is the path to store the file relative to the webroot file in the home
		// directory. The file name will be the time stamp of when it is saved.
		// 'DS' is directory separator '/', '.' is concat
		$uploadFolder = 'files' . DS . 'flights';
		$fileName = time() . '.csv';
		$uploadPath = $uploadFolder . DS . $fileName;

		// Check to see there is a file, if not make one.
		if( !file_exists($uploadFolder) ) {
			mkdir($uploadFolder);
		}

		// When uploaded, it is stored as tmp_name in the upload data array. This moves
		// the csv from that array into our directory. Returns true if completed correctly
		// and false if something went wrong. Then assign the value of the csvPath cell
		// in the database to the path to the file.
		if (move_uploaded_file($uploadData['tmp_name'], $uploadPath)) {
			$this->set('csvPath', $fileName);
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
	public function getLatLong($path = null) {
		if(!$path){
			throw new NotFoundException(__('Invalid file'));
		}
		$file = new File('files'. DS . 'flights' . DS . $path, true, 0644);
		if($file->size() == 0){
			throw new NotFoundException(__('No such file'));
		}

		if($handle = fopen('files'. DS . 'flights' . DS . $path,'r')){

			// Discard the first 3 lines.
			fgetcsv($handle);
			fgetcsv($handle);
			fgetcsv($handle);
			// Initialize the array that we will return as an array of arrays.
			$latLongArray = array('lat' => array(),
														'long'=> array());
			$index = 0;

			// Pull the first line
			$data = fgetcsv($handle);
			// Keep pulling new lines until the lat and long are not blank
			while(ctype_space($data[4]) || ctype_space($data[5])){
				$data = fgetcsv($handle);
			}
			$latLongArray['lat'][$index] = $data[4];
			$latLongArray['long'][$index] = $data[5];
			$maxLat = $data[4];
			$minLat = $data[4];
			$maxLong = $data[5];
			$minLong = $data[5];
			// While there is still data to return...
			while($data = fgetcsv($handle)){
				// Store that data in the array.
				if(!empty($data[4]) && !empty($data[5]) && !ctype_space($data[4]) && !ctype_space($data[5])){
					$latLongArray['lat'][$index]  = $data[4];
					$latLongArray['long'][$index] = $data[5];
					if($data[4] < $minLat){
						$minLat = $data[4];
					}
					if($data[4] > $maxLat){
						$maxLat = $data[4];
					}				
					if($data[5] < $minLong){
						$minLong = $data[5];
					}				
					if($data[5] > $maxLong){
						$maxLong = $data[5];
					}
					$index++;
				}
			}

			$center = array('lat' => ($maxLat + $minLat)/2,
											'long' => ($maxLong + $minLong)/2);

			$minMax = array('minLat' => $minLat,
								  	  'maxLat' => $maxLat,
								 	    'minLong' => $minLong,
											'maxLong' => $maxLong,);

			$zoomLevel = $this->calculateZoom($minMax);

			return array('center' => $center,
									 'latLongArray' => $latLongArray,
									 'zoomLevel' => $zoomLevel);
		}
	}

	private function calculateZoom($minMax){
		$latZoom =  (int)(12 - log(($minMax['maxLat'] - $minMax['minLat'])/0.155,2));
		$longZoom = (int)(12 - log(($minMax['maxLong'] - $minMax['minLong'])/0.22,2));
		if($latZoom < $longZoom)
			return $latZoom;
		return $longZoom;
	}

}

?>