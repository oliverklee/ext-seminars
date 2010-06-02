Ext.ns(
	'TYPO3.Backend.Seminars',
	'TYPO3.Backend.Seminars.Default',
	'TYPO3.Backend.Seminars.Events',
	'TYPO3.Backend.Seminars.Registrations',
	'TYPO3.Backend.Seminars.Speakers',
	'TYPO3.Backend.Seminars.Organizers'
);

TYPO3.Backend.Seminars.Events.Menu = {
	id: 'typo3-backend-seminars-events-menu',
	items: [{
		id: 'typo3-backend-seminars-events-menu-hide',
		iconCls: 'hide',
		text: TYPO3.lang.hide,
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.HideButton.hidden,
		listener: {
			'click': {
				fn: function() {
					// hide
				},
			}
		},
	}, {
		id: 'typo3-backend-seminars-events-menu-unhide',
		iconCls: 'unhide',
		text: TYPO3.lang.unHide,
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.UnhideButton.hidden,
		listener: {
			'click': {
				fn: function() {
					// unhide
				},
			}
		},
	}, {
		id: 'typo3-backend-seminars-events-menu-confirm',
		iconCls: 'confirm',
		text: TYPO3.lang.eventlist_button_confirm,
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.ConfirmButton.hidden,
	}, {
		id: 'typo3-backend-seminars-events-menu-cancel',
		iconCls: 'cancel',
		text: TYPO3.lang.eventlist_button_cancel,
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.CancelButton.hidden,
		listeners: {
			'click': {
				fn: function() {
					Ext.Msg.alert(window.location);
				}
			},
		},
	}, {
		xtype: 'menuseparator',
	}, {
		id: 'typo3-backend-seminars-events-menu-csv',
		iconCls: 'csv',
		text: TYPO3.lang['labels.csv'],
		listeners: {
			'click': {
				fn: function() {
					var uid = Ext.getCmp('typo3-backend-seminars-events-gridpanel').
						getStore().getAt(TYPO3.Backend.Seminars.Events.rowIndex).
						get('uid');
					var url = TYPO3.settings.Backend.Seminars.URL.csv +
						'?id=' + TYPO3.settings.PID +
						'&tx_seminars_pi2[table]=tx_seminars_attendances' +
						'&tx_seminars_pi2[eventUid]=' + uid;
					window.location = url;
				}
			}
		},
	}, {
		xtype: 'menuseparator',
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.EditButton.hidden,
	}, {
		id: 'typo3-backend-seminars-events-menu-edit',
		iconCls: 'edit',
		text: TYPO3.lang.edit,
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.EditButton.hidden,
		listeners: {
			'click': {
				fn: function() {
					var uid = Ext.getCmp('typo3-backend-seminars-events-gridpanel').
						getStore().getAt(TYPO3.Backend.Seminars.Events.rowIndex).
						get('uid');
					var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
						'?returnUrl=' + encodeURIComponent(window.location) +
						'&edit[tx_seminars_seminars][' + uid + ']=edit';
					window.location = url;
				}
			}
		},
	}, {
		xtype: 'menuseparator',
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.DeleteButton.hidden,
	}, {
		id: 'typo3-backend-seminars-events-menu-delete',
		iconCls: 'delete',
		text: TYPO3.lang["delete"],
		hidden: TYPO3.settings.Backend.Seminars.Events.Menu.DeleteButton.hidden,
		listeners: {
			'click': {
				fn: function() {
					// @todo We need a confirmation message here.
					Ext.Msg.confirm(
						'Title',
						'Message',
						function(button) {
							if (button == 'yes') {
								var uid = Ext.getCmp('typo3-backend-seminars-events-gridpanel').
									getStore().getAt(TYPO3.Backend.Seminars.Events.rowIndex).
									get('uid');
								var cmd = 'cmd[tx_seminars_seminars][' + uid + '][delete]';
								var connection = new Ext.data.Connection();
								connection.request({
									url: TYPO3.settings.Backend.Seminars.URL.tce_db,
									params: {
										cmd: 1,
										'prErr': 1,
										'uPT': 0,
									},
								});
							}
						}
					);
				}
			}
		},
	}]
};

