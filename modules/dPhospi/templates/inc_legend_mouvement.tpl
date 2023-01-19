{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=overweight value="dPpatients CPatient overweight"|gconf}}

<table class="main">
  <tr>
    <th colspan="2" class="title">Légende</th>
  </tr>
  <tr>
    <td rowspan="2">
      <table class="tbl">
        <tr>
          <th colspan="2" class="category">Affectation</th>
        </tr>
        <tr>
          <td style="width: 60px">
            <div class="wrapper_line">
              <div class="affect_legend clit" style="width: 33px">&nbsp;</div>
            </div>
          </td>
          <td>
            Affectation dont le début ou la fin ne sont pas visibles
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit debut_sejour" style="width: 30px">&nbsp;</div>
            </div>
          </td>
          <td>
            Affectation dont le début correspond au début du séjour
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit fin_sejour" style="width: 30px">&nbsp;</div>
            </div>
          </td>
          <td>
            Affectation dont la fin correspond à la fin du séjour
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit affect_left" style="width: 30px">&nbsp;</div>
            </div>
          </td>
          <td>
            Affectation faisant suite à une précédente affectation
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit affect_right" style="width: 30px">&nbsp;</div>
            </div>
          </td>
          <td>
            Affectation qui fera suite à une autre affectation
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit" style="width: 30px">
                <div class="wrapper_op">
                  <div class="operation_in_mouv opacity-40" style="width: 15px; left: 7px;"></div>
                </div>
              </div>
            </div>
          </td>
          <td>
            Intervention
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit" style="width: 30px">
                <div class="wrapper_op">
                  <div class="soins_uscpo opacity-40" style="width: 15px; left: 7px;"></div>
                </div>
              </div>
            </div>
          </td>
          <td>
            Soins USCPO
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit sejour_sortie_confirmee" style="width: 33px">&nbsp;</div>
            </div>
          </td>
          <td>
            Sortie confirmée
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit_bloque" style="width: 33px"><span style="font-size: xx-small;">BLOQUE</span></div>
            </div>
          </td>
          <td>
            Lit bloqué
          </td>
        </tr>
        <tr>
          <td>
            <div class="wrapper_line">
              <div class="affect_legend clit" style="width: 30px">
                <div class="affect_legend prolongation opacity-60" style="width: 33px"><span style="font-size: xx-small;"></span></div>
              </div>
            </div>
          </td>
          <td class="text">
            Prolongation anormale (entrée réelle, pas de sortie réelle et sortie non confirmée)
          </td>
        </tr>
        <tr>
          <th class="item_egal">
            Lit
          </th>
          <td>
            Niveau de prestation souhaité égal à celui du lit
          </td>
        </tr>
        <tr>
          <th class="item_inferior">
            Lit
          </th>
          <td>
            Niveau de prestation souhaité supérieur à celui du lit
          </td>
        </tr>
        <tr>
          <th class="item_superior">
            Lit
          </th>
          <td>
            Niveau de prestation souhaité inférieur à celui du lit
          </td>
        </tr>
        <tr>
          <th class="category" colspan="2">
            Sejour
          </th>
        </tr>
        <tr>
          <td style="text-align: right;">
            <i class="fas fa-exclamation-circle" title="" style="cursor:help;color:red;"></i>
          </td>
          <td class="text">Remarques du séjour</td>
        </tr>
      </table>
    </td>
    <td>
      <table class="tbl">
        <tr>
          <th class="category" colspan="2">Patient</th>
        </tr>
        <tr>
          <td>
            M. X y
          </td>
          <td>
            Patient présent
          </td>
        </tr>
        <tr>
          <td class="septique">
            M. X y
          </td>
          <td>
            Patient septique
          </td>
        </tr>
        <tr>
          <td style="font-style: italic">
            M. X y
          </td>
          <td>
            Séjour de type ambulatoire
          </td>
        </tr>
        <tr>
          <td>
            <span class="patient-not-arrived">M. X y</span>
          </td>
          <td>
            Patient non encore arrivé (première affectation)
          </td>
        </tr>
        <tr>
          <td>
            <span class="patient-not-moved">M. X y</span>
          </td>
          <td>
            Patient non encore présent (après déplacement)
          </td>
        </tr>
        <tr>
          <td>
            <span style="text-decoration: line-through">M. X y</span>
          </td>
          <td>
            Patient sorti
          </td>
        </tr>
        {{if $overweight}}
          <tr>
            <td>
              <img src="images/pictures/overweight.png" />
            </td>
            <td>
              Poids supérieur à {{$overweight}} kilogrammes
            </td>
          </tr>
        {{/if}}

        {{mb_include module=hospi template=inc_legend_bmr_bhre}}
      </table>
    </td>
  </tr>
  <tr>
    <td style="vertical-align: top;">
      <table class="tbl">
        <tr>
          <th class="category" colspan="2">
            Alertes
          </th>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/double.png" name="chambre double possible" /></td>
          <td class="text">Chambre double possible</td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/seul.png" name="chambre simple obligatoire" /></td>
          <td class="text">Chambre simple obligatoire</td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/surb.png" name="colision" /></td>
          <td class="text">Colision : deux patients dans un même lit</td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/sexe.png" name="conflit de sexe" /></td>
          <td class="text">Un homme et une femme dans la même chambre</td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/age.png" name="ecart d'age important" /></td>
          <td class="text">Ecart d'age important : plus de 15 ans d'écart</td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/prat.png" name="conflit de praticiens" /></td>
          <td class="text">Conflit de praticiens : deux patients opérés par deux medecins <br />de même spécialité dans la même chambre
          </td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/path.png" name="conflit de pathologie" /></td>
          <td class="text">Pathologies incompatibles dans la même chambre</td>
        </tr>
        <tr>
          <td style="text-align: right;"><img src="modules/dPhospi/images/annule.png" name="Chambre plus utilisée" /></td>
          <td class="text">Chambre plus utilisée</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>