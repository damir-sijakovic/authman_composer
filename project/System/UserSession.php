<?php 

namespace Authman\System;
use Authman\System\Session\Session;
use Authman\System\Connection;
use Authman\System\Encryption;
use Authman\System\CheckString;

class UserSession extends Session
{    
    public function __construct()
    {           
        parent::__construct('_DSIJAK_AUTHMAN_USER_SESSION_');
    }
   
    
    public function login($assoc)
    {       
        if (!$this->keyExists('loginId'))
        {
            $this->set('loginId', bin2hex(openssl_random_pseudo_bytes(16)));
            $this->set('csrfToken', Encryption::generateCsrfToken());
            
            if (isset($assoc['userId'])) $this->set('userId', $assoc['userId']);
            if (isset($assoc['pkId'])) $this->set('pkId', $assoc['pkId']);
            if (isset($assoc['role'])) $this->set('role', $assoc['role']);
            $this->set('loggedOnAt',     time());
            $this->set('loginExpiresAt', strtotime( DS_AM_LOGIN_EXPIRES_AT ));
        }
    }
        
    public function setCsrf($token)
    {  
        return $this->set('csrfToken', $token);
    }
    
    public function getCsrf()
    {  
        return $this->get('csrfToken');
    }
    
    public function getRole()
    {  
        return $this->get('role');
    }
    
    public function setRole($role)
    {  
        if (!CheckString::validRoleName($role))
        {
            return false;
        }
        
        $this->set('role',$role);
        return true;
    }
   
    public function logout()
    {  
        $this->empty();
    }
   
   
    public function getLoginId()
    {  
        if ($this->keyExists('loginId'))
        {
            
            return $this->get('loginId');
        }
      
        return null;
    }
   
    public function getUserId() //this is email
    {  
        
        if ($this->keyExists('userId'))
        {
            
            return $this->get('userId');
        }
      
        return null;
    }
   
    public function getPkId()
    {  
        
        if ($this->keyExists('pkId'))
        {
            
            return intval($this->get('pkId'));
        }
      
        return null;
    }
    
    public function isUserVerified() 
    {       
        if ($this->get('role') === 'user' || $this->get('role') === 'admin')
        {
            return true;
        }
        
        return false;        
    }
    
    public function setConfirmationCode($code) 
    {       
        $this->set('confirmationCode', $code);     
    }
    
    public function getConfirmationCode() 
    {       
        return $this->get('confirmationCode');     
    }
    
    public function removeConfirmationCode() 
    {       
        return $this->delete('confirmationCode');     
    }
    

 
}
    
    
    
