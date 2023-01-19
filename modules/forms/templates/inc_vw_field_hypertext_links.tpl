{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ex_object  value=false}}
{{mb_default var=field_name value=false}}
{{mb_default var=label      value=false}}

{{if !isset($hypertext_links|smarty:nodefaults)}}
  {{mb_default var=hypertext_links value=$object->_ref_hypertext_links}}
{{/if}}

{{assign var=count value=$hypertext_links|@count}}

{{if $count == 1}}
  {{assign var=link value=$hypertext_links|@first}}

  {{if $ex_object && $field_name}}
    {{mb_label object=$ex_object field=$field_name}}
  {{elseif $label}}
    {{$label}}
  {{/if}}

  <a class="not-printable" href="{{$link->link}}" target="_blank" title="{{$link->name}}">
    <i class="fas fa-external-link-alt"></i>
  </a>
{{else}}
  {{if $ex_object && $field_name}}
    {{mb_label object=$ex_object field=$field_name}}
  {{elseif $label}}
    {{$label}}
  {{/if}}

  <span style="display: inline-block; position: relative; vertical-align: top;" class="not-printable" onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}', 'hyperlinks');">
    <i class="fas fa-external-link-alt fa"></i> {{mb_include module=system template=inc_vw_counter_tip count=$count}}
  </span>
{{/if}}