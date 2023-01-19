{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPsante400 script=Idex ajax=true}}

{{if "dPsante400"|module_active && $modules.dPsante400->_can->read}}
  <a style="float: right;" href="#1" title=""
     onclick="Idex.edit('{{$object->_guid}}', '{{$tag}}')"
     onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}', 'identifiers')">
    <span class="texticon texticon-idext">ID</span>
  </a>
{{/if}}