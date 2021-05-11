<?php

//PROJECT NAME
define('DS_AM_PROJECT_NAME', 'MySiteProject');

//MODE
define('DS_AM_MODE', 'development'); // production | development

//MODE
define('DS_AM_NEW_USER_SET_TO_NOT_VERIFIED', false); 

//VERIFY MAIL
define('DS_AM_VERIFY_MAIL_SUBJECT', 'Verify Account - ' . DS_AM_PROJECT_NAME ); 
define('DS_AM_VERIFY_MAIL_MESSAGE', 'Please copy this confirmation code to your website: '); 

//NAME/PASS 
define('DS_AM_USERNAME_MAX_STRLEN', 256);
define('DS_AM_PASSWORD_MAX_STRLEN', 256);
define('DS_AM_EMAIL_MAX_STRLEN',    256);
define('DS_AM_EXTRA_MAX_STRLEN',    256);

//MAX REGISTRATED USERS NUMBER  
define('DS_AM_MAX_USER_NUMBER', 10000);

//ALLOWED CHAR TABLE FOR USERNAME AND PASSWORD
define('DS_AM_USERNAME_TABLE', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$?_+-');
define('DS_AM_PASSWORD_TABLE', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$?_+-');
define('DS_AM_EXTRA_TABLE', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!#$?_+- ');
define('DS_AM_EXTRA_DATA_TABLE', '!"#$%&()*+,-./0123456789:;<=>?@ABCČĆDĐEFGHIJKLMNOPQRSŠTUVWXYZ[\]^_`abcčćdđefghijklmnopqrsštuvwxyz{|}~ ');

//EXTRA COLUMNS, (ALL TEXT TYPE)
define('DS_AM_EXTRA_COLUMNS', ['website','aboutMe','gender','firstName','lastName','address','city','country','phone','postalCode','birthday','hobbies','avatar']);     

//HANDLE ERRORS - THROW EXCEPTION ON ERROR INSIDE LIB
define('DS_AM_ON_ERROR_EXCEPTION', false);

//HOW LONG USER STAYS LOGGED ON
define('DS_AM_LOGIN_EXPIRES_AT', '+30 minutes'); 

//TIME BETWEEN LOGINS
define('DS_AM_BRUTE_FORCE_ENABLED', true);
define('DS_AM_BRUTE_FORCE_THRESHOLD', 3);
define('DS_AM_BRUTE_FORCE',     '+6 seconds'); //strtotime
define('DS_AM_BRUTE_FORCE_BAN', '+10 seconds');
define('DS_AM_BRUTE_FORCE_RESET', '+30 seconds');

//WRONG PASS BAN
define('DS_AM_WRONG_PASS_ENABLED', true);
define('DS_AM_WRONG_PASS_THRESHOLD', 3);
define('DS_AM_WRONG_PASS',     '+3 seconds'); 
define('DS_AM_WRONG_PASS_BAN', '+30 seconds');
define('DS_AM_WRONG_PASS_RESET', '+10 seconds');

//JWT
define ('DS_AM_JWT_EXP', DS_AM_LOGIN_EXPIRES_AT); //value is strtotime() argument 
define ('DS_AM_JWT_NBF', null);   


//ROUTES
define('DS_AM_USER_ONLY_ROUTES', [
    '|^/dashboard?$|',    
    '|^/refresh?$|',   
]);

define('DS_AM_ADMIN_ONLY_ROUTES', [
    '|^/admin?$|'   
]);

