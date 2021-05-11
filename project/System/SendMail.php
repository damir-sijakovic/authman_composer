<?php 

namespace Authman\System;

class SendMail
{
    public static function verify($email, $confirmationCode, $extendedMessage=null) 
    {     
        if ($extendedMessage)
        {
            $message = $extendedMessage . $confirmationCode;        
            return mail($email, DS_AM_VERIFY_MAIL_SUBJECT, $message);  
        }
        else
        {
            $message = DS_AM_VERIFY_MAIL_MESSAGE . $confirmationCode;        
            return mail($email, DS_AM_VERIFY_MAIL_SUBJECT, $message);  
        }
    }
  
}; 
    
    
    
    
    
    
    
    


