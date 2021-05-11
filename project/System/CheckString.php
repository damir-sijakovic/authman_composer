<?php 

namespace Authman\System;

class CheckString
{
	public static function allowedChars($string, $allowedChars)
	{
		for ($i=0; $i<strlen($string); $i++)
		{
			$checkPass = false;
			for ($j=0; $j<strlen($allowedChars); $j++)
			{
				if ($string[$i] === $allowedChars[$j])
				{
					$checkPass = true;
					break;
				}
			}
			
			if (!$checkPass)
			{
				return false;
			}
		}	
		return true;
	}
    	
	public static function sizeof($data, $limit)
	{		
		$dataLen = strlen($data); 
		if ($dataLen > $limit)
		{
			return false;
		}
        return true;
	}
    
    	
    
	public static function password($data, $length=null) 
	{	        
        if (is_string($data))
        {
            if ($length)
            {
                if (!self::sizeof($data, $length))
                {                    
                    return false;
                }
            }                
            
            $allowed = DS_AM_PASSWORD_TABLE;
            if (!self::allowedChars($data, $allowed))
            {                
                return false;
            }            
            return true;
        }
                
        return false;        
	}
	
	public static function username($data, $length=null)
	{		
        if (is_string($data))
        {
            if ($length)
            {
                if (!self::sizeof($data, $length))
                {                    
                    return false;
                }
            }                
            
            $allowed = DS_AM_USERNAME_TABLE;
            if (!self::allowedChars($data, $allowed))
            {                
                return false;
            }            
            return true;
        }
                
        return false;   
	}	
    
	public static function extra($data, $length=null)
	{		
        if (is_string($data))
        {
            if ($length)
            {
                if (!self::sizeof($data, $length))
                {                    
                    return false;
                }
            }                
            
            $allowed = DS_AM_EXTRA_TABLE;
            if (!self::allowedChars($data, $allowed))
            {                
                return false;
            }            
            return true;
        }
                
        return false;   
	}	
    
	public static function extraData($data, $length=null)
	{		
        if (is_string($data))
        {
            if ($length)
            {
                if (!self::sizeof($data, $length))
                {                    
                    return false;
                }
            }                
            
            $allowed = DS_AM_EXTRA_DATA_TABLE;
            if (!self::allowedChars($data, $allowed))
            {                
                return false;
            }            
            return true;
        }
                
        return false;   
	}	
    
    public static function email($data, $length=null)
	{
		if (is_string($data))
        {
            if ($length)
            {
                if(!self::sizeof($data, $length))
                {
                    return false;                    
                }
            }    
            
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) 
            {
                return false;
            }
            
            return true;
        }
        return false;
	}
    
    public static function validExtraColumnName($keys) 
    {
		for ($i=0; $i<count($keys); $i++)
        {
            if (!in_array($keys[$i], DS_AM_EXTRA_COLUMNS))
            {
                return false;
            }
        }
        
        return true;        
    }
    
    
    public static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp) 
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    
    
    public static function validRoleNames($keys) 
    {
		for ($i=0; $i<count($keys); $i++)
        {
            if (!in_array($keys[$i], DS_AM_USER_ROLES))
            {
                return false;
            }
        }
        
        return true;        
    }
    
    public static function validRoleName($name) 
    {
        if (!in_array($name, DS_AM_USER_ROLES))
        {
            return false;
        }
        return true;        
    }
    
    public static function validConfirmationCode($code) 
    {
        if (strlen($code) !== 16)
        {
            return false;
        } 
        
        $code = strtolower($code);
        return self::allowedChars($code, 'abcdef1234567890');       
    }
    
    public static function validRoute($routeArray) 
    {
        //error_log(filter_input(INPUT_SERVER, 'REQUEST_URI'));
        $route = filter_input(INPUT_SERVER, 'REQUEST_URI');
        
        //$result = preg_match('|^login/?$|', 'login/');
       // $result = preg_match('|^/admin?$|', $route);
		//return $result;
        
        //error_log($result);
                
        
        if (is_array($routeArray) && !empty($routeArray))
        {
            for ($i=0; $i<count($routeArray); $i++)
            {
                if (preg_match($routeArray[$i], $route))
                {
                    return true;
                }                
            }            
        }
        
        return false;
           
    }
    
};
