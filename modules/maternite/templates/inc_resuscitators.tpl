{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<ul>
    {{foreach from=$naissance->_ref_resuscitators item=_resuscitator}}
      <li data-id="{{$_resuscitator->_id}}" style="list-style: none;" class='me-margin-top-3'>
        <button type="button" class="remove notext" onclick="DossierMater.addOrDeleteNaissanceReaPrat(null, '{{$naissance->_id}}', '{{$_resuscitator->_id}}', 1)"></button>
          {{mb_value object=$_resuscitator field=rea_par_id}}
          {{if $_resuscitator->rea_par}}
            ({{mb_value object=$_resuscitator field=rea_par}})
          {{/if}}
      </li>
    {{/foreach}}
</ul>
