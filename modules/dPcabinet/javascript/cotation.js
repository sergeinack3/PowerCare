/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Cotation = {
    updatingSecteur3: null,
    pursueTarif:      function () {
        var form = getForm('tarifFrm');
        $V(form.tarif, "pursue");
        $V(form.valide, 0);
        Reglement.submit(form, false);
    },

    cancelTarif: function (action, callback, autoclose) {
        var form = getForm('tarifFrm');

        if (action == "delActes") {
            $V(form._delete_actes, 1);
            $V(form.tarif, "");
        }

        if (autoclose) {
            $V(form.chrono, "48");
        }

        $V(form.valide, 0);
        $V(form.secteur3, 0);
        $V(form._somme, 0);
        $V(form._ttc, 0);

        Reglement.submit(form, true, callback);
    },

    selectTarif: function (form, ald_mandatory) {
        $('reglements_button_cloturer_cotation').disable();
        if (ald_mandatory && $V(getForm('tarifFrm').elements['concerne_ALD']) == '') {
            return this.displayALDMandatory('selectTarif');
        }

        return onSubmitFormAjax(form, Reglement.reload);
    },

    displayALDMandatory: function (action) {
        $V(getForm('editALDMandatory').elements['action'], action);
        Modal.open('modal-concerne_ALD-mandatory', {showClose: false, title: $T('CPatient-ald')});
    },

    submitALDMandatory: function (form) {
        $V(getForm('selectionTarif').concerne_ALD, $V(form.concerne_ALD), true);
        $V(getForm('tarifFrm').concerne_ALD, $V(form.concerne_ALD), true);
        Control.Modal.close();
        if ($V(form.elements['action']) == 'selectTarif') {
            $V(form.action, '');
            return getForm('selectionTarif').onsubmit();
        } else if ($V(form.action) == 'editActes') {
            $V(form.action, '');
            getForm('selectionTarif').onsubmit();
            this.viewActes($V(form.elements['consultation_id']));
        }

        return false;
    },

    validTarif: function () {
        var form = getForm('tarifFrm');

        $V(form.du_tiers, Math.round((parseFloat($V(form._somme)) - parseFloat($V(form.du_patient))) * 100) / 100);

        if ($V(form.tarif) == "") {
            $V(form.tarif, "manuel");
        }
        Reglement.submit(form, true);
    },

    modifTotal:  function (cloture) {
        if (this.updating()) {
            return;
        }
        if (cloture != "1") {
            Cotation.updateTotal();
        }
        this.updatingMontants = 0;
    },
    updateTotal: function () {
        var form = form || getForm('tarifFrm');
        if (!form.secteur1.value) {
            form.secteur1.value = 0;
        }
        var secteur1 = form.secteur1.value;
        if (!form.secteur2.value) {
            form.secteur2.value = 0;
        }
        var secteur2 = form.secteur2.value;
        if (!form.secteur3.value) {
            form.secteur3.value = 0;
        }
        var secteur3 = form.secteur3.value;
        var du_tva = form.du_tva.value;
        var somme = parseFloat(secteur1) + parseFloat(secteur2) + parseFloat(secteur3) + parseFloat(du_tva);
        $V(form._somme, Math.round(100 * (somme)) / 100);
        $V(form.du_patient, form._somme.value);
        $V(form._ttc, form._somme.value);
    },

    modifTVA: function () {
        if (this.updating()) {
            return;
        }
        var form = getForm('tarifFrm');
        if (!form.secteur3.value) {
            form.secteur3.value = 0;
        }
        var secteur3 = form.secteur3.value;
        if (!form.du_tva.value) {
            form.du_tva.value = 0;
        }
        var du_tva = form.du_tva.value;
        var taux_tva = form.taux_tva.value;

        $V(form.du_tva, (secteur3 * (taux_tva) / 100).toFixed(2));
        Cotation.updateTotal();
        this.updatingMontants = 0;
    },

    modifSecteur2: function () {
        if (this.updating()) {
            return;
        }
        var form = getForm('tarifFrm');
        var secteur1 = form.secteur1.value;
        var secteur3 = form.secteur3.value;
        var du_tva = form.du_tva.value;
        var somme = form._somme.value;

        $V(form.du_patient, somme);
        $V(form._ttc, somme);
        $V(form.secteur2, Math.round(100 * (parseFloat(somme) - (Math.round(100 * parseFloat(secteur1)) / 100 + parseFloat(secteur3) + parseFloat(du_tva)))) / 100);
        this.updatingMontants = 0;
    },

    modifSecteur3: function () {
        if (this.updating()) {
            return;
        }
        var form = getForm('tarifFrm');
        var ttc = form._ttc.value;
        var secteur1 = form.secteur1.value;
        var secteur2 = form.secteur2.value;

        var somme_sup = ttc - secteur1 - secteur2;
        var taux_tva = form.taux_tva.value;
        if (taux_tva === "0" && form.taux_tva.options.length > 1) {
            $V(form.taux_tva, form.taux_tva.options[1].value);
            taux_tva = form.taux_tva.value;
        }

        var secteur3 = Math.round(100 * somme_sup / (1 + (taux_tva / 100))) / 100;
        var du_tva = Math.round(100 * (somme_sup - secteur3)) / 100;

        $V(form.du_tva, du_tva);
        $V(form.du_patient, ttc);
        $V(form._somme, ttc);
        $V(form.secteur3, secteur3);
        this.updatingMontants = 0;
    },

    updating: function () {
        if (this.updatingMontants) {
            return true;
        }
        this.updatingMontants = 1;
        return false;
    },

    printActes: function (consult_id) {
        var url = new Url('dPcabinet', 'print_actes');
        url.addParam('consultation_id', consult_id);
        url.popup(600, 600, 'Impression des actes');
    },

    checkActe: function (button) {
        button.form.du_tiers.value = 0;
        button.form.du_patient.value = 0;
        Cotation.cancelTarif(null, null);
    },

    tiersPayant: function () {
        var form = getForm('tarifFrm');
        var du_patient = parseFloat(form.secteur2.value) + parseFloat(form.secteur3.value) + parseFloat(form.du_tva.value);
        $V(form.du_tiers, form.secteur1.value);
        $V(form.du_patient, du_patient);
    },

    createSecondFacture: function () {
        var form = getForm('addFactureDivers');
        return onSubmitFormAjax(form, Reglement.reload.curry());
    },

    viewActes: function (consult_id, ald_mandatory) {
        if (ald_mandatory && $V(getForm('tarifFrm').elements['concerne_ALD']) == '') {
            return this.displayALDMandatory('editActes');
        }

        var url = new Url("cabinet", "ajax_vw_actes");
        url.addParam("consult_id", consult_id);
        url.requestModal("95%", 650, {onClose: Reglement.reload.curry()});
    },

    syncALD: function (field) {
        if (field.checked) {
            $V(getForm('tarifFrm').concerne_ALD, $V(field));
            field.form.onsubmit();
        }
    }
};
