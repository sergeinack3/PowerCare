{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div  style="display: none;">
  <table class="tbl" id="DetailRank1">
    <tr><th class="title" colspan="2">Identification du patient</th></tr>
    <tr>
      <th>Crit�re</th>
      <td>Identification du patient sur toutes les pi�ces du dossier</td>
    </tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td style="white-space: pre-wrap;">Nom, pr�nom et date de naissance du patient indiqu�s sur les documents :
        - Tra�ant la consultation et la visite pr�anesth�sique,
        - Phase per-anesth�sique,
        - Post-interventionnelle.</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank2">
    <tr><th class="title" colspan="2">Medecin anesth�siste</th></tr>
    <tr>
      <th>Crit�re</th>
      <td>Identification du m�decin anesth�siste sur le document tra�ant la phase pr�anesth�sique</td>
    </tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td>Nom du m�decin anesth�siste indiqu� sur le document tra�ant la phase pr�anesth�sique</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank4">
    <tr><th class="title" colspan="2">Traitement habituel</th></tr>
    <tr>
      <th>Crit�re</th>
      <td>Mention du traitement habituel ou de l'absence de traitement dans le document tra�ant la CPA (si applicable)</td>
    </tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td style="white-space: pre-wrap;">Le document tra�ant la CPA indique formellement :
        - Soit l'existence et la mention du traitement habituel,
        - Soit l'absence de traitement.</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank5">
    <tr><th class="title" colspan="2">Risque anesth�sique</th></tr>
    <tr>
      <th>Crit�re</th>
      <td>Mention de l'�valuation du risque anesth�sique dans le document tra�ant la CPA</td>
    </tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td>La mention de l'�valuation du risque anesth�sique est retrouv�e dans le document tra�ant la CPA</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank6">
    <tr><th class="title" colspan="2">Type d'anesth�sie</th></tr>
    <tr>
      <th>Crit�re</th>
      <td>Mention du type d'anesth�sie propos� au patient dans le document tra�ant la CPA</td>
    </tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td>La mention du type d'anesth�sie propos� au patient est retrouv�e dans le document tra�ant la CPA</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank7">
    <tr><th class="title" colspan="2">Voies a�riennes sup�rieures</th></tr>
    <tr>
      <th>Crit�re</th>
      <td>Mention de l'�valuation des conditions d'abord des <strong>voies a�riennes sup�rieures</strong> en phase pr�anesth�sique dans le document tra�ant la CPA</td>
    </tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td>Le score de Mallampati, la distance thyro-mentonni�re ET l'ouverture de bouche sont retrouv�s dans le document tra�ant la CPA
        <br/>OU<br/>Une conclusion explicite est retrouv�e dans le document tra�ant la CPA</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRankPoids">
    <tr><th class="title" colspan="2">Poids</th></tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td>Le poids est renseign� dans les constantes du patient</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRankASA">
    <tr><th class="title" colspan="2">Score ASA</th></tr>
    <tr>
      <th>Elem�nt requis <br/>pour le valider</th>
      <td>Le score ASA est renseign� dans l'intervention pr�vue</td>
    </tr>
  </table>
</div>