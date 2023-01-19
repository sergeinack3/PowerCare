{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=hide_empty_groups value=false}}
{{mb_default var=print value=false}}

{{foreach from=$ex_object->_ref_ex_class->_ref_groups item=_ex_group}}
  {{if !$_ex_group->disabled}}
    {{assign var=go value=true}}

    {{if $hide_empty_groups}}
      {{assign var=any value=false}}

      {{foreach from=$_ex_group->_ref_fields item=_ex_field}}
        {{assign var=field_name value=$_ex_field->name}}

        {{if $ex_object->$field_name !== null}}
          {{assign var=any value=true}}
        {{/if}}
      {{/foreach}}

      {{assign var=go value=$any}}
    {{/if}}

    {{if $go}}
      <h4 style="margin: 0.5em; border-bottom: 1px solid #666;">{{$_ex_group}}</h4>

      <ul class="ex-object_fields">
        {{assign var=any value=false}}

        {{foreach from=$_ex_group->_ranked_items item=_ex_field_or_message_or_host_field}}
          {{if $_ex_field_or_message_or_host_field|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
            {{assign var=_ex_field value=$_ex_field_or_message_or_host_field}}
            {{assign var=field_name value=$_ex_field->name}}

            {{if !$_ex_field->hidden && $ex_object->$field_name !== null}}
              {{assign var=any value=true}}
              <li>
            <span style="color: #666;">
              {{if $print}}
                {{tr}}{{$ex_object->_class}}-{{$field_name}}{{/tr}}
              {{else}}
                {{mb_label object=$ex_object field=$field_name}}
              {{/if}}
            </span> : {{mb_value object=$ex_object field=$field_name}}
              </li>
            {{/if}}
          {{elseif $_ex_field_or_message_or_host_field|instanceof:'Ox\Mediboard\System\Forms\CExClassMessage'}}
            {{assign var=_ex_message value=$_ex_field_or_message_or_host_field}}
            <li style="list-style-type: none; {{if $_ex_message->type == "title"}} border-bottom: 1px solid black; margin: 6px 0 3px -7px; {{/if}}">
              <div class="small-{{$_ex_message->type}}">
                {{$_ex_message->text}}
              </div>
            </li>
          {{else}}
            {{assign var=_ex_host_field value=$_ex_field_or_message_or_host_field}}
            <li>
              <span style="color: #666;">
                {{tr}}{{$_ex_host_field->host_class}}{{/tr}} &ndash;

                {{if $_ex_host_field->_field == '_view'}}
                  Vue
                {{elseif $_ex_host_field->_field == '_shortview'}}
                  Vue courte
                {{else}}
                  {{tr}}{{$_ex_host_field->host_class}}-{{$_ex_host_field->_field}}{{/tr}}
                {{/if}}
              </span> : {{mb_value object=$_ex_host_field->_ref_host_object field=$_ex_host_field->_field}}
            </li>
          {{/if}}
        {{/foreach}}
        {{if !$any}}
          <li class="empty">Aucune valeur</li>
        {{/if}}
      </ul>
      <br />
    {{/if}}
  {{/if}}
{{/foreach}}
