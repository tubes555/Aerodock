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
		if($this->request->is('post')) {
			$this->Flight->create();
			$data = $this->request->data;
			$csvData = $data['Flight']['csvPath'];
			unset($data['Flight']['csvPath']);
			$this->Flight->set('aircraft', "Diamond DA 40");
			$this->Flight->set('instructorID', $this->Auth->user('username'));

			if($this->Flight->save($data)) {

				$loadCSVArray = $this->Flight->uploadFile($csvData, $this->Flight->id);
				$this->Flight->set('duration', $loadCSVArray['duration']);
				$this->Flight->set('date', $loadCSVArray['date']);
				$this->Flight->save();

				$this->Session->setFlash(__('Your post has been saved.'));
				return $this->redirect(
					array('controller' => 'flights', 'action' => 'view', $this->Flight->id)
					);
			}
			$this->Session->setFlash('Unable to add your post.', 'default', array(), 'danger');
		}
	}

	public function delete($id)
	{
		ClassRegistry::init('Log');
		$log = new Log();

		if( $this->request->is('get') )
		{
			$this->Session->setFlash('You can not delete flights.', 'default', array(), 'danger');
			return $this->redirect(array('action' => 'index'));
		}
		
		if($this->Auth->user('type') == 'admin')
		{
			if($this->Flight->delete($id) && $log->deleteLog($id))
			{
				$this->Session->setFlash('The flight has been deleted', 'default', array(), 'success');
			}
			else
			{
				$this->Session->setFlash('Attempt to delete flight failed.', 'default', array(), 'danger');
			}
		} 	
		else
		{
			$this->Session->SetFlash('Only an Administrator may delete flights.', 'default', array(), 'danger');

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
			$this->Session->setFlash('Not authorized to view this flight.','default', array(), 'danger');
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
