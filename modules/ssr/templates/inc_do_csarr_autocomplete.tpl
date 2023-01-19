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
      <span style="display: none" class="collectif">{{if isset($_activite->_ref_reference->collectif|smarty:nodefaults)}}{{$_activite->_ref_reference->collectif}}{{/if}}</span>
      <div class="text" style="width: 300px;">
        <strong>{{$_activite->code|emphasize:$needle}}</strong>
        {{$_activite->libelle|emphasize:$needle}}
      </div>
      <div class="text compact" style="width: 300px;">
        {{$_activite->hierarchie|emphasize:$needle}}:
        {{$_activite->_ref_hierarchie->libelle|emphasize:$needle}}
      </div>
    </li>
   {{foreachelse}}
    <li style="text-align: left;">
      <span class="informal">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>
