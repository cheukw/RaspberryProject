<?php
App::uses('AppModel', 'Model');




class Login{
    
    static public $ERROR = false;
    static public $SUCCESS = true;
    
    public $loginSuccess = false;
    public $loginAccess = false;
    public $loginUser = null;
    public $loginErrors = array();
    
    public $loginMsg = '';
    
    
    function __construct($login_success = false, $login_errors = array(), $login_user = null, $login_access = null) {

        $this->loginUser = $login_user;
        $this->loginAccess = $login_access;
        $this->loginSuccess = $login_success;
        $this->loginErrors = $login_errors;
        
        if($this->loginSuccess){
            $this->loginMsg = 'LOGIN SUCCESS';
        }else{
            $this->loginMsg = 'LOGIN ERROR';
        }
        
    }
    
    public function isSuccess(){
        return $this->loginSuccess;
    }
    
    public function getUser(){
        return $this->loginUser;
    } 
    
    public function getAccess(){
        return $this->loginAccess;
    }

    public function getErrors(){
        return $this->loginErrors;
    } 

    public function getMsg($field_name = false){
        
        if(empty($field_name) || !isset($this->loginErrors[$field_name])){
            return '';
        }
        
        return $this->loginErrors[$field_name];
        
    }
    
    
}


/**
 * Account Model
 *
 * @property User $User
 * @property Service $Service
 * @property UserAccess $UserAccess
 */
class Account extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Service' => array(
			'className' => 'Service',
			'foreignKey' => 'service_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'AccountAccess' => array(
			'className' => 'AccountAccess',
			'foreignKey' => 'account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'AccountIdentity' => array(
			'className' => 'AccountIdentity',
			'foreignKey' => 'account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Notification' => array(
			'className' => 'Notification',
			'foreignKey' => 'account_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
        
        
        public function login($service = null, $user_id = null, $user_pass = null){

            if(empty($service) || empty($user_id) || empty($user_pass)){
                return new Login(Login::$ERROR);
            }
            
            $identity = $this->AccountIdentity->getIdentity($user_id);
            
            if(empty($identity)){
                return array(
                    'success' => false,
                    'error' => array(
                        'msg' => 'Login Error'
                    )
                );
            }
            
            $account = $this->find('first', array(
                'conditions' => array(
                    'Account.service_id' => $service['Service']['id'],
                    'Account.user_password' => sha1($user_pass)
                ),
                'recursive' => 0
            ));

            if(empty($account)){
                return new Login(Login::$ERROR);
            }
            
            $account_identity = $this->AccountIdentity->check($account, $identity);
            
            if(empty($account_identity)){
                return new Login(Login::$ERROR);
            }
            
            /*
             * Se crea un registro  de acceso de usuario
             */
            $this->AccountAccess->createAccess($account);

            return new Login(Login::$SUCCESS, false, $account_identity);
            
        }
        

}
