{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$codes item=_code}}
    <li data-code="{{$_code->code}}">
      <div class="compact" style="float: right;">
        {{foreach from=$_code->activites item=_activite}}
          {{foreach from=$_activite->phases item=_phase}}
             {{if $_phase->tarif}}
             <span title="activité {{$_activite->numero}}, phase {{$_phase->phase}}">
               &bullet;
               {{$_phase->tarif|currency}}
             </span>
             {{/if}}
          {{/foreach}}
        {{/foreach}}
      </div>
      <strong class="code">{{$_code->code}}</strong>
      <br />
      <small>{{$_code->libelleLong|smarty:nodefaults|emphasize:$keywords}}</small>
    </li>
  {{foreachelse}}
    <li>
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>