<?php
class FlightsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('Session');

	public function index(){
		$user = $this->Auth->user();
		$this->set('type', $this->Auth->user('type'));
		if($user['type'] != 'student'){
			$this->set('flights', $this->Flight->find('all'));
		} else {
			$this->set('flights', $this->Flight->findAllByStudentid('student'));
		}
	}

	public function add(){
		if($this->request->is('post')) {
			$this->Flight->create();
			$data = $this->request->data;
			$csvData = $data['Flight']['csvPath'];
			unset($data['Flight']['csvPath']);
			if($this->Flight->save($data)) {
				$this->Flight->uploadFile($csvData, $this->Flight->id);
				
				$this->Session->setFlash(__('Your post has been saved.'));
				return $this->redirect(
					array('controller' => 'flights', 'action' => 'view', $this->Flight->id)
					);
			}
			$this->Session->setFlash(__('Unable to add your post.'));
		}
	}

	public function view($id = null) {
		$user = $this->Auth->user();
		if(!$this->isAuthorized($user, $id)){
			$this->Session->setFlash(__('Not authorized to view this flight.'));
			return $this->redirect(
					array('controller' => 'flights', 'action' => 'index'));
		}

		if(!$id) {
			throw new NotFoundException(__('Invalid flight'));
		}


		$flight = $this->Flight->findById($id);
		if(!$flight){
			throw new NotFoundException(__('Invalid flight'));
		}

		$this->set('flight', $flight);
		$flightInfo = $this->Flight->getLatLong($flight['Flight']['id']);
		$this->set('jspath', 'Flightjs'.DS.'al' . $flight['Flight']['id']);
		$this->set('jslatlng', 'Flightjs'.DS.'latlong' . $flight['Flight']['id']);
		$this->set('center', array_shift($flightInfo));
		$this->set('zoomLevel', array_shift($flightInfo));

	}


	public function isAuthorized($user, $id = NULL) {
		if($id){
			$flight = $this->Flight->findById($id);
	    if ($flight['Flight']['studentid'] != $user['username'] &&
	    		$user['type'] == "student"){
	    	return false;
	    }
	    return true;
	  } else
	  return parent::isAuthorized($user);
	}
} 
