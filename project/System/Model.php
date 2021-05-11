<?php 

namespace Authman\System;
use Authman\System\Helpers;
use Authman\System\CheckString;
use Authman\System\ReturnObject\ReturnOnce;

class Model
{    
    private $pdo;
    
    private function initDatabase()
    {
        if (!file_exists(DS_AM_PATH_DB_FILE))
        {
            error_log('dsijak/authman : Creating database!');
            
            $this->pdo = new \PDO('sqlite:' . DS_AM_PATH_DB_FILE);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);       
            $sql = file_get_contents(DS_AM_PATH_SQL_FILE);
            $this->pdo->exec('PRAGMA foreign_keys = ON');            
            $this->pdo->exec($sql);
          //  $this->pdo->exec("UPDATE authman SET databaseCreatedAt = datetime('now') WHERE id = 1 "); 
            $this->pdo->query( " INSERT INTO authman ( databaseCreatedAt ) VALUES ( datetime('now') ) ");

            
            if (is_array(DS_AM_EXTRA_COLUMNS))
            {
                $extraColumnsLength = count(DS_AM_EXTRA_COLUMNS);
                if ($extraColumnsLength > 0)
                {                    
                    for ($i=0; $i<$extraColumnsLength; $i++)
                    {
                        $column = DS_AM_EXTRA_COLUMNS[$i];
                        $sql = " ALTER TABLE users ADD COLUMN $column TEXT ";
                        $this->pdo->exec($sql);
                    }
                }
            }
        }
        else
        {
            $this->pdo = new \PDO('sqlite:' . DS_AM_PATH_DB_FILE);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);       
        }
    }    
    

    public function __construct()
    {
        $this->initDatabase(); 
    } 
    
    public function databaseCreatedAt()
    { 
        $sql = " SELECT databaseCreatedAt FROM authman WHERE id = 1 ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute())
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result['databaseCreatedAt'])
                {
                    return ReturnOnce::ok($result['databaseCreatedAt']);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:databaseCreatedAt() => ' . $e); 
        }  
    } 
    
      
    function updateUserData($id, $assoc)
    {
             
        if (is_integer($id))    
        {
            if (!Helpers::inArray(DS_AM_EXTRA_COLUMNS, array_keys($assoc)))
            {              
                
                return ReturnOnce::error('Model:updateUserData() => Column name is not listed in config.');
            }
            
            $assoc['id'] = $id;

            $columnNumber = count($assoc);
            if ($columnNumber > 1)
            {
                $output = 'UPDATE users SET ';
                foreach ($assoc as $key => $value)
                {
                    if ($key === 'id')
                    {
                        continue;
                    }
                 
                    if (!CheckString::extraData($value, DS_AM_EXTRA_MAX_STRLEN))
                    {
                        return ReturnOnce::error('"extra" string data has bad chars.');
                    }
                    
                    $output .= $key . ' = :' . $key . ', ';
                }
                
                $output = rtrim($output, ", ");
                $output .= ' WHERE id = :id';
                //echo $output; 
                                
                $sql = $output; 
                $stmt = $this->pdo->prepare($sql);  
                
                try 
                {  
                    if ($stmt->execute($assoc))
                    {
                        return ReturnOnce::ok($stmt->rowCount());  
                    }  
                    return ReturnOnce::fail('No rows are updated');                                  
                }
                catch (\PDOException $e)
                {           
                    return ReturnOnce::error('Model:updateUserData() => ' . $e);                       
                }  
            }
            else
            {
                return ReturnOnce::error('Model:updateUserData() => Argument $assoc must have more then 1 item!');
            }
        }
        else
        {
            return ReturnOnce::error('Model:updateUserData() => Argument not integer!');  
        }  
        
    } 
    
    public function deleteDatabase()
    {
        if (file_exists(DS_AM_PATH_DB_FILE))
        {               
            $unlinkSuccessful = unlink(DS_AM_PATH_DB_FILE);
            
            if ($unlinkSuccessful)
            {
                return ReturnOnce::ok();
            }
            
            return ReturnOnce::fail('Failed to delete file.');
        }
    } 
    
    public function getDatabaseObject()
    {
        return $this->pdo;
    } 
    
    public function addUser($assoc)
    { 
        
        if (Helpers::haveKeys($assoc, ['email', 'password']))
        {     
            if (isset($assoc['username']))
            {
                if (!CheckString::username($assoc['username'], DS_AM_USERNAME_MAX_STRLEN))
                {
                    return ReturnOnce::error('Model:addUser() => Bad "username" string format.');
                }
            }
            else
            {
                $assoc['username'] = null;
            }
            
            /*
            if (!CheckString::password($assoc['password'], DS_AM_PASSWORD_MAX_STRLEN))
            {                
                return ReturnOnce::error('Model:addUser() => Bad "password" string format.');
            }
            */
            if (!CheckString::email($assoc['email'], DS_AM_EMAIL_MAX_STRLEN))
            {
                return ReturnOnce::error('Model:addUser() => Bad "email" string format.');
            }
            
            
            
            
            $sql = "INSERT INTO users(username, email, password, createdAt) VALUES ( :username , :email , :password , datetime('now') )";
            $stmt = $this->pdo->prepare($sql);  
            
            $data = [
                'username' => $assoc['username'], 
                'email' => $assoc['email'], 
                'password' => $assoc['password'],
            ];
                        
            try 
            {  
                $stmt->execute($data);                 
                $lastId = intval($this->pdo->lastInsertId());
            }
            catch (\PDOException $e)
            {           
               return ReturnOnce::error('Model:addUser() => ' . $e);
            }  

            Helpers::removeKeys($assoc, ['username', 'email', 'password']); 
            
            if (count($assoc))
            {
               return $this->updateUserData($lastId, $assoc);
            } 
            else
            {
                return ReturnOnce::ok($lastId);
            }
                   
        }
        else
        {
            return ReturnOnce::error("Model:addUser() => Argument have no: 'username', 'email', 'password' keys.");
        }
    } 
    
    public function tableHaveData()
    { 
        $sql = " SELECT count(*) FROM (select 0 from users limit 1) ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute())
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result['count(*)'])
                {
                    return ReturnOnce::ok(1);
                }
                else
                {
                    return ReturnOnce::fail('Table is empty.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:tableHaveData() => ' . $e); 
        } 
    } 
    
    public function getNumberOfUsers()
    { 
        $sql = " SELECT count(*) FROM users ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute())
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result['count(*)'])
                {
                    return ReturnOnce::ok(intval($result['count(*)']));
                }
                else
                {
                    return ReturnOnce::fail('Table is empty.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getNumberOfUsers() => ' . $e); 
        } 
    } 
   
   
    public function setModifed($id)
    { 
        if (is_integer($id))    
        {
            $data = [
                'id' => $id   
            ];

            $sql = "UPDATE users SET modifiedAt = datetime('now') WHERE id = :id ";
            $stmt = $this->pdo->prepare($sql);  
            
            try 
            {  
                if ($stmt->execute($data))
                {
                    return ReturnOnce::ok($stmt->rowCount());  
                }  
                return null;               
            }
            catch (\PDOException $e)
            {           
                return ReturnOnce::error('Model:setModifed() => ' . $e);
            }         

        }
        else
        {
            return ReturnOnce::error('Model:setModifed() => Argument not integer!');         
        } 
    } 
   
    
    public function getActiveUsers()
    {        
        $sql = " SELECT * FROM users WHERE active = 1 ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute())
            {      
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getActiveUsers() => ' . $e); 
        }  
    } 
    
    public function getLastRecord()
    {        
        $sql = " SELECT * FROM users ORDER BY id DESC LIMIT 1 ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute())
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC); 
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getLastRecord() => ' . $e); 
        }  
    } 
    
    public function getInactiveUsers()
    {        
        $sql = " SELECT * FROM users WHERE active = 0 ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute())
            {      
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getInactiveUsers() => ' . $e); 
        }  
    } 
    
    public function isUserBanned($email)
    { 
        if (!CheckString::email($email, DS_AM_EMAIL_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:isUserBanned() => Bad "email" string format.'); 
        }
        
        $data = [
            'email' => $email  
        ];
        
        $sql = " SELECT role FROM users WHERE email = :email ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result)
                {
                    if (isset($result['role']))
                    {
                        if ($result['role'] === 'banned')
                        {
                            return ReturnOnce::ok(true);
                        }
                    }

                }

                return ReturnOnce::fail('No role data found.');

            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:isUserBanned() => ' . $e); 
        }  
    } 
    
    public function getIdByEmail($email)
    { 
        if (!CheckString::email($email, DS_AM_EMAIL_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:getIdByEmail() => Bad "email" string format.'); 
        }
        
        $data = [
            'email' => $email  
        ];
        
        $sql = " SELECT id FROM users WHERE email = :email ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getIdByEmail() => ' . $e); 
        }  
    } 
    
    public function userExistsByEmail($email)
    { 
        if (!CheckString::email($email, DS_AM_EMAIL_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:userExistsByEmail() => Bad "email" string format.'); 
        }
        
        $data = [
            'email' => $email  
        ];
        
        $sql = " SELECT id FROM users WHERE email = :email ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No email found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:userExistsByEmail() => ' . $e); 
        }  
    } 
    
    public function getUserDataByEmail($email)
    { 
        if (!CheckString::email($email, DS_AM_EMAIL_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:getIdByEmail() => Bad "email" string format.'); 
        }
        
        $data = [
            'email' => $email  
        ];
        
        $sql = " SELECT * FROM users WHERE email = :email ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {      
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);  
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('Email address not found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getIdByEmail() => ' . $e); 
        }  
    } 
    
    public function deleteById($id)
    { 
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:deleteById() => Argument not integer.');
        }
        
        $data = [
            'id' => $id  
        ];

        $sql = " DELETE FROM users WHERE id = :id ";        
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was deleted.'); 
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:deleteById() => ' . $e);
        } 
  
    } 
        
    
    public function findInExtraData($column, $term)
    {         
        if (!CheckString::validExtraColumnName([$column]))
        {
            return ReturnOnce::error('Model:findInExtraData() => Column name is not in DS_AM_EXTRA_COLUMNS constant.');
        }
        
        if (!CheckString::extra($term, DS_AM_EXTRA_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:findInExtraData() => Bad "extra" string format.');
        }
        
        $sql = " SELECT * FROM users WHERE $column LIKE :term "; // $column name is validated by validExtraColumnName() above
        $stmt = $this->pdo->prepare($sql);
        $prepare = [     
            'term' => "%". $term ."%"  
        ]; 
        
        if($stmt)
        {
            $stmt->execute($prepare);
            return ReturnOnce::ok($stmt->fetchAll(\PDO::FETCH_ASSOC));
        }
    } 
        
    public function getUsersWithRole($role)
    {         
        if (!CheckString::validRoleName($role))
        {
            return ReturnOnce::error("Model:getUsersWithRole() => Role name '". $role ."' doesn't exists in 'config.php'.");
        }
        
        $prepare = [     
            'role' => $role  
        ]; 
        $sql = " SELECT * FROM users WHERE role = :role ";
        $stmt = $this->pdo->prepare($sql);
            
        try 
        {  
            if ($stmt->execute($prepare))
            {      
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getUsersWithRole() => ' . $e); 
        } 
       
    } 
        
        
    //This updates only extra data and not: username, password and email.
    public function updateExtraById($id, $assoc)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:deleteById() => Argument not integer.');
        } 
        
        if (!CheckString::validExtraColumnName(array_keys($assoc)))
        {            
            return ReturnOnce::error("Model:updateExtra() => Column name doesn't pass settings in 'config.php'.");
        }

        $updateUserDataReturn = $this->updateUserData($id, $assoc);
        if (!ReturnOnce::isOk($updateUserDataReturn))
        {
            return $updateUserDataReturn;
        } 

        return $this->setModifed($id);
    } 
    
    public function updateUserNameById($id, $username)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:updateUserNameById() => Argument not integer.');
        } 

        if (!CheckString::username($username, DS_AM_USERNAME_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:updateUserNameById() => Bad "username" string format.');
        }

        $data = [
            'id' => $id,
            'username' => $username,   
        ];

        $sql = "UPDATE users SET username = :username , modifiedAt = datetime('now') WHERE id = :id ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                //return $stmt->rowCount();
                //return ReturnOnce::ok($stmt->rowCount());
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }  
          
        }
        catch (\PDOException $e)
        {           
           return ReturnOnce::error('Model:updateUserNameById() => ' . $e);
        }         

    } 
    
    public function updatePasswordById($id, $password)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:updateUserNameById() => Argument not integer.');
        } 

