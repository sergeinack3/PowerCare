{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $long_period}}
  <div class="small-warning">{{tr}}CCompteRendu-alert_long_period{{/tr}}</div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=inc_pagination total=$total_docs current=$page change_page="changePage" step=30}}

<table class="tbl">
  <tr>
    <th class="title" colspan="{{if $section_search == "sejour"}}7{{else}}6{{/if}}">
      Liste des documents <small>({{$total_docs}})</small>
    </th>
  </tr>
  <tr>
    {{if $section_search == "sejour"}}
      <th>NDA</th>
    {{/if}}
    <th>{{mb_label class=CSejour field=patient_id}}</th>
    {{if $section_search == "sejour"}}
      <th>{{mb_label class=CSejour field=_motif_complet}}</th>
      <th>{{mb_label class=CSejour field=entree}}</th>
      <th>{{mb_label class=CSejour field=sortie}}</th>
    {{else}}
      <th>{{mb_label class=COperation field=date}}</th>
      <th>{{mb_label class=COperation field=chir_id}}</th>
      <th>{{mb_label class=COperation field=libelle}}</th>
    {{/if}}
    <th>{{mb_label class=CCompteRendu field=nom}}</th>
    <th>{{mb_label class=CCompteRendu field=_date}}</th>
  </tr>
  
  {{foreach from=$docs item=_doc}}
    {{assign var=ref_object value=$_doc->_ref_object}}
    {{assign var=patient value=$ref_object->_ref_patient}}
    
    <tr>
      {{if $section_search == "sejour"}}
        <td>
          {{$ref_object->_NDA_view}}
        </td>
      {{/if}}
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
          {{$patient->_view}}
        </span>
      </td>
      {{if $section_search == "sejour"}}
        <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$ref_object->_guid}}')">
            {{mb_value object=$ref_object field=_motif_complet}}
          </span>
        </td>
        <td>
          {{mb_value object=$ref_object field=entree}}
        </td>
        <td>
          {{mb_value object=$ref_object field=sortie}}
        </td>
      {{else}}
        <td>
          {{mb_value object=$ref_object field=_datetime}}
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$ref_object->_ref_chir->_guid}}')">
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$ref_object->_ref_chir}}
          </span>
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$ref_object->_guid}}')">
            {{if $ref_object->libelle}}
              {{mb_value object=$ref_object field=libelle}} <br />
            {{/if}}
            {{foreach from=$ref_object->_ext_codes_ccam item=_code name=codes}}
            {{$_code->code}}
              {{if !$smarty.foreach.codes.last}}&mdash;{{/if}}
            {{/foreach}}
          </span>
        </td>
      {{/if}}
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_doc->_guid}}')">
          {{$_doc}}
        </span>
      </td>
      <td>
        {{mb_value object=$_doc field=_date}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="{{if $section_search == "sejour"}}7{{else}}6{{/if}}">{{tr}}CCompteRendu.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>