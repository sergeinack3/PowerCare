<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * dPbloc
 */
?>

<table class="tbl">
  <tr>
    <td class="patient-not-arrived">M. PATIENT Patient</td>
    <td class="text">Patient non encore dans l'établissement</td>
  </tr>
  <tr>
    <td class="septique">M. PATIENT Patient</td>
    <td class="text">Patient septique</td>
  </tr>   
  <tr>
    <td style="background-color:#ffa"></td>
    <td class="text">Patient entré au bloc</td>
  </tr>       
  <tr>
    <td style="background-color:#cfc"></td>
    <td class="text">
      Intervention en cours
      <br />
      (heure d'entrée en salle réelle affichée)
    </td>
  </tr>
  <tr>
    <td style="background-image:url(images/icons/ray.gif); background-repeat:repeat;"></td>
    <td class="text">
      Intervention terminée
      <br />
      (heure d'entrée en salle, de sortie de salle et durée réelles affichées)
    </td>
  </tr> 
  <tr>
    <td style="background-color:#fcc"></td>
    <td class="text">Problème de timing</td>
  </tr>
  <tr>
    <td style="background-color:#ccf"></td>
    <td class="text">Intervention déplacée dans une autre salle</td>
  </tr>
  <tr>
    <td>
      <span class="mediuser" style="border-color: #F99">&nbsp;</span>
    </td>
    <td class="text">Aucun acte codé</td>
  </tr>
</table>