TYPO3.Backend.Seminars.IconRenderer = function(value, metaData, record, rowIndex, colIndex, store) {
	metaData.css = value;
	metaData.attr = ' title="id=' + record.get('uid') + '";'

	return '';
};

TYPO3.Backend.Seminars.Events.StatusRenderer = function(value, metaData, record, rowIndex, colIndex, store) {
	switch (value) {
		case 1:
			metaData.css = 'cancel';
			value = TYPO3.lang['eventlist_status_canceled'];
			break;
		case 2:
			metaData.css = 'confirm';
			value = TYPO3.lang['eventlist_status_confirmed'];
			break;
		default:
			// fall-through is intended
		case 0:
			// @todo invent a locallang label for the status "planned"
			value = 'planned';
			break;
	}

	return value;
}

TYPO3.Backend.Seminars.Events.GridPanel = {
	id: 'typo3-backend-seminars-events-gridpanel',
	xtype: 'grid',
	region: 'center',
	stripeRows: true,
	loadMask: true,
	store: new Ext.data.JsonStore({
		id: 'typo3-backend-seminars-events-store',
		url: TYPO3.settings.Backend.Seminars.URL.ajax + 'Seminars::getEvents',
		root: 'rows',
		idProperty: 'uid',
		fields: [
		    {name: 'iconCls'},
		    {name: 'uid'},
		    {name: 'title'},
		    {name: 'status'},
		]
	}),
	columns: [
	      {dataIndex: 'iconCls', renderer: TYPO3.Backend.Seminars.IconRenderer, width: 30},
	      {header: '#', dataIndex: 'uid'},
          {header: TYPO3.lang['eventlist.title'], dataIndex: 'title'},
          {header: TYPO3.lang['eventlist_status'], dataIndex: 'status', renderer: TYPO3.Backend.Seminars.Events.StatusRenderer},
	],
	tbar: {
		items: [{
			iconCls: 'new',
			text: TYPO3.lang.newRecordGeneral,
			hidden: false,
			listeners: {
				'click': {
					fn: function() {
						var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
							'?returnUrl=' + encodeURIComponent(window.location) +
							'&edit[tx_seminars_seminars][' +
							TYPO3.settings.PID + ']=new';
						window.location = url;
					}
				}
			},
		}, {
			iconCls: 'csv',
			text: TYPO3.lang['labels.csv'],
			listeners: {
				'click': {
					fn: function() {
						var url = TYPO3.settings.Backend.Seminars.URL.csv +
							'?id=' + TYPO3.settings.PID +
							'&tx_seminars_pi2[table]=tx_seminars_seminars' +
							'&tx_seminars_pi2[pid]=' + TYPO3.settings.PID;
						window.location = url;
					}
				}
			},
		}],
	},
	bbar: new Ext.PagingToolbar({
		pageSize: 50,
		store: Ext.StoreMgr.get('typo3-backend-seminars-events-store'),
	}),
	listeners: {
		'rowcontextmenu': {
			fn: function(grid, rowIndex, event) {
				TYPO3.Backend.Seminars.Events.rowIndex = rowIndex;
				var menu = Ext.getCmp('typo3-backend-seminars-events-menu');
				if (!menu) {
				    menu = new Ext.menu.Menu(TYPO3.Backend.Seminars.Events.Menu);
				}
				var row = grid.getStore().getAt(rowIndex);
			    if (row.get('hidden')) {
			    	Ext.getCmp('typo3-backend-seminars-events-menu-unhide').show();
			    	Ext.getCmp('typo3-backend-seminars-events-menu-hide').hide();
			    } else {
			    	Ext.getCmp('typo3-backend-seminars-events-menu-hide').show();
			    	Ext.getCmp('typo3-backend-seminars-events-menu-unhide').hide();
			    }
			    if (row.get('status') > 0) {
			    	Ext.getCmp('typo3-backend-seminars-events-menu-confirm').hide();
			    	Ext.getCmp('typo3-backend-seminars-events-menu-cancel').hide();
			    } else {
			    	Ext.getCmp('typo3-backend-seminars-events-menu-confirm').show();
			    	Ext.getCmp('typo3-backend-seminars-events-menu-cancel').show();
			    }
			    menu.showAt(event.getXY());
			    event.stopEvent();
			}
		},
		'afterrender': {
			fn: function() {
				Ext.StoreMgr.get('typo3-backend-seminars-events-store').load();
			}
		},
	}
};

