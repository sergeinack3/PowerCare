{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=colspan value=2}}
{{mb_default var=css_class value=""}}

<tr>
  {{if $object->_id}}
  <th class="title modify {{$css_class}}" colspan="{{$colspan}}">
    {{mb_include module=system template=inc_object_notes     }}
    {{mb_include module=system template=inc_object_idsante400}}
    {{mb_include module=system template=inc_object_history   }}
    {{mb_include module=system template=inc_object_uf}}
    {{mb_include module=system template=inc_object_idex}}

    {{tr}}{{$object->_class}}-title-modify{{/tr}} 
    <br />
    '{{$object}}'
  </th>
  {{else}}
  <th class="title me-th-new" colspan="{{$colspan}}">
    {{tr}}{{$object->_class}}-title-create{{/tr}} 
  </th>
  {{/if}}
</tr>
