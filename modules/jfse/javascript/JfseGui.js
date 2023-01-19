/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

JfseGui = {
    eventListener: null,
    indexTabs: null,

    /**
     * Initialize the control tabs of the index view of the users management
     */
    initializeIndexView: () => {
        Control.Tabs.create('tabs-user-management-index', true, {
            afterChange: (container) => {
                if (container.id == 'users-container') {
                    JfseGui.manageUsers();
                } else if (container.id == 'establishments-container') {
                    JfseGui.manageEstablishments();
                } else {
                    JfseGui.settings();
                }
            }
        });
    },

    /**
     * Manage users
     */
    manageUsers: () => {
        Jfse.displayGui('gui/users/manage', 'users-container');
    },

    /**
     * Manage establishments
     */
    manageEstablishments: () => {
        Jfse.displayGui('gui/establishments/manage', 'establishments-container');
    },

    /**
     * Manage general settings
     */
    settings: () => {
        Jfse.displayGui('gui/settings', 'settings-container');
    },

    /**
     * Initialize the control tabs of the fse index view of user settings and actions
     */
    initializeFseIndexView: function () {
        this.indexTabs = Control.Tabs.create('tabs-jfse-gui-index', true, {
            afterChange: (container) => {
                switch (container.id) {
                    case 'actions':
                        JfseGui.actions();
                        break;
                    case 'invoices':
                        JfseGui.invoiceDashboard();
                        break;
                    case 'refunds':
                        JfseGui.manageNoemieReturns();
                        break;
                    case 'scor':
                        JfseGui.scorDashboard();
                        break;
                    case 'transmission':
                        JfseGui.globalTeletransmission();
                        break;
                    case 'user-settings':
                        JfseGui.settingsUser();
                        break;
                }
            }
        });
    },

    /**
     * Manage user settings
     */
    settingsUser: () => {
        let parameters = {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        };

        Jfse.displayGui('gui/user/settings', 'user-settings', parameters);
    },

    /**
     * View action buttons in gui mode
     */
    actions: () => {
        let parameters = {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        };

        Jfse.displayView('gui/actions', 'actions', parameters)
    },

    /**
     * Invoice dashboard
     */
    invoiceDashboard: () => {
        let parameters = {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        };

        Jfse.displayGui('gui/invoice/dashboard', 'invoices', parameters);
    },

    /**
     * Scor dashboard
     */
    scorDashboard: () => {
        let parameters = {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        };

        Jfse.displayGui('gui/scor/dashboard', 'scor', parameters);
    },

    /**
     * Global teletransmission of invoices
     */
    globalTeletransmission: () => {
        let parameters = {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        };

        Jfse.displayGui('gui/globalTeletransmission', 'transmission', parameters);
    },

    /**
     * Manage noemie returns
     */
    manageNoemieReturns: () => {
        let parameters = {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        };

        Jfse.displayGui('gui/noemie/manageReturns', 'refunds', parameters);
    },

    /**
     * Manage TLA
     */
    manageTLA: () => {
        Jfse.displayGuiModal('gui/tla/manage');
    },

    /**
     * Version of the jfse module
     */
    moduleVersion: () => {
        Jfse.displayGuiModal('gui/version/module');
    },

    /**
     * Version of the sesam-vitale api module
     */
    apiVersion: () => {
        Jfse.displayGuiModal('gui/version/api');
    },

    mbVersion: () => {
        Jfse.displayViewModal('gui/version/mb', 400, null, {}, {showClose: true, title: $T('jfse-gui-Mb version')});
    },

    showExportPayments: () => {
        Jfse.displayViewModal('noemie/index', null, null, {
            jfse_user_id: JfseGui.getSelectJfseUserId()
        }, {
            showClose: true,
            title: $T('NoemiePayments-action-export')
        });
    },

    reloadInvoice: (consultation_id) => {
        Jfse.displayView('gui/invoice/index', 'jfse_invoice', {consultation_id: consultation_id});
    },

    readCpsCard: async(consultation_id) => {
        const response = await Jfse.requestJson('gui/cps/read', {});

        if (response.success) {
            if (response.url) {
                Jfse.displayJfseIframe(response.url);
                JfseGui.eventListener = JfseGui.readCpsMessageEvent.curry();
                window.addEventListener("message", JfseGui.eventListener, false);
            } else {
                Jfse.displaySuccessMessageModal(response.message);
            }
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        }
    },

    readVitalCard: async(consultation_id) => {
        const response = await Jfse.requestJson('gui/vitalCard/read', {consultation_id: consultation_id});

        if (response.success) {
            if (response.url) {
                Jfse.displayJfseIframe(response.url);
                JfseGui.eventListener = JfseGui.readVitalCardMessageEvent.curry(consultation_id);
                let listener = window.addEventListener("message", JfseGui.eventListener, false);
            } else {
                Jfse.displaySuccessMessageModal(response.message);
            }
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        }
    },

    readCpsMessageEvent: (event) => {
        Control.Modal.close();
        window.removeEventListener("message", JfseGui.eventListener, false);
    },

    readVitalCardMessageEvent: async(consultation_id, event) => {
        window.removeEventListener("message", JfseGui.eventListener, false);
        Control.Modal.close();

        let response = await Jfse.requestJson('gui/vitalCard/handleReading', {
            consultation_id: consultation_id,
            data: event.data
        });

        if (response.success) {
            Jfse.notifySuccessMessage(response.message);
        } else if (response.error) {
            Jfse.notifyErrorMessage(response.error);
        }

        Reglement.reload();
    },

    /**
     * View fse for a consultation
     *
     * @param consult_id
     */
    viewInvoice: (invoice_id) => {
        JfseGui.eventListener = JfseGui.viewInvoiceMessageEvent.curry(false);
        window.addEventListener("message", JfseGui.eventListener, false);
        Jfse.displayGuiModal('gui/invoice/view', {invoice_id: invoice_id});
    },

    createInvoice: async(consultation_id, securing_mode) => {
        const response = await Jfse.requestJson('gui/invoice/create', {consultation_id: consultation_id, securing_mode: securing_mode});

        if (response.url) {
            Jfse.displayJfseIframe(response.url);
            JfseGui.eventListener = JfseGui.viewInvoiceMessageEvent.curry(true);
            window.addEventListener("message", JfseGui.eventListener, false);
        } else if (response.error) {
            Jfse.displayErrorMessageModal(response.error);
        }
    },

    deleteInvoice: async(invoice_id, validated) => {
        let route = 'invoicing/invoice/cancel';
        if (validated) {
            route = 'invoicing/invoice/delete';
        }
        let response = await Jfse.requestJson(route, {invoice_id: invoice_id});

        if (response.success) {
            Jfse.notifySuccessMessage('CJfseInvoice-msg-deleted');
            Reglement.reload();
        }
    },

    viewInvoiceMessageEvent: async(creation, event) => {
        let data = JSON.parse(atob(event.data));
        /* There is an event object in the output when the user click on a button other than "Quit" or "Validate" */
        if (data.method.output.event) {
            return;
        }

        window.removeEventListener("message", JfseGui.eventListener, false);
        Control.Modal.close();
        if (!data.method.cancel) {
            let response = Jfse.requestJson('gui/invoice/handleValidation', {
                invoice_id: data.method.parameters.idFacture
            });

            if (response.success) {
                Jfse.notifySuccessMessage(response.message);
            }

            Reglement.reload();
        } else if (creation) {
            Reglement.reload();
        }
    },

    getSelectJfseUserId: function () {
        let jfse_id;

        if ($('jfse_user-selector')) {
            jfse_id = $V($('jfse_user-selector'));
        }

        return jfse_id;
    },
};
