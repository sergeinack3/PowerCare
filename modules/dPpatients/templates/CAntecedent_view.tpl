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

{{mb_script module=patients script=antecedents}}

{{mb_include template=CMbObject_view}}

{{if "loinc"|module_active && $object->_ref_codes_loinc|@count}}
  <table class="form">
    <tr>
      <th class="category">
        {{tr}}CLoinc-Loinc Codes{{/tr}}
      </th>
    </tr>
    <tr>
      <td>
        {{foreach from=$object->_ref_codes_loinc item=_code name=count_code}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
          {{if !$smarty.foreach.count_code.last}},{{/if}}
        {{/foreach}}
      </td>
    </tr>
  </table>
{{/if}}

{{if "snomed"|module_active && $object->_ref_codes_snomed|@count}}
  <table class="form">
    <tr>
      <th class="category">
        {{tr}}CSnomed-Snomed Codes{{/tr}}
      </th>
    </tr>
    <tr>
      <td>
        {{foreach from=$object->_ref_codes_snomed item=_code name=count_code}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
          {{if !$smarty.foreach.count_code.last}},{{/if}}
        {{/foreach}}
      </td>
    </tr>
  </table>
{{/if}}

{{if $object->_ref_hypertext_links|@count}}
  <table class="tbl">
    <tr>
      <th {{if $object->owner_id == $app->user_id}}colspan="2"{{/if}}>
        Liens
      </th>
    </tr>
    {{foreach from=$object->_ref_hypertext_links item=_link}}
      <tr>
        {{if $object->owner_id == $app->user_id}}
          <td class="narrow">
            <form name="delLink{{$_link->_id}}" method="post"
                  onsubmit="return onSubmitFormAjax(this, (function() { this.up('tr').remove(); }).bind(this));">
              {{mb_class object=$_link}}
              {{mb_key object=$_link}}
              <input type="hidden" name="del" value="1" />
              <button type="button" class="trash notext compact" onclick="this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
            </form>
          </td>
        {{/if}}
        <td>
          <a href="{{$_link->link}}" target="_blank">{{$_link->name}}</a>
        </td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}

{{if $object->annule == 1}}
  <table class="tbl">
    <tr>
      <th class="category cancelled" colspan="3">
        {{tr}}CAntecedent-annule{{/tr}}
      </th>
    </tr>
  </table>
{{/if}}

{{assign var=dossier_medical value=$object->_ref_dossier_medical}}
{{if $object->_can->edit}}
  <table class="form"
   {{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_atcd &&
     !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}style="display: none;" {{/if}}>
    <tr>
      <td class="button">
        {{if $dossier_medical->object_class == "CPatient"}}
          {{assign var=reload value="DossierMedical.reloadDossierPatient"}}
        {{else}}
          {{assign var=reload value="DossierMedical.reloadDossierSejour"}}
        {{/if}}

        <form name="Del-{{$object->_guid}}" action="?m=dPcabinet" method="post">
          <input type="hidden" name="m" value="patients" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="dosql" value="do_antecedent_aed" />

          {{mb_key object=$object}}

          <input type="hidden" name="annule" value="" />
          <input type="hidden" name="reload" value="{{$reload}}" />

          {{if $object->annule == 0}}
            <button title="{{tr}}Cancel{{/tr}}" class="cancel" type="button"
                    onclick="Antecedent.cancel(this.form, {{$reload}}); Antecedent.closeTooltip('{{$object->_guid}}');">
              {{tr}}Cancel{{/tr}}
            </button>
          {{else}}
            <button title="{{tr}}Restore{{/tr}}" class="tick" type="button"
                    onclick="Antecedent.restore(this.form, {{$reload}}); Antecedent.closeTooltip('{{$object->_guid}}');">
              {{tr}}Restore{{/tr}}
            </button>
          {{/if}}

          {{if $object->owner_id == $app->user_id}}
            {{if $dossier_medical->object_class == "CPatient"}}
              <button type="button" class="edit"
                      onclick="Antecedent.editAntecedents('{{$dossier_medical->object_id}}', '', '{{$reload}}', '{{$object->_id}}')">
                {{tr}}Edit{{/tr}}
              </button>
            {{/if}}
            <button title="{{tr}}Delete{{/tr}}" class="trash" type="button"
                    onclick="Antecedent.remove(this.form, {{$reload}}); Antecedent.closeTooltip('{{$object->_guid}}');">
              {{tr}}Delete{{/tr}}
            </button>
          {{elseif $object->annule == 0 && $dossier_medical->object_class == "CPatient"}}
            <button title="{{tr}}Delete{{/tr}}" class="duplicate" type="button"
                    onclick="Antecedent.duplicate(this.form); Antecedent.closeTooltip('{{$object->_guid}}');">
              {{tr}}Cancel{{/tr}} {{tr}}and{{/tr}} {{tr}}Modify{{/tr}}
            </button>
          {{/if}}

            {{if "loinc"|module_active || "snomed"|module_active}}
              <button type="button" title="{{tr}}CAntecedent-Nomenclature|pl-desc{{/tr}}" onclick="Antecedent.showNomenclatures('{{$object->_guid}}');">
                <i class="far fa-eye"></i> {{tr}}CAntecedent-Nomenclature|pl{{/tr}}
              </button>
            {{/if}}
        </form>
      </td>
    </tr>
  </table>
{{/if}}
