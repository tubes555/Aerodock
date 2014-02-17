<?php

class FlightsController extends AppController {
	public $helpers = array('Html', 'Form', 'Session');
	public $components = array('Session');

	public function index(){
		$this->set('flights', $this->Flight->find('all'));
	}

	public function add(){
		if($this->request->is('post')) {
			$this->Flight->create();
			if($this->Flight->save($this->request->data)) {
				$this->Session->setFlash(__('Your post has been saved.'));
				return $this->redirect(array('action' => 'index'));
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
		$this->set('csv', $this->Flight->getFileAsArray($flight['Flight']['csvPath']));
	}
} 
?>