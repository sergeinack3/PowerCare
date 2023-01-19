{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $mode == "view" && !count($object->_ref_notes)}}
  {{mb_return}}
{{/if}}

<span style="position: relative" class="me-note"
  onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}', 'objectNotes')"
  {{if $mode == "edit"}}
    onclick="Note.create('{{$object->_guid}}')"
  {{/if}}
  >

  {{assign var=color value=""}}

  {{if $object->_ref_notes|@count}}
    {{if $object->_degree_notes == "high"}}
      {{assign var=color value="red"}}
    {{elseif $object->_degree_notes == "medium"}}
      {{assign var=color value="orange"}}
    {{elseif $object->_degree_notes == "low"}}
      {{assign var=color value="green"}}
    {{/if}}
  {{elseif $mode == "edit"}}
    {{assign var=color value="transparent"}}
  {{/if}}

  <span class="me-note-icon me-note-{{$color}}"></span>

  {{mb_include module=system template=inc_vw_counter_tip count=$object->_ref_notes|@count}}
</span>
