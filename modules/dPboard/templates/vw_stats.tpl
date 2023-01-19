{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=dPboard script=board_stats ajax=true}}
<script>
  Main.add(function () {
    let sform = getForm("choixStat");
      {{if $stat}}
        BoardStats.selectStats(sform);
      {{/if}}
  });
</script>
<form name="choixStat" method="get">
  <label for="stat" title="{{tr}}viewStats-title-stats to display{{/tr}}">{{tr}}Statistics{{/tr}}</label>
  <input type="hidden" name="praticien_id" value="{{$praticien_id}}">
  <select name="stat" onchange="BoardStats.selectStats(this.form)">
    <option value="">{{tr}}Select{{/tr}}</option>
      {{foreach from=$stats item=_stat}}
    <option value="{{$_stat}}" {{if $_stat == $stat}}selected="selected"{{/if}}>
        {{tr}}mod-dPboard-tab-{{$_stat}}{{/tr}}
        {{/foreach}}
  </select>
</form>

{{if !$stat}}
  <div class="big-info">
      {{tr}}viewStats-title-select stats to display{{/tr}}
  </div>
{{else}}
  <div id="tdbStats"></div>
{{/if}}
