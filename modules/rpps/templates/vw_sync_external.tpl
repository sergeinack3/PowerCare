{{*
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  {{foreach from=$avancements key=short_name item=avancement}}
    <tr>
      <th>{{tr}}{{$short_name}}{{/tr}}</th>
    </tr>

    <tr>
      <td>
        <div class="progressBarModern"
             title="{{$avancement.sync}} / {{$avancement.total}} ({{$avancement.pct}}%)">
          <div class="bar bar-{{$avancement.threshold}}" style="width: {{$avancement.width}}%">
            <div class="progress">{{$avancement.pct}}%</div>
            <div class="values">
              {{$avancement.sync}} / {{$avancement.total}}
            </div>
          </div>
        </div>
      </td>
    </tr>
  {{/foreach}}
</table>

