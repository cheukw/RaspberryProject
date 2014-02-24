<?php

App::uses('AppController', 'Controller');
App::import('Lib', 'Utilities');
App::import('Lib', 'SimpleCaptcha/SimpleCaptcha');

App::import('Lib', 'SecureSender');
App::import('Lib', 'SecureReceiver');

/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 * @property RequestHandlerComponent $RequestHandler
 * @author Ismael Valenzuela <iavalenzu@gmail.com>
 * @package Konalen.Controller
 * 
*/
class FormsController extends AppController {
    

  public $uses = array('PartnerAccess', 'IpAddressAccessAttempt', 'User', 'Partner', 'Identity', 'Service', 'ServiceForm', 'Account', 'AccountAccess', 'OneUsePacketInfo');
    
  public $helpers = array('Captcha');
  
  public function register(){

    $this->autoLayout = false;
    $this->autoRender = false;
    
  }  
  
  
  public function captcha($code){
      
      $this->autoLayout = false;
      $this->autoRender = false;
      
        if(empty($code)){
            return; 
        }
      
        $KonalenPrivateKey = Configure::read('KonalenPrivateKey');
        $KonalenPublicKey = Configure::read('KonalenPublicKey');
        
        if(empty($KonalenPrivateKey) || empty($KonalenPublicKey)){
            return "";
        }      
      
    /*
     * Se crea un recibidor seguro de mensajes y desencriptamos el checksum
     */
      $spr = new SecureReceiver();
      $spr->setSenderPublicKey($KonalenPublicKey);
      $spr->setRecipientPrivateKey($KonalenPrivateKey);
      
      $code_decrypted = $spr->decrypt($code);   

      $this->response->type('jpeg');

      $sc = new SimpleCaptcha();
      $sc->CreateImage($code_decrypted);
      $sc->WriteImage();
      
  }
  
