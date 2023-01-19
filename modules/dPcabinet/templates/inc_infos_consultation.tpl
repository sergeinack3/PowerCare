{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $sejour->_canRead}}
  <script>
    refreshListConsults = function () {
      {{if !$sejour->_id}}
      return false;
      {{else}}
      var url = new Url("dPcabinet", "refreshListConsultationsSejour");
      url.addParam("sejour_id", {{$sejour->_id}});
      url.requestUpdate("consults-sejour-{{$sejour->_guid}}");
      {{/if}}
    };
  </script>

  <table class="tbl me-table-card-list">
    <tr>
      <th class="title" colspan="4">
        {{tr}}CSejour-back-consultations{{/tr}} de séjour
      </th>
    </tr>
    <tr>
      <th></th>
      <th>{{mb_label class=CPlageconsult field="chir_id"}}</th>
      <th>{{tr}}Date{{/tr}}</th>
      <th>{{tr}}Hour{{/tr}}</th>
    </tr>
    <tbody id="consults-sejour-{{$sejour->_guid}}">
      {{mb_include module=cabinet template=inc_infos_consultation_sejour}}
    </tbody>
  </table>
{{elseif $sejour->_id}}
  <div class="small-info">Vous n'avez pas accès au détail des consultations.</div>
{{/if}}



