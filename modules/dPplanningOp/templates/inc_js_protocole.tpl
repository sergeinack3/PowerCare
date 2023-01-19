{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="type_prot_chir" value="prot-"}}
{{assign var="type_prot_anesth" value="prot-"}}
{{assign var=libelle value=""}}
{{if $protocole->protocole_prescription_anesth_class == "CPrescriptionProtocolePack"}}
  {{assign var="type_prot_anesth" value="pack-"}}
{{/if}}
{{if $protocole->protocole_prescription_chir_class == "CPrescriptionProtocolePack"}}
  {{assign var="type_prot_chir" value="pack-"}}
{{/if}}
{{if $protocole->_ref_protocole_prescription_chir}}
  {{assign var=libelle value=$protocole->_ref_protocole_prescription_chir->libelle}}
{{/if}}

protocole_id     : "{{$protocole->protocole_id}}",
chir_id          : "{{$protocole->chir_id}}",
chir_view        : "{{if $protocole->chir_id}}{{$protocole->_ref_chir->_view}}{{/if}}",
codes_ccam       : "{{$protocole->codes_ccam}}",
DP               : "{{$protocole->DP}}",
DR               : "{{$protocole->DR}}",
libelle          : "{{$protocole->libelle|smarty:nodefaults|escape:"javascript"}}",
libelle_sejour   : "{{$protocole->libelle_sejour|smarty:nodefaults|escape:"javascript"}}",
_time_op         : "{{$protocole->_time_op}}",
presence_preop   : "{{$protocole->presence_preop|truncate:5:''}}",
presence_postop  : "{{$protocole->presence_postop|truncate:5:''}}",
duree_bio_nettoyage: "{{$protocole->duree_bio_nettoyage|truncate:5:''}}",
examen           : "{{$protocole->examen|smarty:nodefaults|escape:"javascript"}}",
materiel         : "{{$protocole->materiel|smarty:nodefaults|escape:"javascript"}}",
exam_per_op      : "{{$protocole->exam_per_op|smarty:nodefaults|escape:"javascript"}}",
convalescence    : "{{$protocole->convalescence|smarty:nodefaults|escape:"javascript"}}",
depassement      : "{{$protocole->depassement}}",
forfait          : "{{$protocole->forfait}}",
fournitures      : "{{$protocole->fournitures}}",
type             : "{{$protocole->type}}",
type_pec         : "{{$protocole->type_pec}}",
facturable       : "{{$protocole->facturable}}",
type_anesth      : "{{$protocole->type_anesth}}",
duree_uscpo      : "{{$protocole->duree_uscpo}}",
duree_preop      : "{{$protocole->duree_preop}}",
duree_hospi      : "{{$protocole->duree_hospi}}",
duree_heure_hospi :"{{$protocole->duree_heure_hospi}}",
cote             : "{{$protocole->cote}}",
exam_extempo     : "{{$protocole->exam_extempo}}",
rques_sejour     : "{{$protocole->rques_sejour|smarty:nodefaults|escape:"javascript"}}",
rques_operation  : "{{$protocole->rques_operation|smarty:nodefaults|escape:"javascript"}}",
protocole_prescription_anesth_id: "{{$type_prot_anesth}}{{$protocole->protocole_prescription_anesth_id}}",
protocole_prescription_chir_id  : "{{$type_prot_chir}}{{$protocole->protocole_prescription_chir_id}}",
service_id       : "{{$protocole->service_id}}",
_service_view    : "{{$protocole->_ref_service}}",
uf_hebergement_id: "{{$protocole->uf_hebergement_id}}",
_uf_hebergement_view: "{{$protocole->_ref_uf_hebergement}}",
uf_medicale_id   : "{{$protocole->uf_medicale_id}}",
_uf_medicale_view : "{{$protocole->_ref_uf_medicale}}",
uf_soins_id      : "{{$protocole->uf_soins_id}}",
_uf_soins_view   : "{{$protocole->_ref_uf_soins}}",
charge_id        : "{{$protocole->charge_id}}",
_types_ressources_ids : "{{$protocole->_types_ressources_ids}}",
hospit_de_jour   : "{{$protocole->hospit_de_jour}}",
libelle_protocole_prescription_chir: "{{$libelle}}",
hospit_de_jour   : "{{$protocole->hospit_de_jour}}",
facturation_rapide : "{{$protocole->facturation_rapide}}",
_codage_ccam_chir   : "{{$protocole->codage_ccam_chir}}",
_codage_ccam_anesth : "{{$protocole->codage_ccam_anesth}}",
_codage_ngap_sejour : "{{$protocole->codage_ngap_sejour}}",
_pack_appFine_ids : "{{$protocole->_pack_appFine_ids}}",
_docitems_guid_sejour: "{{$protocole->_docitems_guid_sejour}}",
_docitems_guid_operation: "{{$protocole->_docitems_guid_operation}}",
RRAC              : "{{$protocole->RRAC}}",
hour_entree_prevue : "{{$protocole->time_entree_prevue|date_format:"%H"|ltrim:'0'}}",
min_entree_prevue : "{{$protocole->time_entree_prevue|date_format:"%M"}}",
circuit_ambu      : "{{$protocole->circuit_ambu}}",
sProtocolesOp_ids: "{{"|"|implode:$protocole->_ids_protocoles_op|smarty:nodefaults}}",
sProtocolesOp_libelles_list: {{$protocole->_list_libelles_protocoles_op|@array_values|@json}},
{{if "eds"|module_active && "eds CSejour allow_eds_input"|gconf}}
code_EDS      : "{{$protocole->code_EDS}}",
{{/if}}
