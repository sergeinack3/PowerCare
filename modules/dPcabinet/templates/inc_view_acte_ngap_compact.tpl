{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$acte->_guid}}');">
    {{$acte->_shortview}}
  </span>
  {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$acte->_ref_executant}}
</div>
