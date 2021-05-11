<?php

namespace Authman;

interface AuthmanInterface
{    
    public function register($email, $password, &$message=null);    
    public function addExtraData($assoc, &$message=null); 
    public function unregister($password, &$message=null);     
    public function login($email, $password, &$message=null); 
    public function verifyUser($confirmationCode, &$eMessage=null);     
    public function isUserVerified(); 
    public function sendConfirmationCodeEmail(&$eMessage=null);         
    public function logout(); 
    public function getCsrf(); 
    public function changeRole($role, &$eMessage=null);
    public function isCsrfValid($token); 
    public function isLoggedOn(); 
    public function refresh(&$message=null); 
    public function isRouteLinked();
}
