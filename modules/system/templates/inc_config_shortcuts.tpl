{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="EditConfig-shortcuts" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}appbar_shortcuts{{/tr}}</th>
    </tr>

    {{assign var=class value='appbar_shortcuts'}}

    {{foreach from=$conf.appbar_shortcuts key=var item=value}}
      {{assign var=field  value="$class[$var]"}}
      {{assign var=locale value=$var}}
      <tr>
        <th style="width: 50%;">
          <label for="{{$field}}" title="{{tr}}{{$locale}}-desc{{/tr}}">
            {{tr}}{{$locale}}{{/tr}}
          </label>
        </th>

        <td>
          <label for="{{$field}}_1">{{tr}}bool.1{{/tr}}</label>
          <input type="radio" name="{{$field}}" value="1" {{if $value == "1"}}checked{{/if}} />
          <label for="{{$field}}_0">{{tr}}bool.0{{/tr}}</label>
          <input type="radio" name="{{$field}}" value="0" {{if $value != "1"}}checked{{/if}} />
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="2" class="empty">{{tr}}CMbHandler-none{{/tr}}</td>
      </tr>
    {{/foreach}}

    <tr>
      <td class="button" colspan="6">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
