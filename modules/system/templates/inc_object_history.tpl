{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=object    value=null}}
{{mb_default var=css_style value="float: right;"}}
{{mb_default var=tabindex  value="0"}}


{{if (array_key_exists('system_show_history',$app->user_prefs)) && !$app->user_prefs.system_show_history }}
  {{mb_return}}
{{/if}}

{{if !$object || $object->_spec->loggable === "Ox\Core\CMbObjectSpec"|const:"LOGGABLE_NEVER"
  || $object->_spec->loggable == "Ox\Core\CMbObjectSpec"|const:"LOGGABLE_LEGACY_FALSE"}}
  <a style="{{$css_style}}" href="#1" title="Pas d'historique disponible" class="not-printable" tabindex="{{$tabindex}}">
    {{me_img src="history.gif" width=16 height=16 style="opacity: 0.2;" icon="history" class="me-primary"}}
  </a>
{{else}}
  <a style="{{$css_style}}" href="#1" title="" class="not-printable"
     onclick="guid_log('{{$object->_guid}}')"
     onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}', 'objectViewHistory')" tabindex="{{$tabindex}}">
    {{me_img src="history.gif" icon="history" width=16 height=16 icon="history" class="me-primary"}}
  </a>
{{/if}}

