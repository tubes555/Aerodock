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

  public function logout() {
    return $this->redirect($this->Auth->logout());
  }

  public function view($id = NULL){
    $user = $this->Auth->user();
    if(!$id) {
      throw new NotFoundException(__('Invalid user'));
    }
    if(!$user){
      $this->Session->setFlash(__('Please log in to use site.'));
      return $this->redirect(
          array('controller' => 'users', 'action' => 'login'));
    }
    if($user['id'] != $id && $user['type'] == 'student'){
      $this->Session->setFlash(__('Not authorized to view this user page.'));
      return $this->redirect(
          array('controller' => 'flights', 'action' => 'index'));
    }
    $this->set('user', $user);

    if($user['type'] == 'student'){
      $this->set('type', 0);
    } else if($user['type'] == 'teacher'){
      $this->set('type', 1);
    } else if($user['type'] == "maint"){
      $this->set('type', 2);
    } else if($user['type'] == 'admin'){
      $this->set('type', 3);
    }
  }

  public function edit($id = null){
    $this->User->id = $id;
    if (!$this->User->exists()) {
        throw new NotFoundException(__('Invalid user'));
    }
    if ($this->request->is('post') || $this->request->is('put')) {
      $user = $this->User->findById($id);
      if($this->Auth->user('type') != 'admin'){
        $this->User->set('type', $user['User']['type']);
      } else {
        if($this->request->data['User']['type'] == 0){
          $this->User->set('type', 'student');
        } else if($this->request->data['User']['type'] == 1){
          $this->User->set('type', 'teacher');
        } else if($this->request->data['User']['type'] == 2){
          $this->User->set('type', 'maint');
        } else if($this->request->data['User']['type'] == 3){
          $this->User->set('type', 'admin');
        }
      }

      $this->User->set('firstname', $this->request->data['User']['firstname']);
      $this->User->set('lastname', $this->request->data['User']['lastname']);

      if ($this->User->save()) {
          $this->Session->setFlash(__('The changes has been saved'));
          return $this->redirect(array('controller' => 'flights', 'action' => 'index'));
      }
      $this->Session->setFlash(
          __('The user could not be saved. Please, try again.')
      );
    } else {
      $this->request->data = $this->User->findById($id);
      $user = $this->Auth->user();
      if(!$id) {
        throw new NotFoundException(__('Invalid user'));
      }
      if(!$user){
        $this->Session->setFlash(__('Please log in to use site.'));
        return $this->redirect(
            array('controller' => 'users', 'action' => 'login'));
      }
      if($user['id'] != $id && $user['type'] == 'student'){
        $this->Session->setFlash(__('Not authorized to view this user page.'));
        return $this->redirect(
            array('controller' => 'flights', 'action' => 'index'));
      }
      $this->set('user', $user);

      if($user['type'] == 'student'){
        $this->set('type', 0);
      } else if($user['type'] == 'teacher'){
        $this->set('type', 1);
      } else if($user['type'] == "maint"){
        $this->set('type', 2);
      } else if($user['type'] == 'admin'){
        $this->set('type', 3);
      }
    }
  }


  public function isAuthorized($user) {
    if($user){
      if ($this->Auth->user() != $user &&
          $user['type'] == "student"){
        return false;
      }
      return true;
    } else
    return false;
  }
}