TYPO3.Backend.Seminars.Events.TabPanel = {
	iconCls: 'typo3-backend-seminars-events-tabpanel-icon',
	id: 'typo3-backend-seminars-events-tabpanel',
	title: TYPO3.lang.subModuleTitle_events,
	layout: 'border',
	hidden: TYPO3.settings.Backend.Seminars.Events.TabPanel.hidden,
	items: [TYPO3.Backend.Seminars.Events.GridPanel],
};

TYPO3.Backend.Seminars.Registrations.Menu = {
	id: 'typo3-backend-seminars-registrations-menu',
	items: [{
		iconCls: 'edit',
		text: TYPO3.lang.edit,
		listeners: {
			'click': {
				fn: function() {
					var uid = Ext.getCmp('typo3-backend-seminars-registrations-gridpanel').
						getStore().getAt(TYPO3.Backend.Seminars.Registrations.rowIndex).
						get('uid');
					var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
						'?returnUrl=' + encodeURIComponent(window.location) +
						'&edit[tx_seminars_attendances][' + uid + ']=edit';
					window.location = url;
				}
			}
		},
	}, {
		xtype: 'menuseparator',
	}, {
		iconCls: 'delete',
		text: TYPO3.lang["delete"],
		listeners: {
			'click': {
				fn: function() {
					// @todo We need a confirmation message here.
					Ext.Msg.confirm(
						'Title',
						'Message',
						function(button) {
							if (button == 'yes') {
								var uid = Ext.getCmp('typo3-backend-seminars-registrations-gridpanel').
									getStore().getAt(TYPO3.Backend.Seminars.Registrations.rowIndex).
									get('uid');
								var cmd = 'cmd[tx_seminars_attendances][' + uid + '][delete]';
								var connection = new Ext.data.Connection();
								connection.request({
									url: TYPO3.settings.Backend.Seminars.URL.tce_db,
									params: {
										cmd: 1,
										'prErr': 1,
										'uPT': 0,
									},
								});
							}
						}
					);
				}
			}
		},
	}],
};

TYPO3.Backend.Seminars.Registrations.GridPanel = {
	id: 'typo3-backend-seminars-registrations-gridpanel',
	xtype: 'grid',
	region: 'center',
	stripeRows: true,
	loadMask: true,
	store: new Ext.data.JsonStore({
		id: 'typo3-backend-seminars-registrations-store',
		url: TYPO3.settings.Backend.Seminars.URL.ajax + 'Seminars::getRegistrations',
		root: 'rows',
		idProperty: 'uid',
		fields: [
		    {name: 'uid'},
		    {name: 'title'},
		]
	}),
	columns: [
		{header: TYPO3.lang.uid, dataIndex: 'uid'},
		{header: TYPO3.lang.title, dataIndex: 'title'},
	],
	tbar: {
		items: [{
			iconCls: 'new',
			text: TYPO3.lang.newRecordGeneral,
			hidden: false,
			listeners: {
				'click': {
					fn: function() {
						var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
							'?returnUrl=' + encodeURIComponent(window.location) +
							'&edit[tx_seminars_attendances][' +
							TYPO3.settings.PID + ']=new';
						window.location = url;
					}
				}
			},
		}, {
			iconCls: 'csv',
			text: TYPO3.lang['labels.csv'],
			listeners: {
				'click': {
					fn: function() {
						var url = TYPO3.settings.Backend.Seminars.URL.csv +
							'?id=' + TYPO3.settings.PID +
							'&tx_seminars_pi2[table]=tx_seminars_attendances' +
							'&tx_seminars_pi2[pid]=' + TYPO3.settings.PID;
						window.location = url;
					}
				}
			},
		}],
	},
	bbar: new Ext.PagingToolbar({
		pageSize: 50,
		store: Ext.StoreMgr.get('typo3-backend-seminars-registrations-store'),
	}),
	listeners: {
		'rowcontextmenu': {
			fn: function(grid, rowIndex, event) {
				TYPO3.Backend.Seminars.Registrations.rowIndex = rowIndex;
				var menu = Ext.getCmp('typo3-backend-seminars-registrations-menu');
				if (!menu) {
				    menu = new Ext.menu.Menu(TYPO3.Backend.Seminars.Registrations.Menu);
				}
			    menu.showAt(event.getXY());
			    event.stopEvent();
			}
		},
		'afterrender': {
			fn: function() {
				Ext.StoreMgr.get('typo3-backend-seminars-registrations-store').load();
			}
		},
	}
};

