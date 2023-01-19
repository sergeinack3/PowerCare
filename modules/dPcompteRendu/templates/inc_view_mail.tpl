{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=send_multiple_documents value=false}}
{{mb_default var=destinataires value=false}}

{{if !$send_multiple_documents && !preg_match("/-[0-9]+/", $object->_guid)}}
    <h2>{{tr}}CCompteRendu-store_to_send{{/tr}}</h2>
    {{mb_return}}
{{/if}}

<script>
    Main.add(function () {
        addAddressLine();
    });

    toggleAddressCheckbox = function (field) {
        var checkbox = field.up('tr').down('td.checkbox input');
        checkbox.checked = !!$V(field);
    };

    addAddressLine = function (field) {
        if (!field || ($V(field) && !field.hasClassName('row_added'))) {
            var tbody = $('receivers_list');
            var row = DOM.tr({class: 'other'});
            row.insert(DOM.td({class: 'checkbox'}, DOM.input({
                type: 'checkbox',
                name: 'destinataire',
                'data-object_guid': 'receiver_input'
            })));
            row.insert(DOM.td({class: 'tag', 'data-tag': 'autre'}, $T('CDestinataire.tag.autre')));
            row.insert(DOM.td({class: 'name'}, DOM.input({type: 'text', name: 'name', value: ''})));
            row.insert(DOM.td({class: 'email'}, DOM.input({
                type: 'text',
                name: 'email',
                value: '',
                onkeyup: 'addAddressLine(this); toggleAddressCheckbox(this);'
            })));
            tbody.insert(row);

            if (field) {
                field.addClassName('row_added');
            }
        }
    };

    sendMail = function () {
        $('btn_send_mail').disable();
        var checkboxes = $$("input[name=destinataire]:checked");
        var receivers = [];
        var emails = [];
        var errors = [];

        // S'il n'y a pas de destinataire cochés ou si ce nombre est différent de 1
        if (!checkboxes.length) {
            alert("Veuillez choisir un destinataire.");
            $('btn_send_mail').enable();
            return;
        }

        checkboxes.each(function (dest) {
            var object_guid = dest.get('object_guid');
            dest = dest.up('tr');
            var name = '';
            var email = '';
            if (dest.hasClassName('other')) {
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

        // Test d'intégrité de l'adresse mail
        if (errors.length) {
            alert('Les adresses suivantes ne sont pas valides : ' + errors.join(', '));
            $('btn_send_mail').enable();
            return;
        }

        var documents = [];
        {{if $send_multiple_documents}}
        $$('table#tabDocs input[type="checkbox"]:checked').each(function (input) {
            documents.push(input.readAttribute('data-guid'));
        });

        if (!documents.length) {
            $('btn_send_mail').enable();
            Modal.alert($T('CDocumentItem-error-none_selected'));
            return;
        }
        {{else}}
        documents.push('{{$object->_guid}}');
        {{/if}}

        new Url("compteRendu", "ajax_check_whitelist")
            .addParam("emails[]", emails, true)
            .requestJSON(function (result) {
                if (result.length) {
                    alert($T("CWhiteList-alert_blacklist_addresses") + "\n" + result.join("\n"));

                    $('btn_send_mail').enable();
                    return;
                }

                var form = getForm("formSendMail");

                var url = new Url("compteRendu", "sendMail");
                url.addParam('receivers', Object.toJSON(receivers));
                url.addParam("subject", $V(form.subject));
                url.addParam("body", $V(form.body));
                url.addParam("objects_guids[]", documents, true);

                document.body.down("#systemMsg").style.display = "block";
                url.requestUpdate(document.body.down("#systemMsg"), {
                    method: 'post',
                    getParameters: {m: 'compteRendu', a: 'sendMail'}
                });
                Control.Modal.close();
            });
    };
</script>

<form name="formSendMail" method="get">
    <p>
        <label>{{tr}}CCompteRendu.mail_subject{{/tr}} :
            <input type="text" name="subject" value="{{$send_document_subject}}" style="width: 500px;"/>
        </label>
    </p>
    <p class="me-w75">
        <label>{{tr}}CCompteRendu.mail_body{{/tr}} :
            <textarea name="body">{{$send_document_body}}</textarea>
        </label>
    </p>

    {{if $send_multiple_documents}}
        <div>
            <table class="tbl" id="tabDocs">
                <tr>
                    <th class="title" colspan="2">{{tr}}CDocumentItem-title-select{{/tr}}</th>
                </tr>
                {{if $object->_ref_files|@count || $object->_ref_documents|@count}}
                    {{foreach from=$object->_ref_files item=_file name=files}}
                        {{if $smarty.foreach.files.first}}
                            <tr>
                                <th class="category" colspan="2">{{tr}}CFile|pl{{/tr}}</th>
                            </tr>
                        {{/if}}
                        <tr>
                            <td class="narrow">
                                <input type="checkbox" name="_doc_items" data-guid="{{$_file->_guid}}">
                            </td>
                            <td>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}', 'objectView');">
                  {{$_file}}
                </span>
                            </td>
                        </tr>
                    {{/foreach}}
                    {{foreach from=$object->_ref_documents item=_document name=documents}}
                        {{if $smarty.foreach.documents.first}}
                            <tr>
                                <th class="category" colspan="2">{{tr}}CCompteRendu|pl{{/tr}}</th>
                            </tr>
                        {{/if}}
                        <tr>
                            <td class="narrow">
                                <input type="checkbox" name="_doc_items" data-guid="{{$_document->_guid}}">
                            </td>
                            <td>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_document->_guid}}');">
                  {{$_document}}
                </span>
                            </td>
                        </tr>
                    {{/foreach}}
                {{else}}
                    <tr>
                        <td class="empty" colspan="2">
                            {{tr}}CDocumentItem.none{{/tr}}
                        </td>
                    </tr>
                {{/if}}
            </table>
        </div>
    {{/if}}

    <div style="overflow: auto;">
        <table style="width: 100%;" id="tabMail" class="tbl">
            <tr>
                <th class="title" colspan="4">
                    {{tr}}CDocumentItem-Choose destinataires{{/tr}}
                    {{if !$send_multiple_documents}}
                        - {{$object}}
                    {{/if}}
                </th>
            </tr>
            <tr>
                <th class="narrow"></th>
                <th>
                    {{tr}}Quality{{/tr}}
                </th>
                <th>
                    {{tr}}common-Name{{/tr}}
                </th>
                <th>
                    {{tr}}CCorrespondantPatient-_p_email-desc{{/tr}}
                </th>
            </tr>
            <tbody id="receivers_list">
            {{foreach from=$destinataires item=_destinataire}}
                <tr>
                    <td class="narrow checkbox">
                        <input type="checkbox" name="destinataire" data-object_guid="{{$_destinataire->object_guid}}">
                    </td>
                    <td class="tag" data-tag="{{$_destinataire->tag}}">
                        {{tr}}CDestinataire.tag.{{$_destinataire->tag}}{{/tr}}
                    </td>
                    <td class="name">
                        {{$_destinataire->nom}}
                    </td>
                    <td class="email">
                        {{$_destinataire->email}}
                    </td>
                </tr>
            {{/foreach}}
            {{if $app->_ref_user->destinataire_favori && $app->_ref_user->nom_destinataire_favori}}
                <tr>
                    <td>
                        <input type="checkbox" name="destinataire" data-object_guid="user_favorite">
                    </td>
                    <td>
                        {{tr}}CDestinataire.tag.favori{{/tr}}
                    </td>
                    <td>
                        {{$app->_ref_user->nom_destinataire_favori}}
                    </td>
                    <td>
                        {{$app->_ref_user->destinataire_favori}}
                    </td>
                </tr>
            {{/if}}
            </tbody>
        </table>
    </div>
    <div style="padding-top: 10px; width: 100%; text-align: center;">
        <button id="btn_send_mail" class="tick me-primary" type="button"
                onclick="sendMail();">{{tr}}CCompteRendu.send_mail{{/tr}}</button>
        <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </div>
</form>
