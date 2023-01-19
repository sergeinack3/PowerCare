{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="{{$form_name}}" method="post"
      onsubmit="return onSubmitFormAjax(this, function() { if (window.reloadSortieLine) { reloadSortieLine('{{$ext_sejour->_id}}')}})">
  {{mb_class object=$ext_sejour}}
  {{mb_key object=$ext_sejour}}
  <input type="hidden" name="view_patient" value="{{$ext_sejour->_ref_patient}}">
  <input type="hidden" name="del" value="0" />
  {{mb_field object=$ext_sejour field=entree_reelle hidden=true}}
  {{mb_field object=$ext_sejour field=sortie_reelle hidden=true}}
  {{mb_field object=$ext_sejour field=entree_prevue hidden=true}}
  {{mb_field object=$ext_sejour field=sortie_prevue hidden=true}}
  {{mb_field object=$ext_sejour field=mode_sortie hidden=true}}
  {{mb_field object=$ext_sejour field=confirme hidden=true}}
  {{mb_field object=$ext_sejour field=confirme_user_id hidden=true}}
  {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $list_mode_sortie|@count}}
    {{mb_field object=$ext_sejour field=mode_sortie_id hidden=true}}
  {{/if}}
  {{if $ext_sejour->_sejours_enfants_ids}}
    <input type="hidden" name="_sejours_enfants_ids" value="{{"|"|implode:$ext_sejour->_sejours_enfants_ids}}" />
  {{/if}}
</form>