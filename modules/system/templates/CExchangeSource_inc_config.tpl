{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=exchange_source ajax=true}}

<table class="main">
  {{assign var="_source" value=$source}}
  {{if isset($source->_allowed_instances.$class|smarty:nodefaults)}}
    {{assign var="_source" value=$source->_allowed_instances.$class}}
  {{/if}}
  
  {{if !$_source->_id}}
  <tr>
    <td>
      <a class="button new" onclick="$('config-source-{{$class}}-{{$sourcename}}').show(); Control.Modal.position();">
        {{tr}}{{$class}}-title-create{{/tr}}
      </a>
   </td>
  </tr>
  {{/if}}

  <tr>
    <td id="config-source-{{$class}}-{{$sourcename}}" {{if !$_source->_id}}style="display:none"{{/if}}>
      {{if $_source->_class == $class}}
        {{mb_include module=$mod template="`$class`_inc_config" source=$_source wanted_type=$source->_wanted_type}}
      {{/if}}
    </td>
  </tr>
</table>
