{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pathologie ajax=true}}

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

{{assign var=dossier_medical value=$object->_ref_dossier_medical}}

<table class="form">
  <tr>
    <td class="button">
      <form name="Del-{{$object->_guid}}" action="?m=dPcabinet" method="post">
        <input type="hidden" name="m" value="patients" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_pathologie_aed" />

        {{mb_field object=$object field=annule hidden=true}}
        {{mb_field object=$object field=resolu hidden=true}}

        {{mb_key object=$object}}

        {{if $object->annule == 0}}
          <button title="{{tr}}CPathologie-add_atcd{{/tr}}" class="add" type="button"
                  onclick="Pathologie.addAtcd(this.form);">
            {{tr}}CPathologie-add_atcd{{/tr}}
          </button>
          {{if $object->resolu == 0}} <!-- Not resolved, not canceled -->
            <button title="{{tr}}Cancel{{/tr}}" class="cancel" type="button" onclick="Pathologie.cancel(this.form);">
              {{tr}}Cancel{{/tr}}
            </button>
            <button title="{{tr}}Resolve{{/tr}}" class="tick" type="button" onclick="Pathologie.resolve(this.form);">
              {{tr}}Resolve{{/tr}}
            </button>
          {{else}} <!-- Resolved, not canceled -->
            <button title="{{tr}}Unresolve{{/tr}}" class="undo" type="button" onclick="Pathologie.unresolve(this.form);">
              {{tr}}Unresolve{{/tr}}
            </button>
          {{/if}}

        {{else}} <!-- Canceled, not resolved -->
          <button title="{{tr}}Restore{{/tr}}" class="undo" type="button" onclick="Pathologie.restore(this.form);">
            {{tr}}Restore{{/tr}}
          </button>
        {{/if}}


        {{if $object->owner_id == $app->user_id}}
          <button type="button" class="edit" onclick="Pathologie.edit('{{$object->_id}}');">
            {{tr}}Edit{{/tr}}
          </button>
          <button title="{{tr}}Delete{{/tr}}" class="trash" type="button" onclick="Pathologie.remove(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{elseif $object->annule == 0 && $dossier_medical->object_class == "CPatient"}}
          <button title="{{tr}}Delete{{/tr}}" class="duplicate" type="button" onclick="Pathologie.duplicate(this.form);">
            {{tr}}Cancel{{/tr}} {{tr}}and{{/tr}} {{tr}}Modify{{/tr}}
          </button>
        {{/if}}
      </form>

       {{if "loinc"|module_active || "snomed"|module_active}}
          <button type="button" title="{{tr}}CPathologie-Nomenclature|pl-desc{{/tr}}" onclick="Pathologie.showNomenclatures('{{$object->_guid}}');">
            <i class="far fa-eye"></i> {{tr}}CPathologie-Nomenclature|pl{{/tr}}
          </button>
       {{/if}}
    </td>
  </tr>
</table>