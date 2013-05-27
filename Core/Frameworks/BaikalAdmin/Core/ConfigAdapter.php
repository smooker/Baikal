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

namespace BaikalAdmin\Core;

abstract class ConfigAdapter {

	# Is the config writable ?
	public abstract function writable(\Baikal\Model\Config $configobject);

	# Is the config already persisted, or not (floating) ?
	public abstract function floating();

	# Converts the given config object as an array and returns it
	public abstract function fetch(\Baikal\Model\Config $configobject); 

	# Persists the givent config object
	public abstract function persist(\Baikal\Model\Config $configobject);

}