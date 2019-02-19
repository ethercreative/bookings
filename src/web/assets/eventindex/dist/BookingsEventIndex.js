/** global: Craft */
/** global: Garnish */
/**
 * Event index class
 */
Craft.Bookings.EventIndex = Craft.BaseElementIndex.extend({
	editableEventTypes: null,
	$newEventBtnEventType: null,
	$newEventBtn: null,

	init: function (elementType, $container, settings) {
		this.on('selectSource', $.proxy(this, 'updateButton'));
		this.on('selectSite', $.proxy(this, 'updateButton'));
		this.base(elementType, $container, settings);
	},

	afterInit: function () {
		// Find which of the visible event types the user has permission to create new events in
		this.editableEventTypes = [];

		for (let i = 0; i < Craft.Bookings.editableEventTypes.length; i++) {
			const eventType = Craft.Bookings.editableEventTypes[i];

			if (this.getSourceByKey('eventType:' + eventType.id))
				this.editableEventTypes.push(eventType);
		}

		this.base();
	},

	getDefaultSourceKey: function () {
		// Did they request a specific event eventType in the URL?
		if (this.settings.context === 'index' && typeof defaultEventTypeHandle !== 'undefined') {
			for (let i = 0; i < this.$sources.length; i++) {
				const $source = $(this.$sources[i]);

				if ($source.data('handle') === defaultEventTypeHandle)
					return $source.data('key');
			}
		}

		return this.base();
	},

	updateButton: function () {
		if (!this.$source) {
			return;
		}

		// Get the handle of the selected source
		const selectedSourceHandle = this.$source.data('handle');

		let i, href, label;

		// Update the New Event button
		// ---------------------------------------------------------------------

		if (this.editableEventTypes.length) {
			// Remove the old button, if there is one
			if (this.$newEventBtnEventType)
				this.$newEventBtnEventType.remove();

			// Determine if they are viewing a eventType that they have permission to create events in
			let selectedEventType;

			if (selectedSourceHandle) {
				for (i = 0; i < this.editableEventTypes.length; i++) {
					if (this.editableEventTypes[i].handle === selectedSourceHandle) {
						selectedEventType = this.editableEventTypes[i];
						break;
					}
				}
			}

			this.$newEventBtnEventType = $('<div class="btngroup submit"/>');
			let $menuBtn;

			// If they are, show a primary "New event" button, and a dropdown of the other eventTypes (if any).
			// Otherwise only show a menu button
			if (selectedEventType) {
				href = this._getEventTypeTriggerHref(selectedEventType);
				label = (this.settings.context === 'index' ? Craft.t('bookings', 'New event') : Craft.t('bookings', 'New {eventType} event', { eventType: selectedEventType.name }));
				this.$newEventBtn = $('<a class="btn submit add icon" ' + href + '>' + Craft.escapeHtml(label) + '</a>').appendTo(this.$newEventBtnEventType);

				if (this.settings.context !== 'index') {
					this.addListener(this.$newEventBtn, 'click', function (ev) {
						this._openCreateEventModal(ev.currentTarget.getAttribute('data-id'));
					});
				}

				if (this.editableEventTypes.length > 1) {
					$menuBtn = $('<div class="btn submit menubtn"></div>').appendTo(this.$newEventBtnEventType);
				}
			} else {
				this.$newEventBtn = $menuBtn = $('<div class="btn submit add icon menubtn">' + Craft.t('bookings', 'New event') + '</div>').appendTo(this.$newEventBtnEventType);
			}

			if ($menuBtn) {
				let menuHtml = '<div class="menu"><ul>';

				for (i = 0; i < this.editableEventTypes.length; i++) {
					const eventType = this.editableEventTypes[i];

					if (this.settings.context === 'index' || eventType !== selectedEventType) {
						href  = this._getEventTypeTriggerHref(eventType);
						label = (this.settings.context === 'index' ? eventType.name : Craft.t('bookings', 'New {eventType} event', { eventType: eventType.name }));
						menuHtml += '<li><a ' + href + '">' + Craft.escapeHtml(label) + '</a></li>';
					}
				}

				menuHtml += '</ul></div>';

				$(menuHtml).appendTo(this.$newEventBtnEventType);
				const menuBtn = new Garnish.MenuBtn($menuBtn);

				if (this.settings.context !== 'index') {
					menuBtn.on('optionSelect', $.proxy(function (ev) {
						this._openCreateEventModal(ev.option.getAttribute('data-id'));
					}, this));
				}
			}

			this.addButton(this.$newEventBtnEventType);
		}

		// Update the URL if we're on the Categories index
		// ---------------------------------------------------------------------

		if (this.settings.context === 'index' && typeof history !== 'undefined') {
			var uri = 'bookings/events';

			if (selectedSourceHandle) {
				uri += '/' + selectedSourceHandle;
			}

			history.replaceState({}, '', Craft.getUrl(uri));
		}
	},

	_getEventTypeTriggerHref: function (eventType) {
		if (this.settings.context === 'index') {
			let uri = 'bookings/events/' + eventType.handle + '/new';

			if (this.siteId && this.siteId !== Craft.primarySiteId)
				for (let i = 0; i < Craft.sites.length; i++)
					if (Craft.sites[i].id === this.siteId)
						uri += '/' + Craft.sites[i].handle;

			return 'href="' + Craft.getUrl(uri) + '"';
		} else {
			return 'data-id="' + eventType.id + '"';
		}
	},

	_openCreateEventModal: function (eventTypeId) {
		if (this.$newEventBtn.hasClass('loading'))
			return;

		// Find the eventType
		let eventType;

		for (let i = 0; i < this.editableEventTypes.length; i++) {
			if (this.editableEventTypes[i].id === eventTypeId) {
				eventType = this.editableEventTypes[i];
				break;
			}
		}

		if (!eventType)
			return;

		this.$newEventBtn.addClass('inactive');
		const newEventBtnText = this.$newEventBtn.text();
		this.$newEventBtn.text(Craft.t('bookings', 'New {eventType} event', { eventType: eventType.name }));

		Craft.createElementEditor(this.elementType, {
			hudTrigger: this.$newEventBtnEventType,
			elementType: 'ether\\bookings\\elements\\Event',
			siteId: this.siteId,
			attributes: {
				eventTypeId: eventTypeId
			},
			onBeginLoading: $.proxy(function () {
				this.$newEventBtn.addClass('loading');
			}, this),
			onEndLoading: $.proxy(function () {
				this.$newEventBtn.removeClass('loading');
			}, this),
			onHideHud: $.proxy(function () {
				this.$newEventBtn.removeClass('inactive').text(newEventBtnText);
			}, this),
			onSaveElement: $.proxy(function (response) {
				// Make sure the right eventType is selected
				var eventTypeSourceKey = 'eventType:' + eventTypeId;

				if (this.sourceKey !== eventTypeSourceKey) {
					this.selectSourceByKey(eventTypeSourceKey);
				}

				this.selectElementAfterUpdate(response.id);
				this.updateElements();
			}, this)
		});
	}
});

// Register it!
Craft.registerElementIndexClass('ether\\bookings\\elements\\Event', Craft.Bookings.EventIndex);
