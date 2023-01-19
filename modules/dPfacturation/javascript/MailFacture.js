/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MailFacture = {
    toggleAddressCheckbox: function (field) {
        let checkbox = field.up('tr').down('td.checkbox input');
        checkbox.checked = !!$V(field);
    },

    addAddressLine: function (field) {
        if (!field || ($V(field) && !field.hasClassName('row_added'))) {
            let tbody = $('receivers_list');
            let row = DOM.tr({class: 'manual_input'});
            row.insert(DOM.td({class: 'checkbox'}, DOM.input({
                type: 'checkbox',
                name: 'receivers',
                'data-object_guid': 'receiver_input'
            })));
            row.insert(DOM.td({class: 'tag', 'data-tag': $T('CDestinataire.tag.autre')}, $T('CDestinataire.tag.autre')));
            row.insert(DOM.td({class: 'name'}, DOM.input({type: 'text', name: 'name', value: ''})));
            row.insert(DOM.td({class: 'email'}, DOM.input({
                type: 'text',
                name: 'email',
                value: '',
                onkeyup: 'MailFacture.addAddressLine(this); MailFacture.toggleAddressCheckbox(this);'
            })));
            tbody.insert(row);

            if (field) {
                field.addClassName('row_added');
            }
        }
    },

    send: function (form) {
        $('btn_send_facture_mail').disable();
        let checkboxes = $$('input[name=receivers]:checked');
        let receivers = [];
        let emails = [];
        let errors = [];

        /* If no receivers are selected, the mail is not send */
        if (!checkboxes.length) {
            alert('Veuillez choisir un destinataire.');
            $('btn_send_facture_mail').enable();
            return;
        }

        checkboxes.each(function (dest) {
            let object_guid = dest.get('object_guid');
            dest = dest.up('tr');
            let name = '';
            let email = '';
            if (dest.hasClassName('manual_input')) {
                name = $V(dest.down('td.name input'));
                email = $V(dest.down('td.email input'));
            } else {
                name = dest.down('td.name').innerHTML;
                email = dest.down('td.email').innerHTML;
            }

            if (email.indexOf('@') == -1) {
                errors.push(email);
            } else {
                receivers.push({
                    object_guid: object_guid,
                    name: name.trim(),
                    email: email.trim(),
                    tag: dest.down('td.tag').get('tag')
                });

                emails.push(email);
            }
        });

        if (errors.length) {
            alert('Les adresses suivantes ne sont pas valides : ' + errors.join(', '));
            $('btn_send_facture_mail').enable();
            return;
        }

        new Url('compteRendu', 'ajax_check_whitelist')
            .addParam('emails[]', emails, true)
            .requestJSON(function (result) {
                if (result.length) {
                    alert($T('CWhiteList-alert_blacklist_addresses') + '\n' + result.join('\n'));

                    $('btn_send_facture_mail').enable();
                    return;
                }

                document.body.down('#systemMsg').style.display = 'block';
                new Url('facturation', 'sendFactureByMail')
                    .addParam('receivers', Object.toJSON(receivers))
                    .addParam('subject', $V(form.elements['subject']))
                    .addParam('body', $V(form.elements['body']))
                    .addParam('facture_class', $V(form.elements['facture_class']))
                    .addParam('facture_id', $V(form.elements['facture_id']))
                    .requestUpdate(document.body.down('#systemMsg'), {
                        method: 'post',
                        getParameters: {m: 'facturation', a: 'sendFactureByMail'}
                    });
                Control.Modal.close();
            });
    },
};
