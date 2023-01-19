{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=act_view value=false}}
{{mb_default var=linked value=false}}

{{if $act->_class == 'CActeCCAM'}}
    {{mb_include module=jfse template=medical_act/act_ccam_line act=$act act_view=$act_view}}
{{elseif $act->_class == 'CActeLPP'}}
    {{mb_include module=jfse template=medical_act/act_lpp_line act=$act act_view=$act_view}}
{{elseif $act->_class == 'CActeNGAP'}}
    {{mb_include module=jfse template=medical_act/act_ngap_line act=$act act_view=$act_view}}
{{/if}}
<td class="narrow">
    {{if $linked}}
        <button type="button" class="edit notext" onclick="MedicalActs.editAct('{{$invoice->id}}', '{{$act->_guid}}')">{{tr}}Edit{{/tr}}</button>
    {{/if}}
    {{if $consultation->valide !== '1'}}
        <form name="deleteAct-{{$act->_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Invoicing.reload.bind(Invoicing, '{{$consultation->_id}}', '{{$invoice->id}}'));">
            {{mb_class object=$act}}
            {{mb_key   object=$act}}
            <input type="hidden" name="del" value="1" />
            <button type="button" class="trash notext" onclick="this.form.onsubmit();"{{if !$invoice->data_model->isPending()}} disabled="disabled"{{/if}}>{{tr}}Delete{{/tr}}</button>
        </form>
    {{/if}}
</td>
