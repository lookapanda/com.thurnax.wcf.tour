<?php
namespace wcf\system\tour\storage;

/**
 * Tour state storage for users
 * 
 * @author	Magnus Kühn
 * @copyright	2013-2014 Thurnax.com
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.thurnax.wcf.tour
 */
abstract class AbstractTourStateStorage implements ITourStateStorage {
	const STORAGE_NAME = 'tourCache';
	
	/**
	 * cache for the current user
	 * @var	array<string>
	 */
	protected $cache = array('availableTours' => array(), 'takenTours' => array());
	
	/**
	 * Initializes the tour state storage
	 */
	abstract public function __construct();
	
	/**
	 * @see	\wcf\system\tour\storage\ITourStateStorage::getAvailableTours(getAvailableManualTours
	 */
	public function getAvailableManualTours() {
		return $this->cache['availableTours'];
	}
	
	/**
	 * @see	\wcf\system\tour\storage\ITourStateStorage::getTakenTours()
	 */
	public function getTakenTours() {
		return $this->cache['takenTours'];
	}
	
	/**
	 * @see	\wcf\system\tour\storage\ITourStateStorage::takeTour()
	 */
	public function takeTour($tourID) {
		// update cache
		$this->cache['takenTours'][] = $tourID;
		if (isset($this->cache['availableTours'][$tourID])) {
			unset ($this->cache['availableTours'][$tourID]);
		}
	}
}