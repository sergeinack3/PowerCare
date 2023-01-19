{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=MailFacture ajax=true}}

<form name="send{{$facture->_guid}}ByMail" method="post" action="?" onsubmit="return false;">
    <input type="hidden" name="facture_class" value="{{$facture->_class}}">
    <input type="hidden" name="facture_id" value="{{$facture->_id}}">

    <table class="form">
        <tr>
            {{me_form_field label='CCompteRendu.mail_subject' nb_cells=1}}
                <input type="text" name="subject" value="{{$subject}}">
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field label='CCompteRendu.mail_body' nb_cells=1}}
                <textarea name="body" cols="30" rows="5">{{$body|smarty:nodefaults}}</textarea>
            {{/me_form_field}}
        </tr>
    </table>

    <table class="tbl">
        <tr>
            <th class="title" colspan="4">
                Sélection des destinataires
            </th>
        </tr>
        <tr>
            <th class="narrow"></th>
            <th>{{tr}}Quality{{/tr}}</th>
            <th>{{tr}}Name{{/tr}}</th>
            <th>{{tr}}CSourceSMTP-email{{/tr}}</th>
        </tr>
        <tbody id="receivers_list">
            {{foreach from=$receivers item=_receiver_by_class}}
                {{foreach from=$_receiver_by_class item=_receiver}}
                    <tr>
                        <td class="narrow checkbox">
                            <input type="checkbox" name="receivers" data-object_guid="{{$_receiver->object_guid}}"/>
                        </td>
                        <td class="tag" data-tag="{{$_receiver->tag}}">
                            {{tr}}CDestinataire.tag.{{$_receiver->tag}}{{/tr}}
                        </td>
                        <td class="name">{{$_receiver->nom}}</td>
                        <td class="email">{{$_receiver->email}}</td>
                    </tr>
                {{/foreach}}
            {{/foreach}}
            <tr class="manual_input">
                <td class="narrow checkbox">
                    <input type="checkbox" name="receivers" data-object_guid="receiver_input"/>
                </td>
                <td data-tag="{{tr}}CDestinataire.tag.autre{{/tr}}" class="tag">
                    {{tr}}CDestinataire.tag.autre{{/tr}}
                </td>
                <td class="name">
                    <input type="text" name="name" value="">
                </td>
                <td class="email">
                    <input type="text" name="email" value="" onkeyup="MailFacture.addAddressLine(this); MailFacture.toggleAddressCheckbox(this);">
                </td>
            </tr>
        </tbody>
    </table>

    <div style="padding: 10px 0px; width: 100%; text-align: center;">
        <button id="btn_send_facture_mail" class="tick me-primary" type="button"
                onclick="MailFacture.send(this.form);">{{tr}}CCompteRendu.send_mail{{/tr}}</button>
        <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </div>
</form>
