<?php


class Utilities {
    
    public function getHeader($name = null, $require = true){
        
        $headers = apache_request_headers();
        
        if(isset($headers[$name]))
            return $headers[$name];

        if($require)
            return null;
        
        return null;
        
    }
    
    public function getRawPostData($require = true) {
        
        $output = null;
        $raw_data = file_get_contents('php://input');
        
        $content_type = env('CONTENT_TYPE');
        
        switch ($content_type) {
            case "application/json":
                $output = json_decode($raw_data, true);
                break;
            case "application/x-www-form-urlencoded;charset=UTF-8":
                parse_str($raw_data, $output);
                break;

            default:
                //Por defecto se considera como si las variables vinieran en texto plano
                parse_str($raw_data, $output);
                break;
        }
        
        if($require && empty($output))
            throw new BadRequestException(ResponseStatus::$missing_data);
        
        return $output;
        
    }
    
    public function exists($values = array(), $name = null, $require = true, $empty = false, $default = false){

        if(empty($values) || empty($name))
            throw new BadRequestException();
            
        if(isset($values[$name])){

            $value = trim($values[$name]);
            
            if(!$empty && empty($value)){
                //Si el valor es vacio y no es posible que sea vacio lanzamos una excepcion
                throw new BadRequestException(ResponseStatus::$missing_parameters);
            }else if($default){
                //Si esta permitido que sea vacio y esta definido el dafault, lo retornamos
                return $default;
            }
            
            return $value;
        }

        //Si en este punto no hemos retornado y el valor es requerido retornamos una excepcion
        if($require){
            throw new BadRequestException(ResponseStatus::$missing_parameters);
        }
        
        return null;
        
    }
    
    public function getAuthorizationKey() {
        
        $authorization = Utilities::getHeader('Authorization', true);
        
        if(empty($authorization))
            return null;
        
        $matches = array();
        
        if(!preg_match('/key=([a-zA-Z0-9_]+)/i', $authorization, $matches)){
            return null;
        }
        
        return isset($matches[1]) ? $matches[1] : null;

    }
    
    
    /**
   * Get the IP the client is using, or says they are using.
   *
   * @param boolean $safe Use safe = false when you think the user might manipulate their HTTP_CLIENT_IP
   *   header. Setting $safe = false will will also look at HTTP_X_FORWARDED_FOR
   * @return string The client IP.
   */
      public function clientIp($safe = true) {
          
          if (!$safe && env('HTTP_X_FORWARDED_FOR')) {
              $ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
          } else {
              if (env('HTTP_CLIENT_IP')) {
                  $ipaddr = env('HTTP_CLIENT_IP');
              } else {
                  $ipaddr = env('REMOTE_ADDR');
              }
          }
  
          if (env('HTTP_CLIENTADDRESS')) {
              $tmpipaddr = env('HTTP_CLIENTADDRESS');
  
              if (!empty($tmpipaddr)) {
                  $ipaddr = preg_replace('/(?:,.*)/', '', $tmpipaddr);
              }
          }
          return trim($ipaddr);
      }    

      public function clientUserAgent() {
          
          $user_agent = '';
          
          if(env('HTTP_USER_AGENT')){
              $user_agent = env('HTTP_USER_AGENT');
              $user_agent = trim($user_agent);
          }
          
          return $user_agent;
          
     }    
      
      
    /*Agrega un checksum al codigo generado para luego poder comparar la integridad del codigo y identificar si se envian codigos incorrectos*/
    
    public function createCode($min = 50, $max = 70) {
        
        $length = mt_rand($min, $max);
        
        $characters = '0123456789abc' . 'efghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        
        $crc = crc32($randomString);
        $crc = base_convert($crc, 10, 26);
        
        return $randomString . 'd' . $crc;
        
    }

    public function checkCode($code = null) {

        if(empty($code))
            return false;
        
        //Buscamos la primera aparicion de la letra 'd'
        $randomString = strstr($code, 'd', true);
        
        $crc = crc32($randomString);
        $crc = base_convert($crc, 10, 26);
        $randomString = $randomString . 'd' . $crc;
        
        return strcmp($code, $randomString) == 0;
        
    }
    
    
}

?>