TYPO3.Backend.Seminars.Registrations.TabPanel = {
	iconCls: 'typo3-backend-seminars-registrations-tabpanel-icon',
	id: 'typo3-backend-seminars-registrations-tabpanel',
	title: TYPO3.lang.subModuleTitle_registrations,
	hidden: TYPO3.settings.Backend.Seminars.Registrations.TabPanel.hidden,
	layout: 'border',
	items: [TYPO3.Backend.Seminars.Registrations.GridPanel],
};

TYPO3.Backend.Seminars.Speakers.Menu = {
	id: 'typo3-backend-seminars-speakers-menu',
	items: [{
		iconCls: 'edit',
		text: TYPO3.lang.edit,
		listeners: {
			'click': {
				fn: function() {
					var uid = Ext.getCmp('typo3-backend-seminars-speakers-gridpanel').
						getStore().getAt(TYPO3.Backend.Seminars.Speakers.rowIndex).
						get('uid');
					var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
						'?returnUrl=' + encodeURIComponent(window.location) +
						'&edit[tx_seminars_speakers][' + uid + ']=edit';
					window.location = url;
				}
			}
		},
	}, {
		xtype: 'menuseparator',
	}, {
		iconCls: 'delete',
		text: TYPO3.lang["delete"],
		listeners: {
			'click': {
				fn: function() {
					// @todo We need a confirmation message here.
					Ext.Msg.confirm(
						'Title',
						'Message',
						function(button) {
							if (button == 'yes') {
								var uid = Ext.getCmp('typo3-backend-seminars-speakers-gridpanel').
									getStore().getAt(TYPO3.Backend.Seminars.Speakers.rowIndex).
									get('uid');
								var cmd = 'cmd[tx_seminars_speakers][' + uid + '][delete]';
								var connection = new Ext.data.Connection();
								connection.request({
									url: TYPO3.settings.Backend.Seminars.URL.tce_db,
									params: {
										cmd: 1,
										'prErr': 1,
										'uPT': 0,
									},
								});
							}
						}
					);
				}
			}
		},
	}],
};

