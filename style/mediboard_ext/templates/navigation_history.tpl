{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $dialog || !$app->user_id || @$app->user_prefs.navigationHistoryLength <= 0}}
  {{mb_return}}
{{/if}}

<button class="history nav-history not-printable" {{if !$navigatory_history|@is_countable || $navigatory_history|@count == 0}} disabled {{/if}}
        onmouseover="ObjectTooltip.createDOM(this, this.next(), {duration:0})">
  {{if !$navigatory_history|@is_countable}}
    0
  {{else}}
    {{$navigatory_history|@count}}
  {{/if}}
</button>

<table class="nav-history" style="display: none;">
  {{foreach from=$navigatory_history key=_key item=_entry}}
    {{assign var=nav_object value=$_entry->getObject()}}

    {{if $nav_object}}
      {{assign var=history_url value=$_entry->getURL()|smarty:nodefaults}}
      <tr class="nav-type-{{$_entry->type}}">
        <td class="nav-block nav-tab nav-m-{{$_entry->m}}">
          <a href="?{{$history_url}}" target="_top">
            <img src="modules/{{$_entry->m}}/images/icon.png" width="16" />
            {{$_entry->getTabName()}}
          </a>
        </td>

        <td class="nav-block nav-object nav-class-{{$nav_object->_class}}" style="min-width: 16em;">
          <a href="?{{$history_url}}" target="_top">
            {{$nav_object}}
          </a>
        </td>
        <td class="nav-block nav-time">
          <a href="?{{$history_url}}" target="_top">
            {{$_entry->datetime|date_format:$conf.time}}
          </a>
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>