{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=attributes value='Ox\Mediboard\Messagerie\CJeeboxLDAPRecipient'|static:'attributes'}}

<table class="tbl">
  <tr>
    <th class="title" colspan="8">{{tr}}Results{{/tr}}</th>
  </tr>
  <tr>
    <th></th>
    {{foreach from=$attributes.$type item=_field}}
      <th>{{mb_title class=CJeeboxLDAPRecipient field=$_field}}</th>
    {{/foreach}}
  </tr>
  {{foreach from=$results item=_result}}
    <tr>
      <td>
        <button type=button" class="add notext" title="{{tr}}Add{{/tr}}" onclick="addResultAddress('{{$_result->mail}}');"></button>
      </td>
      {{foreach from=$attributes.$type item=_field}}
        <td>{{mb_value class=$_result field=$_field}}</td>
      {{/foreach}}
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="8">Aucun résultat</td>
    </tr>
  {{/foreach}}
</table>