{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="_adresse_par_prat" style="{{if !$medecin_adresse_par}}display:none{{/if}}; width: 300px;">
    {{if $medecin_adresse_par}}{{tr}}common-Other|pl{{/tr}} : {{$medecin_adresse_par->_view}}{{/if}}
</div>

<div id="medecin_exercice_place">
  {{if $medecin->_id}}
    {{mb_include module=patients template=inc_choose_medecin_exercice_place
      medecin=$medecin
      object=$object
      field=$field
      submit_on_change=0}}
  {{/if}}
</div>
