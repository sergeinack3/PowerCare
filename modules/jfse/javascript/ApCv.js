/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ApCv = {
    form: null,
    scan_loading_container: null,
    scan_message_container: null,
    qrcode_container: null,
    action : null,
    patient_id : null,
    invoice_context : null,
    consultation_id : null,
    invoice_id : null,

    initializeView: function (form = null) {
        if (!form) {
            form = getForm('Jfse-QrCode-scanner');
        }

        this.form = form;
        if (this.form.elements['jfse-apcv-qrcode']) {
            this.form.elements['jfse-apcv-qrcode'].observe('keypress', this.getApCvContextWithQrCode.curry());
        }

        if ($('jfse-vital-qrcode_scan_loading_container')) {
            this.scan_loading_container = $('jfse-vital-qrcode_scan_loading_container');
        }

        if ($('jfse-vital-qrcode_scan_message_container')) {
            this.scan_message_container = $('jfse-vital-qrcode_scan_message_container');
        }

        if ($('jfse-vital-qrcode_container')) {
            this.qrcode_container = $('jfse-vital-qrcode_container');
        }
    },

    getApCvContextWithNfc: async function (action, patient_id, invoice_context, consultation_id) {
        let params = {
            mode: 1,
        };

        if (!ApCv.invoice_context) {
            params.user_id = ApCv.getOxCabinetSelectUser();
        } else if (ApCv.consultation_id) {
            params.consultation_id = ApCv.consultation_id;
        }

        let data = await Jfse.requestJson('vitalCard/apCv/get', params, {});

        if (invoice_context && consultation_id) {
            data.consultation_id = consultation_id;
        }

        VitalCard.handleReadData(data, action, patient_id);
    },

    getApCvContextFromCache: async function (action, patient_id, invoice_context, consultation_id) {
        let params = {};

        if (consultation_id) {
            params.consultation_id = consultation_id;
        } else if (!invoice_context) {
            params.user_id = ApCv.getOxCabinetSelectUser();
        }

        let data = await Jfse.requestJson('vitalCard/apCv/getFromCache', params, {});

        if (invoice_context && consultation_id) {
            data.consultation_id = consultation_id;
        }

        if (data.error) {
            Jfse.displayErrorMessageModal(data.error);
        } else {
            VitalCard.handleReadData(data, action, patient_id);
        }
    },

    scanQrCode: function (action, patient_id, invoice_context, consultation_id) {
        if (this.qrcode_container && this.scan_loading_container && this.scan_message_container) {
            this.action = action;
            this.patient_id = patient_id;
            this.invoice_context = invoice_context;
            this.consultation_id = consultation_id;
            this.scan_loading_container.hide();
            this.scan_message_container.show();

            Modal.open(this.qrcode_container, {
                showClose: true,
                title: $T('CApCvContext-action-read_with_qr_code')
            });

            $V(this.form.elements['jfse-apcv-qrcode'], '');
            this.form.elements['jfse-apcv-qrcode'].focus();
        } else {
            console.error('QrCode Scanner elements not found');
        }
    },

    getApCvContextWithQrCode: async function (event) {
        if (event.keyCode === 13) {
            Control.Modal.close();

            if (ApCv.action === 'renew_context_apcv_invoice') {
                const response = await Jfse.requestJson('vitalCard/apCv/renewApCvContextForInvoice', {invoice_id: ApCv.invoice_id, mode: 2, context: $V(this)}, {});

                if (response.result) {
                    Invoicing.validateInvoice(ApCv.invoice_id);
                } else {
                    Reglement.reload();
                }
            } else if (ApCv.action === 'switch_invoice_to_apcv') {
                await Invoicing.switchToApCv(ApCv.invoice_id, 1, $V(this));
            } else {
                let params = {
                    mode: 2,
                    context: $V(this)
                };

                if (!ApCv.invoice_context) {
                    params.user_id = ApCv.getOxCabinetSelectUser();
                } else if (ApCv.consultation_id) {
                    params.consultation_id = ApCv.consultation_id;
                }

                let data = await Jfse.requestJson('vitalCard/apCv/get', params, {});

                if (ApCv.invoice_context && ApCv.consultation_id) {
                    data.consultation_id = ApCv.consultation_id;
                }

                VitalCard.handleReadData(data, ApCv.action, ApCv.patient_id);
            }

            ApCv.action = null;
            ApCv.patient_id = null;
            ApCv.invoice_context = null;
            ApCv.consultation_id = null;
            ApCv.invoice_id = null;
        } else if (ApCv.scan_message_container && ApCv.scan_message_container.visible()) {
            ApCv.scan_message_container.hide();
            ApCv.scan_loading_container.show();
        }
    },

    renewApCvContextWithNfc: async function (invoice_id) {
        const response = await Jfse.requestJson('vitalCard/apCv/renewApCvContextForInvoice', {invoice_id: invoice_id, mode: 1}, {});
        Control.Modal.close();

        if (response.result) {
            Invoicing.validateInvoice(invoice_id);
        } else {
            Reglement.reload();
        }
    },

    switchInvoiceToApCvWithNfc: async function (invoice_id) {
        Invoicing.switchToApCv(invoice_id, 1);
    },

    renewApCvContextWithQrCode: async function (invoice_id) {
        if (this.qrcode_container && this.scan_loading_container && this.scan_message_container) {
            this.action = 'renew_context_apcv_invoice';
            this.invoice_id = invoice_id;
            this.scan_loading_container.hide();
            this.scan_message_container.show();
            Control.Modal.close();

            Modal.open(this.qrcode_container, {
                showClose: true,
                title: $T('CApCvContext-action-read_with_qr_code')
            });

            $V(this.form.elements['jfse-apcv-qrcode'], '');
            this.form.elements['jfse-apcv-qrcode'].focus();
        } else {
            console.error('QrCode Scanner elements not found');
        }
    },

    switchInvoiceToApCvWithQrCode: async function (invoice_id) {
        if (this.qrcode_container && this.scan_loading_container && this.scan_message_container) {
            this.action = 'switch_invoice_to_apcv';
            this.invoice_id = invoice_id;
            this.scan_loading_container.hide();
            this.scan_message_container.show();

            Modal.open(this.qrcode_container, {
                showClose: true,
                title: $T('CApCvContext-action-read_with_qr_code')
            });

            $V(this.form.elements['jfse-apcv-qrcode'], '');
            this.form.elements['jfse-apcv-qrcode'].focus();
        } else {
            console.error('QrCode Scanner elements not found');
        }
    },

    getOxCabinetSelectUser: function () {
        let element = $('filtreTdb_praticien_id');
        let user_id = null;

        if (element) {
            user_id = $V(element);
        }

        return user_id;
    },
};
