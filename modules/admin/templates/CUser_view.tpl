{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_ref_mediuser->_id}}
  {{mb_include module=mediusers template=CMediusers_view object=$object->_ref_mediuser}}
{{elseif $object->canEdit()}}
  {{mb_include template=CMbObject_view object=$object}}
{{elseif $object->canRead()}}
  {{mb_include module=admin template=CUser_minimalist_view object=$object}}
{{else}}
  <div class="small-info">
    {{tr}}access-forbidden{{/tr}}
  </div>

  {{mb_return}}
{{/if}}
