{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$tree item=_subtree}}
  <li>
  {{if $_subtree.type == "segment"}}
    <a href="#" onclick="HL7_Transformation.viewFields('{{$profil}}', '{{$_subtree.name}}',
      '{{$version}}', '{{$extension}}', '{{$message}}', '{{$target}}', '{{$_subtree.fullpath}}')">
      <span class="type-{{$_subtree.type}}">{{$_subtree.name}}</span>
    </a>
    <strong class="field-description">{{$_subtree.description}}</strong>
  {{else}}
    <span class="type-{{$_subtree.type}}">{{$_subtree.name}}</span>

    <ul>
      {{mb_include module=hl7 template=inc_segment_tree tree=$_subtree.children target=$target}}
    </ul>
  {{/if}}
  </li>
{{/foreach}}
