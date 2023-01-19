{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=redirect_synthese value=""}}

<fieldset class="me-small-form">
  <legend>Actions médicales</legend>
  <table class="main">
    <tr>
      <td class="button">
        <!-- Réévaluer le degré d'urgence -->
        <button class="new singleclick" type="button" onclick="Urgences.editReevaluatePEC(null, '{{$rpu->_id}}');">
          {{tr}}CRPUReevalPEC-action-Reevaluate the support{{/tr}}
        </button>

        {{if !$rpu->mutation_sejour_id}}
          {{if $conf.dPurgences.gerer_reconvoc == "1"}}
            <!-- Reconvocation => formulaire de creation de consultation avec champs pre-remplis -->
            <button id="button_reconvoc"
                    class="new singleclick"
                    {{if ($conf.dPurgences.hide_reconvoc_sans_sortie == "1") && !$sejour->sortie_reelle}}disabled{{/if}}
                    type="button"
                    onclick="Urgences.modalSortie(ContraintesRPU.checkObligatory.curry('{{$rpu->_id}}', getForm('editSejour'), newConsultation.curry({{$consult->_ref_plageconsult->chir_id}},{{$consult->patient_id}},{{$consult->_id}})));">
                {{tr}}CRPU-action-Reconvene{{/tr}}
            </button>
          {{/if}}
          {{if "ecap"|module_active && ("ecap dhe dhe_mode_choice"|gconf == "new" && !"dPurgences CRPU gerer_hospi"|gconf)}}
            {{mb_include module=ecap template=inc_button_non_prevue patient_id=$rpu->_patient_id}}
          {{else}}
            {{if "dPurgences CRPU gerer_hospi"|gconf == "1" && !$sejour->sortie_reelle}}
              <!-- Hospitalisation immediate, creation d'un sejour et transfert des actes dans le nouveau sejour -->
              <button class="new singleclick" type="button"
                      onclick="Urgences.verifyNbInscription('{{$rpu->_id}}', 'Urgences.modalSortie.curry(ContraintesRPU.checkObligatory.curry( \'{{$rpu->_id}}\', getForm(\'editSejour\'), Urgences.hospitalize.curry(\'{{$rpu->_id}}\')))')">
                Hospitaliser
              </button>
            {{/if}}
          {{/if}}

          {{if ("dPurgences CRPU type_sejour"|gconf !== "urg_consult") && $sejour->type != "consult"}}
            {{mb_include module=urgences template=inc_uhcd}}
          {{elseif ("dPurgences CRPU type_sejour"|gconf === "urg_consult")}}
            {{mb_include module=urgences template=inc_cnsp}}
          {{/if}}
        {{/if}}
        <!--  Autoriser sortie du patient --> <!--  Autoriser sortie du patient et valider la sortie -->
        <form name="editSortieAutorise" method="post" action="?m={{$m}}">
          {{mb_class object=$rpu}}
          {{mb_key   object=$rpu}}
          <input type="hidden" name="del" value="0"/>
          <input type="hidden" name="sortie_autorisee" value="1"/>
          <input type="hidden" name="date_sortie_aut" value="now"/>
        </form>
        <div id="div_sortie_reelle" style="display: inline-block;">
          {{mb_include module=urgences template=inc_sortie_reelle}}
        </div>
      </td>
    </tr>
  </table>
</fieldset>
