<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Api;

use Ox\Mediboard\Admin\CUser;

/**
 * Class CAPICMediusers
 */
class CAPICMediusers extends CAPIObject {
  public $id;
  public $login;
  public $nom;
  public $prenom;
  public $type;
  public $email;
  public $tel;
  public $astreinte;
  public $adresse;
  public $cp;
  public $ville;
  public $forcer_changmt_mdp;
  public $autoriser_changmt_mdp;
  public $mot_de_passe;
  public $acces_local;
  public $adeli;
  public $rpps;
  public $inami;
  public $cps;
  public $titres;
  public $initiales;
  public $couleur;
  public $bris_de_glace;
  public $commentaires;
  public $actif;
  public $debut_activite;
  public $fin_activite;
  public $rib;
  public $code_interv_ssr;
  public $secteur;
  public $pratique_tarifaire;
  public $mode_tp_acs;
  public $cab;
  public $conv;
  public $zisd;
  public $ik;
  public $ean;
  public $ean_facturation;
  public $num_ofac;
  public $texte_relance;
  public $mail_apicrypt;
  public $adresse_mssante;
  public $deleguer_compta;
  public $fonction_id;
  public $profil_id;

  static $fields = array(
    'user_id'                => 'id',
    '_user_username'         => 'login',
    '_user_last_name'        => 'nom',
    '_user_first_name'       => 'prenom',
    '_user_type'             => 'type',
    '_user_email'            => 'email',
    '_user_phone'            => 'tel',
    '_user_astreinte'        => 'astreinte',
    '_user_adresse'          => 'adresse',
    '_user_cp'               => 'cp',
    '_user_ville'            => 'ville',
    '_force_change_password' => 'forcer_changmt_mdp',
    '_allow_change_password' => 'autoriser_changmt_mdp',
    '_user_password'         => 'mot_de_passe',
    'remote'                 => 'acces_local',
    'adeli'                  => 'adeli',
    'rpps'                   => 'rpps',
    'inami'                  => 'inami',
    'cps'                    => 'cps',
    'titres'                 => 'titres',
    'initials'               => 'initiales',
    'color'                  => 'couleur',
    'use_bris_de_glace'      => 'bris_de_glace',
    'commentaires'           => 'commentaires',
    'actif'                  => 'actif',
    'deb_activite'           => 'debut_activite',
    'fin_activite'           => 'fin_activite',
    'compte'                 => 'rib',
    'code_intervenant_cdarr' => 'code_interv_ssr',
    'secteur'                => 'secteur',
    'pratique_tarifaire'     => 'pratique_tarifaire',
    'mode_tp_acs'            => 'mode_tp_acs',
    'cab'                    => 'cab',
    'conv'                   => 'conv',
    'zisd'                   => 'zisd',
    'ik'                     => 'ik',
    'ean'                    => 'ean',
    'ean_base'               => 'ean_facturation',
    'ofac_id'                => 'num_ofac',
    'reminder_text'          => 'texte_relance',
    'mail_apicrypt'          => 'mail_apicrypt',
    'mssante_address'        => 'adresse_mssante',
    'compta_deleguee'        => 'deleguer_compta',
    'function_id'            => 'fonction_id',
    '_profile_id'            => 'profil_id',
  );

  static $refs = array(
    'fonction' => array('CFunctions' => 'fonction_id'),
  );

  /**
   * @see parent::updateFields()
   */
  function updateFields() {
    $this->type = ($this->type && isset(CUser::$types[$this->type])) ? utf8_encode(CUser::$types[$this->type]) : null;
  }
}
