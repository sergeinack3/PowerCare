/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Slot = {
    modalReplaySlot: function () {
        new Url("cabinet", "modalReplaySlot")
            .requestModal()
    },

    replaySlot: function (start = 0) {
        let form = getForm("replay_slot")
        form.down("button").addClassName("loading")
        new Url("cabinet", "replaySlot")
            .addParam("start", start)
            .requestJSON(function (data) {
                if (parseInt(data.countPlage) == 1000) {
                    Slot.replaySlot(start + 1000)
                }
                else {
                    form.down("button").removeClassName("loading")
                    SystemMessage.notify("<div class=\"small-info\"> Correction des créneaux terminée </div>")
                    Control.Modal.close()
                }
            })
    },

    /**
     * Open the modal to correct consultation and slot
     */
    modalReplayConsultationToSlot: function () {
        new Url("cabinet", "modalReplayConsultationToSlot")
            .requestModal()
    },

    /**
     * Launch script to correct consultation and slot
     *
     * @param start
     */
    replayConsultationToSlot: function (start = 0) {
        let form = getForm("replay_consultation_slot");
        form.down("button").addClassName("loading");
        let message = $T('CSlot-msg-Correction of the association between time slots and consultations completed');
        new Url("cabinet", "replayConsultationToSlot")
            .addParam("start", start)
            .requestJSON(function (data) {
                if (parseInt(data.countPlage) == 1000) {
                    Slot.replayConsultationToSlot(start + 1000)
                }
                else {
                    form.down("button").removeClassName("loading");
                    SystemMessage.notify("<div class=\"small-info\">" + message + "</div>");
                    Control.Modal.close();
                }
            })
    },
};
