{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{tr}}common-ID{{/tr}}</th>
    <th>{{tr}}common-Content{{/tr}}</th>
    <th title="{{tr}}CObjectIndexer-pertinence-desc{{/tr}}">{{tr}}common-Pertinence{{/tr}}</th>
  </tr>
  {{foreach from=$objects item=_object}}
    <tr>
      <td {{if $class == 'Ox\Core\Index\ClassMetadata'}}onmouseover="ObjectTooltip.createEx(this, '{{$class}}-{{$_object._id}}')"{{/if}}>{{$_object._id}}</td>
      <td class="text">{{$_object.body|emphasize:$tokens:'strong'}}</td>
      <td>{{if $_object.pertinence}}{{$_object.pertinence|percent}}{{/if}}</td>
    </tr>
    {{foreachelse}}
    <tr><td colspan="3" class="empty">{{tr}}CMbObject.none{{/tr}}</td></tr>
  {{/foreach}}
</table>
