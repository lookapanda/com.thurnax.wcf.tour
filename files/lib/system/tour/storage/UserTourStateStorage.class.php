<?php
namespace wcf\system\tour\storage;
use wcf\data\tour\Tour;
use wcf\system\cache\builder\TourCacheBuilder;
use wcf\system\cache\builder\TourTriggerCacheBuilder;
use wcf\system\tour\TourHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Tour state storage for users
 *
 * @author    Magnus Kühn
 * @copyright 2013-2014 Thurnax.com
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package   com.thurnax.wcf.tour
 */
class UserTourStateStorage extends AbstractTourStateStorage {
	/**
	 * Initializes the tour state storage
	 */
	public function __construct() {
		UserStorageHandler::getInstance()->loadStorage(array(WCF::getUser()->userID));
		$data = UserStorageHandler::getInstance()->getStorage(array(WCF::getUser()->userID), self::STORAGE_NAME);

		if ($data[WCF::getUser()->userID] === null) {
			// import cookie data to database
			$this->readCookie();
			if ($this->cache['takenTours']) {
				$sql = "INSERT IGNORE INTO ".Tour::getDatabaseTableName()."_user (tourID, userID) VALUES (?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				$viewableTours = TourCacheBuilder::getInstance()->getData(array(), 'tours');
				foreach ($this->getTakenTours() as $takenTourID) {
					if (isset($viewableTours[$takenTourID])) {
						$statement->execute(array($takenTourID, WCF::getUser()->userID));
					}
				}

				// cleanup cookie
				HeaderUtil::setCookie(self::STORAGE_NAME);
				$this->cache['takenTours'] = array();
			}

			// get taken tour ids from database
			$sql = "SELECT	tourID, takeTime
				FROM	".Tour::getDatabaseTableName()."_user
				WHERE	userID = ?
				ORDER BY takeTime DESC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(WCF::getUser()->userID));
			while ($row = $statement->fetchArray()) {
				if (!$this->cache['lastTourTime']) {
					$this->cache['lastTourTime'] = $row['takeTime'];
				}

				$this->cache['takenTours'][] = $row['tourID'];
			}

			// get available tours
			foreach (TourTriggerCacheBuilder::getInstance()->getData(array(), 'manual') as $tourID) {
				if (!in_array($tourID, $this->cache['takenTours']) && TourHandler::canViewTour($tourID)) {
					$this->cache['availableTours'][] = $tourID;
				}
			}

			// update user storage
			UserStorageHandler::getInstance()->update(WCF::getUser()->userID, self::STORAGE_NAME, serialize($this->cache));
		} else {
			$this->cache = unserialize($data[WCF::getUser()->userID]);
		}
	}

	/**
	 * Marks a tour as taken
	 *
	 * @param int $tourID
	 */
	public function takeTour($tourID) {
		$sql = "INSERT INTO ".Tour::getDatabaseTableName()."_user (tourID, userID, takeTime) VALUES (?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($tourID, WCF::getUser()->userID, TIME_NOW));

		AbstractTourStateStorage::takeTour($tourID);
		UserStorageHandler::getInstance()->update(WCF::getUser()->userID, self::STORAGE_NAME, serialize($this->cache));
	}
}
