{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td class="text">
    <div class="small-info">
      {{tr}}CPatient-msg-Iconography for use in document templates{{/tr}}
    </div>
  </td>
</tr>

{{if $object->_id}} 
<tr>
  <th class="title">{{tr}}CPatient-msg-Portrait{{/tr}}</th>
</tr>
<tr>
  <td class="button">
    {{mb_include module=files template=inc_named_file object=$object name=identite.jpg mode=edit}}
  </td>
</tr>

<tr>
  <th class="title">{{tr}}common-Signature{{/tr}}</th>
</tr>
<tr>
  <td class="button">
    {{mb_include module=files template=inc_named_file object=$object name=signature.jpg mode=edit}}
  </td>
</tr>

{{else}}
<tr>
  <td class="text">
    <div class="small-warning">
      {{tr}}CPatient-msg-Available only after user creation{{/tr}}
    </div>
  </td>
</tr>
{{/if}}
