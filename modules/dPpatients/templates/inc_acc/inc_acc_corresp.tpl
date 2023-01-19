{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$patient->_id}}
  <div class="small-info">
    {{tr}}CCorrespondantPatient-msg-create-fiche-patient{{/tr}}
  </div>
{{else}}
  <button type="button" class="add"
          onclick="Correspondant.edit(0, '{{$patient->_id}}', Correspondant.refreshList.curry('{{$patient->_id}}'))">
    {{tr}}CCorrespondantPatient-title-create{{/tr}}
  </button>
  <span>
    <form name="allow_corresp_patient" method="post"
          onsubmit="onSubmitFormAjax(this,{onComplete: Correspondant.refreshList.curry('{{$patient->_id}}')});">
      {{mb_key object=$patient}}
      {{mb_class object=$patient}}

       <input type="checkbox" name="_allow_pers_prevenir"
              {{if !$patient->allow_pers_prevenir}}checked{{/if}}
              onchange="$V(this.form.allow_pers_prevenir, this.checked ? 0 : 1); this.form.onsubmit();" /> {{mb_label object=$patient field="allow_pers_prevenir"}}
       <input class="me-margin-left-12" type="checkbox" name="_allow_pers_confiance"
              {{if !$patient->allow_pers_confiance}}checked{{/if}}
              onchange="$V(this.form.allow_pers_confiance, this.checked ? 0 : 1); this.form.onsubmit();" /> {{mb_label object=$patient field="allow_pers_confiance"}}

      <input type="hidden" name="allow_pers_prevenir" value="1">
      <input type="hidden" name="allow_pers_confiance" value="1">
    </form>
  </span>
  <div id="list-correspondants">
    {{mb_include module=patients template=inc_list_correspondants
    correspondants_by_relation=`$patient->_ref_cp_by_relation`
    nb_correspondants=$patient->_ref_correspondants_patient|@count}}
  </div>
{{/if}}