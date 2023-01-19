{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $ecg_tabs && $ecg_tabs|@count}}
    {{foreach from=$ecg_tabs item=ecg_tab}}
      <li class="form-tab">
        <a href="#ecgTab-{{$ecg_tab->_id}}">{{$ecg_tab->nom}}</a>
      </li>
    {{/foreach}}
{{/if}}
