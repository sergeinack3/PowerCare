{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_properties value=$_message->_default_properties}}

{{assign var=_style value=""}}
{{foreach from=$_properties key=_type item=_value}}
  {{if $_value != ""}}
    {{assign var=_style value="$_style $_type:$_value;"}}
  {{/if}}
{{/foreach}}

{{if $_message->type == "title"}}
  <div class="ex-message-title" style="{{$_style}}" {{if $_message->description}} title="{{$_message->description}}" {{/if}}>
    {{if $_message->_ref_hypertext_links && ($ex_class->pixel_positionning || ($_message->coord_title_x == null && $_message->coord_title_y == null))}}
      {{mb_include module=forms template=inc_vw_field_hypertext_links object=$_message}}
    {{/if}}

    {{$_message->text}}
  </div>
  <span class="ex-message-title-spacer">&nbsp;</span>
{{else}}
  <div class="ex-message small-{{$_message->type}}" style="{{$_style}}" {{if $_message->description}} title="{{$_message->description}}" {{/if}}>
    {{if $_message->_ref_hypertext_links && ($ex_class->pixel_positionning || ($_message->coord_title_x == null && $_message->coord_title_y == null))}}
      {{mb_include module=forms template=inc_vw_field_hypertext_links object=$_message}}
    {{/if}}

    <span class="message-content">{{$_message->text}}</span>
  </div>
{{/if}}