<?php 

namespace Authman\System;
//use \Firebase\JWT\JWT;
use Authman\System\Jwt\Jwt;

class Encryption
{

	public static function createPassword($pass) 
	{		
		return password_hash(strval($pass), PASSWORD_BCRYPT, ['cost'=>12]);
	}

	public static function verifyPassword($passStr, $passHash) 
	{		
		return password_verify(strval($passStr), $passHash);
	}
	
	public static function generateToken($userId, $exp, $nbf=null)  //exp = strtotime("+40 minutes") strtotime("+1 day")
	{	
		$payload = [
			"userId" => $userId, 
            "iat" => time(),
			"exp" => $exp
        ];            
        
        if (isset($nbf))
        {
			$payload['nbf'] = $nbf; 
		}
                
        return JWT::encode($payload, DS_AM_SERVER_KEY); 
	}
	
	public static function verifyToken($token)  
	{	
		return JWT::decode($token, DS_AM_SERVER_KEY, array('HS256'));
	}
	    
    public static function isTokedExpired($token)  
	{	   
        $returnMessage = '';
             
        try
		{
           JWT::decode($token, DS_AM_SERVER_KEY, array('HS256'));     
		}
		catch (\Exception $e)
		{			
			$returnMessage = $e->getMessage();		
		}  
        
        if ($returnMessage === 'Expired token')
        {
            return true;
        }
        else
        {
            return false;
        }
	}    
    
    public static function verifyTokenSilent($token, &$message)  
	{	  
        try
		{
            return JWT::decode($token, DS_AM_SERVER_KEY, array('HS256'));     
		}
		catch (\Exception $e)
		{			
			$message = $e->getMessage();		
		}  
	}    
        
    public static function encodeJwt($data)  
    {	
        return JWT::encode($data, DS_AM_SERVER_KEY);
    }

    public static function decodeJwt($data)  
    {	
        return JWT::decode($data, DS_AM_SERVER_KEY, array('HS256'));
    }

    public static function generateCsrfToken()  
    {	
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    public static function generateRand8()
    {
        return bin2hex(openssl_random_pseudo_bytes(8));
    }
    
    public static function generateConfirmationCode()
    {
        return bin2hex(openssl_random_pseudo_bytes(8));
    }
    
}; 
	
	
	
	
	
	
	
	


