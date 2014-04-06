<?php
class FlightsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('Session');
	
	public function beforeFilter() {
	    parent::beforeFilter();
	}

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
		ClassRegistry::init('User');
		$user = new User();
		if($this->request->is('post')) {
			if($this->request->data['Flight']['csvPath']['size'] == 0){
				$this->Session->setFlash('Attach CSV to upload flight.', 'fail');
				return $this->redirect(array('action' => 'add'));
			}
			if($this->request->data['Flight']['csvPath']['type'] != 'text/csv'){
				$this->Session->setFlash('Only attach CSVs for upload.', 'fail');
				return $this->redirect(array('action' => 'add'));
			}
			if(empty($user->findByUsername($this->request->data['Flight']['studentid']))){
				$this->Session->setFlash('User with this ID is not in the system.', 'fail');
				return $this->redirect(array('action' => 'add'));
			}
			$this->Flight->create();
			$data = $this->request->data;
			$csvData = $data['Flight']['csvPath'];
			unset($data['Flight']['csvPath']);
			$this->Flight->set('instructorID', $this->Auth->user('username'));

			if($this->Flight->save($data)) {

				$loadCSVArray = $this->Flight->uploadFile($csvData, $this->Flight->id);
				$this->Flight->save($loadCSVArray);

				$this->Session->setFlash('Your flight has been saved.', 'success');
				return $this->redirect(
					array('controller' => 'flights', 'action' => 'view', $this->Flight->id)
					);
			}
			$this->Session->setFlash('Unable to add your flight.', 'fail');
		}
	}

	public function delete($id)
	{
		ClassRegistry::init('Log');
		$log = new Log();

		if( $this->request->is('get') )
		{
			$this->Session->setFlash('You can not delete flights.', 'fail');
			return $this->redirect(array('action' => 'index'));
		}
		
		if($this->Auth->user('type') == 'admin')
		{
			if($this->Flight->delete($id) && $log->deleteLog($id))
			{
				$this->Session->setFlash("The flight has been deleted", 'success';
			}
			else
			{
				$this->Session->setFlash('Attempt to delete flight failed.', 'fail';
			}
		} 	
		else
		{
			$this->Session->SetFlash('Only an Administrator may delete flights.', 'fail');

		}
		return $this->redirect(array('contoller' => 'flights', 'action' => 'index'));

	}


	public function view($id = null) {
		if(!$id) {
			throw new NotFoundException(__('Invalid flight'));
		}		
		$flight = $this->Flight->findById($id);	
		if(!$flight){
			throw new NotFoundException(__('Invalid flight'));
		}	
		if($flight['Flight']['studentid'] != $this->Auth->user('username') && 
				$this->Auth->user('type') == 'student' ){
			$this->Session->setFlash('Not authorized to view this flight.','fail');
			return $this->redirect(
					array('controller' => 'flights', 'action' => 'index'));
		}

		$this->set('flight', $flight);
		$flightInfo = $this->Flight->getLatLong($flight['Flight']['id']);
		$this->set('jspath', 'Flightjs'.DS.'al' . $flight['Flight']['id']);
		$this->set('jslatlng', 'Flightjs'.DS.'latlong' . $flight['Flight']['id']);
		$this->set('center', array_shift($flightInfo));
		$this->set('zoomLevel', array_shift($flightInfo));

	}

	public function isAuthorized($user) {
	    // Admin can access every action
	    if ($user) {
	        return true;
	    }

	    // Default deny
	    return false;
	}
} 
