/**
 * @package Mediboard\oxCabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Planning = {
    togglePractitioners: function () {
        $$('.check-practitioners')[0].on('click', this._togglePractitioners.bind(this));
    },
    _togglePractitioners: function (el) {
        $$('#filter_prats input[type="checkbox"]').forEach(function (e) {
            e.checked = el.target.checked;
        })
    },

    /**
     * Deletes a ressource reservation
     */
    deleteRessourceReservation: function () {
        $$('.delete-ressource-slot')[0].observe('click', this._deleteRessourceReservation.bind(this));
    },
    _deleteRessourceReservation: function (event) {
        var form = event.target.form;
        form.insert(DOM.input({type: "hidden", name: "del", value: "1"}));
        form.onSubmit();
    },

    /**
     * Reloads resources for filters
     *
     * @param {int}    function_id
     * @param {string} date
     */
    reloadRessources: function (function_id, date) {
        new Url('cabinet', 'ajax_filter_items')
            .addParam('type', 'ressources')
            .addParam('function_id', function_id)
            .addParam('date', date)
            .requestUpdate('filter_ressources');
    },

    /**
     * Cancels an appointments
     *
     * @param {int} consult_id - appointment id
     */
    cancelRdv: function (consult_id) {
        var url = new Url("cabinet", "ajax_cancel_rdv_planning");
        url.addParam("consultation_id", consult_id);
        url.requestModal(
            null,
            null,
            {
                onClose: function () {
                    refreshPlanning();
                }
            }
        );
    },

    /**
     * Restores an appointment
     *
     * @param {int} consult_id - appointment id
     */
    restoreConsult: function (consult_id) {
        Modal.confirm(
            $T('CConsultation-confirm-cancel-0'),
            {
                onValidate: function () {
                    var form = getForm('restoreConsult');
                    $V(form.consultation_id, consult_id);
                    onSubmitFormAjax(form, {onComplete: refreshPlanning});
                }
            }
        );
    }
};
