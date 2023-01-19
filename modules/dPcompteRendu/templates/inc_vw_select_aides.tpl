{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<option value="">&mdash; {{tr}}Choose{{/tr}}</option>
{{if $no_enum && $field}}
  {{html_options options=$object->_aides.$field.no_enum}}
{{else}}
  {{foreach from=$object->_aides item=_list key=sTitleOpt}}
    {{foreach from=$_list item=list_aides_by_type key=_type_aide}}
      {{if $_type_aide != "no_enum"}}
        {{* FIXME: Les optgroups n'ont pas le droit d'etre imbriqués (un seul niveau autorisé) *}}
        <optgroup label="{{$_type_aide}}">
          {{foreach from=$list_aides_by_type item=_list_aides key=cat}}
            <optgroup label="{{$cat}}" style="padding-left: 10px;">
              {{html_options options=$_list_aides}}
            </optgroup>
          {{/foreach}}
        </optgroup>
      {{/if}}
    {{/foreach}}
  {{/foreach}}
{{/if}}