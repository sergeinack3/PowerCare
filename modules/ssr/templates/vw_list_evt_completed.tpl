{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="list_evts_to_corrected">
  <script>
    changePage = function(page) {
      var url = new Url("ssr" , "vw_correct_evt_completed");
      url.addParam('page', page);
      url.requestUpdate("list_evts_to_corrected");
    };
    correctedEvts = function() {
      var url = new Url("ssr" , "vw_correct_evt_completed");
      url.addParam('clean', 1);
      url.requestUpdate("list_evts_to_corrected");
    };
  </script>
  {{if $clean}}
    <div class="small-info">
      {{$nb_corrected}} {{tr}}ssr-tools-seance_collectives_corrected{{/tr}}
    </div>
  {{/if}}

  <button type="button" class="cleanup" onclick="correctedEvts();">
    {{tr}}ssr-tools-clean_seance_collectives{{/tr}}
  </button>
  {{if $total_evts > 10}}
    {{mb_include module=system template=inc_pagination total=$total_evts current=$page step=50 change_page=changePage}}
  {{/if}}

  <table class="tbl">
    <tr>
      <th colspan="3" class="title">
        {{tr}}ssr-tools-correct_seance_collectives{{/tr}}
      </th>
    </tr>
    <tr>
      <th class="narrow">{{tr}}CEvenementSSR-therapeute_id{{/tr}}</th>
      <th>{{tr}}CEvenementSSR-debut{{/tr}}</th>
      <th>{{tr}}CEvenementSSR-duree{{/tr}}</th>
    </tr>
    {{foreach from=$evenements item=_evenement}}
      <tr>
        <td>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_evenement->_ref_therapeute}}
        </td>
        <td style="text-align:center;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_evenement->_guid}}')">
          {{mb_value object=$_evenement field=debut}}
        </span>
        </td >
        <td>{{mb_value object=$_evenement field=duree}} min</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CEvenementSSR-_no_evt_error{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>