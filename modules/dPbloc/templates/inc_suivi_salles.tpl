{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $mode_presentation && "brancardage"|module_active && "brancardage General use_brancardage"|gconf}}
  {{mb_script module=brancardage script=brancardage ajax=true}}
{{/if}}

<script>
  mineSalleForDay = function(salle_id, date) {
    var url = new Url('bloc', 'do_mine_salle_day', 'dosql');
    url.addParam('salle_id', salle_id);
    url.addParam('date', date);
    url.requestUpdate("systemMsg", {ajax: true, method: 'post'});
  };
</script>

<table class="main" id="suivi-salles">
  <tr>
    <th colspan="100">
      <h1 class="no-break">
        {{$date_suivi|date_format:$conf.longdate}} <br>
        <span style="font-size:0.8em;">
          {{tr var1=$completed_op_count}}COperation-%s intervention completed|pl{{/tr}} - {{tr var1=$in_progress_op_count}}COperation-%s intervention in progress|pl{{/tr}} - {{tr var1=$planned_op_count}}COperation-%s intervention planned|pl{{/tr}}
        </span>
      </h1>
    </th>
  </tr>
  <tr class="not-printable">
    <td class="button" colspan="100">
      {{if $page}}
        <div>
          {{foreach from=1|range:$page_count item=i}}
            <span class="circled" {{if $i == $current_page+1}} style="background: orange" {{/if}}>&nbsp;&nbsp;&nbsp;&nbsp;</span>
          {{/foreach}}
        </div>
      {{else}}
        {{foreach from=$salles item=_salle}}
          <label class="me-color-black-high-emphasis">
            <input class="salle-toggler" data-salle_id="{{$_salle->_id}}" type="checkbox" onclick="Effect.toggle('salle-{{$_salle->_id}}', 'appear');" checked />
            {{$_salle->nom}}
          </label>
        {{/foreach}}
        {{if $non_traitees|@count}}
          <label class="me-color-black-high-emphasis">
            <input class="salle-toggler" data-salle_id="non-traitees" type="checkbox" onclick="Effect.toggle('non-traitees', 'appear');" checked />
            {{tr}}CSejour.type.hors_plage{{/tr}}
          </label>
        {{/if}}
      {{/if}}
    </td>
  </tr>
  <tr>
    {{foreach from=$salles item=_salle}}
      <td id="salle-{{$_salle->_id}}" style="width: {{math equation=100/x x=$salles|@count}}%;">
        <table class="tbl">
          <tr>
            <th class="title" style="{{if $_salle->color}}border-bottom: 4px solid #{{$_salle->color}};{{else}} margin-bottom: 4px;{{/if}}">
              {{if $app->_ref_user->isAdmin()}}
                <button style="float:right;" onclick="mineSalleForDay('{{$_salle->_id}}', '{{$date_suivi}}')" class="change notext"></button>
              {{/if}}
              {{$_salle->nom}}

              {{if $dmi_active}}
                {{mb_include module=dmi template=inc_list_dm lines_dm=$_salle->_ref_lines_dm context=$_salle}}
              {{/if}}
            </th>
          </tr>
        </table>
        {{mb_include module=salleOp template=inc_details_plages salle=$_salle redirect_tab=1}}
      </td>
    {{foreachelse}}
      <td class="empty">{{tr}}CSalle.none{{/tr}}</td>
    {{/foreach}}

    {{if $non_traitees|@count}}
      {{assign var=salle value=""}}
      <td id="non-traitees">
        <table class="tbl">
          <tr>
            <th class="title" colspan="5">
              {{tr}}CSejour.type.hors_plage{{/tr}}

              {{if $dmi_active}}
                {{mb_include module=dmi template=inc_list_dm lines_dm=$_salle->_ref_lines_dm_urgence context=$_salle}}
              {{/if}}
            </th>
          </tr>
          {{mb_include module="salleOp" template="inc_liste_operations" urgence=1 operations=$non_traitees redirect_tab=1 ajax_salle=1}}
        </table>
      </td>
    {{/if}}
  </tr>
</table>

{{if $dmi_active}}
  {{mb_include module=dmi template=inc_print_dm_salles}}
{{/if}}
