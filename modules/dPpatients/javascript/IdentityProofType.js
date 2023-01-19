/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

IdentityProofType = {
    filter: (form, page) => {
        if (!page) {
            page = 0;
        }
        new Url('patients', 'filterIdentityProofTypes')
            .addFormData(form)
            .addParam('page', page)
            .requestUpdate('list_identity_proof_types', {
                method: 'post',
                getParameters: {m: 'patients', a: 'filterIdentityProofTypes'}
            });

        return false;
    },

    emptyFilters: (form) => {
        $V(form.elements['code'], '');
        $V(form.elements['label'], '');
        $V(form.elements['trust_level'], '');
        $V(form.elements['active'], '1');
        this.filter(form);
    },

    edit: (type_id) => {
        if (!type_id) {
            type_id = '';
        }
        new Url('patients', 'editIdentityProofType')
            .addParam('identity_proof_type_id', type_id)
            .requestModal(325, null, {
                onClose: IdentityProofType.refresh.bind(IdentityProofType)
            });
    },

    save: (form) => {
        return onSubmitFormAjax(form, Control.Modal.close.curry());
    },

    delete: (form, modal) => {
        $V(form.elements['del'], '1');
        let callback = IdentityProofType.refresh.bind(IdentityProofType);
        if (modal) {
            callback = Control.Modal.close.curry();
        }
        return onSubmitFormAjax(form, callback);
    },

    refresh: () => {
        IdentityProofType.filter(IdentityProofType.getFilterForm(), IdentityProofType.getActivePage());
    },

    getActivePage: () => {
        return $('list_identity_proof_types').down('table').get('activePage');
    },

    changePage: (page) => {
        this.filter(this.getFilterForm(), page);
    },

    getFilterForm: () => {
        return getForm('filterIdentityTypes');
    },

    getTrustLevel: (select) => {
      $$('.validate_identity').invoke(
        ((select.options[select.selectedIndex].value == 3) ? 'show' : 'hide')
      )
    }
};
