/**
 * JS-API for starting hopscotch tours
 *
 * @author Magnus Kühn
 * @copyright 2013-2014 Thurnax.com
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package com.thurnax.wcf.tour
 */
WCF.Tour = {
	/**
	 * list of manual tours
	 * @var array<integer>
	 */
	manualTours: [],

	/**
	 * list of available manual tours
	 * @var array<integer>
	 */
	availableManualTours: [],

	/**
	 * show tour on mobile
	 * @var boolean
	 */
	showMobile: false,

	/**
	 * action proxy
	 * @var WCF.Action.Proxy
	 */
	_proxy: null,

	/**
	 * ID of the currently active tour
	 * @var integer
	 */
	_activeTourID: null,

	/**
	 * Loads a tour by the id.
	 *
	 * @param tourID int
	 * @param forceStop bool
	 */
	loadTour: function(tourID, forceStop) {
		// check mobile
		if (WCF.System.Mobile.UX._enabled && !this.showMobile) {
			return;
		}

		// a tour is already running
		if (this._activeTourID) {
			if (forceStop) { // stop tour
				hopscotch.endTour();
			} else { // cancel request
				return;
			}
		}

		// setup
		if (this._proxy === null) {
			// init proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this),
				failure: $.proxy(this._failure, this),
				showLoadingOverlay: false
			});

			// load hopscotch
			head.load([ WCF_PATH + 'js/3rdParty/hopscotch-0.2.0/js/hopscotch.min.js', WCF_PATH + 'js/3rdParty/hopscotch-0.2.0/css/hopscotch.min.css' ], $.proxy(this._initHopscotch, this));
		}

		// send request
		this._proxy.setOption('data', {
			className: 'wcf\\data\\tour\\TourAction',
			actionName: 'loadTour',
			objectIDs: [ tourID ]
		});
		this._proxy.sendRequest();
	},

	/**
	 * Loads a tour by the identifier
	 *
	 * @param identifier string
	 * @param force bool
	 * @param forceStop bool
	 */
	loadTourByIdentifier: function(identifier, force, forceStop) {
		var $tourID = this.manualTours[identifier];
		if ($tourID !== undefined && (force || WCF.inArray($tourID, this.availableManualTours))) {
			this.loadTour($tourID, forceStop);
		}
	},

	/**
	 * Callback after loading hopscotch.
	 */
	_initHopscotch: function() {
		// register helpers
		hopscotch.registerHelper('redirect_forward', function(url) {
			location.href = url;
		});
		hopscotch.registerHelper('redirect_back', function() {
			history.back();
		});
		hopscotch.registerHelper('custom_callback', function(callback) {
			eval(callback);
		});

		WCF.System.Dependency.Manager.invoke('hopscotch');
	},

	/**
	 * Handles AJAX responses.
	 *
	 * @param data array<string>
	 */
	_success: function(data) {
		if (data.actionName == 'loadTour' && this._activeTourID === null) {
			this._activeTourID = data.objectIDs.pop();
			var $tour = {
				id: 'com.thurnax.wcf.tour' + this._activeTourID,
				i18n: {
					nextBtn: WCF.Language.get('wcf.tour.step.locales.nextBtn'),
					prevBtn: WCF.Language.get('wcf.tour.step.locales.prevBtn'),
					doneBtn: WCF.Language.get('wcf.tour.step.locales.doneBtn'),
					skipBtn: WCF.Language.get('wcf.tour.step.locales.skipBtn'),
					closeTooltip: WCF.Language.get('wcf.tour.step.locales.closeTooltip')
				},
				steps: this._fixSteps(data.returnValues),
				onEnd: $.proxy(this._end, this),
				onClose: $.proxy(this._end, this),
				onError: $.proxy(this._error, this)
			};

			// start tour after hopscotch is loaded
			WCF.System.Dependency.Manager.register('hopscotch', function() {
				hopscotch.startTour($tour);
			});
		}
	},

	/**
	 * Fixes the step array.
	 * Callbacks for onCTA must be converted into a javascript function.
	 *
	 * @param steps array<array<string>>
	 * @return array<array<string>>
	 */
	_fixSteps: function(steps) {
		for (var i in steps) {
			if (steps[i].onCTA !== undefined) {
				steps[i].onCTA = new Function(steps[i].onCTA);
			}
		}

		return steps;
	},

	/**
	 * Handles AJAX errors. Ignores errors when not in debug mode (stacktrace is not sent)
	 *
	 * @param data array<string>
	 */
	_failure: function(data) {
		return (data && data.stacktrace ? true : false);
	},

	/**
	 * Invoked when the tour ends or the user closes the tour.
	 */
	_end: function() {
		// remove action tour from available tours
		if (WCF.inArray(this._activeTourID, this.availableManualTours)) {
			this.availableManualTours.splice(this.availableManualTours.indexOf(this._activeTourID), 1);
		}

		// send request
		this._activeTourID = null;
		this._proxy.setOption('data', {
			className: 'wcf\\data\\tour\\TourAction',
			actionName: 'endTour'
		});
		this._proxy.sendRequest();
	},

	/**
	 * Invoked when the specified target element doesn't exist on the page.
	 */
	_error: function() {
		console.log('[WCF.Tour]: An error occurred while showing the tour with ID ' + this._activeTourID + '.');

		// wait for hopscotch to end the tour
		setTimeout($.proxy(function() {
			if (hopscotch.getCurrStepNum() === 0) { // this was the last step
				this._end();
			}
		}, this), 100);
	}
};