  public function two_step_verification(){
      
        $this->Components->unload('DebugKit.Toolbar');

        $this->layout = 'api';

        $account_identity_id = Utilities::exists($this->request->query, 'AccountIdentityId', true, true, false);
        $service_id = Utilities::exists($this->request->query, 'ServiceId', true, true, false);
        $transaction_id = Utilities::exists($this->request->query, 'TransactionId', true, true, false);
        
        $checksum_key_encrypted = Utilities::exists($this->request->query, 'CheckSumKey', true, true, false);
        $checksum = Utilities::exists($this->request->query, 'CheckSum', true, true, false);
      
        /*
         * Se verifica que la ip que hace la peticion no se encuentre bloqueada
         */
        
        if($this->IpAddressAccessAttempt->isIpAddressBlocked()){
            
            /*
             * Si esta bloqueada denegamos el acceso
             */
            
            throw new UnauthorizedException(ResponseStatus::$access_denied);            
        }    
        
        /*
         * Se obtiene el servicio asociado
         */
        $service = $this->Service->findById($service_id);

        if(empty($service)){
            $this->IpAddressAccessAttempt->attempt();
            throw new UnauthorizedException(ResponseStatus::$access_denied);
        }
        
        /*
         * Se crea un recibidor seguro de mensajes y desencriptamos el checksum
         */
        $spr = new SecureReceiver();
        $spr->setSenderPublicKey($service['Partner']['public_key']);
        $spr->setRecipientPrivateKey(Configure::read('KonalenPrivateKey'));

        $checksum_key_decrypted = $spr->decrypt($checksum_key_encrypted);        
 
        if(empty($checksum_key_decrypted)){
            $this->IpAddressAccessAttempt->attempt();
            throw new UnauthorizedException(ResponseStatus::$access_denied);
        }

        /*
         * Se verifica que el checksum corresponda a la data enviada
         */
        if(hash_hmac('sha256', implode('.', array($account_identity_id, $service_id, $transaction_id)), $checksum_key_decrypted) !== $checksum){
            $this->IpAddressAccessAttempt->attempt();
            throw new UnauthorizedException(ResponseStatus::$access_denied);   
        }        
        
        $this->set('account_identity_id', $account_identity_id);
        $this->set('service_id', $service_id);
        $this->set('transaction_id', $transaction_id);
        $this->set('checksum', hash_hmac('sha256', implode('.', array($account_identity_id, $service_id, $transaction_id)), $checksum_key_decrypted));
        $this->set('checksum_key', $checksum_key_encrypted);

  }

  
  public function login(){
        
        $this->Components->unload('DebugKit.Toolbar');

        $this->layout = 'api';

        $partner_id = Utilities::exists($this->request->query, 'PartnerId', Utilities::$REQUIRED, Utilities::$NOT_EMPTY, '');
        $format_data = Utilities::exists($this->request->query, 'FormatData', Utilities::$REQUIRED, Utilities::$NOT_EMPTY, '');
        $enc_data = Utilities::exists($this->request->query, 'EncData', Utilities::$REQUIRED, Utilities::$NOT_EMPTY, '');

        /*
         * Se verifica que la ip que hace la peticion no se encuentre bloqueada
         */
        
        if($this->IpAddressAccessAttempt->isIpAddressBlocked()){
            
            /*
             * Si esta bloqueada se debe generar un codigo de desbloqueo que puede ser un captcha
             */
            
            $new_code = $this->IpAddressAccessAttempt->changeUnblockingCode();
            
            $this->set('captcha_code', $new_code);  
            
        }        
        
        /*
         * Se obtiene el partner asociado
         */
        
        $partner = $this->Partner->findById($partner_id);

        if(empty($partner)){
            $this->IpAddressAccessAttempt->attempt();
            throw new UnauthorizedException(ResponseStatus::$access_denied);
        }
        
        /*
         * Se crea un recibidor seguro de mensajes y desencriptamos el checksum
         */
        $spr = new SecureReceiver();
        $spr->setSenderPublicKey($partner['Partner']['public_key']);
        $spr->setRecipientPrivateKey(Configure::read('KonalenPrivateKey'));

        $dec_json_data = $spr->decrypt($enc_data);        

        /*
         * Se acuerdo al formato de la data decodeamos la data
         */
        
        if(strcasecmp($format_data, 'JSON') == 0){
            $data = json_decode($dec_json_data, true);
            $data = $data['data'];
        }
        
        $form_id = Utilities::exists($data, 'ServiceId', Utilities::$REQUIRED, Utilities::$EMPTY, '');
        $service_id = Utilities::exists($data, 'ServiceId', Utilities::$REQUIRED, Utilities::$NOT_EMPTY, '');
        $transaction_id = Utilities::exists($data, 'TransactionId', Utilities::$REQUIRED, Utilities::$NOT_EMPTY, '');
        
        /*
         * Se obtiene el servicio asociado
         */
        $service = $this->Service->findById($service_id);

        if(empty($service)){
            $this->IpAddressAccessAttempt->attempt();
            throw new UnauthorizedException(ResponseStatus::$access_denied);
        }
        
        /*
         * Se verifica que el service pertenezca al partner
         */
        
        if($service['Service']['partner_id'] != $partner_id){
            $this->IpAddressAccessAttempt->attempt();
            throw new UnauthorizedException(ResponseStatus::$access_denied);
        }
     
        /*
         * Se obtiene el form asociado al id
         */
        
        $service_form = $this->ServiceForm->getForm($form_id);
        
        if(empty($service_form)){
            $service_form = $this->ServiceForm->createForm($service);
        }

        /*
         * Dado que el ingreso fue exitoso, reseteamos los valores de intento de acceso
         */
        //$this->IpAddressAccessAttempt->reset();
        
        /*
         * Registramos la info del acceso
         */
        //$this->PartnerAccess->access($service);        

        $form_data = $service_form['ServiceForm']['data'];
        $form_id = $service_form['ServiceForm']['form_id'];
        $form_checksum_key = $service_form['ServiceForm']['checksum_key'];
        
        $this->set('form_data', $form_data);
        
        /*
         * Se chequea si el formulario esta en la verificacion de dos pasos
         */
        if($service_form['ServiceForm']['two_step_auth'] == 1){
            
            $this->set('two_step_auth_form', true);
            
        }        

        $this->set('form_id', $form_id);
        $this->set('service_id', $service_id);
        $this->set('transaction_id', $transaction_id);  

        $this->set('checksum', hash_hmac('sha256', implode('.', array($form_id, $service_id, $transaction_id)), $form_checksum_key));        
                
    }    
    
}

?>