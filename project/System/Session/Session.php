<?php 

namespace Authman\System\Session;
use Authman\System\Cookie;

class Session
{
    protected $sessionIdentifier;
    
    protected function __construct($sessionIdentifier) 
	{
        $this->sessionIdentifier = $sessionIdentifier;        
         	
	    $isHttps = false;        
        $c1 = filter_input(INPUT_SERVER, 'HTTPS') !== null;
		$c2 = filter_input(INPUT_SERVER, 'SERVER_PORT', FILTER_SANITIZE_NUMBER_INT);
		$c2 = intval($c2) === 443;
		if ($c1 || $c2) $isHttps = true;        
		
        if (!isset($_SESSION))
        {
            if ($isHttps) 
            {
                session_start([
                    'cookie_lifetime' => 0,
                    'cookie_secure' => true,
                    'cookie_httponly' => true,
                    'cookie_samesite' => 'Strict',
                    'cookie_path' => '/',
                ]);
            }
            else
            {
                session_start([
                    'cookie_lifetime' => 0,
                    'cookie_secure' => false,
                    'cookie_httponly' => true,
                    'cookie_samesite' => 'Strict',
                    'cookie_path' => '/',
                ]);
            }
        }
    }    
   
    protected function begin() 
	{
         	
	    $isHttps = false;        
        $c1 = filter_input(INPUT_SERVER, 'HTTPS') !== null;
		$c2 = filter_input(INPUT_SERVER, 'SERVER_PORT', FILTER_SANITIZE_NUMBER_INT);
		$c2 = intval($c2) === 443;
		if ($c1 || $c2) $isHttps = true;        
		
		if ($isHttps) 
		{
			session_start([
				'cookie_lifetime' => 0,
				'cookie_secure' => true,
				'cookie_httponly' => true,
				'cookie_samesite' => 'Strict',
				'cookie_path' => '/',
			]);
		}
		else
		{
			session_start([
				'cookie_lifetime' => 0,
				'cookie_secure' => false,
				'cookie_httponly' => true,
				'cookie_samesite' => 'Strict',
				'cookie_path' => '/',
			]);
		}
    }    
   
	
	protected function end() 
	{
        if (isset($_SESSION))
        {        
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        }
	}
	
	public function haveSession() 
	{
		if (isset($_SESSION[$this->sessionIdentifier]))
		{
			return true;
		}
		return false;		
	}
	

	public function destroy() 
	{
		if (isset($_SESSION[$this->sessionIdentifier]))
		{
			unset ($_SESSION[$this->sessionIdentifier]);
		}
	}
	    
	public function empty() 
	{
		if (isset($_SESSION[$this->sessionIdentifier]))
		{
			unset ($_SESSION[$this->sessionIdentifier]);
            $_SESSION[$this->sessionIdentifier] = [];
		}
	}
	
	public function set($key, $value) 
	{
		$_SESSION[$this->sessionIdentifier][$key] = $value;
	}
	
	public function delete($key) 
	{
		if (isset($_SESSION[$this->sessionIdentifier][$key]))
		{
			unset($_SESSION[$this->sessionIdentifier][$key]);
			return true;
		}
		return false;		
	}
	
	public function init($key, $value) 
	{
		if (!$this->keyExists($key))
		{ 
			$_SESSION[$this->sessionIdentifier][$key] = $value;
		}
	}
	

	public function get($key) 
	{
		if (isset($_SESSION[$this->sessionIdentifier]))
		{
			if (isset($_SESSION[$this->sessionIdentifier][$key])) 
			{
				return $_SESSION[$this->sessionIdentifier][$key];
				
			}
			return false;
		}
	}
	
	public function keyExists($key) 
	{        
		if (isset($_SESSION[$this->sessionIdentifier]))
		{            
			if (isset($_SESSION[$this->sessionIdentifier][$key])) 
			{               
				return true;				
			}
			return false;
		}
	}
		
	public function getArray() 
	{
		if (isset($_SESSION[$this->sessionIdentifier]))
		{
			return $_SESSION[$this->sessionIdentifier];
		}
	}
	
	public function setMultipleValues($arr) 
	{
		foreach ($arr as $k => $v)
		{
			$_SESSION[$this->sessionIdentifier][$k] = $v;
		}
	}



};
