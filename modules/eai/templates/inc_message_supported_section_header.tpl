{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th style="text-align: left;" class="section" colspan="4">
    {{* checkAll *}}
    <button class="fa fa-check notext" onclick="checkAll('{{$_family_name}}', '{{$category_uid}}')"></button>

    {{* Category name *}}
    {{if $_category_name != "none"}}
        {{tr}}{{$_category_name}}{{/tr}} (
      <em>{{$_category_name}})</em>
    {{else}}
        {{tr}}All{{/tr}}
    {{/if}}

    {{if $_families->_versions_category}}
      {{assign var=_message_supported value=$_messages_supported|@first}}
      <form name="messages_versions_{{$_family_name}}_{{$category_uid}}" method="post" onsubmit="InteropActor.updateMessageSupported(this)">
        <input type="hidden" name="object_id" value="{{$_message_supported->object_id}}" />
        <input type="hidden" name="object_class" value="{{$_message_supported->object_class}}" />
        <input type="hidden" name="transaction" value="{{$_message_supported->transaction}}" />
        <input type="hidden" name="profil" value="{{$_family_name}}" />

        {{* Category version *}}
        {{if $_families->_versions_category}}
          {{assign var=_message_supported value=$_messages_supported|@first}}
          {{mb_include module=eai template=inc_message_supported_section_version}}
        {{/if}}
      </form>
    {{/if}}
  </th>
</tr>
