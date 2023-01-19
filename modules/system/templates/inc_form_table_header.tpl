{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=colspan value=2}}
{{mb_default var=css_class value=""}}
{{mb_default var=duplicate value=false}}
{{mb_default var=show_history value=true}}
{{mb_default var=show_notes value=true}}
{{mb_default var=show_identifiers value=true}}
{{mb_default var=show_id value=false}}

<tr>
  {{if $object->_id}}
    <th class="title modify {{$css_class}}" colspan="{{$colspan}}">
      {{if $show_notes}}
        {{mb_include module=system template=inc_object_notes}}
      {{/if}}

      {{if $show_identifiers}}
        {{mb_include module=system template=inc_object_idsante400}}
      {{/if}}

      {{if $show_history}}
        {{mb_include module=system template=inc_object_history}}
      {{/if}}

      {{tr}}{{$object->_class}}-title-modify{{/tr}}
      <br />
      '{{$object}}' {{if $show_id}}(#{{$object->_id}}){{/if}}
    </th>
  {{elseif $duplicate}}
    <th class="title duplicate" colspan="{{$colspan}}">
      {{tr}}{{$object->_class}}-title-duplicate{{/tr}}
    </th>
  {{else}}
    <th class="title me-th-new" colspan="{{$colspan}}">
      {{tr}}{{$object->_class}}-title-create{{/tr}}
    </th>
  {{/if}}
</tr>
