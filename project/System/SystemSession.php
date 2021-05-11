<?php 

namespace Authman\System;
use Authman\System\Session\Session;
use Authman\System\Connection;
use Authman\System\Encryption;



class SystemSession extends Session
{    
    private $userSession;

    //on request
    public function __construct(UserSession $userSession) 
    {
        parent::__construct('_DSIJAK_AUTHMAN_SYSTEM_SESSION_');        
        $this->user = $userSession;
    }
    
        
    public function request()
    {
        if ($this->keyExists('requestId'))
        {
            $requestId = intval($this->get('requestId')) + 1;
            $this->set('requestId', $requestId);
        }
        else
        {
            $this->set('requestId', 1000);
        }

        //$this->set('requestId', bin2hex(openssl_random_pseudo_bytes(7)));
        $this->set('requestedAt', time());
        $this->set('requestIp', Connection::getIpAddress());
        $this->set('requestType', Connection::isPost() ? 'post' : 'get' );  
         
        if (Connection::isPost())
        {
            $this->set('requestType', 'post' );    
        }
        else
        {
            $this->set('requestType', 'get' );
        }    
    }


    public function afterRequest()
    {
        if (DS_AM_WRONG_PASS_ENABLED)
        {
            //if banned wrong pass 
            if ($this->keyExists('wrongPassCounterBan'))
            {
                $banExpiresAt = intval($this->get('wrongPassCounterBan'));
                $requestedAt = intval($this->get('requestedAt'));
                
                if ($requestedAt > $banExpiresAt)
                {
                    $this->delete('wrongPassCounterBan');
                }                
                else
                {
                    $this->user->logout();
                    return false;
                }
                   
                $this->clearTimers();
               // echo "{BANNED} ";
            }
        }
        
        if (DS_AM_BRUTE_FORCE_ENABLED)
        {
         
            //if banned brute force 
            if ($this->keyExists('bruteForceCounterBan'))
            {
                $banExpiresAt = $this->get('bruteForceCounterBan');
                $requestedAt = $this->get('requestedAt');
                
                if ($requestedAt > $banExpiresAt)
                {
                    $this->delete('bruteForceCounterBan');
                }
                else
                {
                    $this->user->logout();
                    return false;
                }
                
                $this->clearTimers();
                //BANNED;
            }
        }
        
        //is login expired
         if ($this->user->keyExists('loginExpiresAt'))
        {
            $loginExpiresAt = intval($this->user->get('loginExpiresAt'));
            $requestedAt = intval($this->get('requestedAt'));
            
            if ($requestedAt > $loginExpiresAt)
            {
                $this->user->logout();
            }
        } 
    }





    
    public function wrongPassCounter()
    {
        if (!$this->keyExists('wrongPassCounterBan'))
        {          
            
            if ($this->keyExists('wrongPassCounter'))
            {
               
                $wrongPassCounterAt = intval($this->get('wrongPassCounterAt'));
                $wrongPassCounterExpiresAt = intval($this->get('wrongPassCounterExpiresAt'));

                if ($wrongPassCounterExpiresAt < time())
                {
                    
                    //FULL RESET
                    if (strtotime(DS_AM_WRONG_PASS_RESET, $wrongPassCounterAt) < time())
                    {
                        // wrongpass TIMER-RESET 
                        $this->clearTimers();
                        return true;                    
                    }
                    
                    $wrongPassCount = intval($this->get('wrongPassCounter'));

                    
                    if ($wrongPassCount >= DS_AM_WRONG_PASS_THRESHOLD)
                    {
                        $this->set('wrongPassCounterBan', strtotime(DS_AM_WRONG_PASS_BAN));
                        $this->clearTimers();
                        return false;
                    } 
                    else
                    {
                        $wrongPassCount += 1;
                        $this->set('wrongPassCounter', $wrongPassCount);
                        $this->set('wrongPassCounterAt', time());
                        return true;
                    }
                
                }
            } 
            else
            {
                //INIT COUNTER
                $this->set('wrongPassCounter', 1);
                $this->set('wrongPassCounterAt', time());
                $this->set('wrongPassCounterExpiresAt', strtotime(DS_AM_WRONG_PASS));
                return true;
            }  
        }
        return false;     
    }

    
    
    public function bruteForceCounter()
    { 
        if (!$this->keyExists('bruteForceCounterBan'))
        {   
            if ($this->keyExists('bruteForceCounter'))
            {
                $bruteForceCounterAt = intval($this->get('bruteForceCounterAt'));
                $bruteForceCounterExpiresAt = intval($this->get('bruteForceCounterExpiresAt'));
                    
                if ($bruteForceCounterExpiresAt < time())
                {
                    if (strtotime(DS_AM_BRUTE_FORCE_RESET, $bruteForceCounterAt) < time())
                    {
                        //BRUTE-FORCE-TIMER-RESET
                        
                        $this->clearTimers();
                        return true;                    
                    }
                    
                    $wrongPassCount = intval($this->get('bruteForceCounter'));

                    
                    if ($wrongPassCount >= DS_AM_BRUTE_FORCE_THRESHOLD)
                    {
                        $this->set('bruteForceCounterBan', strtotime(DS_AM_BRUTE_FORCE_BAN));
                        $this->clearTimers();
                        return false;
                    } 
                    else
                    {
                        $wrongPassCount += 1;
                        $this->set('bruteForceCounter', $wrongPassCount);
                        $this->set('bruteForceCounterAt', time());
                        $this->set('bruteForceCounterExpiresAt', strtotime(DS_AM_BRUTE_FORCE));
                        return true;
                    }
                
                }
            } 
            else
            {
                //INIT COUNTER
                $this->set('bruteForceCounter', 1);
                $this->set('bruteForceCounterAt', time());
                return true;
            }  
        }
        return false;     
    }


    public function isLoginBanned()
    {
        if ($this->keyExists('bruteForceCounterBan'))
        {            
           return true;
        }
        if ($this->keyExists('wrongPassCounterBan'))
        {            
           return true;
        }
        
        return false;     
    }

    public function clearTimers()
    {
        $this->delete('bruteForceCounter');
        $this->delete('bruteForceCounterAt');
        $this->delete('bruteForceCounterExpiresAt'); 
        $this->delete('wrongPassCounter');
        $this->delete('wrongPassCounterAt');
        $this->delete('wrongPassCounterExpiresAt');
    }



    public function login($assoc)
    {  
        $this->afterRequest();
        $this->user->login($assoc);        
    }

    public function isUserLoggedOn()
    {          
        return $this->user->keyExists('loginId');        
    }


    public function getCsrf()
    {  
        return $this->user->get('csrfToken');
    }




    public function logout()
    {  
        $this->clearTimers();
        $this->user->logout();  
        return true;
    }


}
  

