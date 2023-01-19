{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="category me-text-align-right">{{tr}}Name{{/tr}}</th>
    <th class="category me-text-align-left">{{tr}}Value{{/tr}}</th>
  </tr>

  {{foreach from=$actor->_tags key=_tag_name item=_tag_value}}
    {{assign var=actor_classname value=$actor->_class}}
    <tr>
      <th>{{mb_label class=$actor_classname field=$_tag_name}}</th>
      <td>
        {{if $_tag_value}}
          {{$_tag_value}}
        {{else}}
          <div class="small-error">{{tr}}CInteropActor-no_tags{{/tr}}</div>
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>
