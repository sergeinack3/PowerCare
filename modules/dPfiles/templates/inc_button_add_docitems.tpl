{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type value=""}}
{{mb_script module=dPfiles script=file ajax=true}}

{{assign var=prop value="_count_docitems"}}
{{if $type}}
  {{assign var=prop value="`$prop`_`$type`"}}
{{/if}}

{{assign var=count value=$context->$prop}}

<button type="button" class="search"
        onclick="File.openProtocoleDocItems(this, '{{$type}}', '{{$context->_class}}', '{{$context->_id}}');">
  {{tr}}CMbObject-back-documents{{/tr}} <span>({{$count}})</span>
</button>