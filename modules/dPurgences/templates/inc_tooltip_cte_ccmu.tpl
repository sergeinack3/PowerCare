{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="display:none;">
  <table class="form" id="glasgow_tooltip">
    <tr>
      <th class="category" style="text-align: center">Glasgow</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> &le; 8</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 9-13</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 14-15</td>
    </tr>
  </table>
  <table class="form" id="pupilles_tooltip">
    <tr>
      <th class="category" style="text-align: center">Pupilles</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> Asym�triques ou ar�actives</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> Sym�triques ou reactives</td>
    </tr>
  </table>
  <table class="form" id="pouls_tooltip">
    <tr>
      <th class="category" style="text-align: center">Pulsations</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> < 40 ou > 150</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 40-50 ou 130-150</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 51-129</td>
    </tr>
  </table>
  <table class="form" id="ta_gauche_tooltip">
    <tr>
      <th class="category" style="text-align: center">Tension (en mmHg)</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> TAS &ge; 230 ou &le; 70 ou TAD &ge; 130</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> TAS 181-229 ou 71-90 ou TAD 115-129</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> TAS 91-180 ou TAD < 115</td>
    </tr>
    <tr>
      <th class="category" style="text-align: center">Femme enceinte</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> TAS &ge; 180 ou &le; 70 ou TAD &ge; 115</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> TAS 160-179 ou 71-80 ou TAD 105-114</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> TAS 81-159 ou TAD < 105</td>
    </tr>
  </table>
  <table class="form" id="ta_droit_tooltip">
    <tr>
      <th class="category" style="text-align: center">Tension (en mmHg)</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> TAS &ge; 230 ou &le; 70 ou TAD &ge; 130</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> TAS 181-229 ou 71-90 ou TAD 115-129</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> TAS 91-180 ou TAD < 115</td>
    </tr>
    <tr>
      <th class="category" style="text-align: center">Femme enceinte</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> TAS &ge; 180 ou &le; 70 ou TAD &ge; 115</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> TAS 160-179 ou 71-80 ou TAD 105-114</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> TAS 81-159 ou TAD < 105</td>
    </tr>
  </table>
  <table class="form" id="ta_tooltip">
    <tr>
      <th class="category" style="text-align: center">Tension (en mmHg)</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> TAS &ge; 230 ou &le; 70 ou TAD &ge; 130</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> TAS 181-229 ou 71-90 ou TAD 115-129</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> TAS 91-180 ou TAD < 115</td>
    </tr>
    <tr>
      <th class="category" style="text-align: center">Femme enceinte</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> TAS &ge; 180 ou &le; 70 ou TAD &ge; 115</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> TAS 160-179 ou 71-80 ou TAD 105-114</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> TAS 81-159 ou TAD < 105</td>
    </tr>
  </table>
  <table class="form" id="frequence_respiratoire_tooltip">
    <tr>
      <th class="category" style="text-align: center">Fr�quence respiratoire</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> > 35 ou &le; 8</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 25-35 ou 9-12</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 13-24</td>
    </tr>
  </table>
  <table class="form" id="spo2_tooltip">
    <tr>
      <th class="category" style="text-align: center">SPO2</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> < 90</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 90-93</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 94-100</td>
    </tr>
  </table>
  <table class="form" id="saturation_air_tooltip">
    <tr>
      <th class="category" style="text-align: center">SpO2 air ambiant</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> < 90</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 90-93</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 94-100</td>
    </tr>
  </table>
  <table class="form" id="peak_flow_tooltip">
    <tr>
      <th class="category" style="text-align: center">PEAK-FLOW</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> &le; 50% de la valeur  pr�dite</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> > 50%</td>
    </tr>
  </table>
  <table class="form" id="temperature_tooltip">
    <tr>
      <th class="category" style="text-align: center">Temp�rature</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> < 32</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 32-35 ou > 40</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 35,1-40</td>
    </tr>
  </table>
  <table class="form" id="glycemie_tooltip">
    <tr>
      <th class="category" style="text-align: center">Glyc�mie (en mmol/l)</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> < 4 ou &ge; 25 </td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 4-24,9</td>
    </tr>
  </table>
  <table class="form" id="cetonemie_tooltip">
    <tr>
      <th class="category" style="text-align: center">C�ton�mie (en mmol/l)</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> &ge; 0,6 </td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> < 0,6</td>
    </tr>
  </table>
  <table class="form" id="proteinurie_tooltip">
    <tr>
      <th class="category" style="text-align: center">Prot�inurie si TAS &ge; 140 et/ou TAD &ge; 90</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> Positive avec TA en D2 </td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> Positive avec TA en D3 </td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> N�gative</td>
    </tr>
  </table>
  <table class="form" id="contraction_uterine_tooltip">
    <tr>
      <th class="category" style="text-align: center">Contractions ut�rines</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> &ge; 3/10mn</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> 1 � 2/10mn</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> < 1/10mn</td>
    </tr>
  </table>
  <table class="form" id="bruit_foetal_tooltip">
    <tr>
      <th class="category" style="text-align: center">Bruit du coeur foetal > 24 SA</th>
    </tr>
    <tr>
      <td><strong>Degr� 1:</strong> 40-100 ou &ge; 180</td>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong>Absent, 101-119 ou 160-179</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> 120-159</td>
    </tr>
    <tr>
      <th class="category" style="text-align: center">Bruit du coeur foetal entre 14 et 24 SA</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> Absent</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> Pr�sent</td>
    </tr>
  </table>
  <table class="form" id="liquide_amniotique_tooltip">
    <tr>
      <th class="category" style="text-align: center">Liquide amniotique</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> M�conial</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> Teint�/clair/ros�</td>
    </tr>
  </table>
  <table class="form" id="EVA_tooltip">
    <tr>
      <th class="category" style="text-align: center">Douleur</th>
    </tr>
    <tr>
      <td><strong>0:</strong> Absence Totale de Douleur</td>
    </tr>
    <tr>
      <td><strong>10:</strong> Pire douleur inimaginable</td>
    </tr>
  </table>

  <table class="form" id="idx_choc" style="display:none;">
    <tr>
      <th class="category" style="text-align: center">Index de choc</th>
    </tr>
    <tr>
      <td><strong>Degr� 2:</strong> Pouls > TAS</td>
    </tr>
    <tr>
      <td><strong>Degr� {{'Ox\Mediboard\Urgences\CRPU'|static:default_degre_cte}}:</strong> Pouls &le; TAS</td>
    </tr>
  </table>
</div>