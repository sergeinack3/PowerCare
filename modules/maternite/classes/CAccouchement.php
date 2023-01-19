<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;

/**
 * Accouchement
 */
class CAccouchement extends CMbObject {
  // DB Table key
  public $accouchement_id;

  public $dossier_perinat_id;
  public $date;
  public $sage_femme_resp_id;
  public $medecin_resp_id;
  public $effectue_par_type;
  public $effectue_par_type_autre;
  public $presentation;
  public $moment_rupt_membranes;
  public $qte_liquide_rupt_membranes;
  public $aspect_liquide_rupt_membranes;
  public $aspect_liquide_rupt_membranes_desc;
  public $aspect_liquide_post_rupt_membranes;
  public $aspect_liquide_post_rupt_membranes_desc;
  public $voie_basse_spont;
  public $pos_voie_basse_spont;
  public $interv_voie_basse;
  public $interv_voie_basse_forceps;
  public $interv_voie_basse_ventouse;
  public $interv_voie_basse_spatules;
  public $interv_voie_basse_pet_extr_siege;
  public $interv_voie_basse_grd_extr_siege;
  public $interv_voie_basse_autre_man_siege;
  public $interv_voie_basse_man_dyst_epaules;
  public $interv_voie_basse_man_dyst_epaules_desc;
  public $interv_voie_basse_autre_man;
  public $interv_voie_basse_autre_man_desc;
  public $interv_voie_basse_motif;
  public $interv_voie_basse_motif_asso;
  public $cesar_avt_travail;
  public $cesar_avt_travail_type;
  public $cesar_pdt_travail;
  public $cesar_pdt_travail_type;
  public $cesar_motif;
  public $cesar_motif_asso;
  public $endroit_action_interv_voie_basse;
  public $type_cesar;
  public $remarques_cesar;
  public $actes_associes_cesar;
  public $actes_associes_cesar_hysterectomie_hemostase;
  public $actes_associes_cesar_kystectomie_ovarienne;
  public $actes_associes_cesar_myomectomie_unique;
  public $actes_associes_cesar_ste_tubaire;
  public $actes_associes_cesar_interv_gross_abd;
  public $pb_cordon;
  public $pb_cordon_procidence;
  public $pb_cordon_circ_serre;
  public $pb_cordon_noeud_vrai;
  public $pb_cordon_brievete;
  public $pb_cordon_insert_velament;
  public $pb_cordon_autre;
  public $pb_cordon_autre_desc;
  public $duree_ouverture_oeuf_jours;
  public $duree_ouverture_oeuf_heures;
  public $duree_travail_heures;
  public $duree_travail_de_5cm_heures;
  public $duree_deambulation_heures;
  public $duree_deambulation_minutes;
  public $duree_entre_dilat_efforts_expuls;
  public $duree_efforts_expuls;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "accouchement";
    $spec->key   = "accouchement_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                                                 = parent::getProps();
    $props["dossier_perinat_id"]                           = "ref notNull class|CDossierPerinat back|accouchements";
    $props["date"]                                         = "dateTime";
    $props["sage_femme_resp_id"]                           = "ref class|CMediusers back|accouchements_sf";
    $props["medecin_resp_id"]                              = "ref class|CMediusers back|accouchements_med_resp";
    $props["effectue_par_type"]                            = "enum list|med|sf|autre";
    $props["effectue_par_type_autre"]                      = "str";
    $props["presentation"]                                 = "enum list|sommop|sommos|face|bregma|front|siegecomp|siegdecomp|transv";
    $props["moment_rupt_membranes"]                        = "enum list|avant|spont|artif|cesar";
    $props["qte_liquide_rupt_membranes"]                   = "enum list|norm|oligoa|hydroa|absent";
    $props["aspect_liquide_rupt_membranes"]                = "enum list|clair|meco|sang|teinte|autre";
    $props["aspect_liquide_rupt_membranes_desc"]           = "str";
    $props["aspect_liquide_post_rupt_membranes"]           = "enum list|clair|meco|sang|teinte|autre";
    $props["aspect_liquide_post_rupt_membranes_desc"]      = "str";
    $props["voie_basse_spont"]                             = "bool";
    $props["pos_voie_basse_spont"]                         = "enum list|decubdors|decublat|vert";
    $props["interv_voie_basse"]                            = "bool";
    $props["interv_voie_basse_forceps"]                    = "bool";
    $props["interv_voie_basse_ventouse"]                   = "bool";
    $props["interv_voie_basse_spatules"]                   = "bool";
    $props["interv_voie_basse_pet_extr_siege"]             = "bool";
    $props["interv_voie_basse_grd_extr_siege"]             = "bool";
    $props["interv_voie_basse_autre_man_siege"]            = "bool";
    $props["interv_voie_basse_man_dyst_epaules"]           = "bool";
    $props["interv_voie_basse_man_dyst_epaules_desc"]      = "str";
    $props["interv_voie_basse_autre_man"]                  = "bool";
    $props["interv_voie_basse_autre_man_desc"]             = "str";
    $props["interv_voie_basse_motif"]                      = "enum list|mat|foet";
    $props["interv_voie_basse_motif_asso"]                 = "str";
    $props["cesar_avt_travail"]                            = "bool";
    $props["cesar_avt_travail_type"]                       = "enum list|prog|urg";
    $props["cesar_pdt_travail"]                            = "bool";
    $props["cesar_pdt_travail_type"]                       = "enum list|urg|prog";
    $props["cesar_motif"]                                  = "enum list|mat|foet";
    $props["cesar_motif_asso"]                             = "str";
    $props["endroit_action_interv_voie_basse"]             = "enum list|detinf|detmoy|detsup|tete";
    $props["type_cesar"]                                   = "enum list|segtransv|segvert|corpo|segcorpo|vag";
    $props["remarques_cesar"]                              = "text helped";
    $props["actes_associes_cesar"]                         = "bool";
    $props["actes_associes_cesar_hysterectomie_hemostase"] = "bool";
    $props["actes_associes_cesar_kystectomie_ovarienne"]   = "bool";
    $props["actes_associes_cesar_myomectomie_unique"]      = "bool";
    $props["actes_associes_cesar_ste_tubaire"]             = "bool";
    $props["actes_associes_cesar_interv_gross_abd"]        = "bool";
    $props["pb_cordon"]                                    = "bool";
    $props["pb_cordon_procidence"]                         = "bool";
    $props["pb_cordon_circ_serre"]                         = "bool";
    $props["pb_cordon_noeud_vrai"]                         = "bool";
    $props["pb_cordon_brievete"]                           = "bool";
    $props["pb_cordon_insert_velament"]                    = "bool";
    $props["pb_cordon_autre"]                              = "bool";
    $props["pb_cordon_autre_desc"]                         = "str";
    $props["duree_ouverture_oeuf_jours"]                   = "num";
    $props["duree_ouverture_oeuf_heures"]                  = "num";
    $props["duree_travail_heures"]                         = "num";
    $props["duree_travail_de_5cm_heures"]                  = "num";
    $props["duree_deambulation_heures"]                    = "num";
    $props["duree_deambulation_minutes"]                   = "num";
    $props["duree_entre_dilat_efforts_expuls"]             = "num";
    $props["duree_efforts_expuls"]                         = "num";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->date ?
      CAppUI::tr("CAccouchement-of", CMbDT::format($this->date, CAppUI::conf("datetime"))) : CAppUI::tr("CAccouchement");
  }
}
