{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <col style="width: 200px;" />
  <col style="width: 80px;" />

  <tr>
    <th colspan="3" class="category">JS</th>
  </tr>

  <tr style="font-weight: bold;">
    <th>Total</th>
    <td colspan="2">{{$info.js_total|decabinary}}</td>
  </tr>

  {{foreach from=$info.js item=_entry}}
    <tr>
      <th>{{$_entry.name}}</th>
      <td>{{$_entry.size|decabinary}}</td>
      <td>{{$_entry.date}}</td>
    </tr>
  {{/foreach}}

  <tr>
    <th colspan="3" class="category">CSS</th>
  </tr>

  <tr style="font-weight: bold; border-bottom: 1px solid #ddd;">
    <th>Total</th>
    <td colspan="2">{{$info.css_total|decabinary}}</td>
  </tr>

  {{foreach from=$info.css item=_entry}}
    <tr>
      <th>{{$_entry.name}}</th>
      <td>{{$_entry.size|decabinary}}</td>
      <td>{{$_entry.date}}</td>
    </tr>
  {{/foreach}}
</table>