{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=dietetique value=0}}

{{assign var=curr_user value=$app->_ref_user}}

{{if !$isPraticien}}
  <button class="add me-tertiary" style="display: inline !important;"
          onclick="Soins.addTransmission('{{$sejour->_id}}', '{{$curr_user->_id}}', null, null, null, null, null, 1, null, null, {{$dietetique}});">
    Ajouter une transmission
  </button>
  {{if $count_macrocibles}}
    <button class="add me-tertiary" onclick="Soins.addMacrocible('{{$sejour->_id}}')" style="display: inline !important;">Macrocible</button>
  {{/if}}
{{/if}}

{{if ($curr_user->isChirurgien()  && "soins suivi obs_chir"|gconf)        ||
($curr_user->isAnesth()      && "soins suivi obs_anesth"|gconf)      ||
($curr_user->isMedecin()     && "soins suivi obs_med"|gconf)         ||
(($curr_user->isInfirmiere() || $curr_user->isAideSoignant()) && "soins suivi obs_infirmiere"|gconf) ||
($curr_user->isKine()        && "soins suivi obs_reeducateur"|gconf) ||
($curr_user->isSageFemme()   && "soins suivi obs_sagefemme"|gconf)   ||
($curr_user->isDentiste()    && "soins suivi obs_dentiste"|gconf)    ||
($curr_user->isDieteticien() && "soins suivi obs_dieteticien"|gconf)}}
  <button class="add me-tertiary" onclick="Soins.addObservation('{{$sejour->_id}}', '{{$curr_user->_id}}', null, {{$dietetique}});"
          style="display: inline !important;">
    {{tr}}CObservationMedicale-add{{/tr}}
  </button>
{{/if}}