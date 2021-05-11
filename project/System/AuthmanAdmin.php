<?php

namespace Authman\System;

use Authman\System\Model;
use Authman\System\Encryption;
use Authman\System\ReturnObject\ReturnOnce;
use Authman\System\CheckString;
use Authman\System\SendMail;


class AuthmanAdmin 
{
    public $model;
    private $systemSession;    
    private $userSession;   
    
    public function __construct($systemSession, $userSession) 
    {
        $this->model = new Model();
        $this->systemSession = $systemSession;
        $this->userSession = $userSession;
    }
        
    public function destroySession() 
    {   
        $this->systemSession->empty();
        $this->userSession->empty();
        
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
    
    public function addAdmin($email, $password, &$eMessage=null) 
    {   
        $output = $this->addUser(['email'=>$email, 'password'=>$password], $eMessage);
        $pkid = $this->getIdByEmail($email);
        
        if ($pkid > 0)
        {        
            if ($output)
            {
                return $this->setUserRole($pkid, 'admin');
            }         
        }
        
        $eMessage = 'Admin user was not added.';
        return false;
    }

    public function banUser($email, &$eMessage=null) 
    {          
        $returnObj = $this->model->banUserByEmail($email);
        $eMessage = $returnObj->message;
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }
        
        $eMessage = 'Model error. Could not ban user!';
        return false;
    }
    
    public function unBanUser($email, &$eMessage=null) 
    {          
        $returnObj = $this->model->unBanUserByEmail($email);
        $eMessage = $returnObj->message;
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }
        
