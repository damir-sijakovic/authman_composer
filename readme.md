# AUTHMAN

## PHP authorization module.

Easy drag-and-drop-and-use authorisation module.

### Features
* JWT tokens in http-only cookies
* no need to waste time on setting users model, data us stored into Sqlite DB file
* email verification
* backup
* user roles
* extra column data is configurable via config.php
* login brute force lock with timers
* wrong pass lock with timers
* link routes to user roles

## Install

        composer require dsijak/authman

On install new Sqlite project.db file is created and new JWT server.key 
will be generated.


### Usage
Make sure authman dir is set to read/write before usage.


        namespace Authman;
        $errorMessage;        
        $authman = new Authman();
        
        //register
        $registerSuccessful = $authman->register('foobar@mail.com', '1234', $errorMessage);
        if (!$registerSuccessful)
        {
            //do something with error message.
            error_log($errorMessage);
        }
        
        //login
        $loginSuccessful = $authman->login('foobar@mail.com', '1234', $errorMessage);
        if (!$loginSuccessful)
        {
            //do something with error message.
            error_log($errorMessage);
        }
        
        //get login details
        $authman->admin->debugHtml();

        //logout
        $authman->logout(); 


### Config

To configure Authman edit '/conf/config.php'.

Options

* DS_AM_PROJECT_NAME - Name of your project
* DS_AM_MODE - production|development, blocks html debug if set to production
* DS_AM_NEW_USER_SET_TO_NOT_VERIFIED - set to false if you don't need email verification
* DS_AM_VERIFY_MAIL_SUBJECT - Email subject of email verification mail
* DS_AM_VERIFY_MAIL_MESSAGE - Email message of email verification mail with confirmation code
* DS_AM_MAX_USER_NUMBER - maximum number of members
* DS_AM_USER_ONLY_ROUTES - link routes with user role
* DS_AM_ADMIN_ONLY_ROUTES - link routes with admin role
* DS_AM_USERNAME_MAX_STRLEN - allowed length of username string
* DS_AM_PASSWORD_MAX_STRLEN - allowd length of password string
* DS_AM_EMAIL_MAX_STRLEN - allowed length of email string
* DS_AM_EXTRA_MAX_STRLEN - allowed length of extra column string
* DS_AM_USERNAME_TABLE - allowed characters to be used as username string
* DS_AM_PASSWORD_TABLE - allowed characters to be used as password string
* DS_AM_EXTRA_TABLE - allowed characters to be used as extra column string
* DS_AM_EXTRA_COLUMNS - additional columns in user table 
* DS_AM_ON_ERROR_EXCEPTION - throw exception on error
* DS_AM_LOGIN_EXPIRES_AT - login expires at strtotime() string

### Isn't there an bottleneck/limit for SQLite if site is too busy?

Sqlite should handle up to 100 connections at same time.  
If you have more then 100 login/logouts at same time, use something else.   

### API

Methods return boolean and $message argument references fail/error message.

**register($email, $password, &$message=null)**    
Register user with email and password.   
Returns: boolean.   

**unregister($password, &$message=null)**   
Unregister. Set account 'active' column to 0.    
Returns: boolean.   
 
**login($email, $password, &$message=null)**    
Login user with email and password.    
Returns: boolean.   

**logout()**    
Logout user.   
Returns: boolean. 

**getCsrf()**  
Returns csrf token.    
Returns: string/null. 


**isCsrfValid($token)**  
Validates csrf token.    
Returns: boolean.   

**getRole()**   
Returns logged on user role.   
Returns: string/null.   

**setUserRole($role)**   
Changes logged on user role to string listed in DS_AM_USER_ROLES.    
Returns: boolean.  

**isLoggedOn()**   
Returns boolean if user is logged on.   
Returns: boolean.  

**refresh(&$message=null)**    
Check if session has expired.   
Returns: boolean.  
 
**isRouteLinked()**   
Check if logged on user is linked with route. For example if user role     
is 'admin' and route is '/admin', returns true.    
Links are set in 'config.php' DS_AM_USER_ONLY_ROUTES and DS_AM_ADMIN_ONLY_ROUTES.   
Returns: boolean.   

**changeRole($role)**    
Change logged on user role to 'banned', 'notVerified', 'user' or 'admin'.   
Returns: boolean.     


**addExtraData($assoc, &$message=null)**    
Add extra data to current logged on user. for example: ['city'=>'Smalltown'] will add 'Smalltown' to 'city' column.   
Returns: boolean.    

**verifyUser($confirmationCode, &$eMessage=null)**   
Verify user with confirmation code from verification email.    
Returns: boolean.   
 
**isUserVerified()**    
Returns false if user have 'notVerified' role.    
Returns: boolean.   
 
**sendConfirmationCodeEmail(&$eMessage=null)**       
Sends verification email with confirmation code.    
Returns: boolean.   
 



### Licence
MIT


### Have Fun
