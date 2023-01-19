{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="object" value=$evenement}}
{{assign var="do_subject_aed" value="do_evenement_patient_aed"}}
{{mb_include module=salleOp template=js_codage_ccam}}
{{mb_script module=facturation script=facture   ajax=true}}
{{mb_script module=cabinet     script=reglement ajax=true}}

<script>
  Main.add(function () {
    var tabsReglement = Control.Tabs.create('tab-evt-reglement', true);
    if (tabsReglement.activeLink.key == "reglement_evt") {
      Facture.reloadEvt('{{$evenement->_guid}}');
    }
    Facture.evenement_guid = '{{$evenement->_guid}}';
    Facture.evenement_id = '{{$evenement->_id}}';
    Facture.user_id = '{{$evenement->_ref_praticien->_id}}';
  });
</script>

<!-- Formulaire pour réactualiseér -->
<form name="editFrmFinish" method="get">
  {{mb_key object=$object}}
</form>

<ul id="tab-evt-reglement" class="control_tabs small">
  <li onmousedown="Facture.reloadEvt('{{$evenement->_guid}}');" id="a_reglements_evt"><a href="#reglement_evt">{{tr}}module-dPfacturation-court{{/tr}}</a></li>
  {{if $app->user_prefs.ccam_consultation == 1}}
    {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
      <li><a href="#ccam"{{if $object->_ref_actes_ccam|@count == 0}} class="empty"{{/if}}>{{tr}}CActeCCAM{{/tr}} <small id="count_ccam_{{$object->_guid}}">({{$object->_ref_actes_ccam|@count}})</small></a></li>
      <li><a href="#ngap"{{if $object->_ref_actes_ngap|@count == 0}} class="empty"{{/if}}>{{tr}}CActeNGAP{{/tr}} <small id="count_ngap_{{$object->_guid}}">({{$object->_ref_actes_ngap|@count}})</small></a></li>
    {{/if}}
    {{if "dPccam frais_divers use_frais_divers_CEvenementPatient"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
      <li><a href="#fraisdivers">{{tr}}CFraisDivers{{/tr}}</a></li>
    {{/if}}
  {{/if}}
</ul>

<div id="reglement_evt" style="display: none;"></div>

{{if $app->user_prefs.ccam_consultation == 1}}
  <div id="ccam" style="display: none;">
    {{assign var="module" value="oxCabinet"}}
    {{assign var="subject" value=$object}}
    {{mb_include module=salleOp template=inc_codage_ccam}}
  </div>

  <div id="ngap" style="display: none;">
    <div id="listActesNGAP" data-object_id="{{$object->_id}}" data-object_class="{{$object->_class}}">
      {{assign var="_object_class" value="CConsultation"}}
      {{mb_include module=cabinet template=inc_codage_ngap object=$object}}
    </div>
  </div>
  
  {{if "dPccam frais_divers use_frais_divers_CEvenementPatient"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
    <div id="fraisdivers" style="display: none;">
      {{mb_include module=ccam template=inc_frais_divers object=$object}}
    </div>
  {{/if}}
{{/if}}
