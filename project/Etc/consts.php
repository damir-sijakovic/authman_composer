<?php

//PATHS
define('DS_AM_ROOT_DIR',         __DIR__ . '/..');
define('DS_AM_ETC_DIR',         DS_AM_ROOT_DIR . '/Etc');
define('DS_AM_SYSTEM_DIR',       DS_AM_ROOT_DIR . '/System');
define('DS_AM_PATH_SQL_FILE',    DS_AM_ETC_DIR . '/init.sql');
define('DS_AM_PATH_DB_FILE',     DS_AM_ETC_DIR . '/project.db');
define('DS_AM_PATH_DB_BAK_FILE', DS_AM_ETC_DIR . '/project_db_BAK');
define('DS_AM_PATH_ACCESS_FILE', DS_AM_ETC_DIR . '/access.php');

define('DS_AM_USER_ROLES', ['banned', 'notVerified', 'user', 'admin']);     
  
