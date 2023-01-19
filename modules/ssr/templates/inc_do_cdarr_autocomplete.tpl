{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$activites item=_activite}}
    <li>
      <span style="display: none" class="value">{{$_activite->code}}</span>
      <strong>{{$_activite->code|emphasize:$needle}}</strong>
      {{$_activite->libelle|emphasize:$needle}}
      <br />
      <small class="opacity-70">
        {{$_activite->_ref_type_activite->_view|emphasize:$needle}}
      </small>
    </li>
   {{foreachelse}}
    <li style="text-align: left;">
      <span class="informal">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>