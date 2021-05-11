<?php 

namespace Authman\System;


class Connection
{    
    
    public static function isPost()
    {		
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            return true;
        }
        return false;
    } 	

    public static function isGet()
    {		
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            return true;
        }
        return false;
    } 	
    
    

    public static function getFullUrl()
    {
        $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if (substr($url, -1) == '/')
        {
            $url = substr($url, 0, -1);
        }
        
        return $url;
    } 

    public static function getUrlRoute()
    {
        if (isset($_SERVER['PATH_INFO']))
        {	

            $route = $_SERVER['PATH_INFO'];			
            if (substr($route, -1) == '/')
            {
                $route = substr($route, 0, -1);
            }	
                            
            return $route; 
        }
        
        return '/';
    }
    
    
    public static function getIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
        
    public static function getRequestTime()
    {
        return time();
    }

    public static function getHeader($headerName)
	{
		return getallheaders()[$headerName];
	}
    
    public static function isHttps() 
    {
        if (isset($_SERVER['HTTPS']))
        {
            if ('on' == strtolower( $_SERVER['HTTPS'])) 
            {
                return true;
            } 
            elseif ('1' == $_SERVER['HTTPS']) 
            {
                return true;
            }
        }

        if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) 
        {
            return true;
        }
        return false;
    }

    
};




/*
class Connection
{    
    private function getFullUrl()
    {
        $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if (substr($url, -1) == '/')
        {
            $url = substr($url, 0, -1);
        }
        
        return $url;
    } 

    private function getUrlRoute()
    {
        if (isset($_SERVER['PATH_INFO']))
        {	

            $route = $_SERVER['PATH_INFO'];			
            if (substr($route, -1) == '/')
            {
                $route = substr($route, 0, -1);
            }	
                            
            return $route; 
        }
        
        return '/';
    }
    
    
    private function getIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
        
    private function getRequestTime()
    {
       // return date("Y-m-d H:i:s");
        return time();
    }
    
    private $ip;
    private $route;
    private $requestTime;
    private $url;
    private $headers;
    private $isPost;
    private $isFiles;
  
    
    public function __construct()
    {        
        $this->ip = $this->getIpAddress();
        $this->route = $this->getUrlRoute();
        $this->url = $this->getFullUrl();
        $this->requestTime = $this->getRequestTime();
        
        $this->headers = getallheaders();        
        $this->isPost = ($_SERVER['REQUEST_METHOD'] == 'POST') ? true : false; 
        $this->isFiles = (!empty($_FILES)) ? true : false;         
    }

    public function getHeader($headerName)
	{
		return isset($headerName) ? $this->headers[$headerName] : null;
	}
    
    public function isHttps() 
    {
        if (isset($_SERVER['HTTPS']))
        {
            if ('on' == strtolower( $_SERVER['HTTPS'])) 
            {
                return true;
            } 
            elseif ('1' == $_SERVER['HTTPS']) 
            {
                return true;
            }
        }

        if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) 
        {
            return true;
        }
        return false;
    }


    
};

*/






















/*

return object with:
ip
route
headers
requestTime
url

*/
