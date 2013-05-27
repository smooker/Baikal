<?php
#################################################################
#  Copyright notice
#
#  (c) 2012 Jérôme Schneider <mail@jeromeschneider.fr>
#  All rights reserved
#
#  http://flake.codr.fr
#
#  This script is part of the Flake project. The Flake
#  project is free software; you can redistribute it
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

namespace Flake\Core\Database\Postgresql;

class PDOWrapper extends \PDO {
	protected $lasttable = FALSE;
	
	public function lastInsertId() {
		if($this->lasttable === FALSE) {
			#\Flake\Util\Tools::log("lastInsertId::NONE");
			return 0;
		}
		
/*		$sSQL = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . addslashes($this->lasttable) . "' AND column_default!='' AND data_type='integer'";
		error_log("\n\n----------------\lastInsertId:BEFORE:(" . $this->lasttable . "):" . $sSQL, 3, "/tmp/baikal.heroku.local.log");
		
		$rSql = parent::query($sSQL);
		if(($aRs = $rSql->fetch()) !== FALSE) {
			$sIdName = $aRs["column_name"];
			
			$sSQL2 = "SELECT currval('" . $this->lasttable . "_" . $sIdName . "_seq')";
			error_log("\n\n----------------\lastInsertId:BEFORE2:(" . $this->lasttable . "):" . $sSQL2, 3, "/tmp/baikal.heroku.local.log");
			
			$rSql2 = parent::query($sSQL2);
			if(($aRs = $rSql2->fetch()) !== FALSE) {
				$iId = intval($aRs["currval"]);
				error_log("\n\n----------------\lastInsertId::FOUND:" . $sIdName . "(" . $iId . ")", 3, "/tmp/baikal.heroku.local.log");
				
				return $iId;
			}
		}*/
		
		$sSql = "SELECT c.relname as seqname FROM pg_class c WHERE c.relkind = 'S' AND c.relname LIKE '" . $this->lasttable . "_%'";
		#\Flake\Util\Tools::log("lastInsertId:BEFORE:(" . $this->lasttable . "):" . $sSql);
		
		$rSql = parent::query($sSql);
		if(($aRs = $rSql->fetch()) === FALSE) {
			#\Flake\Util\Tools::log("lastInsertId::SEQUENCE_NOTFOUND");
			return 0;
		}
		
		$sSql2 = "SELECT currval('" . $aRs["seqname"] . "') as currentid";
		#\Flake\Util\Tools::log("lastInsertId:GET_SEQUENCE_ID:(" . $this->lasttable . "):" . $sSql2);
		
		$rSql2 = parent::query($sSql2);
		if(($aRs = $rSql2->fetch()) === FALSE) {
			#\Flake\Util\Tools::log("lastInsertId::SEQUENCE_ID_NOTFOUND");
			return 0;
		}
		
		#\Flake\Util\Tools::log("lastInsertId::SEQUENCE_ID_FOUND::" . intval($aRs["currentid"]));
		
		return intval($aRs["currentid"]);
	}
	
	public function query() {
		
		$aArgs = func_get_args();
		$aArgs[0] = str_replace("`", '"', $aArgs[0]);
		
		if(preg_match("/^INSERT\s.*$/six", $aArgs[0])) {
			$aMatches = array();
			preg_match("/\sINTO\s(.*?)[\s|$]/six", $aArgs[0], $aMatches);
			$this->lasttable = $aMatches[1];	
		}
		
		#\Flake\Util\Tools::log("query::" . $aArgs[0]);
		return call_user_func_array("parent::query", $aArgs);
	}
	
	public function prepare() {
		
		$aArgs = func_get_args();
		$aArgs[0] = str_replace("`", '"', $aArgs[0]);
		
		if(preg_match("/^INSERT\s.*$/six", $aArgs[0])) {
			$aMatches = array();
			preg_match("/\sINTO\s(.*?)[\s|$]/six", $aArgs[0], $aMatches);
			$this->lasttable = $aMatches[1];
		}
		
		#\Flake\Util\Tools::log("prepare::" . $aArgs[0]);
		$oRes = new \Flake\Core\Database\Postgresql\PDOStatementWrapper(call_user_func_array("parent::prepare", $aArgs));
		
		return $oRes;
	}
}	