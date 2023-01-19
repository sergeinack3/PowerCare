{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$prestas item=_presta}}
    <li>
      <span style="display: none" class="value">{{$_presta->code}}</span>
      <div class="text" style="width: 300px;">
        <strong>{{$_presta->code}}</strong>
        {{$_presta->libelle}}
      </div>
      <div class="text compact" style="width: 300px;">
        {{tr}}CPrestaSSR.type.{{$_presta->type}}{{/tr}}
      </div>
    </li>
   {{foreachelse}}
    <li style="text-align: left;">
      <span class="empty">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>
