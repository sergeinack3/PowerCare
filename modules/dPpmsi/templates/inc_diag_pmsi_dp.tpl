{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    DiagPMSI.getAutocompleteCim10(getForm("editDP"), getForm("editDP").DP, null{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, '{{$sejour->type}}', 'dp'{{/if}});
  });
</script>

<table class="tbl">
  <tr>
    <th class="category" colspan="2">{{tr}}PMSI.Diagnostic Principal{{/tr}}</th>
  </tr>
  <!--  Diagnostic Principal OMS (séjour)-->
  <tr>
    <td class="narrow">
      <form name="editDP" action="?m={{$m}}" method="post"
            onsubmit="return onSubmitFormAjax(this, PMSI.afterEditDiag.curry('{{$sejour->_id}}', {{$modal}}, '{{$rss_id}}'));">
        {{mb_key object=$sejour}}
        {{mb_class object=$sejour}}
        <input type="hidden" name="_praticien_id" value="{{$sejour->praticien_id}}" />
        <label for="keywords_code" title="{{tr}}PMSI-dp-desc{{/tr}}">{{tr}}PMSI-cim10-oms{{/tr}}</label>
        <input type="text" name="keywords_code" class="autocomplete str  code cim10" value="{{$sejour->DP}}" size="10"/>
        <input type="hidden" name="DP" onchange="this.form.onsubmit();"/>
        <button class="search notext me-tertiary" type="button" onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), this.form.elements['_praticien_id']{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, '{{$sejour->type}}', 'dp'{{/if}});">
          {{tr}}Search{{/tr}}
        </button>
        </form>
    </td>
  </tr>

  <tr>
    <!--  Diagnostic Principal avec CIM-10 OMS (séjour)-->
    <td>
      {{if $sejour->_ext_diagnostic_principal}}
        <ul class="tags" style="float: none; display: inline-block;">
          <li class="tag me-tag" style="white-space:normal">
            <button type="button" class="delete notext me-tertiary" onclick="DiagPMSI.deleteDiag(getForm('editDP'), getForm('editDP').DP);" style="display: inline-block !important;"></button>
              {{$sejour->_ext_diagnostic_principal->code}} - {{$sejour->_ext_diagnostic_principal->libelle|smarty:nodefaults}}
          </li>
        </ul>
        {{if $codes_dp|@count && in_array($sejour->_ext_diagnostic_principal->code, $codes_dp)}}
          <div class="small-warning" style="margin-left: 10px; display: inline-block;">{{tr}}CCIM10-Prohibited diagnosis{{/tr}}</div>
        {{/if}}
      {{/if}}
    </td>
  </tr>
</table>