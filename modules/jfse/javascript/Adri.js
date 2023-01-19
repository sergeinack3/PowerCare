/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Adri = {
    /**
     * Update patient's insurance information from the Adri service
     *
     * @param {int} patient_id
     * @return {Promise<void>}
     */
    updatePatient: async(patient_id) => {
        const data = await Jfse.requestJson('adri/patient/update', {patient_id: patient_id}, {});

        VitalCard.updatePatientForm(data)
    },

    getInvoiceData: async(invoice_id) => {
        const response = await Jfse.requestJson('adri/invoice', {invoice_id: invoice_id}, {});

        if (response.success) {
            Invoicing.reload(Invoicing.getConsultationId(invoice_id), invoice_id);
        } else {
            if (response.messages) {
                Jfse.displayMessagesModal(response.messages);
            } else {
                Jfse.displayErrorMessageModal(response.error);
            }
        }
    }
};
