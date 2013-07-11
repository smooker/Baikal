<?php
#################################################################
#  Copyright notice
#
#  (c) 2012 Jérôme Schneider <mail@jeromeschneider.fr>
#  All rights reserved
#
#  http://baikal.codr.fr
#
#  This script is part of the Baïkal Server project. The Baïkal
#  Server project is free software; you can redistribute it
#  and/or modify it under the terms of the GNU General Public
#  License as published by the Free Software Foundation; either
#  version 2 of the License, or (at your option) any later version.
#
#  The GNU General Public License can be found at
#  http://www.gnu.org/copyleft/gpl.html.
#
#  This script is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  This copyright notice MUST APPEAR in all copies of the script!
#################################################################

namespace BaikalAdmin\Controller\Install;

class InitializeHeroku extends \Flake\Core\Controller {

	const DB_STATUS_STRUCTURE_EMPTY = 'DB_STATUS_STRUCTURE_EMPTY';
	const DB_STATUS_STRUCTURE_INCOMPLETE = 'DB_STATUS_STRUCTURE_INCOMPLETE';
	const DB_STATUS_STRUCTURE_COMPLETE = 'DB_STATUS_STRUCTURE_COMPLETE';

	protected $dbhasbeeninitialized = FALSE;	# Serves as a flag to enable communication between execute() and render()

	public function execute() {
		self::assertHerokuEnvironment();

		if(intval(\Flake\Util\Tools::POST('proceed-wih-db-init')) === 1) {
			if(self::createSchemasIfNecessary($GLOBALS['DB'])) {
				$this->dbhasbeeninitialized = TRUE;
			}
		}
	}

	public function render() {

		$sBaikalVersion = BAIKAL_VERSION;

		if($this->dbhasbeeninitialized === TRUE) {
			$oView = new \BaikalAdmin\View\Install\InitializeHeroku\DbInitialized();
			$oView->setData('settingsuri',  PROJECT_URI . 'admin/install/');

		} else {
			switch(self::getDatabaseStructureStatus($GLOBALS['DB'])) {
				case self::DB_STATUS_STRUCTURE_EMPTY: {

					$oView = new \BaikalAdmin\View\Install\InitializeHeroku\DbEmpty();
					$oView->setData('thisuri', PROJECT_URI . 'admin/install/');
					break;
				}
				case self::DB_STATUS_STRUCTURE_INCOMPLETE: {

					$missingtables = \Baikal\Core\Tools::isDBStructurallyComplete($GLOBALS['DB']);
					$oView = new \BaikalAdmin\View\Install\InitializeHeroku\DbIncomplete();
					$oView->setData('missingtables', $missingtables);

					break;
				}
				case self::DB_STATUS_STRUCTURE_COMPLETE: {
					
					$oView = new \BaikalAdmin\View\Install\InitializeHeroku\DbComplete();
					break;
				}
				default: {
					throw new \Exception('Cannot determine database status');
				}
			}
		}

		$oView->setData("baikalversion", BAIKAL_VERSION);
		return $oView->render();
	}

	public function getDatabaseStructureStatus(\Flake\Core\Database $db) {
		
		if(($aMissingTables = \Baikal\Core\Tools::isDBStructurallyComplete($db)) === TRUE) {
			return self::DB_STATUS_STRUCTURE_COMPLETE;
		}

		# Checking if all tables are missing
		$aRequiredTables = \Baikal\Core\Tools::getRequiredTablesList();

		if(count($aRequiredTables) !== count($aMissingTables)) {
			# The database contains not any of the required tables
			return self::DB_STATUS_STRUCTURE_INCOMPLETE;
		}

		# The database is structurally complete
		return self::DB_STATUS_STRUCTURE_EMPTY;
	}

	protected static function assertHerokuEnvironment() {

		if(!defined('HEROKU_SERVER') || !HEROKU_SERVER) {
			throw new \Exception("<strong>Fatal error</strong>: InitializeHeroku cannot be executed on a non-heroku environment.");
		}

		# DB connexion has not been asserted earlier by Flake, to give us a chance to trigger the install tool
		# We assert it right now

		if(!\Flake\Framework::isDBInitialized() && (!defined("BAIKAL_CONTEXT_INSTALL") || BAIKAL_CONTEXT_INSTALL === FALSE)) {
			throw new \Exception("<strong>Fatal error</strong>: no connection to a database is available.");
		}
	}

	protected static function createSchemasIfNecessary(\Flake\Core\Database $db) {

		if(self::getDatabaseStructureStatus($db) !== self::DB_STATUS_STRUCTURE_EMPTY) {
			return FALSE;
		}

		$sqldefinitionfiles = array(
			PROJECT_PATH_VENDOR . 'sabre/dav/examples/sql/pgsql.addressbook.sql',
			PROJECT_PATH_VENDOR . 'sabre/dav/examples/sql/pgsql.calendars.sql',
			PROJECT_PATH_VENDOR . 'sabre/dav/examples/sql/pgsql.locks.sql',
			PROJECT_PATH_VENDOR . 'sabre/dav/examples/sql/pgsql.principals.sql',
			PROJECT_PATH_VENDOR . 'sabre/dav/examples/sql/pgsql.users.sql',
			PROJECT_PATH_CORERESOURCES . 'Db/PostgreSql/db.sql',
		);

		$SQLbuffer = array();
		foreach($sqldefinitionfiles as $sqldefinitionfile) {
			$SQLbuffer[] = file_get_contents($sqldefinitionfile);
		}
		
		$completesql = implode("\n", $SQLbuffer);

		# Removing comments
		$completesql = preg_replace('%^--.*?$\n?%smixu', '', $completesql);
		$completesql = preg_replace('%^\#.*?$\n?%smixu', '', $completesql);

		# Exploding into single queries
		$queries = preg_split('%;\s*?$\n?%smixu', $completesql, -1, PREG_SPLIT_NO_EMPTY);

		array_walk($queries, 'trim');

		foreach($queries as $query) {

			# Avoid demo content
			if(preg_match('%^INSERT\s+?INTO\s+?%smixu', $query)) {
				continue;
			}

			$db->query($query);
		}

		return TRUE;
	}
}