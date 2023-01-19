{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{mb_title class=CHyperTextLink field=name}}</th>
    <th>{{mb_title class=CHyperTextLink field=link}}</th>
  </tr>

  {{foreach from=$hyperlinks item=_link}}
    <tr>
      <td>{{mb_value object=$_link field=name}}</td>

      <td>
        <a href="{{$_link->link}}" target="_blank">
          {{mb_value object=$_link field=link}}
        </a>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CHyperTextLink.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
