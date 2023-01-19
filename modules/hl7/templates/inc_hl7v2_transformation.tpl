{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$tree item=_segment}}
  <li>
    {{if $_segment.name == $tree_fields.name}}
      <input type="checkbox" name="address" value="{{$tree_fields.fullpath}}" />
      <span class="field-name">{{$tree_fields.name}}</span>

      {{mb_include template="inc_hl7v2_transformation_group" component=$tree_fields}}
    {{else}}
      {{if $_segment.type == "segment"}}
        <a href="#" onclick="HL7_Transformation.viewFields('{{$profil}}', '{{$_segment.name}}',
          '{{$version}}', '{{$extension}}', '{{$message}}', null, '{{$_segment.fullpath}}')">
          <span class="type-{{$_segment.type}}">{{$_segment.name}}</span>
        </a>
        <strong class="field-description">{{$_segment.description}}</strong>
      {{else}}
        <span class="type-{{$_segment.type}}">{{$_segment.name}}</span>

        <ul>
          {{mb_include module=hl7 template=inc_hl7v2_transformation tree=$_segment.children}}
        </ul>
      {{/if}}
    {{/if}}
  </li>
{{/foreach}}
