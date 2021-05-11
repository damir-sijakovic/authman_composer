<?php 

namespace Authman\System;
use Authman\System\Connection;

class Cookie
{
	public static function set($key, $value) 
	{                
        $secure = Connection::isHttps();
        
		return setcookie($key, $value, [
			'expires' => 0,
			'path' => '/',
			'domain' => '',
			'secure' => $secure,
			'httponly' => true,
			'samesite' => 'Strict',
		]);
	}

	public static function get($key) 
	{
        if (isset($_COOKIE[$key]))
        {
		    return $_COOKIE[$key];
        }
        return null;
	}

	public static function delete($key) 
	{        
        if (isset($_COOKIE[$key]))
        {
		    return setcookie($key, '', 1, '/');
        }

        return null;
	}


	public static function keyExists($key) 
	{        
        if (isset($_COOKIE[$key]))
        {
		    return true;
        }

        return false;
	}


	public static function deleteAll() 
	{        
        if (isset($_SERVER['HTTP_COOKIE'])) 
        {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) 
            {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', 1);
                setcookie($name, '', 1, '/');
            }
        }
	}

    	
}; 
	
	
	
	
	
	
	
	


