{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=colspan_sejour value=6}}
{{if $prestation->_id}}{{assign var=colspan_sejour value=$colspan_sejour+1}}{{/if}}
{{if $filter->_notes}}{{assign var=colspan_sejour value=$colspan_sejour+1}}{{/if}}
{{if $filter->_by_date}}{{assign var=colspan_sejour value=$colspan_sejour+1}}{{/if}}

<th colspan="{{$colspan_sejour}}"><strong>{{tr}}CSejour{{/tr}}</strong></th>