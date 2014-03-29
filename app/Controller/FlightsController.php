<?php
class FlightsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('Session');

	public function index(){
		$this->set('flights', $this->Flight->find('all'));
	}

	public function add(){
		$timefile = new File('timeController', true, 0644);
		$timefile->create();
		if($this->request->is('post')) {
			$this->Flight->create();
			$data = $this->request->data;
			$csvData = $data['Flight']['csvPath'];
			unset($data['Flight']['csvPath']);
			if($this->Flight->save($data)) {
				$pre = microtime(true);
				$this->Flight->uploadFile($csvData, $this->Flight->id);
				$post = microtime(true);
				
				$timefile->write($post-$pre."\n");
				$this->Session->setFlash(__('Your post has been saved.'));
				return $this->redirect(
					array('controller' => 'flights', 'action' => 'view', $this->Flight->id)
					);
			}
			$this->Session->setFlash(__('Unable to add your post.'));
		}
	}

	public function view($id = null) {
		if(!$id) {
			throw new NotFoundException(__('Invalid flight'));
		}

		$flight = $this->Flight->findById($id);
		if(!$flight){
			throw new NotFoundException(__('Invalid flight'));
		}

		$this->set('flight', $flight);
		$flightInfo = $this->Flight->getLatLong($flight['Flight']['id']);
		$this->set('jspath', 'al' . $flight['Flight']['id']);
		$this->set('jslatlng', 'latlong' . $flight['Flight']['id']);
		$this->set('center', array_shift($flightInfo));
		$this->set('zoomLevel', array_shift($flightInfo));

	}
} 
