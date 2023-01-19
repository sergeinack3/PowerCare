{{*
 * @package Mediboard\Developpement\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  {{foreach from=$pages item=_pages key=_type}}
    <tr>
      <th colspan="2">{{tr}}dPdeveloppement-type.{{$_type}}{{/tr}}</th>
    </tr>

    {{foreach from=$_pages item=_page}}
      <tr>
        <td>
          <a href="?m=developpement&tab={{$_page}}" class="button lookup">
            {{tr}}mod-dPdeveloppement-tab-{{$_page}}{{/tr}}
          </a>
        </td>
        <td>
          {{tr}}dPdeveloppement-description.{{$_page}}{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>