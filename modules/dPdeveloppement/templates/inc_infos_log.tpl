{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$logs item=_info key=_title}}
  <div style='margin:10px;'>
    <b>{{$_title}} :</b>
    <pre>{{$_info}}</pre>
  </div>
{{/foreach}}
