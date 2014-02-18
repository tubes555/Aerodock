
<?php
class Flight extends AppModel {
//page 317
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
		pr($check);
		$uploadData = array_shift($check);

		if( $uploadData['size'] == 0 || $uploadData['error'] !== 0) {
			return false;
		}

		$uploadFolder = 'files' . DS . 'flights';
		$fileName = time() . '.csv';
		$uploadPath = $uploadFolder . DS . $fileName;

		if( !file_exists($uploadFolder) ) {
			mkdir($uploadFolder);
		}

		if (move_uploaded_file($uploadData['tmp_name'], $uploadPath)) {
			$this->set('csvPath', $fileName);
			return true;
		}

		return false;
	}

	public function getFileAsString($path = null){
		$file = new File('files'. DS . 'flights' . DS . $path, true, 0644);
		if($file->open('r',false)){
			return $file->read(false,'r', true);
		}
	}

	public function getFileAsArray($path = null){
		if(!$path){
			throw new NotFoundException(__('Invalid file'));
		}
		$file = new File('files'. DS . 'flights' . DS . $path, true, 0644);
		if($file->size() == 0){
			throw new NotFoundException(__('No such file'));
		}

		if($handle = fopen('files'. DS . 'flights' . DS . $path,'r')){
			
			fgetcsv($handle);
			fgetcsv($handle);
			$headerArray =fgetcsv($handle);

			return $headerArray;
		}
	}

	public function getLatLong($path = null) {
		if(!$path){
			throw new NotFoundException(__('Invalid file'));
		}
		$file = new File('files'. DS . 'flights' . DS . $path, true, 0644);
		if($file->size() == 0){
			throw new NotFoundException(__('No such file'));
		}

		if($handle = fopen('files'. DS . 'flights' . DS . $path,'r')){
			
			fgetcsv($handle);
			fgetcsv($handle);
			fgetcsv($handle);
			$latLongArray = array('lat' => array(),
														'long'=> array());
			$index = 0;
			while($data = fgetcsv($handle)){
				$latLongArray['lat'][$index] = $data[4];
				$latLongArray['long'][$index] = $data[5];
				$index++;
			}

			return $latLongArray;
		}
	}
}

?>