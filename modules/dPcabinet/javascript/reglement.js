/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Reglement = {
    consultation_id: null,
    user_id:         null,
    view:            'cabinet',
    only_cotation:   0,
    cotation_full:   0,

    register: function (load) {
        if (Object.isUndefined(load)) {
            load = true;
        }

        document.write('<div id="facturation" class="me-align-auto"></div>');
        if (load) {
            Main.add(Reglement.reload);
        }
    },

    submit: function (oForm, reload_acts, callback) {
        onSubmitFormAjax(oForm, function () {
            Reglement.reload(callback);
            if (Preferences.autoCloseConsult == "1") {
                reloadFinishBanner();
            }
        });
    },

    reload: function (callback) {
        new Url("cabinet", "ajax_vw_facturation")
            .addParam("selConsult", Reglement.consultation_id)
            .addParam("chirSel", Reglement.user_id)
            .addParam('view', Reglement.view)
            .addParam("only_cotation", Reglement.only_cotation)
            .addParam("cotation_full", Reglement.cotation_full)
            .requestUpdate('facturation', callback);
    },

    cotationModal: function () {
        new Url("cabinet", "ajax_vw_cotation")
            .addParam("selConsult", Reglement.consultation_id)
            .addParam("view", Reglement.view)
            .modal({onClose: Reglement.reload});
    },

    cancel: function (reglement_id) {
        var oForm = getForm('reglement-delete');
        $V(oForm.reglement_id, reglement_id);
        confirmDeletion(oForm, {ajax: true, typeName: 'le règlement'}, {onComplete: Reglement.reload.curry()});
        return false;
    },

    updateBanque:               function (mode) {
        var banque_id = mode.form.banque_id;
        var num_bvr = mode.form.num_bvr;

        var div_num_bvr = $('numero_bvr');
        var choice_reference = $('choice_reference');
        var choice_banque = $('choice_banque');

        div_num_bvr.hide();
        choice_banque.hide();
        choice_reference.show();
        Reglement.updateTireurByTypeEmetteur(mode.form);
        if ($V(mode) != "BVR") {
            $V(num_bvr, 0);
        }
        if ($V(mode) != "cheque") {
            $V(banque_id, "");
        }

        switch ($V(mode)) {
            case "cheque":
                choice_banque.show();
                break;
            case "BVR":
                div_num_bvr.show();
                break;
            case "virement":
            case "autre":
                break;
            default:
                choice_reference.hide();
                $V(mode.form.reference, "");
        }
    },
    updateTireurByTypeEmetteur: function (form) {
        var type_emet = form.emetteur;
        var mode = form.mode;
        var choice_tireur = $('choice_tireur');
        if (!choice_tireur) {
            return;
        }
        if ($V(type_emet) === 'tiers' || $V(mode) === 'BVR' || $V(mode) === 'cheque') {
            choice_tireur.show();
        } else {
            choice_tireur.hide();
            $V(form.tireur, "");
        }
    },
    modifMontantBVR:            function (form, num_bvr) {
        var eclat = num_bvr.split('>')[0];
        form.montant.value = eclat.substring(2, 12) / 100;
    },
    updateDebiteur:             function (debiteur_id) {
        var url = new Url('dPfacturation', 'ajax_edit_debiteur');
        url.addParam('debiteur_id', debiteur_id);
        url.addParam('debiteur_desc', 1);
        url.requestUpdate("reload_debiteur_desc");
    },
    delReglement:               function (reglement_id, facture_class, facture_id) {
        var oForm = getForm('reglement-delete');
        $V(oForm.reglement_id, reglement_id);
        return confirmDeletion(oForm, {ajax: true, typeName: 'le règlement'}, {
            onComplete: function () {
                Reglement.refreshAfterChange(facture_id, facture_class);
                if (!$('load_facture')) {
                    Control.Modal.refresh();
                }
            }
        });
    },
    editReglementDate:          function (reglement_id, date, facture_id, facture_class) {
        var oForm = getForm('reglement-edit-date');
        $V(oForm.reglement_id, reglement_id);
        $V(oForm.date, date);

        return onSubmitFormAjax(oForm, function () {
            if ($('a_reglements_consult')) {
                Reglement.reload();
            }
            Facture.reloadFactureModal(facture_id, facture_class);
        });
    },
    editAquittementDate:        function (date, facture_id, facture_class) {
        var form = getForm('edit-date-aquittement-' + facture_class + '-' + facture_id);
        $V(form.patient_date_reglement, date);
        return onSubmitFormAjax(form, function () {
            Reglement.refreshAfterChange(facture_id, facture_class);
        });
    },
    addReglement:               function (form) {
        return onSubmitFormAjax(form, function () {
            Reglement.refreshAfterChange($V(form.object_id), $V(form.object_class));
        });
    },
    refreshAfterChange:         function (facture_id, facture_class) {
        if ($('a_reglements_consult')) {
            Reglement.reload();
        } else if ($('a_reglements_evt')) {
            Facture.reloadEvt(null, false);
        } else {
            Facture.reloadFactureModal(facture_id, facture_class);
        }
    }
};
