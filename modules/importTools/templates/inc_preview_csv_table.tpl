{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  {{foreach from=$rows item=_row}}
    <tr>
    {{foreach from=$_row item=_cell}}
      <td>{{$_cell|spancate:80}}</td>
    {{/foreach}}
    </tr>
  {{/foreach}}
</table>