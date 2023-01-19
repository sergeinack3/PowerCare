{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$pathologies_fields key=patho_field item=patho_name}}
    <li data-pathologie_name="{{$patho_field}}">
      {{tr}}{{$patho_name}}{{/tr}}
    </li>
  {{foreachelse}}
    <li class="empty">{{tr}}CDossierMedical-back-pathologies.empty{{/tr}}</li>
  {{/foreach}}
</ul>
