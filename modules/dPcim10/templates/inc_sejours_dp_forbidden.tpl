{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePage = function (page) {
    Control.Modal.close();
    listDPforbidden(page);
  };

  listDPforbidden = function (page) {
    new Url("cim10", "ajax_sejours_dp_forbidden")
      .addParam('page', page)
      .requestModal("60%", "70%");
  };
</script>

{{mb_include module=system template=inc_pagination total=$nb_sejours current=$page change_page='changePage' step=25}}

<table class="tbl">
  <tr>
    <th colspan="5" class="title">{{tr}}CCodeCIM10-action-List of stays with a prohibited DP{{/tr}} ({{$nb_sejours}})</th>
  </tr>
  <tr>
    <th rowspan="2">{{tr}}CPatient{{/tr}}</th>
    <th rowspan="2">{{tr}}CSejour{{/tr}}</th>
    <th colspan="3">{{tr}}Type{{/tr}}</th>
  </tr>
  <tr>
    <th title="{{tr}}PMSI.Diagnostic Principal{{/tr}}">{{tr}}CCIM10.DP{{/tr}}</th>
    <th title="{{tr}}PMSI.Diagnostic Relie{{/tr}}">{{tr}}CCIM10.DR{{/tr}}</th>
    <th title="{{tr}}PMSI.Diagnostic Associe{{/tr}}">{{tr}}CCIM10.DAS{{/tr}}</th>
  </tr>
  {{foreach from=$sejours item=_sejour}}
    <tr>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_ref_patient->_guid}}')"><strong>{{$_sejour->_ref_patient->_view}}</strong></span>
      </td>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')"><strong>{{$_sejour->_shortview}}</strong></span></td>
      <td>
        <img src="images/icons/note_red.png" title="{{tr}}CCIM10-Prohibited diagnosis{{/tr}}"> {{$_sejour->DP}}
      </td>
      <td>
        {{if !$_sejour->DR}}
          <img src="images/icons/no.png" width="16" height="16" title="{{tr}}CCIM10-Diagnosis not indicated{{/tr}}" />
        {{elseif !in_array($_sejour->DR, $codes)}}
          <img title="{{tr}}CCIM10.type.0{{/tr}}" src="images/icons/note_green.png"> {{$_sejour->DR}}
        {{else}}
          <img title="{{tr}}CCIM10-Prohibited diagnosis{{/tr}}" src="images/icons/note_red.png"> {{$_sejour->DR}}
        {{/if}}
      </td>
      <td>
        {{if !$_sejour->_ref_dossier_medical->_codes_cim|@count}}
          <img src="images/icons/no.png" width="16" height="16" title="{{tr}}CCIM10-Diagnosis not indicated{{/tr}}" />
        {{else}}
          {{foreach from=$_sejour->_ref_dossier_medical->_ext_codes_cim item=curr_cim}}
            {{if !in_array($curr_cim->code, $codes)}}
              <img title="{{tr}}CCIM10.type.0{{/tr}}" src="images/icons/note_green.png"> {{$curr_cim->code}}
            {{else}}
              <img title="{{tr}}CCIM10-Prohibited diagnosis{{/tr}}" src="images/icons/note_red.png"> {{$curr_cim->code}}
            {{/if}}
          {{/foreach}}
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
