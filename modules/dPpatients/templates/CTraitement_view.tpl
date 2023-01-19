{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=patients script=traitements}}

{{mb_include template=CMbObject_view}}

{{if $object->annule == 1}}
  <table class="tbl">
    <tr>
      <th class="category cancelled" colspan="3">
        {{tr}}CTraitement-annule{{/tr}}
      </th>
    </tr>
  </table>
{{/if}}

{{if $object->_ref_dossier_medical->object_class == "CPatient"}}
  {{assign var=reload value="DossierMedical.reloadDossierPatient"}}
{{else}}
  {{assign var=reload value="DossierMedical.reloadDossierSejour"}}
{{/if}}

{{if !"dPpatients CAntecedent create_treatment_only_prat"|gconf || $app->user_prefs.allowed_to_edit_atcd ||
  $app->_ref_user->isPraticien() || $app->_ref_user->isSageFemme()}}
  <table class="form">
    <tr>
      <td class="button">
        <form name="Del-{{$object->_guid}}" action="?m=dPcabinet" method="post">
          <input type="hidden" name="m" value="dPpatients" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="dosql" value="do_traitement_aed" />

          {{mb_key object=$object}}

          <input type="hidden" name="traitement_id" value="{{$object->_id}}" />
          <input type="hidden" name="annule" value="" />

          {{if $object->annule == 0}}
            <button title="{{tr}}Cancel{{/tr}}" class="cancel" type="button"
                    onclick="Traitement.cancel(this.form, {{$reload}}); $('{{$object->_guid}}_tooltip').up('.tooltip').remove();">
              Stopper
            </button>
          {{else}}
            <button title="{{tr}}Restore{{/tr}}" class="tick" type="button"
                    onclick="Traitement.restore(this.form, {{$reload}}); $('{{$object->_guid}}_tooltip').up('.tooltip').remove();">
              {{tr}}Restore{{/tr}}
            </button>
          {{/if}}

          {{if $object->owner_id == $app->user_id}}
            <button title="{{tr}}Delete{{/tr}}" class="trash" type="button"
                    onclick="Traitement.remove(this.form, {{$reload}}); $('{{$object->_guid}}_tooltip').up('.tooltip').remove();">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        </form>
      </td>
    </tr>
  </table>
{{/if}}