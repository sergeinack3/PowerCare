{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    DiagPMSI.getAutocompleteCim10(getForm("editDR"), getForm("editDR").DR, null{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, '{{$sejour->type}}', 'dr'{{/if}});
  });
</script>

<!--  Diagnostic Relié avec CIM10 à visée PMSI-->
<table class="tbl">
  <tr>
    <th class="category" colspan="2">{{tr}}PMSI.Diagnostic Relie{{/tr}}</th>
  </tr>
  <!--  Diagnostic Relié avec CIM10 OMS (séjour)-->
  <tr>
    <td class="narrow">
      <form name="editDR" action="?m={{$m}}" method="post"
          onsubmit="return onSubmitFormAjax(this, PMSI.afterEditDiag.curry('{{$sejour->_id}}', {{$modal}}))">
        <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
        <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
        {{mb_key object=$sejour}}
        {{mb_class object=$sejour}}

        <label for="keywords_code" title="{{tr}}PMSI-dr-desc{{/tr}}">{{tr}}PMSI-cim10-oms{{/tr}}</label>
        <input type="text" name="keywords_code" class="autocomplete str code cim10" value="{{$sejour->DR}}" size="10"/>
        <input type="hidden" name="DR" onchange="this.form.onsubmit();"/>
        <button class="search notext me-tertiary" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), this.form.elements['_praticien_id']{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dr'{{/if}});">
          {{tr}}Search{{/tr}}
        </button>
      </form>
    </td>
  </tr>

  <tr>
    <!--  Diagnostic Relié avec CIM10 OMS (séjour)-->
    <td>
      {{if $sejour->_ext_diagnostic_relie}}
        <ul class="tags" style="float: none; display: inline-block;">
          <li class="tag me-tag" style="white-space:normal">
            <button type="button" class="delete notext me-tertiary" onclick="DiagPMSI.deleteDiag(getForm('editDR'), 'DR');" style="display: inline-block !important;"></button>
              {{$sejour->_ext_diagnostic_relie->code}} - {{$sejour->_ext_diagnostic_relie->libelle|smarty:nodefaults}}
          </li>
        </ul>
        {{if $codes_dr|@count && in_array($sejour->_ext_diagnostic_relie->code, $codes_dr)}}
          <div class="small-warning" style="margin-left: 10px; display: inline-block; width: 20%;">{{tr}}CCIM10-Prohibited diagnosis{{/tr}}</div>
        {{/if}}
      {{/if}}
    </td>
  </tr>
</table>