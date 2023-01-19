/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
ProofAmo = {
    form: null,

    initializeView: function (form) {
        this.form = form;
        Jfse.setInputNotNull(this.form.elements['nature']);
        this.selectProofAmoType(this.form.elements['nature']);
    },

    /**
     * Save the the AMO proof to Jfse
     *
     * @param {HTMLFormElement} form
     */
    saveProof: async function () {
        if (checkForm(this.form)) {
            const response = await Jfse.requestJson('proofAMO/store', {form: this.form}, {});

            if (response.success) {
                Jfse.notifySuccessMessage(response.message);
                Control.Modal.close();
            } else {
                if (response.messages) {
                    Jfse.displayMessages(response.messages, this.getMessageElement());
                } else {
                    Jfse.displayErrorMessage(response.error, this.getMessageElement());
                }
            }
        }
    },

    selectProofAmoType: function (input) {
        switch (input.value) {
            case '2':
                /* Carte d'assuré social */
                $('proof_amo_date_container').show();
                $('proof_amo_origin_container').show();
                break;
            case '4':
                /* Carte Vitale */
                $('proof_amo_date_container').show();
                $('proof_amo_origin_container').hide();
                $V(this.form.elements['origin'], '');
                break;
            case '0':
                /* No proof Amo */
            case '1':
                /* Bulletin de salaire */
            default:
                $('proof_amo_date_container').hide();
                $V(this.form.elements['date'], '');
                $V(this.form.elements['date_da'], '');
                $('proof_amo_origin_container').hide();
                $V(this.form.elements['origin'], '');
        }
    },

    getMessageElement: function () {
        return $('edit_proof_amo_message');
    }
};
