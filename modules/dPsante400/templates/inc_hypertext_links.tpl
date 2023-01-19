{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=hyperTextLink ajax=true}}

{{if isset($object->_back.hypertext_links|smarty:nodefaults)}}
  {{foreach from=$object->_back.hypertext_links item=_hypertext_link}}
    <button class="glob notext me-tertiary" type="button" title="{{$_hypertext_link->name}}"
            onclick="HyperTextLink.accessLink('{{$_hypertext_link->name|smarty:nodefaults|JSAttribute}}', '{{$_hypertext_link->link}}')">
      {{$_hypertext_link->name}}
    </button>
  {{/foreach}}
{{/if}}