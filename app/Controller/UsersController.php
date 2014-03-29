<?php
class UsersController extends AppController {

	public function login() {
    if ($this->request->is('post')) {
    	// Validate pipeline request here
    	$user = $this->User->findByUsername($this->request->data['User']['username']);
      if ($this->Auth->login()) {
        return $this->redirect(array('controller' => 'flights', 'action' => 'index'));
      }
      $this->Session->setFlash(__('Invalid username or password, try again'));
    }
  }
}