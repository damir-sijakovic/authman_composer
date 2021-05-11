<?php

namespace Authman;
use Authman\System\AuthmanAdmin;
use Authman\System\SystemSession;
use Authman\System\UserSession;
use Authman\System\Encryption;
use Authman\System\Cookie;
use Authman\System\CheckString;

class Authman 
{
    public $admin;  
      
    private $systemSession;    
    private $userSession;    
    
    public function __construct() 
    {  
        $this->userSession = new UserSession();
        $this->systemSession = new SystemSession($this->userSession);   
        
        $this->admin = new AuthmanAdmin($this->systemSession, $this->userSession);
    }
     
    public function register($email, $password, &$message=null) 
    {        
        if ($this->isLoggedOn())
        {
            $message = 'You are already logged on.'; 
            return false;
        }    
        
        return $this->admin->addUser(['email'=>$email, 'password'=>$password], $message);
    }
    
    public function addExtraData($assoc, &$message=null) 
    {            
        if (!$this->isLoggedOn())
        {
            $message = 'Not logged on.'; 
            return false;
        }
        return $this->admin->addExtraData($assoc, $message);
    }
    
    
    public function unregister($password, &$message=null) 
    {      
        if (!$this->isLoggedOn())
        {
            $message = 'Not logged on.'; 
            return false;
        }
        
          
        if ($this->admin->verifyLoggedOnPassword($password))
        {            
            $pkid = $this->userSession->getPkId();
            return $this->admin->deactivateUser($pkid);
        }
        else
        {
            $message = 'Invalid password.'; 
            return false;
        }

    }
    
    
    public function login($email, $password, &$message=null) 
    {      
        
        $this->userSession->logout();
        $this->systemSession->request();
        $this->systemSession->afterRequest();
           
        if (DS_AM_WRONG_PASS_ENABLED)
        {
            if ($this->systemSession->isLoginBanned())
            {
                $message = 'Login is banned by brute-force protection.'; 
                $this->logout();           
                return false;
            }
        }       
        
        if (DS_AM_BRUTE_FORCE_ENABLED)
        {     
            $bruteForceCheck = $this->systemSession->bruteForceCounter();
            if (!$bruteForceCheck)
            {
                $message = 'Login blocked by brute-force protection.';            
                return false;
            }
        }
        
        //check if email exists         
        $returnArr = $this->admin->getUserDataByEmail($email, $message);
        if (empty($returnArr))
        {
            return false;
        }
        
        if ($returnArr['active'] === '0')
        {
            $message = "Account is dectivated.";
            return false;
        }        
        
        $passOk = Encryption::verifyPassword($password, $returnArr['password']);
        
        if (!$passOk)
        {            
            if (DS_AM_WRONG_PASS_ENABLED)
            {
                $wrongPassCounterCheck = $this->systemSession->wrongPassCounter();
                if (!$wrongPassCounterCheck)
                {
                    $message = "You’ve reached the maximum login attempts.";
                    return false;
                }   
            }
            
            $message = 'Invalid password!';
            return false;
        }
        
        if ($this->admin->isUserBanned($email))
        {
            //error_log('USER-BANNED');
            $message = 'User is banned!';
            return false;  
        }      
             
        if (!$this->systemSession->isUserLoggedOn())
        {  
            $this->systemSession->login([
                'userId'=>$email,
                'pkId'=>$returnArr['id'],
                'role'=>$returnArr['role'],
            ]); 
            
            $jwt = Encryption::generateToken($this->userSession->getLoginId(), strtotime(DS_AM_JWT_EXP), null);
            Cookie::set('Authorization', 'Bearer ' . $jwt); //httpOnly cookie
            
            if ($this->admin->isUserVerified())
            {
                //error_log('USERISVERIFIED');
            }
            else
            {
                //error_log('USERISNOTVERIFIED');                
                $confirmationCode = $this->admin->generateConfirmationCode();
                
            }             
            
            return true;
        }
        
        
        return false;
       
    }
     
    
    public function verifyUser($confirmationCode, &$eMessage=null) 
    {    
        if (!$this->isLoggedOn())
        {
            $message = 'Not logged on.'; 
            return false;
        }
        
        if ($this->isUserVerified())
        {
             $eMessage = 'User is already verified!';
             return false;
        }       
             
        if (!CheckString::validConfirmationCode($confirmationCode))
        {
            $eMessage = 'Invalid confirmation code string!';
            return false;
        }
            
        return $this->admin->verifyUserConfirmationCode($confirmationCode, $eMessage);
    }
    
    public function isUserVerified() 
    {        
        if (!$this->isLoggedOn())
        {
            $message = 'Not logged on.'; 
            return false;
        }
           
        return $this->admin->isUserVerified();
    }
    
    public function sendConfirmationCodeEmail(&$eMessage=null) 
    {        
        if ($this->isLoggedOn())
        {
            if ($this->isUserVerified())
            {
                return false;
            }
            
            return $this->admin->sendConfirmationCodeEmail($eMessage); 
        }
        
        return false;
    }
        
    public function logout() 
    {            
        if (!$this->isLoggedOn())
        {
            return false;
        }
        
        Cookie::delete('Authorization');
        $this->systemSession->logout();  
        
        return true;
    }
    
    public function getCsrf() 
    {            
        if (!$this->isLoggedOn())
        {
            return null;
        }
        
        return $this->userSession->getCsrf();
    }

    public function changeRole($role, &$eMessage=null) 
    {     
        if (!$this->isLoggedOn())
        {
            return null;
        }
        
        $pkid = $this->userSession->getPkId();
        return $this->admin->setUserRole($pkid, $role, $eMessage);   
    }

    public function isCsrfValid($token) 
    {      
        if (!$this->isLoggedOn())
        {
            return null;
        }
              
        $sessionToken = $this->systemSession->getCsrf();  
        if ($sessionToken)
        {
            if ($sessionToken === $token)
            {
                return true;
            }
        }

        return false;
    }

    public function isLoggedOn() 
    {            
       return $this->systemSession->isUserLoggedOn();
    }
    


    public function refresh(&$message=null) 
    {   
        if (!$this->isLoggedOn())
        {
            return false;
        }
           
        if (Cookie::keyExists('Authorization'))
        {
            $token = str_replace('Bearer ', '', Cookie::get('Authorization'));
                             
            $decodedData = Encryption::verifyTokenSilent($token, $message);
            if ($message)
            {                
                
                $this->logout();
                return false;
            }
            
            if (isset($decodedData->userId))
            {
                if ($decodedData->userId === $this->userSession->getLoginId())
                {
                    return true;
                }
                else
                {
                    $this->logout();
                    return false;
                }
            }
            else
            {
                $message = 'Bad JWT token!';
                $this->logout();
                return false;
            }
            
            
        }
        else
        {            
            $message = 'No JWT token found!';
            $this->logout();
            return false;
        } 
       
    }
       

    public function isRouteLinked() 
    {           
        if ($this->isLoggedOn())
        {
            $data = $this->admin->getUserRoleByEmail($this->userSession->getUserId());
            
            if (!empty($data))
            {
                $role = $data['role'];
                //$role = 'admin';
                if ($role === 'admin')
                {                    
                    if (CheckString::validRoute(DS_AM_ADMIN_ONLY_ROUTES))
                    {
                        return true;
                    }
                    return false;
                }
                else if ($role === 'user')
                {
                    if (CheckString::validRoute(DS_AM_USER_ONLY_ROUTES))
                    {
                        return true;
                    }
                    return false;
                }
                
                return false;
            }
            else
            {
                return false;
            }
            
            
        }
    }
        



    
    
}

