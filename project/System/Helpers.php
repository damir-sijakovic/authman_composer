<?php 

namespace Authman\System;

class Helpers
{    
   
	public static function haveKeys($assoc, $keys) 
    {
		$assocKeys = array_keys($assoc);		
		for ($i=0; $i<count($keys); $i++)
        {
            if (!in_array($keys[$i], $assocKeys))
            {
                return false;
            }
        }
        
        return true;        
    }
    

	public static function inArray($array, $keys) 
    {
        error_log(print_r($keys, true));
        
		for ($i=0; $i<count($keys); $i++)
        {
            if (!in_array($keys[$i], $array))
            {
                return false;
            }
        }
        
        return true;        
    }
        
	public static function haveKeysStrict($assoc, $keys) //Assoc::haveKeys($assoc, ['d','b','c','a'])
    {
		$assocKeys = array_keys($assoc);		
		$intersectKeys = array_intersect($assocKeys, $keys);
		
		if (count($assocKeys) == count($intersectKeys))
		{
			return true;
		}
		else
		{
			return false;
		}		
    }
    
	public static function removeKeys(&$assoc, $keys)
    {
        $assocLength = count($assoc);
        $keysLength = count($keys);
        
		if ($assocLength && $keysLength)
        {
            for ($i=0; $i<$keysLength; $i++)
            {
                unset($assoc[ $keys[$i] ]);
            }
        }	
        
        return $assoc;
    }
    
    
    public static function createExtraPdoString()
    {        
        $columnNumber = count(DS_AM_EXTRA_COLUMNS);
        if ($columnNumber)
        {
            $output = '';                        
            for ($i=0; $i<$columnNumber; $i++)
            {
                $output .= ' :' . DS_AM_EXTRA_COLUMNS[$i] . ', ';
            }     
            
            $output = rtrim($output, ", ");
            echo $output;     
        }
        
        return null;
    }
   

   
   
    public static function createUpdateExtraPdoString($id, $assoc)
    {        
        $columnNumber = count($assoc);
        if ($columnNumber)
        {
            $output = 'UPDATE users SET ';                        
            
            foreach ($assoc as $key => $value)
            {
                $output .= ' :' . $key . ', ';
            }
            $output .= 'WHERE id = :id';            
            $output = rtrim($output, ", ");
    
            echo $output; 

        }
        
        return null;
    }
   
   	public static function debugPdoPrepareString($sql, $params) 
	{
		$keys = array();

		foreach ($params as $key => $value) 
		{
			if (is_string($key)) {
				$keys[] = '/:'.$key.'/';
			} else {
				$keys[] = '/[?]/';
			}
		}

		 return preg_replace($keys, $params, $sql, 1, $count);
	}
    
    public static function consoleDump($msg, $title=null, $echo=null)
    {
        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
        
        $titleString = '';
        
        if (isset($title))
        {
            $titleString = '"'. $title .'"';
        }
        
        if (isset($echo))
        {                    
            echo "<pre style='padding:10%; color:black; background:#aaa;'>";
            echo '<h2>' . $titleString . ' @ '. $file . '('. $line . ') </h2><br>';
            echo print_r($msg, true);
            echo "</pre>";
        }
                
        error_log("\033[0;32m".'<CONSOLEDUMP> ' . $titleString . ' @ '. $file . '('. $line . ')');
        error_log(print_r($msg, true));
        error_log('<!CONSOLEDUMP>'."\033[0m");
    }	
    
    
    
   
};
