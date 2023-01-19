/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

BanqueEdit = {
    form: null,

    /**
     * Bank account autoComplete
     */
    autoComplete: function () {
        new Url("mediusers", "listBanques").autoComplete(getForm(this.form).banque_id_autocomplete_view, null, {
            dropdown:           true,
            afterUpdateElement: function (field, selected) {
                $V(field, selected.down('.view').innerHTML);
                $V(field.form.elements['banque_id'], selected.dataset.banqueId);
            }
        });
    },

    /**
     * Bank account form value
     * 
     * @param banque_id Bank Id
     * @param banque    Bank
     */
    setValue: function (banque_id, banque) {
        let form = getForm(this.form);

        if (form) {
            $V(form.banque_id_autocomplete_view, banque.nom);
            $V(form.banque_id, banque.banque_id);
        }
    },

    /**
     * Show or not edit bank account button
     */
    editButton: function () {
        let button = document.getElementById('edit_button'),
            select = document.getElementById(this.form + '_banque_id');

        (select && select.value !== '')
            ? button.show()
            : button.hide();
    },

    /**
     * Edit bank account
     *
     * @param banqueId      Bank Id
     * @param buttonElement Element
     * @param modal         Modal
     */
    edit: function (banqueId, buttonElement, modal) {
        let url = new Url('cabinet', 'editBank')
            .addParam('banque_id', banqueId);

        (modal)
          ? url.requestModal("50%", "70%")
          : url.requestUpdate('banque_edit_container');

        $$('.banque-line').invoke('removeClassName', 'selected');

        if (buttonElement) {
            buttonElement.up('tr').addClassName('selected');
        }
    },

    /**
     * Save bank account
     *
     * @param form
     */
    save: function (form) {
        return onSubmitFormAjax(form, function () {
            Control.Modal.close();
            try {
                Control.Tabs.GroupedTabs.refresh();
            } catch (e) {
            }
        });
    },

    /**
     * Delete bank account
     *
     * @param form
     * @param options
     */
    delete: function (form, options) {
        options.ajax = 1;
        return confirmDeletion(
            form,
            options,
            function () {
                Control.Modal.close();
                try {
                    Control.Tabs.GroupedTabs.refresh();
                } catch (e) {
                }
            }
        );
    }
};
