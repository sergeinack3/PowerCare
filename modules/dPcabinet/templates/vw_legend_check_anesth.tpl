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
      <th>Critère</th>
      <td>Identification du patient sur toutes les pièces du dossier</td>
    </tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td style="white-space: pre-wrap;">Nom, prénom et date de naissance du patient indiqués sur les documents :
        - Traçant la consultation et la visite préanesthésique,
        - Phase per-anesthésique,
        - Post-interventionnelle.</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank2">
    <tr><th class="title" colspan="2">Medecin anesthésiste</th></tr>
    <tr>
      <th>Critère</th>
      <td>Identification du médecin anesthésiste sur le document traçant la phase préanesthésique</td>
    </tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td>Nom du médecin anesthésiste indiqué sur le document traçant la phase préanesthésique</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank4">
    <tr><th class="title" colspan="2">Traitement habituel</th></tr>
    <tr>
      <th>Critère</th>
      <td>Mention du traitement habituel ou de l'absence de traitement dans le document traçant la CPA (si applicable)</td>
    </tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td style="white-space: pre-wrap;">Le document traçant la CPA indique formellement :
        - Soit l'existence et la mention du traitement habituel,
        - Soit l'absence de traitement.</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank5">
    <tr><th class="title" colspan="2">Risque anesthésique</th></tr>
    <tr>
      <th>Critère</th>
      <td>Mention de l'évaluation du risque anesthésique dans le document traçant la CPA</td>
    </tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td>La mention de l'évaluation du risque anesthésique est retrouvée dans le document traçant la CPA</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank6">
    <tr><th class="title" colspan="2">Type d'anesthésie</th></tr>
    <tr>
      <th>Critère</th>
      <td>Mention du type d'anesthésie proposé au patient dans le document traçant la CPA</td>
    </tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td>La mention du type d'anesthésie proposé au patient est retrouvée dans le document traçant la CPA</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRank7">
    <tr><th class="title" colspan="2">Voies aériennes supérieures</th></tr>
    <tr>
      <th>Critère</th>
      <td>Mention de l'évaluation des conditions d'abord des <strong>voies aériennes supérieures</strong> en phase préanesthésique dans le document traçant la CPA</td>
    </tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td>Le score de Mallampati, la distance thyro-mentonnière ET l'ouverture de bouche sont retrouvés dans le document traçant la CPA
        <br/>OU<br/>Une conclusion explicite est retrouvée dans le document traçant la CPA</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRankPoids">
    <tr><th class="title" colspan="2">Poids</th></tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td>Le poids est renseigné dans les constantes du patient</td>
    </tr>
  </table>

  <table class="tbl" id="DetailRankASA">
    <tr><th class="title" colspan="2">Score ASA</th></tr>
    <tr>
      <th>Elemént requis <br/>pour le valider</th>
      <td>Le score ASA est renseigné dans l'intervention prévue</td>
    </tr>
  </table>
</div>