TYPO3.Backend.Seminars.Speakers.GridPanel = {
	id: 'typo3-backend-seminars-speakers-gridpanel',
	xtype: 'grid',
	region: 'center',
	stripeRows: true,
	loadMask: true,
	store: new Ext.data.JsonStore({
		id: 'typo3-backend-seminars-speakers-store',
		url: TYPO3.settings.Backend.Seminars.URL.ajax + 'Seminars::getSpeakers',
		root: 'rows',
		idProperty: 'uid',
		fields: [
			{name: 'uid'},
			{name: 'title'},
		]
	}),
	columns: [
		{header: TYPO3.lang.uid, dataIndex: 'uid'},
		{header: TYPO3.lang.title, dataIndex: 'title'},
	],
	tbar: {
		items:[{
			iconCls: 'new',
			text: TYPO3.lang.newRecordGeneral,
			listeners: {
				'click': {
					fn: function() {
						var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
							'?returnUrl=' + encodeURIComponent(window.location) +
							'&edit[tx_seminars_speakers][' +
							TYPO3.settings.PID + ']=new';
						window.location = url;
					}
				}
			},
		}],
	},
	bbar: new Ext.PagingToolbar({
		pageSize: 50,
		store: Ext.StoreMgr.get('typo3-backend-seminars-speakers-store'),
	}),
	listeners: {
		'rowcontextmenu': {
			fn: function(grid, rowIndex, event) {
				TYPO3.Backend.Seminars.Speakers.rowIndex = rowIndex;
				var menu = Ext.getCmp('typo3-backend-seminars-speakers-menu');
				if (!menu) {
				    menu = new Ext.menu.Menu(TYPO3.Backend.Seminars.Speakers.Menu);
				}
			    menu.showAt(event.getXY());
			    event.stopEvent();
			}
		},
		'afterrender': {
			fn: function() {
				Ext.StoreMgr.get('typo3-backend-seminars-speakers-store').load();
			}
		},
	}
};

TYPO3.Backend.Seminars.Speakers.TabPanel = {
	iconCls: 'typo3-backend-seminars-speakers-tabpanel-icon',
	id: 'typo3-backend-seminars-speakers-tabpanel',
	title: TYPO3.lang.subModuleTitle_speakers,
	hidden: TYPO3.settings.Backend.Seminars.Speakers.TabPanel.hidden,
	layout: 'border',
	items: [TYPO3.Backend.Seminars.Speakers.GridPanel],
};

TYPO3.Backend.Seminars.Organizers.Menu = {
	id: 'typo3-backend-seminars-organizers-menu',
	items: [{
		iconCls: 'edit',
		text: TYPO3.lang.edit,
		listeners: {
			'click': {
				fn: function() {
					var uid = Ext.getCmp('typo3-backend-seminars-organizers-gridpanel').
						getStore().getAt(TYPO3.Backend.Seminars.Organizers.rowIndex).
						get('uid');
					var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
						'?returnUrl=' + encodeURIComponent(window.location) +
						'&edit[tx_seminars_organizers][' + uid + ']=edit';
					window.location = url;
				}
			}
		},
	}, {
		xtype: 'menuseparator',
	}, {
		iconCls: 'delete',
		text: TYPO3.lang["delete"],
		listeners: {
			'click': {
				fn: function() {
					// @todo We need a confirmation message here.
					Ext.Msg.confirm(
						'Title',
						'Message',
						function(button) {
							if (button == 'yes') {
								var uid = Ext.getCmp('typo3-backend-seminars-organizers-gridpanel').
									getStore().getAt(TYPO3.Backend.Seminars.Organizers.rowIndex).
									get('uid');
								var cmd = 'cmd[tx_seminars_organizers][' + uid + '][delete]';
								var connection = new Ext.data.Connection();
								connection.request({
									url: TYPO3.settings.Backend.Seminars.URL.tce_db,
									params: {
										cmd: 1,
										'prErr': 1,
										'uPT': 0,
									},
								});
							}
						}
					);
				}
			}
		},
	}],
};

