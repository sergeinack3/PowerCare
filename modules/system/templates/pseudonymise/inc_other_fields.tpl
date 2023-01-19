{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{tr}}system-msg-Pseudonymise fields to empty{{/tr}} :

<ul>
  {{foreach from=$_fields item=_field}}
    <li>{{tr}}{{$_class}}-{{$_field}}{{/tr}}</li>
  {{/foreach}}
</ul>