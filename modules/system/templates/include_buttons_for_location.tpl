{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ajax value=false}}

{{foreach from=$buttons item=_button}}
  {{if $_button->getScriptName()}}
    {{mb_script module=$_button->getModuleName() script=$_button->getScriptName() ajax=$ajax}}
  {{/if}}

  <button type="button" class="{{$_button->getClassNames()}}" {{if $_button->isDisabled()}} disabled{{/if}}
          onclick="{{$_button->getOnClick()}}">
    {{$_button->getLabel()}}
  </button>
{{/foreach}}