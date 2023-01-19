/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Formula = {

    /**
     *  Display the list of operands
     */
    listOperands: () => {
        Jfse.displayView(
            'formulas/operands/get',
            'operands-list-container',
            {},
        );
    },

    /**
     *  Display the list of formulas
     */
    listeFormulas: () => {
        Jfse.displayView(
            'formulas/formulas/get',
            'formulas-list-container',
            {}
        );
    },

    /**
     * Edit a formula
     *
     * @param formule_id
     */
    edit: (formule_id) => {
        Jfse.displayViewModal(
            'formulas/formula/edit',
            '500',
            '1000',
            {formula_id: formule_id},
            {title: $T('CFormulaEdit-title-edit')}
        );
    },

    /**
     * Save formula content
     *
     * @param form
     */
    save: (form) => {
        Jfse.displayView('formulas/formula/store',
            'systemMsg',
            {form}
        );
    },

    /**
     * @param formule_id
     */
    delete: (formule_id) => {
        if (confirm($T('CFormula-delete-confirm'))) {
            Jfse.displayView(
                'formulas/formula/delete',
                'systemMsg',
                {formula_id: formule_id}
            );
        }
    },
};
