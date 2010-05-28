Ext.ns(
	'TYPO3.Backend.Seminars',
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
	}, {
		id: 'typo3-backend-seminars-events-menu-cancel',
		iconCls: 'cancel',
		text: TYPO3.lang.eventlist_button_cancel,
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
	}, {
		id: 'typo3-backend-seminars-events-menu-edit',
		iconCls: 'edit',
		text: TYPO3.lang.edit,
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
	}, {
		id: 'typo3-backend-seminars-events-menu-delete',
		iconCls: 'delete',
		text: TYPO3.lang["delete"],
		listeners: {
			'click': {
				fn: function() {
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
			// TODO: invent a locallang label for the status "planned"
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
		url: TYPO3.settings.Backend.Seminars.Events.Store.autoLoadURL,
		autoLoad: true,
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
		}
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
					Ext.Msg.confirm(
						'Title',
						'Message',
						function(button) {
							if (button == 'yes') {
								var uid = Ext.getCmp('typo3-backend-seminars-events-gridpanel').
									getStore().getAt(TYPO3.Backend.Seminars.Events.rowIndex).
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
		url: TYPO3.settings.Backend.Seminars.Registrations.Store.autoLoadURL,
		autoLoad: true,
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
};

TYPO3.Backend.Seminars.Registrations.TabPanel = {
	iconCls: 'typo3-backend-seminars-registrations-tabpanel-icon',
	id: 'typo3-backend-seminars-registrations-tabpanel',
	title: TYPO3.lang.subModuleTitle_registrations,
	hidden: TYPO3.settings.Backend.Seminars.Registrations.TabPanel.hidden,
	layout: 'border',
	items: [TYPO3.Backend.Seminars.Registrations.GridPanel],
};

TYPO3.Backend.Seminars.Speakers.TabPanel = {
	iconCls: 'typo3-backend-seminars-speakers-tabpanel-icon',
	id: 'typo3-backend-seminars-speakers-tabpanel',
	title: TYPO3.lang.subModuleTitle_speakers,
	hidden: TYPO3.settings.Backend.Seminars.Speakers.TabPanel.hidden,
	layout: 'border',
	items: [{
		id: 'typo3-backend-seminars-speakers-gridpanel',
		xtype: 'grid',
		region: 'center',
		store: new Ext.data.JsonStore({
			id: 'typo3-backend-seminars-speakers-store',
			url: TYPO3.settings.Backend.Seminars.Speakers.Store.autoLoadURL,
			autoLoad: true,
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
		stripeRows: true,
		loadMask: true,
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
	}],
};

TYPO3.Backend.Seminars.Organizers.TabPanel = {
	iconCls: 'typo3-backend-seminars-organizers-tabpanel-icon',
	id: 'typo3-backend-seminars-organizers-tabpanel',
	title: TYPO3.lang.subModuleTitle_organizers,
	hidden: TYPO3.settings.Backend.Seminars.Organizers.TabPanel.hidden,
	layout: 'border',
	items: [{
		id: 'typo3-backend-seminars-organizers-gridpanel',
		xtype: 'grid',
		region: 'center',
		store: new Ext.data.JsonStore({
			id: 'typo3-backend-seminars-organizers-store',
			url: TYPO3.settings.Backend.Seminars.Organizers.Store.autoLoadURL,
			autoLoad: true,
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
		stripeRows: true,
		loadMask: true,
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
	}],
};

TYPO3.Backend.Seminars.TabPanel = {
	region: 'center',
	xtype: 'tabpanel',
	activeTab: 0,
	items: [
	    TYPO3.Backend.Seminars.Events.TabPanel,
	    TYPO3.Backend.Seminars.Registrations.TabPanel,
	    TYPO3.Backend.Seminars.Speakers.TabPanel,
	    TYPO3.Backend.Seminars.Organizers.TabPanel
	]
};

Ext.onReady(function(){
	TYPO3.Backend.Seminars.viewport = new Ext.Viewport({
		layout: 'border',
		items: TYPO3.Backend.Seminars.TabPanel,
	});
});