{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=thcolspan value=2}}

<tr>
  <th colspan="{{$thcolspan}}" class="category">
  	{{if $class.0 == "C"}} 
      {{tr}}{{$class}}{{/tr}}
  	{{else}}
      {{tr}}config-{{$m}}-{{$class}}{{/tr}}
  	{{/if}}
  </th>
</tr>  
