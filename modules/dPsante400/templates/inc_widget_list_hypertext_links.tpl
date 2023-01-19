{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=hyperTextLink ajax=true}}

{{if !isset($hypertext_links|smarty:nodefaults)}}
  {{mb_default var=hypertext_links value=$object->_ref_hypertext_links}}
  {{mb_default var=object_id       value=$object->_id}}
  {{mb_default var=object_class    value=$object->_class}}
{{/if}}

{{mb_default var=show_separator value=true}}
{{mb_default var=link_readonly value=false}}

<div id="list-hypertext_links">
  {{if $show_separator}}
    <hr class="me-display-none"/>
  {{/if}}

  {{foreach from=$hypertext_links item=_hypertext_link}}
    <tr>
      <td>
        <a href="{{$_hypertext_link->link}}" target="_blank" onmouseover="ObjectTooltip.createEx(this, '{{$_hypertext_link->_guid}}')">
          {{$_hypertext_link->name}} <i class="fas fa-external-link-alt"></i>
        </a>
      </td>
    </tr>
  {{/foreach}}
  {{if !$link_readonly}}
    <button type="button" class="add notext me-tertiary" style="float: right"
            onclick="HyperTextLink.edit('{{$object_id}}', '{{$object_class}}', 0, 1)">
      {{tr}}CHyperTextLink-action-Add{{/tr}}
    </button>
  {{/if}}
</div>