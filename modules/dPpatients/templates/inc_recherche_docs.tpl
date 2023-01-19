{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    $("results_docs").makeIntuitiveCheck("line", "doc");
  });
</script>

{{mb_include module=system template=inc_pagination change_page="RechercheDoc.listDocs" total=$total current=$page step=30}}

<table id="results_docs" class="tbl">
  <tr>
    <th class="title" colspan="7">{{tr}}mod-dPpatients-tab-vw_all_docs{{/tr}}</th>
  </tr>

  <tr>
    <th class="narrow">
      <input type="checkbox" onclick="$$('input[type=checkbox].doc').invoke('writeAttribute', 'checked', this.checked)" />
    </th>
    <th class="narrow">{{mb_label class=CCompteRendu field="creation_date"}}</th>
    <th style="width: 15%">{{tr}}Patient{{/tr}}</th>
    <th style="width: 15%">{{mb_label class=CCompteRendu field="object_class"}}</th>
    <th style="width: 40%">{{mb_label class=CCompteRendu field="nom"}}</th>
    <th>{{tr}}CMediusers-in_charge{{/tr}}</th>
    <th>{{tr}}Owner{{/tr}}</th>
  </tr>

  {{foreach from=$documents item=_document}}
    {{assign var=patient value=$_document->_ref_object->_ref_patient}}
    <tr class="line">
      <td>
        <input type="checkbox" class="doc" value="{{$_document->_id}}" data-guid="{{$_document->_guid}}" data-object_class="{{$_document->object_class}}"
               data-object_id="{{$_document->object_id}}" />
      </td>
      <td>
        {{mb_value object=$_document field=creation_date}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient}}
          </span>
      </td>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_document->_ref_object->_guid}}');">
          {{$_document->_ref_object}}
        </span>
      </td>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_document->_guid}}');">
          {{$_document}}
        </span>
      </td>
      <td>
        {{if $_document->_ref_object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'
        || $_document->_ref_object|instanceof:'Ox\Mediboard\Cabinet\CConsultAnesth'
        || $_document->_ref_object|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_document->_ref_object->_ref_chir->_guid}}');">
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_document->_ref_object->_ref_chir}}
          </span>
        {{elseif $_document->_ref_object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_document->_ref_object->_ref_praticien->_guid}}');">
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_document->_ref_object->_ref_praticien}}
          </span>
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_document->_ref_author->_guid}}');">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_document->_ref_author}}
        </span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="7">{{tr}}CCompteRendu.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>