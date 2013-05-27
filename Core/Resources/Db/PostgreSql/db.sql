#
# This is the empty database schema for Baïkal
# Corresponds to the PostgreSQL Schema definition of project SabreDAV 1.6.4
# http://code.google.com/p/sabredav/
#

CREATE TABLE users (
	id SERIAL,
	username VARCHAR(50),
	digesta1 VARCHAR(32),
	UNIQUE(username)
);

CREATE TABLE principals (
	id SERIAL,
	uri VARCHAR(200) NOT NULL,
	email VARCHAR(80),
	displayname VARCHAR(80),
	vcardurl VARCHAR(80),
	UNIQUE(uri)
);

CREATE TABLE groupmembers (
	id SERIAL,
	principal_id INT,
	member_id INT,
	UNIQUE(principal_id, member_id)
);

CREATE TABLE locks (
	id SERIAL,
	owner VARCHAR(100),
	timeout INT,
	created INT,
	token VARCHAR(100),
	scope INT,
	depth INT,
	uri TEXT
);

CREATE TABLE calendarobjects (
	id SERIAL,
	calendardata BYTEA,
	uri VARCHAR(200),
	calendarid INT,
	lastmodified INT,
	UNIQUE(calendarid, uri)
);

CREATE TABLE calendars (
	id SERIAL,
	principaluri VARCHAR(100),
	displayname VARCHAR(100),
	uri VARCHAR(200),
	ctag INT,
	description TEXT,
	calendarorder INT,
	calendarcolor VARCHAR(10),
	timezone TEXT,
	components VARCHAR(20),
	UNIQUE(principaluri, uri)
);

CREATE TABLE addressbooks (
	id SERIAL,
	principaluri VARCHAR(255),
	displayname VARCHAR(255),
	uri VARCHAR(200),
	description TEXT,
	ctag INT DEFAULT 1,
	UNIQUE(principaluri, uri)
);

CREATE TABLE cards (
	id SERIAL,
	addressbookid INT,
	carddata BYTEA,
	uri VARCHAR(200),
	lastmodified INT
);