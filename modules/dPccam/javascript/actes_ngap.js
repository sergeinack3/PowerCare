/**
 * @package Mediboard\CCAM
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ActesNGAP = {
    changePage: function (target, page) {
        ActesNGAP.refreshList(target, null, null, page);
    },

    refreshList: function (target, order_col, order_way, page) {
        if (!target) {
            target = $('listActesNGAP');
        } else if (typeof target == 'string') {
            target = $(target);
        }

        let url = new Url("dPcabinet", "httpreq_vw_actes_ngap");

        url.addParam("object_id", target.get('object_id'));
        url.addParam("object_class", target.get('object_class'));
        url.addParam("refresh_list", 1);

        if (target.get('executant_id')) {
            url.addParam('executant_id', target.get('executant_id'));
        }
        if (target.get('execution')) {
            url.addParam('execution', target.get('execution'));
        }
        if (target.get('display')) {
            url.addParam('display', target.get('display'));
        }
        if (target.get('code')) {
            url.addParam('code', target.get('code'));
        }
        if (target.get('coefficient')) {
            url.addParam('coefficient', target.get('coefficient'));
        }
        if (target.get('show_tarifs')) {
            url.addParam('show_tarifs', target.get('show_tarifs'));
        }

        url.addParam('target', target.id);

        if (!Object.isUndefined(page)) {
            url.addParam('page', page);
        }
        if (order_col) {
            url.addParam('order_col', order_col);
            target.writeAttribute('data-order_col', order_col);
        } else if (target.get('order_col')) {
            url.addParam('order_col', target.get('order_col'));
        }
        if (order_way) {
            url.addParam('order_way', order_way);
            target.writeAttribute('data-order_way', order_way);
        } else if (target.get('order_way')) {
            url.addParam('order_way', target.get('order_way'));
        }

        let object_guid = target.get('object_class') + '-' + target.get('object_id');

        if (getForm('filterActs-' + object_guid)) {
            let filterForm = getForm('filterActs-' + object_guid);
            url.addParam('filter_executant_id', $V(filterForm.elements['executant_id']));
            url.addParam('filter_function_id', $V(filterForm.elements['function_id']));
            url.addParam('filter_facturable', $V(filterForm.elements['facturable']));
            url.addParam('filter_date_min', $V(filterForm.elements['date_min']));
            url.addParam('filter_date_max', $V(filterForm.elements['date_max']));
        }

        url.requestUpdate(target, {onComplete: function() {
            if ($('count_ngap_' + object_guid)) {
                let url = new Url('ccam', 'updateActsCounter');
                url.addParam('subject_guid', object_guid);
                url.addParam('type', 'ngap');
                url.requestUpdate('count_ngap_' + object_guid, {
                    insertion: function (element, content) {
                        element.innerHTML = content;
                    }
                });
            }
        }});
    },

    remove: function (form) {
        $V(form.del, 1);
        form.onsubmit();
    },

    edit: function (acte_id, target) {
        new Url('cabinet', 'ajax_edit_acte_ngap')
            .addParam('acte_id', acte_id)
            .requestModal('800px', '550px', {onClose: function() {ActesNGAP.refreshList(target);}});
    },

    checkExecutant: function (form) {
        if (!$V(form._executant_spec_cpam)) {
            alert($T("CActeNGAP-specialty-undefined_user"));
        }
    },

    checkNumTooth: function (input, view) {
        let num_tooth = $V(input);

        if (num_tooth < 11 || (num_tooth > 18 && num_tooth < 21) || (num_tooth > 28 && num_tooth < 31) || (num_tooth > 38 && num_tooth < 41) || (num_tooth > 48 && num_tooth < 51) || (num_tooth > 55 && num_tooth < 61) || (num_tooth > 65 && num_tooth < 71) || (num_tooth > 75 && num_tooth < 81) ||  num_tooth > 85) {
            alert("Le numéro de dent saisi ne correspond pas à la numérotation internationale!");
        } else {
            ActesNGAP.syncCodageField(this, view);
        }
    },

    /**
     * Edit NGAP Act DEP
     *
     * @param act_id   NGAP act id
     * @param view     Form view
     * @param readonly Form is readonly or not ?
     */
    editDEP: function (act_id, view, readonly) {
        let ngap_form = getForm('editActeNGAP' + view),
            url = new Url('cabinet', 'editDEP')
                .addParam('act_id', act_id)
                .addParam('view', view)
                .addParam('readonly', readonly);

        if ($V(ngap_form.accord_prealable) !== "") {
            url.addParam('dep', $V(ngap_form.accord_prealable));
        }

        if ($V(ngap_form.date_demande_accord) !== "") {
            url.addParam('date_request_agreement', $V(ngap_form.date_demande_accord));
        }

        if ($V(ngap_form.reponse_accord) !== "") {
            url.addParam('response_agreement', $V(ngap_form.reponse_accord));
        }

        url.requestModal('300px', '250px');
    },

    /**
     * Sumbit DEP for new NGAP Act
     *
     * @param view Form view
     */
    submitDEP: function (view) {
        let ngap_form = getForm('editActeNGAP' + view),
            dep_form  = getForm('editActeNGAP-accord_prealable' + view);

        $V(ngap_form.accord_prealable, dep_form.accord_prealable.value);
        $V(ngap_form.date_demande_accord, dep_form.date_demande_accord.value);
        $V(ngap_form.reponse_accord, dep_form.reponse_accord.value);

        Control.Modal.close();
    },

    toggleDateDEP: function (element, view) {
        if (element.value == 1) {
            $('accord_infos' + view).show();
        } else {
            $('accord_infos' + view).hide();
        }
    },

    syncDEPFields: function (form, view) {
        ActesNGAP.syncCodageField(form.down('[name="accord_prealable"]:checked'), view);
        ActesNGAP.syncCodageField(form.date_demande_accord, view);
        ActesNGAP.syncCodageField(form.reponse_accord, view);
        Control.Modal.close();
    },

    checkDEP: function (view) {
        let element = $('info_dep' + view),
            form = getForm('editActeNGAP-accord_prealable' + view);

        if (element != null) {
            if ($V(form.accord_prealable) == '1' && $V(form.date_demande_accord) && $V(form.reponse_accord)) {
                element.setStyle({color: '#197837'});
            } else {
                element.setStyle({color: '#ffa30c'});
            }
        }
    },

    setCoefficient: function (element, view) {
        let value = $V(element)
        if (value != '') {
            ActesNGAP.syncCodageField(element, view);
        }
    },

    refreshTarif: function (view) {
        if ($('inc_codage_ngap_button_create')) {
            $('inc_codage_ngap_button_create').disabled = true;
        }

        let form = getForm('editActeNGAP' + view),
            url = new Url("cabinet", "httpreq_vw_tarif_code_ngap");

        url.addElement(form.acte_ngap_id);
        url.addElement(form.quantite);
        url.addElement(form.code);
        url.addElement(form.coefficient);
        url.addElement(form.demi);
        url.addElement(form.complement);
        url.addElement(form.executant_id);
        url.addElement(form.gratuit);
        url.addElement(form.execution);
        url.addElement(form.taux_abattement);
        url.addParam('view', view);

        if ($V(form.acte_ngap_id)) {
            url.addParam('disabled', 1);
        }

        url.requestUpdate('tarifActe' + view, function() {
            if ($('inc_codage_ngap_button_create')) {
                $('inc_codage_ngap_button_create').disabled = false;
            }
        });
    },

    syncCodageField: function (element, view, fire) {
        fire = Object.isUndefined(fire) ? true : fire;

        if (element.name == 'quantite' || element.name == 'coefficient') {
            if (parseFloat($V(element)) <= 0) {
                $V(element, 1);
            }
        }

        let form = getForm('editActeNGAP' + view),
            fieldName = element.name,
            fieldValue = $V(element);

        $V(form[fieldName], fieldValue, fire);
    },

    changeTauxAbattement: function (element, view) {
        if ($V(element) == 0) {
            $V(getForm('editActeNGAP-gratuit' + view).elements['gratuit'], '1', false);
        } else {
            $V(getForm('editActeNGAP-gratuit' + view).elements['gratuit'], '0', false);
        }

        this.syncCodageField(getForm('editActeNGAP-gratuit' + view).elements['gratuit'], view, false);
        this.syncCodageField(element, view);
    },

    submit: function (form, target) {
        if (!$V(form.acte_ngap_id)) {
            ActesNGAP.checkExecutant(form);
        }
        return onSubmitFormAjax(form, function() {
            ActesNGAP.refreshList(target);
            if (typeof DevisCodage !== 'undefined') {
                target = $(target);
                DevisCodage.refresh(target.get('object_id'));
            }
        });
    },

    duplicate: function (acte_guid, target) {
        target = $(target);
        if (target && target.get('object_class') == 'CSejour') {
            let url = new Url('ccam', 'viewDuplicateNgap');
            url.addParam('codable_guid', target.get('object_class') + '-' + target.get('object_id'));
            url.addParam('acte_guid', acte_guid);
            url.requestModal(null, null, {onClose: function() {ActesNGAP.refreshList(target)}});
        }
    },

    editComment: function (acte_id, target) {
        target = $(target);
        new Url('cabinet', 'editComment')
            .addParam('acte_id', acte_id)
            .requestModal('500px', '250px', {onClose: function() {ActesNGAP.refreshList(target);}});
    },

    /**
     * Show view for add a comment for NGAP act
     * @param name_form
     */
    addComment: function (name_form) {
        let form    = document.forms[name_form],
            comment = form.elements['comment_acte'].value

        let url = new Url('cabinet', 'editComment')
            .addParam('name_form', name_form);

        if (comment.value !== "") {
            url.addParam('comment_acte', comment)
        }

        url.requestModal('500px', '250px');
    },

    /**
     * Get the comment for a new NGAP act before creating
     * @param name_form
     */
    submitComment: function (name_form) {
        let comment = document.forms['editcomment-actengap'].elements['comment_acte'].value,
            form = document.forms[name_form]

        form.elements['comment_acte'].value = comment
        Control.Modal.close()
    }
};
