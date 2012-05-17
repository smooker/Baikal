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

namespace BaikalAdmin\Controller\User;

class AddressBooks extends \Flake\Core\Controller {
	
	protected $aMessages = array();
	protected $oModel;	# \Baikal\Model\Contact
	protected $oUser;	# \Baikal\Model\User
	protected $oForm;	# \Formal\Form
	
	public function __construct() {
		parent::__construct();
		
		if(($iUser = $this->currentUserId()) === FALSE) {
			throw new \Exception("BaikalAdmin\Controller\User\Contacts::render(): User get-parameter not found.");
		}
		
		$this->oUser = new \Baikal\Model\User($iUser);
		
		if(($iModel = self::editRequested()) !== FALSE) {
			$this->oModel = new \Baikal\Model\AddressBook($iModel);
			$this->initForm();
		}

		if(self::newRequested()) {
			# building floating object
			$this->oModel = new \Baikal\Model\AddressBook();
			$this->oModel->set(
				"principaluri",
				$this->oUser->get("uri")
			);
			
			$this->oModel->set(
				"ctag",
				"1"
			);
			
			$this->initForm();
		}
	}

	public function execute() {
		if(self::editRequested()) {
			if($this->oForm->submitted()) {
				$this->oForm->execute();
			}
		}
		
		if(self::newRequested()) {
			if($this->oForm->submitted()) {
				$this->oForm->execute();
				
				if($this->oForm->persisted()) {
					$this->oForm->setOption(
						"action",
						$this->linkEdit(
							$this->oForm->modelInstance()
						)
					);
				}
			}
		}
		
		if(($iModel = self::deleteRequested()) !== FALSE) {
			
			if(self::deleteConfirmed() !== FALSE) {
				
				# catching Exception thrown when model already destroyed
					# happens when user refreshes page on delete-URL, for instance
					
				try {
					$oModel = new \Baikal\Model\AddressBook($iModel);
					$oModel->destroy();				
				} catch(\Exception $e) {
					# already deleted; silently discarding
				}
				
				# Redirecting to admin home
				\Flake\Util\Tools::redirectUsingMeta(self::linkHome());
			} else {
				
				$oModel = new \Baikal\Model\AddressBook($iModel);
				$this->aMessages[] = \Formal\Core\Message::warningConfirmMessage(
					"Check twice, you're about to delete " . $oModel->label() . "</strong> from the database !",
					"<p>You are about to delete a contact book and all it's visiting cards. This operation cannot be undone.</p><p>So, now that you know all that, what shall we do ?</p>",
					self::linkDeleteConfirm($oModel),
					"Delete <strong><i class='" . $oModel->icon() . " icon-white'></i> " . $oModel->label() . "</strong>",
					self::linkHome()
				);
			}
		}
	}

	public function render() {
		
		$oView = new \BaikalAdmin\View\User\AddressBooks();
		
		# User
		$oView->setData("user", $this->oUser);
		
		# Render list of address books
		$aAddressBooks = array();
		
		$oAddressBooks = $this->oUser->getAddressBooksBaseRequester()->execute();
		
		reset($oAddressBooks);
		foreach($oAddressBooks as $addressbook) {
			$aAddressBooks[] = array(
				"linkedit" => \BaikalAdmin\Controller\User\AddressBooks::linkEdit($addressbook),
				"linkdelete" => \BaikalAdmin\Controller\User\AddressBooks::linkDelete($addressbook),
				"icon" => $addressbook->icon(),
				"label" => $addressbook->label(),
				"description" => $addressbook->get("description"),
			);
		}
		
		$oView->setData("addressbooks", $aAddressBooks);
		
		# Messages
		$sMessages = implode("\n", $this->aMessages);
		$oView->setData("messages", $sMessages);

		if(self::newRequested() || self::editRequested()) {
			$sForm = $this->oForm->render();
		} else {
			$sForm = "";
		}
		
		$oView->setData("form", $sForm);
		$oView->setData("titleicon", \Baikal\Model\AddressBook::bigicon());
		$oView->setData("modelicon", $this->oUser->mediumIcon());
		$oView->setData("modellabel", $this->oUser->label());
		$oView->setData("linkback", \BaikalAdmin\Controller\Users::link());
		$oView->setData("linknew", \BaikalAdmin\Controller\User\AddressBooks::linkNew());
		$oView->setData("addressbookicon", \Baikal\Model\AddressBook::icon());

		return $oView->render();
	}
	
	protected function initForm() {
		if($this->editRequested() || $this->newRequested()) {
			$aOptions = array(
				"closeurl" => $this->linkHome()
			);
			
			$this->oForm = $this->oModel->formForThisModelInstance($aOptions);
		}
	}
	
	protected static function currentUserId() {
		$aParams = $GLOBALS["ROUTER"]::getURLParams();
		if(($iUser = intval($aParams[0])) === 0) {
			return FALSE;
		}
		
		return $iUser;
	}
	
	protected static function newRequested() {
		$aParams = $GLOBALS["ROUTER"]::getURLParams();
		return (count($aParams) >= 2) && $aParams[1] === "new";
	}
	
	protected static function editRequested() {
		$aParams = $GLOBALS["ROUTER"]::getURLParams();
		if((count($aParams) >= 3) && ($aParams[1] === "edit") && intval($aParams[2]) > 0) {
			return intval($aParams[2]);
		}
		
		return FALSE;
	}
	
	protected static function deleteRequested() {
		$aParams = $GLOBALS["ROUTER"]::getURLParams();
		if((count($aParams) >= 3) && ($aParams[1] === "delete") && intval($aParams[2]) > 0) {
			return intval($aParams[2]);
		}
		
		return FALSE;
	}
	
	protected static function deleteConfirmed() {
		if(($iPrimary = self::deleteRequested()) === FALSE) {
			return FALSE;
		}
		
		$aParams = $GLOBALS["ROUTER"]::getURLParams();
		if((count($aParams) >= 4) && $aParams[3] === "confirm") {
			return $iPrimary;
		}
		
		return FALSE;
	}
	
	public static function linkNew() {
		return $GLOBALS["ROUTER"]::buildRouteForController(
			get_called_class(),
			self::currentUserId(),
			"new"
		) . "#form";
	}
	
	public static function linkEdit(\Baikal\Model\AddressBook $oModel) {
		return $GLOBALS["ROUTER"]::buildRouteForController(
			get_called_class(),
			self::currentUserId(),
			"edit",
			$oModel->get("id")
		) . "#form";
	}
	
	public static function linkDelete(\Baikal\Model\AddressBook $oModel) {
		return $GLOBALS["ROUTER"]::buildRouteForController(
			get_called_class(),
			self::currentUserId(),
			"delete",
			$oModel->get("id")
		) . "#message";
	}
	
	public static function linkDeleteConfirm(\Baikal\Model\AddressBook $oModel) {
		return $GLOBALS["ROUTER"]::buildRouteForController(
			get_called_class(),
			self::currentUserId(),
			"delete",
			$oModel->get("id"),
			"confirm"
		) . "#message";
	}
	
	public static function linkHome() {
		return $GLOBALS["ROUTER"]::buildRouteForController(
			get_called_class(),
			self::currentUserId()
		);
	}
}