        $eMessage = 'Model error. Could not un-ban user!';
        return false;
    }

    public function addUser($assoc, &$message=null)
    {
        if (!CheckString::password($assoc['password'], DS_AM_PASSWORD_MAX_STRLEN))
        {                
            $message = 'Bad "password" string format.';
            return false;
        }   
        
        $returnObj = $this->model->getNumberOfUsers();
        if (ReturnOnce::isOk($returnObj))
        {
            if ($returnObj->data+1 > DS_AM_MAX_USER_NUMBER)
            {
                $message = 'Maximum number of users reached!';
                return false;
            }
        } 
        
        $returnObj = $this->model->userExistsByEmail($assoc['email']);
        if (ReturnOnce::isOk($returnObj))
        {
            $message = 'User with that email already exists!';
            return false;
        } 
        
        
        $passwordHash = Encryption::createPassword($assoc['password']);
        $assoc['password'] = $passwordHash;
        
        $returnObj = $this->model->addUser($assoc);
        $message = $returnObj->message;
            
        if (ReturnOnce::isOk($returnObj))
        {
            if (DS_AM_NEW_USER_SET_TO_NOT_VERIFIED === false)
            {
                $lastId = intval($returnObj->data);

                $returnObj = $this->model->setUserRole($lastId, 'user');                
                if (ReturnOnce::isOk($returnObj))
                {
                    return $lastId;
                }
                else
                {
                    $message = "Can't set user to admin role.";
                    return -1;
                }
                
                //$returnObj = $this->model->setUserRole($id, $role)
            }            
            
            return true;
        }    
        else
        {
            return false;
        }
    } 
    
    
    public function updateUserData($id, $assoc)
    {
        $returnObj = $this->model->updateUserData($id, $assoc);
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
    
    public function haveNoUsers()
    {
        $returnObj = $this->model->tableHaveData();
        if (ReturnOnce::isOk($returnObj))
        {
            return false;
        }    
        else
        {
            return true;
        }
    } 
    
    public function updateUserNameById($id, $username)
    {
        $returnObj = $this->model->updateUserNameById($id, $username);
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
        
    public function updatePasswordById($id, $password)
    {
        $returnObj = $this->model->updatePasswordById($id, $password);
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
        
    public function updateEmailById($id, $email)
    {
        $returnObj = $this->model->updateEmailById($id, $email);
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
        
    public function setUserRole($id, $role, &$eMessage=null)
    {
        $returnObj = $this->model->setUserRole($id, $role);
        $eMessage = $returnObj->message;
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
    
    public function setUserRoleByEmail($email, $role)
    {
        $id = $this->getIdByEmail($email);
        
        if ($id > 0)
        {
            $returnObj = $this->model->setUserRole($id, $role);
            if (ReturnOnce::isOk($returnObj))
            {
                return true;
            }    
            else
            {
                return false;
            }
        }
        
        return false;
    } 
    
    public function isUserBanned($email, &$eMessage=null)
    {
        $returnObj = $this->model->getUserDataByEmail($email);        
        $eMessage = $returnObj->message;
        
        if (ReturnOnce::isOk($returnObj))
        {
            if (isset($returnObj->data['role']))
            {
                if ($returnObj->data['role'] === 'banned')
                {
                    return true;
                }
            }  
        }
        
        return false;        
    } 
    
    public function getUserRoleByEmail($email, &$message=null)
    {
        $returnObj = $this->model->getUserDataByEmail($email);        
        $message = $returnObj->message;
        
        if (ReturnOnce::isOk($returnObj))
        {
            if (isset($returnObj->data['role']))
            {
                return $returnObj->data['role'];
            }           
            
        }
        
        return false;        
    } 
        
    public function activateUser($id)
    {
        $returnObj = $this->model->activateUser($id);
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
        
    public function deactivateUser($id)
    {
        $returnObj = $this->model->deactivateUser($id);
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    } 
    
    public function deleteDatabase()
    {
        $returnObj = $this->model->deleteDatabase();
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    }     
     
    public function getIdByEmail($email)
    {
        $returnObj = $this->model->getIdByEmail($email);
        if (ReturnOnce::isOk($returnObj))
        {
            return intval($returnObj->data['id']);
        }    
        else
        {
            return -1;
        }
    } 
    
    public function getUserDataByEmail($email, &$eMessage)
    {
        $returnObj = $this->model->getUserDataByEmail($email);
        $eMessage = $returnObj->message;
        
        if (ReturnOnce::isOk($returnObj))
        {
            return $returnObj->data;
        }    
        else
        {
            return [];
        }
    } 
       

    public function findInExtraData($column, $term)
    {
        $returnObj = $this->model->findInExtraData($column, $term);
        if (ReturnOnce::isOk($returnObj))
        {
            return $returnObj->data;
        }    
        else
        {
            return [];
        }
    } 
    
    
    public function deleteById($id)
    {
        $returnObj = $this->model->deleteById($id);
      
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
       
    } 
     
    public function verifyLoggedOnPassword($password)
    {
        $userId = $this->userSession->getUserId();
        
        if (!$userId)
        {
            return false;
        }
        
        $userData = $this->model->getUserDataByEmail($userId);
        
        if (isset($userData->data['password']))
        {
            $passOk = Encryption::verifyPassword($password, $userData->data['password']);            
            if ($passOk)
            {
                return true;
            }
        }
        
        return false;
    } 
    
    public function addExtraData($assoc, &$eMessage=null) 
    {            
        $pkid = $this->userSession->getPkId();
        $returnObj = $this->model->updateExtraById($pkid, $assoc);
        $eMessage = $returnObj->message;
        
        if (ReturnOnce::isOk($returnObj))
        {
            return true;
        }    
        else
        {
            return false;
        }
    }
    
    public function databaseLastModifiedAt() 
    {            
        return filemtime(DS_AM_PATH_DB_FILE);
    }
    
    public function databaseCreatedAt() 
    {            
        $returnObj = $this->model->databaseCreatedAt();
        $message = $returnObj->message;
        
        if (ReturnOnce::isOk($returnObj))
        {
            return strtotime($returnObj->data);
        }    
        else
        {
            return null;
        }
    }
    
    public function haveBackup() 
    {            
        return file_exists(DS_AM_PATH_DB_BAK_FILE);
    }
    
    public function restoreBackup() 
    {       
        if (file_exists(DS_AM_PATH_DB_BAK_FILE))
        {
            if (!copy(DS_AM_PATH_DB_BAK_FILE, DS_AM_PATH_DB_FILE)) 
            {
                return false;
            }
            else
            {
                return true;
            }
        } 
        return false;
    }
    
    public function deleteBackup() 
    {       
        if (file_exists(DS_AM_PATH_DB_BAK_FILE))
        {
            return unlink(DS_AM_PATH_DB_BAK_FILE);
        } 
        return false;
    }
    
    public function createBackup() 
    {             
        if (file_exists(DS_AM_PATH_DB_FILE))
        {
            if (file_exists(DS_AM_PATH_DB_BAK_FILE))
            {
                unlink(DS_AM_PATH_DB_BAK_FILE);
            }            
            
            if (!copy(DS_AM_PATH_DB_FILE, DS_AM_PATH_DB_BAK_FILE)) 
            {
                return false;
            }
            else
            {
                return true;
            }
        }
           
        return false;
    }
    
    public function isUserVerified() 
    {             
       return $this->userSession->isUserVerified();
    }
    
    public function generateConfirmationCode() 
    {             
        $confirmationCode = Encryption::generateConfirmationCode();              
        $this->userSession->setConfirmationCode($confirmationCode);
        return $confirmationCode;
    }
    
    public function getConfirmationCode() 
    {             
        return $this->userSession->getConfirmationCode();
    }
    
    public function verifyUserConfirmationCode($confirmationCode, &$eMessage=null) 
    {   
        $sessionConfirmationCode = $this->userSession->getConfirmationCode();
        
        if ($confirmationCode === $sessionConfirmationCode)  
        {
            $pkid = $this->userSession->getPkId();
            $returnObj = $this->model->setUserRole($pkid, 'user');
            $eMessage = $returnObj->message;
            
            if (ReturnOnce::isOk($returnObj))
            {
                $this->userSession->setRole('user');
                $this->userSession->removeConfirmationCode();
                return true;
            }    
            else
            {
                return false;
            }
        }
        
        $eMessage = 'Confirmation code not valid.';
        return false;
    }
    
    public function sendConfirmationCodeEmail(&$eMessage=null) 
    {       
        $email = $this->userSession->getUserId();     
        $confirmationCode = $this->userSession->getConfirmationCode();  
        
        if ($email && $confirmationCode)
        {    
            $output = SendMail::verify($email, $confirmationCode);      
            return $output;
        }
        else
        {
            $eMessage = 'No email or confirmation code found in session!';
            return null;
        }            
    }
           
    public function generateCsrf() 
    {            
        $csrf = Encryption::generateCsrfToken();
        $this->userSession->setCsrf($csrf);
        
        return $csrf;  
    }
     
    public function debugHtml() 
    {   
        if (DS_AM_MODE !== 'development') return false;
        
        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
        $titleString = 'dsijak\authman';
        echo "<pre style='padding:3.14%; color:black; background:#aaa;'>";
        echo '<h2>' . $titleString . ' @ '. $file . '('. $line . ') </h2><br>';
        echo '<h4>System session:</h4>';
        echo print_r($_SESSION['_DSIJAK_AUTHMAN_SYSTEM_SESSION_'], true);
        echo '<h4>User session:</h4>';
        echo print_r($_SESSION['_DSIJAK_AUTHMAN_USER_SESSION_'], true);
        echo "</pre>";
    }

    public function debugJson() 
    {   
        if (DS_AM_MODE !== 'development') return false;
        
        if (isset($_SESSION['_DSIJAK_AUTHMAN_SYSTEM_SESSION_']) && isset($_SESSION['_DSIJAK_AUTHMAN_USER_SESSION_']))
        {
            return json_encode([
                'requestTime' => time(),
                'systemSession' => $_SESSION['_DSIJAK_AUTHMAN_SYSTEM_SESSION_'],
                'userSession' => $_SESSION['_DSIJAK_AUTHMAN_USER_SESSION_'],            
            ]);        
        }
        else
        {
            return json_encode([
                'authman-debug' => 'No session.'           
            ]);   
        }
    }

    public function debugArray() 
    {   
        if (DS_AM_MODE !== 'development') return false;
                
        if (isset($_SESSION['_DSIJAK_AUTHMAN_SYSTEM_SESSION_']) && isset($_SESSION['_DSIJAK_AUTHMAN_USER_SESSION_']))
        {
            return [
                'requestTime' => time(),
                'systemSession' => $_SESSION['_DSIJAK_AUTHMAN_SYSTEM_SESSION_'],
                'userSession' => $_SESSION['_DSIJAK_AUTHMAN_USER_SESSION_'],            
            ];        
        }
        else
        {
            return [
                'authman-debug' => 'No session.'           
            ];   
        }
    }   
    
    
    
    
}
