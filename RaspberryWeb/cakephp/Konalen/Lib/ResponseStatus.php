<?php

/**
 * @author Ismael Valenzuela <iavalenzu@gmail.com>
 * @package Konalen.Lib
 */

class ResponseStatus {
    
    public static $user_already_registered = 'USER_ALREADY_REGISTERED';    
    public static $server_error = 'SERVER_ERROR';   
    public static $ip_address_blocked = 'IP_ADDRESS_BLOCKED';
    public static $missing_header = 'MISSING_HEADER';
    public static $missing_parameters = 'MISSING_PARAMETERS';
    public static $missing_data = 'MISSING_DATA';
    public static $missing_auth_key = 'MISSING_AUTH_KEY';
    public static $access_denied = 'ACCESS_DENIED';
    public static $user_inactive = 'USER_INACTIVE';
    public static $max_login_attempts_exceeded = 'MAX_LOGIN_ATTEMPTS_EXCEEDED';
    public static $session_invalid = 'SESSION_INVALID';
    public static $ok = 'OK';
    public static $error = 'ERROR';
    public static $invalid_session = 'INVALID_SESSION';
    
}
?>
