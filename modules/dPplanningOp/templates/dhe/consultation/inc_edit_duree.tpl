{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=frequency value=15}}

<select name="duree" onchange="DHE.consult.syncView(this);">
  {{foreach from=1|range:15 item=_i}}
    {{math assign=_duree equation=x*y x=$_i y=$frequency}}
    <option value="{{$_i}}"{{if $consult->duree == $_i}} selected{{/if}}>
      {{$_duree}} min
    </option>
  {{/foreach}}

  {{if $consult->duree > 15}}
    {{math assign=duree equation=x*y x=$consult->duree y=$frequency}}
    <option value="{{$consult->duree}}" selected>
      {{$duree}} min
    </option>
  {{/if}}
</select>