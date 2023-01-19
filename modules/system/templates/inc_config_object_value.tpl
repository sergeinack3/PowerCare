{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="text" style="text-align: right; width: 33.33%">{{mb_label object=$object field=$_field}}</th>
  <td>
    <button class="notext cancel" type="button" onclick="resetValue('{{$object->_id}}', '{{$_field}}');">
      {{tr}}Delete{{/tr}}
    </button>
    {{if $object->_specs.$_field|instanceof:'Ox\Core\FieldSpecs\CEnumSpec' || $object->_specs.$_field|instanceof:'Ox\Core\FieldSpecs\CBoolSpec'}}
      {{mb_field object=$object field=$_field typeEnum=select emptyLabel="Undefined"}}
    {{else}}
      {{mb_field object=$object field=$_field}}
    {{/if}}
  </td>
  <td {{if $object->$_field !== null}}class="arretee"{{/if}} style="width: 33.33%">
    {{if $object->_default_specs_values}}
      {{mb_value object=$default_config field=$_field}}
    {{else}}
      {{mb_value object=$default field=$_field}}
    {{/if}}
  </td>
</tr>