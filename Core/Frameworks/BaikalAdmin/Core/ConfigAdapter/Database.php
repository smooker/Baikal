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

namespace BaikalAdmin\Core\ConfigAdapter;

class Database extends \BaikalAdmin\Core\ConfigAdapter {

	protected function getTableName(\Baikal\Model\Config $configobject) {
		
		$tableforclass = array(
			'Baikal\Model\Config\Standard' => 'baikal_config_standard',
			'Baikal\Model\Config\System' => 'baikal_config_system',
		);

		return $tableforclass[get_class($configobject)];
	}

	public function writable(\Baikal\Model\Config $configobject) {
		return TRUE;
	}

	public function floating(\Baikal\Model\Config $configobject) {
		$oStmt = $GLOBALS['DB']->exec_SELECTquery(
			'*',
			$this->getTableName($configobject),
			'1=1'
		);

		return ($oStmt->fetch()) === FALSE;
	}

	public function fetch(\Baikal\Model\Config $configobject) {
		if($this->floating($configobject)) {
			return $configobject->aData;
		}

		$oStmt = $GLOBALS['DB']->exec_SELECTquery(
			'*',
			$this->getTableName($configobject),
			'1=1'
		);

		return $oStmt->fetch();
	}

	public function persist(\Baikal\Model\Config $configobject) {
		if($this->floating($configobject)) {
			$GLOBALS['DB']->exec_INSERTquery(
				$this->getTableName($configobject),
				$configobject->aData
			);
		} else {
			$GLOBALS['DB']->exec_UPDATEquery(
				$this->getTableName($configobject),
				'1=1',
				$configobject->aData
			);
		}

		return TRUE;
	}
}