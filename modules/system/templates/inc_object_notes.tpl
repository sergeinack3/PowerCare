{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=float value=left}}
{{mb_default var=note_class value=""}}
{{if is_array($object->_ref_notes)}}
  <div style="float: {{$float}}" class="noteDiv {{$object->_guid}} initialized not-printable {{$note_class}}">
  {{mb_include module=system template=inc_get_notes_image mode=edit float=left object=$object}}
  </div>
{{else}}
  <div style="float: {{$float}};" class="noteDiv {{$object->_guid}} not-printable">
    <img title="{{tr}}CNote-title-create{{/tr}}" src="images/icons/note_grey.png" width="16" height="16" />
  </div>
{{/if}}
