{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset class="me-small">
  <legend>{{tr}}CRPU-Actions adm{{/tr}}</legend>
  <table class="main">
    <tr>
      <td class="button">
        {{if $rpu->_id}}
          {{mb_ternary var=annule_text test=$sejour->annule value="Rétablir" other="Annuler le RPU"}}
          {{mb_ternary var=annule_class test=$sejour->annule value="change" other="cancel"}}

          {{if $sejour->type === "urg"}}
            <button class="{{$annule_class}}" type="button" onclick="Urgences.cancelRPU();">
              {{$annule_text}}
            </button>
          {{/if}}

          {{if !$sejour->entree_preparee}}
            <button class="tick" type="submit"
                    onclick="var form = getForm('editRPU'); $V(form._entree_preparee, 1); form.submit();">
              {{tr}}CSejour-entree_preparee{{/tr}}
            </button>
          {{/if}}

          {{if $can->admin && $view_mode == "infirmier"}}
            <button class="trash" type="button" onclick="confirmDeletion(getForm('editRPU'),{typeName:'l\'urgence ',objName:'{{$rpu->_view|smarty:nodefaults|JSAttribute}}'})">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}

          <button type="button" class="print" onclick="Urgences.printDossier({{$rpu->_id}})">
            {{tr}}Print{{/tr}} dossier
          </button>

          <button type="button" class="print" onclick="Urgences.printEtiquettes({{$rpu->_id}});">
            {{tr}}CModeleEtiquette.print_labels{{/tr}}
          </button>

          {{mb_include module=patients template=inc_button_vue_globale_docs object=$sejour patient_id=$sejour->patient_id display_center=0}}

          {{if "ecap"|module_active && $current_group|idex:"ecap"|is_numeric}}
            {{mb_include module=ecap template=inc_button_dhe_urgence sejour_id=$sejour->_id}}
          {{/if}}

          {{if "web100T"|module_active}}
            {{mb_include module=web100T template=inc_button_iframe _sejour=$sejour notext=""}}
          {{/if}}

          <a class="button new" href="#1" onclick="window.parent.Control.Modal.close(); window.parent.Urgences.pecInf();">
            {{tr}}CRPU-title-create{{/tr}}
          </a>

          {{if $view_mode == "infirmier"}}
            <!-- Réévaluer le degré d'urgence -->
            <button class="new singleclick" type="button" onclick="Urgences.editReevaluatePEC(null, '{{$rpu->_id}}');">
                {{tr}}CRPUReevalPEC-action-Reevaluate the support{{/tr}}
            </button>
          {{/if}}
        {{else}}
          <button class="submit" type="submit" onclick="getForm('editRPU').onsubmit();">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</fieldset>