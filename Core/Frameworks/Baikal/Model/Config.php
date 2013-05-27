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

namespace Baikal\Model;

abstract class Config extends \Flake\Core\Model\NoDb {

	protected $aConstants = array();
	protected $aData = array();

	public function __construct() {
		$this->aData = $GLOBALS['SERVICES']['CONFIGADAPTER']->fetch($this);
	}

	public function floating() {
		$GLOBALS['SERVICES']['CONFIGADAPTER']->floating($this);
	}

	public static function icon() {
		return "icon-cog";
	}

	public static function mediumicon() {
		return "glyph-cogwheel";
	}

	public static function bigicon() {
		return "glyph2x-cogwheel";
	}

	public function destroy() {
	}

	protected static function getDefaultConfig() {
	}

	public function persist() {
		$GLOBALS['SERVICES']['CONFIGADAPTER']->persist($this);
	}
}