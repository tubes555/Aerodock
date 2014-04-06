<?php
class UsersController extends AppController {

	public function login() {
    if ($this->request->is('post')) {
    	// Validate pipeline request here
    	$user = $this->User->findByUsername($this->request->data['User']['username']);
      if ($this->Auth->login()) {
        return $this->redirect(array('controller' => 'flights', 'action' => 'index'));
      }
      $this->Session->setFlash('Invalid username or password, try again', 'fail');
    }
  }

  public function add() {
    if($this->request->is('post')){
      $this->User->create();
      $data = $this->request->data;
      $csvData = $data['User']['csvPath'];
      unset($data['User']['csvPath']);
      if($this->User->uploadUsers($csvData))
      {
        $this->Session->setFlash('New users file submitted.', 'success');
        return $this->redirect(
          array('conroller' => 'users', 'action' => 'index'));
      }
      $this->Session->setFlash('Unable to add new users.','fail');
    }

  }

  public function delete($id) 
  {

    ClassRegistry::init('User');
    $user = new User();
    $user->delete($id);
 
    if($this->request->is('get'))
    {
      throw new MethodNotAllowedException();
    }
    if($this->Auth->user('type') == "admin")
    {
      $user->delete($id);
    }

  }

  public function logout() {
    return $this->redirect($this->Auth->logout());
  }

  public function view($id = NULL){
    $user = $this->Auth->user();
    if(!$id) {
      throw new NotFoundException('Invalid user','fail');
    }
    if(!$user){
      $this->Session->setFlash('Please log in to use site.','fail');
      return $this->redirect(
          array('controller' => 'users', 'action' => 'login'));
    }
    if($user['id'] != $id && $user['type'] == 'student'){
      $this->Session->setFlash('Not authorized to view this user page.','fail');
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
        throw new NotFoundException('Invalid user','fail');
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
          $this->Session->setFlash('The changes has been saved','success');
          return $this->redirect(array('controller' => 'flights', 'action' => 'index'));
      }
      $this->Session->setFlash('The user could not be saved. Please, try again.','fail');
    } else {
      $user = $this->User->findById($id);
      $this->request->data = $user;
      if(!$id) {
        throw new NotFoundException(__('Invalid user'));
      }
      if(!$user){
        $this->Session->setFlash('Please log in to use site.','fail');
        return $this->redirect(
            array('controller' => 'users', 'action' => 'login'));
      }
      if($this->Auth->user('id') != $id && $this->Auth->user('type') == 'student'){
        $this->Session->setFlash('Not authorized to view this user page.','fail');
        return $this->redirect(
            array('controller' => 'flights', 'action' => 'index'));
      }
      $this->set('user', $user);

      if($user['User']['type'] == 'student'){
        $this->set('type', 0);
      } else if($user['User']['type'] == 'teacher'){
        $this->set('type', 1);
      } else if($user['User']['type'] == "maint"){
        $this->set('type', 2);
      } else if($user['User']['type'] == 'admin'){
        $this->set('type', 3);
      }
    }
  }

  public function index(){
    if($this->Auth->user('type') == 'admin'){
      $this->set('users', $this->User->find('all'));
    } else if($this->Auth->user('type') == 'teacher'){
      $this->set('users', $this->User->findAllByType('student'));
    } else {
      $this->Session->setFlash('Not authorized to view this user page.','fail');
       return $this->redirect(
            array('controller' => 'flights', 'action' => 'index'));
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