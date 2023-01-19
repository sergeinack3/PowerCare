{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=hide_empty value=null}}

{{if @$hide_empty}}
  {{if $ipp}}[{{$ipp}}]{{/if}}
{{else}}
  [{{$ipp|default:"-"}}]
{{/if}}
