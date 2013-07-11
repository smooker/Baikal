#
# This is the empty database schema for Baïkal-specific tables
# Dependencies like SabreDAV may need some more tables to function properly
#

CREATE TABLE baikal_config_standard (
    "PROJECT_TIMEZONE" varchar(64) NOT NULL DEFAULT 'Europe/Paris',
    "BAIKAL_CARD_ENABLED" integer NOT NULL DEFAULT 1,
    "BAIKAL_CAL_ENABLED" integer NOT NULL DEFAULT 1,
    "BAIKAL_ADMIN_ENABLED" integer NOT NULL DEFAULT 1,
    "BAIKAL_ADMIN_AUTOLOCKENABLED" integer NOT NULL DEFAULT 0,
    "BAIKAL_ADMIN_PASSWORDHASH" varchar(32) NOT NULL DEFAULT '',
    "BAIKAL_DAV_AUTH_TYPE" varchar(32) NOT NULL DEFAULT 'Digest'
);

CREATE TABLE baikal_config_system (
    "BAIKAL_PATH_SABREDAV" varchar(255) NOT NULL DEFAULT '',
    "BAIKAL_AUTH_REALM" varchar(255) NOT NULL DEFAULT '',
    "BAIKAL_CARD_BASEURI" varchar(255) NOT NULL DEFAULT '',
    "BAIKAL_CAL_BASEURI" varchar(255) NOT NULL DEFAULT '',
    "BAIKAL_ENCRYPTION_KEY" varchar(255) NOT NULL DEFAULT '',
    "BAIKAL_CONFIGURED_VERSION" varchar(255) NOT NULL DEFAULT ''
);