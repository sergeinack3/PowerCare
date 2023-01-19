{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset class="me-no-box-shadow">
  <legend>{{tr}}CInfoChecklistItem-title-send_to_patient{{/tr}}</legend>
  {{foreach from=$consult_ref->_refs_info_checklist item=_info name=infos_checklist}}
    {{if !$smarty.foreach.infos_checklist.first}}<br/>{{/if}}
    <form name="editInfo-{{$_info->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
      {{assign var=item value=$consult_ref->_ref_info_checklist_item}}
      {{if $_info->_item_id}}
        {{assign var=_item_id value=$_info->_item_id}}
        {{assign var=item value=$consult_ref->_refs_info_check_items.$_item_id}}
      {{/if}}
      {{mb_class object=$item}}
      {{mb_key   object=$item}}
      {{if !$item->_id}}
        {{mb_field object=$item field=info_checklist_id value=$_info->_id hidden=hidden}}
        {{mb_field object=$item field=consultation_id value=$consult_ref->_id hidden=hidden}}
        {{mb_field object=$item field=consultation_class value=$consult_ref->_class hidden=hidden}}
      {{/if}}
      {{mb_field object=$item field="reponse" onchange="this.form.onsubmit()" typeEnum=checkbox}}
      <label>
        {{$_info->libelle}}
      </label>
    </form>
  {{/foreach}}
</fieldset>