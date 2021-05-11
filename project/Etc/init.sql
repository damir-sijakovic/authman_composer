CREATE TABLE users (
	id INTEGER PRIMARY KEY,
	username TEXT,	
	email TEXT NOT NULL UNIQUE,
	password TEXT NOT NULL,
    createdAt TEXT NOT NULL,
    modifiedAt TEXT,    
    role TEXT DEFAULT 'notVerified', 
    active INTEGER DEFAULT 1, 
    userData TEXT
);

CREATE TABLE authman (
	id INTEGER PRIMARY KEY,
	databaseCreatedAt TEXT
);

/* extra data is added via config.php/DS_AM_EXTRA_COLUMNS as TEXT datatype */

