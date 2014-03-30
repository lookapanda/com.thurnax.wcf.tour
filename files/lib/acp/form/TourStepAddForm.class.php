<?php
namespace wcf\acp\form;
use wcf\data\package\PackageCache;
use wcf\data\tour\step\TourStep;
use wcf\data\tour\step\TourStepAction;
use wcf\data\tour\step\TourStepEditor;
use wcf\data\tour\TourList;
use wcf\form\AbstractForm;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the tour step add form.
 * 
 * @author	Magnus Kühn
 * @copyright	2013-2014 Thurnax.com
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.thurnax.wcf.tour
 */
class TourStepAddForm extends AbstractForm {
	/**
	 * @see	\wcf\acp\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.tour.step.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canManageTour');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_TOUR');
	
	/**
	 * tour id
	 * @var	integer
	 */
	public $tourID = null;
	
	/**
	 * list of all tours
	 * @var	array<\wcf\data\tour\Tour>
	 */
	public $tours = null;
	
	/**
	 * target
	 * @var	string
	 */
	public $target = '';
	
	/**
	 * orientation
	 * @var	string
	 */
	public $orientation =  'left';
	
	/**
	 * valid orientations
	 * @var	string
	 */
	public $validOrientations = array('top-left', 'top-right', 'bottom-left', 'bottom-right');
	
	/**
	 * content
	 * @var	string
	 */
	public $stepContent = '';
	
	/**
	 * title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * x offset
	 * @var	integer
	 */
	public $xOffset = 0;
	
	/**
	 * y offset
	 * @var	integer
	 */
	public $yOffset = 0;
	
	/**
	 * url to redirect to
	 * @var	string
	 */
	public $url = '';
	
	/**
	 * callback for when previous-button is clicked
	 * @var	string
	 */
	public $callbackBefore = '';
	
	/**
	 * callback for when next-button is clicked 
	 * @var	string
	 */
	public $callbackAfter = '';
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read id => tour id
		if (isset($_REQUEST['id'])) {
			$this->tourID = intval($_REQUEST['id']);
		}
		
		// register I18n-items
		I18nHandler::getInstance()->register('title');
		I18nHandler::getInstance()->register('stepContent');
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		if (isset($_POST['tourID'])) $this->tourID = intval($_POST['tourID']);
		if (isset($_POST['target'])) $this->target = $_POST['target'];
		if (isset($_POST['orientation'])) $this->orientation = $_POST['orientation'];
		if (isset($_POST['stepContent'])) $this->stepContent = StringUtil::trim($_POST['stepContent']);
		
		// optionals
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['xOffset'])) $this->xOffset = intval($_POST['xOffset']);
		if (isset($_POST['yOffset'])) $this->yOffset = intval($_POST['yOffset']);
		if (isset($_POST['url'])) $this->url = $_POST['url'];
		if (isset($_POST['callbackBefore'])) $this->callbackBefore = StringUtil::trim($_POST['callbackBefore']);
		if (isset($_POST['callbackAfter'])) $this->callbackAfter = StringUtil::trim($_POST['callbackAfter']);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read tours
		$tourList = new TourList();
		$tourList->sqlOrderBy = 'visibleName ASC';
		$tourList->readObjects();
		$this->tours = $tourList->getObjects();
		
		if (empty($this->tours)) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.acp.tour.step.noTours'));
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate target
		if (empty($this->target)) {
			throw new UserInputException('target');
		}
		
		// validate orientation
		if (empty($this->orientation) || !in_array($this->orientation, $this->validOrientations)) {
			throw new UserInputException('orientation');
		}
		
		// validate title
		if (!I18nHandler::getInstance()->validateValue('title')) { // optional
			if (!I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title', 'multilingual');
			}
		}
		
		// validate content
		if (!I18nHandler::getInstance()->validateValue('stepContent')) {
			if (I18nHandler::getInstance()->isPlainValue('stepContent')) {
				throw new UserInputException('stepContent');
			} else {
				throw new UserInputException('stepContent', 'multilingual');
			}
		}
		
		// validate to use either url or callbackAfter
		if ($this->url && $this->callbackAfter) {
			throw new UserInputException('eitherUrlOrCallbackAfter');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		$packageID = PackageCache::getInstance()->getPackageID('com.thurnax.wcf.tour');
		
		// save tour point
		$this->objectAction = new TourStepAction(array(), 'create', array('data' => array(
			'tourID' => $this->tourID,
			'showOrder' => $this->getShowOrder(),
			'packageID' => $packageID,
			'target' => $this->target,
			'orientation' => $this->orientation,
			'content' => $this->stepContent,
			
			// optionals
			'title' => ($this->title ?: null),
			'xOffset' => ($this->xOffset ?: null),
			'yOffset' => ($this->yOffset ?: null),
			'url' => ($this->url ?: null),
			'callbackBefore' => ($this->callbackBefore ?: null),
			'callbackAfter' => ($this->callbackAfter ?: null)
		)));
		$this->objectAction->executeAction();
		$this->saved();
		
		// save I18n-values
		$returnValues = $this->objectAction->getReturnValues();
		$tourStepID = $returnValues['returnValues']->tourStepID;
		$updateData = array();
		
		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'wcf.acp.tour.step.title'.$tourStepID, 'wcf.acp.tour', $packageID);
			$updateData['title'] = 'wcf.acp.tour.step.title'.$tourStepID;
		}
		if (!I18nHandler::getInstance()->isPlainValue('stepContent')) {
			I18nHandler::getInstance()->save('stepContent', 'wcf.acp.tour.step.content'.$tourStepID, 'wcf.acp.tour', $packageID);
			$updateData['content'] = 'wcf.acp.tour.step.content'.$tourStepID;
		}
		
		// update tour step
		if ($updateData) {
			$tourStepEditor = new TourStepEditor($returnValues['returnValues']);
			$tourStepEditor->update($updateData);
		}
		
		// reset values
		$this->target = $this->stepContent = $this->url = $this->callbackBefore = $this->callbackAfter = '';
		$this->orientation = 'left';
		$this->xOffset = $this->yOffset = 0;
		I18nHandler::getInstance()->reset();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}

	/**
	 * Fetches the next show order
	 * 
	 * @return	integer
	 */
	protected function getShowOrder() {
		$sql = "SELECT	showOrder
			FROM	".TourStep::getDatabaseTableName()."
			WHERE	tourID = ?
			ORDER BY showOrder DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array($this->tourID));
		$row = $statement->fetchArray();
		
		return ($row ? $row['showOrder'] + 1 : 1);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'tourID' => $this->tourID,
			'tours' => $this->tours,
			'target' => $this->target,
			'orientation' => $this->orientation,
			'validOrientations' => $this->validOrientations,
			'content' => $this->stepContent,
			
			// optionals
			'xOffset' => $this->xOffset,
			'yOffset' => $this->yOffset,
			'url' => $this->url,
			'callbackBefore' => $this->callbackBefore,
			'callbackAfter' => $this->callbackAfter
		));
	}
}
