{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=is_subitem value=0}}

{{if $item->getSeverity() == 1}}
  {{assign var=icon value="bug"}}
  {{assign var=color value="red"}}
{{elseif $item->getSeverity() == 2}}
  {{assign var=icon value="exclamation-triangle"}}
  {{assign var=color value="orange"}}
{{elseif $item->getSeverity() == 3}}
  {{assign var=icon value="check"}}
  {{assign var=color value="green"}}
{{/if}}

<tr {{if $is_subitem}}class="{{$item_uniqid}}" style="display: none"{{/if}}>
  {{if !$is_subitem}}
    {{* Icon *}}
    <td
      title="{{tr}}CItemReport-severity-{{$item->getSeverity()}}{{/tr}}"
      style="text-align: center;">
      <i class="fas fa-{{$icon}}" style="color:{{$color}}"></i>
    </td>
    {{* Data *}}
    <td {{if !$hasSubItems}}colspan="3"{{/if}} class="text">
      {{$item->getData()}}
    </td>
    {{* Expends button *}}
    {{if !$is_subitem && $hasSubItems}}
      <td class="narrow text-center">
        <button id="button-{{$item_uniqid}}" type="button"
                class="fas fa-chevron-circle-down  notext me-tertiary"
                onclick="toggleItems(this, '{{$item_uniqid}}')"></button>
      </td>
    {{/if}}

  {{* SubItems *}}
  {{else}}
    <td></td>
    <td colspan="2">
      <table>
        <tr>
          {{* Icon *}}
          <td
            title="{{tr}}CItemReport-severity-{{$item->getSeverity()}}{{/tr}}"
            style="text-align: center;">
            <i class="fas fa-{{$icon}}" style="color:{{$color}}"></i>
          </td>

          {{* Data *}}
          <td {{if !$hasSubItems}}colspan="3"{{/if}} class="text">
            {{$item->getData()}}
          </td>
        </tr>
      </table>
    </td>
  {{/if}}
</tr>
