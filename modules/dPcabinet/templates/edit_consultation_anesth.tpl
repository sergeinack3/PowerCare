{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=auto_refresh_frequency value="dPcabinet CConsultation auto_refresh_frequency"|gconf}}

<script type="text/javascript">

Main.add(function () {
  ListConsults.init("{{$consult->_id}}", "{{$userSel->_id}}", "{{$date}}", "{{$vue}}", "{{$current_m}}", "{{$auto_refresh_frequency}}");

  // @todo : Chargements inutiles ?
  // Chargement pour le sejour
  // DossierMedical.reloadDossierSejour();

  {{if $consult->_id}}
  // Chargement des antecedents, traitements, diagnostics du patients
  // DossierMedical.reloadDossierPatient();
  {{/if}}

  if (document.editAntFrm) {
    document.editAntFrm.type.onchange();
  }
});

</script>

<table class="main">
  <tr>
    <td id="listConsult" style="width: 240px;"></td>
    <td>{{mb_include module=dPcabinet template=inc_full_consult}}</td>
  </tr>
</table>