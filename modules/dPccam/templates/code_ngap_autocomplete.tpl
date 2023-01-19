{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{if $spe_undefined}}
    <li disabled><i>{{tr}}CActeNGAP-specialty-undefined{{/tr}}</i></li>
  {{/if}}
  {{foreach from=$codes item=ngap}}
    <li data-code="{{$ngap->code}}" data-entente="{{$ngap->_tarif->entente_prealable}}">
      <strong>
        <span class="code">{{$ngap->code}}</span>
         (<span class="tarif">{{$ngap->_tarif->tarif}}</span>)
      </strong>
      {{if $ngap->lettre_cle}}
        <strong><small>{{$ngap->libelle}}</small></strong>
      {{else}}
        <small>{{$ngap->libelle}}</small>
      {{/if}}
    </li>
  {{foreachelse}}
    <li>
      <i>Aucun acte NGAP correspondant</i>
    </li>
  {{/foreach}}
</ul>