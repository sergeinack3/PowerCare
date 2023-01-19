{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=0}}
{{mb_default var=header   value=1}}

<script>
 {{if ($sejour->_count_rdv_externe !== null)}}
    if ($('rdv_externe')) {
      Control.Tabs.setTabCount('rdv_externe', {{$sejour->_count_rdv_externe}});
    }
    var rdvs_count_span = $('rdvs_count');
    if (rdvs_count_span) {
      rdvs_count_span.update('{{$sejour->_count_rdv_externe}}');
    }
{{/if}}
</script>

{{if !$readonly}}
  <button type="button" class="add me-margin-8" onclick="Soins.editRDVExterne('0', '{{$sejour->_id}}');">
    {{tr}}CRDVExterne-Creating an external event{{/tr}}
  </button>
{{/if}}

{{assign var=rdvs_externe value=$sejour->_refs_rdv_externes}}
{{assign var=patient value=$sejour->_ref_patient}}
<table class="tbl print_tasks me-no-align me-no-box-shadow">
  {{if $header}}
    <tr>
      <th class="title" colspan="8">
        {{tr}}CRDVExterne-List of external RDV|pl{{/tr}}
        <label style="float: right; font-size: 0.8em; font-weight: normal;">
          <input type="checkbox" name="show_canceled" onchange="$$('tr.hatching').invoke('toggle');">
            {{tr}}common-action-Show canceled{{/tr}}
        </label>
      </th>
    </tr>
  {{/if}}
  <tr>
    {{if !$readonly}}
      <th></th>
    {{/if}}
    <th>{{mb_label class=CRDVExterne field=libelle}}</th>
    <th class="text">{{mb_label class=CRDVExterne field=description}}</th>
    {{if !$readonly}}
      <th>{{tr}}CCompteRendu|pl{{/tr}}</th>
    {{/if}}
    <th class="narrow">{{mb_label class=CRDVExterne field=date_debut}}</th>
    <th class="narrow">{{mb_label class=CRDVExterne field=duree}}</th>
    <th class="narrow">{{mb_label class=CRDVExterne field=statut}}</th>
    <th class="text">{{mb_label class=CRDVExterne field=commentaire}}</th>
  </tr>
  {{foreach from=$rdvs_externe item=_rdv}}
    <tr {{if $_rdv->statut == "annule"}}class="hatching" style="display: none;"{{/if}}>
      {{if !$readonly}}
        <td class="narrow">
          <button type="button" class="edit notext" onclick="Soins.editRDVExterne('{{$_rdv->_id}}', '{{$sejour->_id}}');">
            {{tr}}CSejourTask-title-create{{/tr}}
          </button>
        </td>
      {{/if}}
      <td>{{mb_value object=$_rdv field=libelle}}</td>
      <td>{{mb_value object=$_rdv field=description}}</td>
      {{if !$readonly}}
        <td>
          {{mb_include module=patients template=inc_button_add_doc context_guid=$_rdv->_guid patient_id=$patient->_id
          callback="function(){Soins.showRDVExternal('`$sejour->_id`');}"}}
          {{mb_include module=patients template=inc_widget_count_documents object=$_rdv patient_id=$patient->_id
          show_object_class=false
          hide_hatching=true callback="function(){Soins.showRDVExternal('`$sejour->_id`');}"}}
        </td>
      {{/if}}
      <td>{{mb_value object=$_rdv field=date_debut}}</td>
      <td>{{mb_value object=$_rdv field=duree}}</td>
      <td>{{mb_value object=$_rdv field=statut}}</td>
      <td>{{mb_value object=$_rdv field=commentaire}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CRDVExterne.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