TYPO3.Backend.Seminars.Organizers.GridPanel = {
	id: 'typo3-backend-seminars-organizers-gridpanel',
	xtype: 'grid',
	region: 'center',
	stripeRows: true,
	loadMask: true,
	store: new Ext.data.JsonStore({
		id: 'typo3-backend-seminars-organizers-store',
		url: TYPO3.settings.Backend.Seminars.URL.ajax + 'Seminars::getOrganizers',
		root: 'rows',
		idProperty: 'uid',
		fields: [
			{name: 'uid'},
			{name: 'title'},
		]
	}),
	columns: [
		{header: TYPO3.lang.uid, dataIndex: 'uid'},
		{header: TYPO3.lang.title, dataIndex: 'title'},
	],
	tbar: {
		items:[{
			iconCls: 'new',
			text: TYPO3.lang.newRecordGeneral,
			listeners: {
				'click': {
					fn: function() {
						var url = TYPO3.settings.Backend.Seminars.URL.alt_doc +
							'?returnUrl=' + encodeURIComponent(window.location) +
							'&edit[tx_seminars_organizers][' +
							TYPO3.settings.PID + ']=new';
						window.location = url;
					}
				}
			},
		}],
	},
	bbar: new Ext.PagingToolbar({
		pageSize: 50,
		store: Ext.StoreMgr.get('typo3-backend-seminars-organizers-store'),
	}),
	listeners: {
		'rowcontextmenu': {
			fn: function(grid, rowIndex, event) {
				TYPO3.Backend.Seminars.Organizers.rowIndex = rowIndex;
				var menu = Ext.getCmp('typo3-backend-seminars-organizers-menu');
				if (!menu) {
				    menu = new Ext.menu.Menu(TYPO3.Backend.Seminars.Organizers.Menu);
				}
			    menu.showAt(event.getXY());
			    event.stopEvent();
			}
		},
		'afterrender': {
			fn: function() {
				Ext.StoreMgr.get('typo3-backend-seminars-organizers-store').load();
			}
		},
	}
};

TYPO3.Backend.Seminars.Organizers.TabPanel = {
	iconCls: 'typo3-backend-seminars-organizers-tabpanel-icon',
	id: 'typo3-backend-seminars-organizers-tabpanel',
	title: TYPO3.lang.subModuleTitle_organizers,
	layout: 'border',
	items: [TYPO3.Backend.Seminars.Organizers.GridPanel],
};

/**
 * The default tab panel of the ExtJS back-end module. This is shown if no other
 * tab panel is active.
 */
TYPO3.Backend.Seminars.Default.TabPanel = {
	iconCls: 'typo3-backend-seminars-default-tabpanel-icon',
	id: 'typo3-backend-seminars-default-tabpanel',
	title: 'Default',
};

TYPO3.Backend.Seminars.TabPanel = {
	id: 'typo3-backend-seminars-tabpanel',
	region: 'center',
	xtype: 'tabpanel',
	activeTab: 0,
	items: [TYPO3.Backend.Seminars.Default.TabPanel]
};

Ext.onReady(function(){
	new Ext.TabPanel(TYPO3.Backend.Seminars.TabPanel);

	if (!TYPO3.settings.Backend.Seminars.Events.TabPanel.hidden) {
		Ext.getCmp('typo3-backend-seminars-tabpanel').add(
			TYPO3.Backend.Seminars.Events.TabPanel
		);
	}

	if (!TYPO3.settings.Backend.Seminars.Registrations.TabPanel.hidden) {
		Ext.getCmp('typo3-backend-seminars-tabpanel').add(
			TYPO3.Backend.Seminars.Registrations.TabPanel
		);
	}

	if (!TYPO3.settings.Backend.Seminars.Speakers.TabPanel.hidden) {
		Ext.getCmp('typo3-backend-seminars-tabpanel').add(
			TYPO3.Backend.Seminars.Speakers.TabPanel
		);
	}

	if (!TYPO3.settings.Backend.Seminars.Organizers.TabPanel.hidden) {
		Ext.getCmp('typo3-backend-seminars-tabpanel').add(
			TYPO3.Backend.Seminars.Organizers.TabPanel
		);
	}

	// Removes the default tab panel if there's at least one other tab panel
	// active.
	if (Ext.getCmp('typo3-backend-seminars-tabpanel').items.getCount() > 1) {
		Ext.getCmp('typo3-backend-seminars-tabpanel').remove(
			Ext.getCmp('typo3-backend-seminars-default-tabpanel')
		);
	}

	TYPO3.Backend.Seminars.viewport = new Ext.Viewport({
		layout: 'border',
		items: Ext.getCmp('typo3-backend-seminars-tabpanel'),
	});
});