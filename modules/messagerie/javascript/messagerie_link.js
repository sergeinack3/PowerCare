/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MessagingLink = window.MessagingLink || {
    selectMode: true,

    /**
     * Init patient autocomplete
     *
     * @param {string} field
     */
    initPatientSearch: function (field) {
        const form = getForm('search-menu'),
          oField = form.elements[field];

        new Url('messagerie', 'searchPatient').autoComplete(oField, null, {
            minChars: 3,
            method: 'get',
            afterUpdateElement: function (input, selected) {
                $V(input, '');
                $V(form.elements['patient_id'], $(selected).get('id'));
            }
        });
    },

    /**
     * Submit function with validator
     *
     * @param {HTMLFormElement} form
     */
    submitSearch: function (form) {
        // Validator
        if (form.elements['patient_id'].value === '') {
            return false;
        }

        return onSubmitFormAjax(
            form,
            {
                onComplete: function () {
                    MessagingLink.checkLink()
                }
            },
            'result-search'
        )
    },

    /**
     * Show a preview of attachment in a popup
     *
     * @param {string} attachment_type
     * @param {string} attachment_id
     * @param {string} attachment_ref_file_id
     */
    showAttachment: function (attachment_type, attachment_id, attachment_ref_file_id) {
        new Url()
          .ViewFilePopup(attachment_type, attachment_id, 'CFile', attachment_ref_file_id, '0');
    },

    /**
     * Selects or deselects all attachment for link
     *
     * @param {HTMLInputElement} elt
     */
    selectAttachments: function (elt) {
        const selects = document.querySelectorAll('input[type=checkbox][name="checkboxItem"]:not(:checked)');

        if (selects.length > 0 && MessagingLink.selectMode) {
            elt.title = $T('CMessagingLink-Title-Deselect all items');
            MessagingLink.selectMode = false;

            selects.forEach(function (select) {
                select.closest('.MessagingLinkCard-view').classList.add('selected');
                select.checked = true;
            });
        } else {
            elt.title = $T('CMessagingLink-Title-Select all items');
            MessagingLink.selectMode = true;

            document.querySelectorAll('input[type=checkbox][name="checkboxItem"]:checked').forEach(function (select) {
                select.closest('.MessagingLinkCard-view').classList.remove('selected');
                select.checked = false;
            });
        }

        // Check button link
        MessagingLink.checkLink();
    },

    /**
     * Select or deselect an attachment for link
     *
     * @param {HTMLInputElement|HTMLDivElement} elt
     */
    selectAttachment: function (elt) {
        const parent = elt.closest('.MessagingLinkCard-view'),
          checkboxItems = document.querySelector('input[type=checkbox][name="checkboxItems"]');

        if (elt instanceof HTMLDivElement) {
            // Relaunch the event from the checkbox
            parent.querySelector('input[type=checkbox][name="checkboxItem"]').click();
        } else {
            (elt.checked)
                ? parent.classList.add('selected')
                : parent.classList.remove('selected');
        }

        // Updates the global selector status
        if (document.querySelectorAll('input[type=checkbox][name="checkboxItem"]:checked').length > 0) {
            checkboxItems.checked = true;
            MessagingLink.selectMode = false;
        } else {
            checkboxItems.checked = false;
            MessagingLink.selectMode = true;
        }

        // Check button link
        MessagingLink.checkLink();
    },

    /**
     * Edit attachment for link
     *
     * @param {HTMLButtonElement} elt
     */
    editAttachment: function (elt) {
        const parent = elt.closest('.MessagingLinkCard'),
          form = parent.querySelector('.MessagingLinkCard-edit');

        if (elt.dataset.edit_mode === 'true') {
            elt.dataset.edit_mode = 'false';

            form.classList.remove('show');
        } else {
            elt.dataset.edit_mode = 'true';

            form.classList.add('show');
        }
    },

    /**
     * Update name attachment for link
     *
     * @param {HTMLInputElement} elt
     */
    updateAttachmentName: function (elt) {
        const parent = elt.closest('.MessagingLinkCard'),
          attachment_title = parent.querySelector('.MessagingLinkCard-titleName');

        attachment_title.innerHTML = elt.value;
    },

    /**
     * Check if all conditions is good for link
     */
    checkLink: function () {
        const button_link = document.querySelector('button[id="buttonLink"]');

        if (
            document.querySelectorAll('input[type=checkbox][name="checkboxItem"]:checked').length > 0
            && document.querySelector('input[type=radio][name="radioItem"]:checked') !== null
        ) {
            button_link.disabled = false;
            button_link.onclick = function () {
              MessagingLink.link();
            }
        } else {
            button_link.disabled = true;
            button_link.onclick = null;
        }
    },

    /**
     * Show more context of patient
     *
     * @param {HTMLButtonElement} elt
     * @param {string} context
     * @param {string} start
     * @param {string} offset
     */
    showMorePatientContext: function (elt, context, start, offset) {
        const form = getForm('search-menu'),
          parent = elt.parentElement;

        new Url('messagerie', 'showMorePatientContext')
            .addParam('patient_id', form.elements['patient_id'].value)
            .addParam('context', context)
            .addParam('start', start)
            .addParam('offset', offset)
            .requestUpdate(parent, {
                onComplete: function () {
                    parent.classList.add('loaded');
                }
            });
    },

    /**
     * Link action
     */
    link: function () {
        // Get context data
        const link_context = document.querySelector('input[type=radio][name="radioItem"]:checked').dataset.id,
          link_attachments = [];

        // Get attachments data
        document.querySelectorAll('input[type=checkbox][name="checkboxItem"]:checked').forEach(function (element) {
            const form = getForm(`edit-${element.dataset.id}`);

            link_attachments.push(
                {
                    guid    : element.dataset.id,
                    name    : form.elements['name'].value + form.elements['file_extension'].value,
                    category: form.elements['category_id'].value
                }
            );
        });

        new Url('messagerie', 'linkAttachments')
            .addParam('link_context', link_context)
            .addParam('link_attachments', JSON.stringify(link_attachments))
            .requestUpdate('systemMsg', {
                method: 'post',
                getParameters: {
                    m: 'messagerie',
                    a: 'linkAttachments'
                },
                onComplete: function () {
                    Control.Modal.close();
                }
            });
    }
};