/*
        if (!CheckString::password($password, DS_AM_PASSWORD_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:updatePasswordById() => Bad "password" string format.');
        }
*/
        $data = [
            'id' => $id,
            'password' => $password,   
        ];

        $sql = "UPDATE users SET password = :password , modifiedAt = datetime('now') WHERE id = :id ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }  
 
        }
        catch (\PDOException $e)
        {    
            return ReturnOnce::error('Model:updatePasswordById() => ' . $e);    
        }         

    } 
    
    public function updateEmailById($id, $email)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:updateEmailById() => Argument not integer.');
        } 

        if (!CheckString::email($email, DS_AM_PASSWORD_MAX_STRLEN))
        {
            return ReturnOnce::error('Model:updateEmailById() => Bad "email" string format.');
        }

        $data = [
            'id' => $id,
            'email' => $email,   
        ];

        $sql = "UPDATE users SET email = :email  , modifiedAt = datetime('now') WHERE id = :id ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }
        }
        catch (\PDOException $e)
        {           
           return ReturnOnce::error('Model:updateEmailById() => ' . $e);
        }         

    } 
    
    
    public function setUserRole($id, $role)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:setUserRole() => Argument not integer.');
        } 

        if (!CheckString::validRoleName($role))
        {
            return ReturnOnce::error("Model:setUserRole() => Role name '". $role ."' doesn't exists in 'config.php'.");
        }

        $data = [
            'id' => $id,
            'role' => $role,   
        ];

        $sql = "UPDATE users SET role = :role , modifiedAt = datetime('now') WHERE id = :id ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            } 
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:setUserRole() => ' . $e);
        }         

    } 
    
    
    public function activateUser($id)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:activateUser() => Argument not integer.');
        } 

        $data = [
            'id' => $id
        ];

        $sql = "UPDATE users SET active = 1 , modifiedAt = datetime('now') WHERE id = :id ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }  
        }
        catch (\PDOException $e)
        {       
            return ReturnOnce::error('Model:activateUser() => ' . $e);    
        } 
    } 
    
    
    public function deactivateUser($id)
    {   
        if (!is_int($id))
        {
            return ReturnOnce::error('Model:deactivateUser() => Argument not integer.');  
        } 

        $data = [
            'id' => $id
        ];

        $sql = "UPDATE users SET active = 0 , modifiedAt = datetime('now') WHERE id = :id ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }
        }
        catch (\PDOException $e)
        {           
           return ReturnOnce::error('Model:deactivateUser() => ' . $e); 
        }        

    } 
    
    public function banUserByEmail($email)
    {   

        $data = [
            'email' => $email
        ];

        $sql = "UPDATE users SET role = 'banned' , modifiedAt = datetime('now') WHERE email = :email ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }
        }
        catch (\PDOException $e)
        {           
           return ReturnOnce::error('Model:banUserByEmail() => ' . $e); 
        }        

    } 
    
    public function unBanUserByEmail($email)
    {   

        $data = [
            'email' => $email
        ];

        $sql = "UPDATE users SET role = 'user' , modifiedAt = datetime('now') WHERE email = :email ";
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($data))
            {
                $rowCount = $stmt->rowCount();
                if ($rowCount > 0)
                {
                    return ReturnOnce::ok($stmt->rowCount());
                }
                else
                {
                   return ReturnOnce::fail('Nothing was updated. Is index in range?'); 
                }
            }
        }
        catch (\PDOException $e)
        {           
           return ReturnOnce::error('Model:unBanUserByEmail() => ' . $e); 
        }        

    } 
    
    
    
    public function getCreatedBefore($unixtime)
    {   
        if (!CheckString::isValidTimeStamp($unixtime))
        {
            return ReturnOnce::error('Model:getCreatedBefore() => Argument not valid timestamp.');
        } 

        $data = [
            'unixtime' => date('Y-m-d H:i:s', $unixtime)
        ];

        $sql = 'SELECT * FROM users WHERE createdAt BETWEEN "1970-01-01 00:00:00" AND :unixtime ';
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($prepare))
            {      
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getCreatedBefore() => ' . $e); 
        }
    } 
    
    public function getCreatedAfter($unixtime)
    {   
        if (!CheckString::isValidTimeStamp($unixtime))
        {
            return ReturnOnce::error('Model:getCreatedAfter() => Argument not valid timestamp.');
        } 

        $data = [
            'unixtime' => date('Y-m-d H:i:s', $unixtime)
        ];

        $sql = 'SELECT * FROM users WHERE createdAt BETWEEN :unixtime AND datetime("now") ';
        $stmt = $this->pdo->prepare($sql);  
        
        try 
        {  
            if ($stmt->execute($prepare))
            {      
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if ($result)
                {
                    return ReturnOnce::ok($result);
                }
                else
                {
                    return ReturnOnce::fail('No data found.');
                }
            }
        }
        catch (\PDOException $e)
        {           
            return ReturnOnce::error('Model:getCreatedAfter() => ' . $e); 
        }
    } 
    
    /*
    public function testSql($sql)
    {       
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($rows);  
        echo "</pre>";      
    } 
    */

    
};
