{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$intervenants item=_intervenant}}
    <li>
      <span style="display: none" class="values">
        <span class="executant_id">{{$_intervenant->user_id}}</span>
        <span class="code_intervenant_cdarr">{{$_intervenant->code_intervenant_cdarr}}</span>
        <span class="_executant">{{$_intervenant}}</span>
      </span>
      {{$_intervenant->_view|emphasize:$needle}}
      <br />
      <small class="opacity-70">
        {{$_intervenant->_ref_intervenant_cdarr}}
      </small>
    </li>
   {{foreachelse}}
    <li style="text-align: left;"><span class="informal">{{tr}}No result{{/tr}}</span></li>
  {{/foreach}}
</ul>