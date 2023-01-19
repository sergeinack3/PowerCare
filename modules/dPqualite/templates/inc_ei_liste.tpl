{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var list = $("tab-incident").select('a[href="#{{$type}}"] span');
    list.last().update("{{$countFiches}}");
  });
</script>

{{if $countFiches > 20}}
  <div style="text-align: right;">
    {{if $first >= 20}}
      <a href="#1" onclick="loadListFiches('{{$type}}', '{{$first-20}}')" style="font-weight: bold; font-size: 1.5em; float: left;">
        [{{$first-19}} - {{$first}}] &lt;&lt;
      </a>
    {{/if}}
    {{if $first < $countFiches - 20}}
      <a href="#1" onclick="loadListFiches('{{$type}}', '{{$first+20}}')" style="font-weight: bold; font-size: 1.5em;">
        &gt;&gt; [{{$first+21}} - {{$first+40}}]
      </a>
    {{/if}}
  </div>
{{/if}}

<table class="tbl me-no-align me-no-border-radius-top" style="clear: both;">
  <tr>
    <th class="category">#</th>
    <th class="category">{{tr}}Date{{/tr}}</th>
    <th class="category">{{tr}}CFicheEi-user_id-court{{/tr}}</th>
    <th class="category">{{tr}}CFicheEi-service_valid_user_id-court{{/tr}}</th>
    <th class="category">{{tr}}CFicheEi-degre_urgence-court{{/tr}}</th>
    <th class="category">{{tr}}CFicheEi-_criticite-court{{/tr}}</th>
    <th class="category">{{tr}}CFicheEi-qualite_date_verification-court{{/tr}}</th>
    <th class="category">{{tr}}CFicheEi-qualite_date_controle-court{{/tr}}</th>
  </tr>
  {{foreach from=$listeFiches item=currFiche}}
    <tr {{if $currFiche->_id == $selected_fiche_id}}class="selected"{{/if}}>
      <td>{{$currFiche->_id}}</td>
      <td class="text">
        <a href="?m=qualite&tab=vw_incidentvalid&fiche_ei_id={{$currFiche->fiche_ei_id}}">
          {{$currFiche->date_incident|date_format:$conf.datetime}}
        </a>
      </td>
      <td class="text">
        <a href="?m=qualite&tab=vw_incidentvalid&fiche_ei_id={{$currFiche->fiche_ei_id}}">
          {{if $conf.dPqualite.CFicheEi.mode_anonyme && !$modules.dPcabinet->_can->admin && ($currFiche->_ref_user->user_id != $app->user_id)}}
            Anonyme
          {{else}}
            {{$currFiche->_ref_user}}
          {{/if}}
        </a>
      </td>
      <td class="text">
        <a href="?m=qualite&tab=vw_incidentvalid&fiche_ei_id={{$currFiche->fiche_ei_id}}">
          {{$currFiche->_ref_service_valid->_view}}
        </a>
      </td>
      <td>
        {{if $currFiche->degre_urgence}}
          {{$currFiche->degre_urgence}}
        {{else}}-{{/if}}
      </td>
      <td>
        {{if $currFiche->_criticite}}
          {{$currFiche->_criticite}}
        {{else}}-{{/if}}
      </td>
      <td>
        {{if $currFiche->qualite_date_verification}}
          {{$currFiche->qualite_date_verification|date_format:$conf.date}}
        {{else}}-{{/if}}
      </td>
      <td>
        {{if $currFiche->qualite_date_controle}}
          {{$currFiche->qualite_date_controle|date_format:$conf.date}}
        {{else}}-{{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CFicheEi.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>