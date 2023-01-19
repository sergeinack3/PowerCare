{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file ajax=$ajax}}

<script>
  Main.add(function () {
    File.controleTab('tab-group');
  })

</script>
<table class="tbl">
  <tr>
    <th colspan="2">{{tr}}CFile-_count{{/tr}}</th>
    <th colspan="2">{{tr}}CFile-_total_weight{{/tr}}</th>
    <th>{{tr}}CFile-_average_weight{{/tr}}</th>
      {{if $is_doc}}
        <th>{{tr}}CCompteRendu-duree_lecture_average{{/tr}}</th>
        <th>{{tr}}CCompteRendu-duree_ecriture_average{{/tr}}</th>
      {{/if}}
    <th>{{tr}}Owner{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  <tr style="font-weight: bold;">
    <td class="narrow" style="text-align: right;">{{$total.docs_count|integer}}</td>
    <td class="narrow" style="text-align: right;">{{1|percent}}</td>
    <td class="narrow" style="text-align: right;">{{$total.docs_weight|decabinary}}</td>
    <td class="narrow" style="text-align: right;">{{1|percent}}</td>
    <td class="narrow" style="text-align: right;">{{$total._docs_average_weight|decabinary}}</td>
      {{if $is_doc}}
        <td class="narrow" style="text-align: center;">{{$total.docs_average_read_time}}</td>
        <td class="narrow" style="text-align: center;">{{$total.docs_average_write_time}}</td>
      {{/if}}
    <td>{{tr}}Total{{/tr}}
    <td>
      <button class="search notext compact me-tertiary me-dark" type="button"
              onclick="Details.statOwner('{{$doc_class}}', null, null, null, null, null, '{{$factory}}');">
          {{tr}}Details{{/tr}}
      </button>
      <button class="stats notext compact me-tertiary" type="button"
              onclick="Details.statPeriodicalOwner('{{$doc_class}}', null, null, null, null, null, '{{$factory}}');">
          {{tr}}Periodical details{{/tr}}
      </button>
    </td>
  </tr>

  <tbody id="tab-group" >
  {{mb_include template=inc_stats_owner stats=$groups_stats}}
  </tbody>

  <tbody id="tab-func" style="display:none">
  {{mb_include template=inc_stats_owner stats=$funcs_stats}}
  </tbody>

  <tbody id="tab-user" style="display:none">
  {{mb_include template=inc_stats_owner stats=$users_stats}}
  </tbody>
</table>
