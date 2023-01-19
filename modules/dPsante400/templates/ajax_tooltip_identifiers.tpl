{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{foreach from=$identifiers item=_identifier}}
    <tr>
      <td>{{mb_value object=$_identifier field=id400}}</td>
      {{if $can->admin && $_identifier->_type}}
        <td><strong>{{mb_value object=$_identifier field=tag}}</strong></td>
      {{/if}}
      <td>
        {{if $_identifier->_type}}
          <span class="idex-special idex-special-{{$_identifier->_type}}">
          {{$_identifier->_type}}
        </span>
        {{else}}
          <strong>{{mb_value object=$_identifier field=tag}}</strong>
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">{{tr}}CIdSante400.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
