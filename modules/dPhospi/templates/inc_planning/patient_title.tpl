{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=colspan_patient value=3}}
{{if $filter->_coordonnees}}{{assign var=colspan_patient value=$colspan_patient+2}}{{/if}}

<th colspan="{{$colspan_patient}}"><strong>{{tr}}CPatient{{/tr}}</strong></th>