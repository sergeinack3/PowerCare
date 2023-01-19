{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=spec value=$object->_specs.$prop}}
{{assign var=value value=$object->$prop}}

{{if $spec->show !== "0"}}
    {{if $prop.0 != "_" || $spec->show}}
        {{if $value || $value === "0" || $value === 0 || $spec->show}}
            <strong>{{mb_label object=$object field=$prop}}</strong>
            :

            {{if $spec|instanceof:'Ox\Core\FieldSpecs\CRefSpec'}}
                {{if $prop == $object->_spec->key}}
                    {{$object->$prop}}
                {{else}}
                    {{assign var=ref value=$object->_fwd.$prop}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$ref->_guid}}');">
            {{$ref}}
          </span>
                {{/if}}

            {{elseif $spec|instanceof:'Ox\Core\FieldSpecs\CHtmlSpec'}}
                {{$value|count_words}} mots

            {{elseif $spec|instanceof:'Ox\Core\FieldSpecs\CTextSpec'}}
                {{* FIXME: smarty:nodefault is required because HTML entities are double escaped *}}
                {{$value|smarty:nodefaults|truncate:200|nl2br}}

            {{else}}
                {{mb_value object=$object field=$prop}}

            {{/if}}
            <br/>
        {{/if}}
    {{/if}}
{{/if}}
