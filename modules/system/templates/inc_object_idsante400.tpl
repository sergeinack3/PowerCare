{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{if "appFineClient"|module_active}}
  {{mb_include module="appFineClient" template="inc_object_idex"}}
{{/if}}

{{if "vivalto"|module_active}}
  {{mb_include module="vivalto" template="inc_object_idex"}}
{{/if}}

{{if "doctolib"|module_active}}
  {{mb_include module="doctolib" template="inc_object_idex"}}
{{/if}}

{{if "dPsante400"|module_active && $modules.dPsante400->_can->read}}
  <a style="float: right;" href="#1" title="" class="not-printable"
     onclick="guid_ids('{{$object->_guid}}')"
     onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}', 'identifiers')">
    {{me_img src="external.png" icon="link" class="me-primary" width=16 height=16}}
  </a>
{{/if}}

