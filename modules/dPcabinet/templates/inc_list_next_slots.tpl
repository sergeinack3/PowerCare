{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=plage_selector}}

{{assign var=plage_count value=0}}

<script>
  Main.add(function() {
    $("listMediusersDiv").fixedTableHeaders();
  });
</script>

<div id="listMediusersDiv">
  <table class="tbl">
    <tbody id="table_time_slot"></tbody>

    <thead>
    <tr>
      <th class="title" colspan="4">{{tr}}CPlageconsult-Free slot|pl{{/tr}}</th>
    </tr>
    <tr>
      <th style="width: 30%;">{{tr}}Day{{/tr}}</th>
      <th style="width: 30%;">{{tr}}common-Hour{{/tr}}</th>
      <th style="width: 30%;">{{tr}}common-Practitioner{{/tr}}</th>
      <th class="narrow"></th>
    </tr>
    </thead>
  </table>
</div>
