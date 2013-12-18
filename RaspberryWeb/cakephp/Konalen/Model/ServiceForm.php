<?php
App::uses('AppModel', 'Model');
/**
 * ServiceForm Model
 *
 * @property Service $Service
 */
class ServiceForm extends AppModel {


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
         * 
         * @param Partner $partner
         * @return PartnerForm 
         */
        
        public function getForm($service = null, $form_id = null){
            
            if(empty($service)){
                return false;
            }
            
            $service_form = $this->find('first', array(
                'conditions' => array(
                    'ServiceForm.form_id' => $form_id,
                    'ServiceForm.service_id' => $service['Service']['id'],
                    'ServiceForm.form_expire >' => date('Y-m-d H:i:s') 
                )
            ));            
            
            if(empty($service_form)){
                //Crea uno nuevo
                $service_form = $this->createForm($service);
                
            }
            
            return $service_form;
            
        }          
        
        public function createForm($service = null){
            
            if(empty($service)){
                return false;
            }
            
            $dataSource = $this->getDataSource();
            
            $dataSource->begin();
            
            $form_id = $this->createFormId();
            
            $form_timeout = $service['Service']['form_timeout'];
            
            $service_form = array(
                'ServiceForm' => array(
                    'service_id' => $service['Service']['id'],
                    'form_id' => $form_id,
                    'form_expire' => date('Y-m-d H:i:s', time() + $form_timeout)
                )
            );

            $this->create();
            
            if($form_id && $this->save($service_form)){
                if($dataSource->commit()){
                    return $this->findById($this->id);
                }
            }else{
                $dataSource->rollback();
            }

            throw new InternalErrorException(ResponseStatus::$server_error);
                        
        }        
        
        
        /* 
         * @return string En caso de no lograr generar el nuevo id de sesion retorna false.
         */
        
        public function createFormId(){

            $num_bits = 4096;
            $max_attempts = Configure::read('SessionIdGenerationAttempts');
            
            for($i=0; $i<$max_attempts; $i++){
                
                $code = Utilities::getRandomCode($num_bits);
                
                $form = $this->findByFormId($code);
                
                if($code && empty($form)){
                    return $code;
                }
            }

            return false;
            
        }               
        
}
