{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=event_name value=$event|getShortName}}
{{assign var=message_name value=$message|getShortName}}

<div>
  <button onclick="EAITransformation.link('{{$message_name}}', '{{$event_name}}', '{{$actor->_guid}}');" class="button new">
    {{tr}}CTransformation-title-create{{/tr}}
  </button>
</div>

<table class="main tbl">
  <tr>
    <th colspan="15" class="title">
      {{tr}}CTransformation.all{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th class="narrow button"></th>
    <th> {{mb_title class=CTransformation field=eai_transformation_id}} </th>
    <th> {{mb_title class=CTransformation field=eai_transformation_rule_id}} </th>
    <th> {{mb_title class=CTransformation field=standard}} </th>
    <th> {{mb_title class=CTransformation field=domain}} </th>
    <th> {{mb_title class=CTransformation field=profil}} </th>
    <th> {{mb_title class=CTransformation field=message}} </th>
    <th> {{mb_title class=CTransformation field=transaction}} </th>
    <th> {{mb_title class=CTransformation field=version}} </th>
    <th> {{mb_title class=CTransformation field=extension}} </th>
    <th> {{mb_title class=CTransformationRule field=action_type}} </th>
    <th> {{mb_title class=CTransformation field=active}} </th>
    {{if $readonly}}
      <th class="narrow"> {{mb_title class=CTransformation field=rank}} </th>
    {{/if}}
  </tr>

  <tbody id="transformations">
    {{mb_include template="inc_list_transformations_lines"}}
  </tbody>
</table>
