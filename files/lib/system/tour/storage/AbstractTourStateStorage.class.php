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
	/**
	 * cache for the current user
	 * @var	array<mixed>
	 */
	protected $cache = array('availableTours' => array(), 'takenTours' => array(), 'lastTourTime' => 0);
	
	/**
	 * Initializes the tour state storage
	 */
	abstract public function __construct();
	
	/**
	 * Reads cookie data
	 */
	protected function readCookie() {
		if (isset($_COOKIE[COOKIE_PREFIX.self::STORAGE_NAME])) {
			$this->cache['takenTours'] = @unserialize($_COOKIE[COOKIE_PREFIX.self::STORAGE_NAME]);
			
			if (!$this->cache['takenTours'] || !is_array($this->cache['takenTours'])) {
				$this->cache['takenTours'] = array();
			}
		}
	}
	
	/**
	 * @see	\wcf\system\tour\storage\ITourStateStorage::getAvailableTours()
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
	 * @see	\wcf\system\tour\storage\ITourStateStorage::shouldStartTour()
	 */
	public function shouldStartTour() {
		return ($this->cache['lastTourTime'] + TOUR_COOLDOWN_TIME * 60) <= TIME_NOW;
	}
	
	/**
	 * @see	\wcf\system\tour\storage\ITourStateStorage::takeTour()
	 */
	public function takeTour($tourID) {
		// update cache
		$this->cache['takenTours'][] = $tourID;
		$this->cache['lastTourTime'] = TIME_NOW;
		if (($index = array_search($tourID, $this->cache['availableTours'])) !== null) {
			unset ($this->cache['availableTours'][$index]);
		}
	}
}
