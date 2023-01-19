{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left;">
  {{foreach from=$codes item=_code}}
    <li>
      <span class="code">{{$_code->code}}</span>
      <div style="color: #888">
        {{$_code->short_name|spancate:40}}
      </div>
      <span id="type" class="informal" style="display:none">
         {{$_code->type}}
      </span>
    </li>
  {{/foreach}}
</ul>