<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

/**
 * Description
 */
class CHDFinance extends CHDObject {
  /** @var integer Primary key */
  public $hd_finance_id;

  public $annee;
  public $marge_brute;
  public $caf;
  public $caf_nette;
  public $duree_dette;
  public $inde_finance;
  public $intensite_invest;
  public $vetuste_equip;
  public $vetuste_bat;
  public $besoin_fonds_roulement;
  public $fond_roulement_net;
  public $creances_non_recouvrees;
  public $dette_fournisseur;

  static public $field_page = array(
    'marge_brute'             => '60',
    'caf'                     => '61',
    'caf_nette'               => '62',
    'duree_dette'             => '63',
    'inde_finance'            => '64',
    'intensite_invest'        => '65',
    'vetuste_equip'           => '66',
    'vetuste_bat'             => '67',
    'besoin_fonds_roulement'  => '68',
    'fond_roulement_net'      => '69',
    'creances_non_recouvrees' => '70',
    'dette_fournisseur'       => '71',
  );

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hd_finance";
    $spec->key   = "hd_finance_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['annee']                   = 'num notNull';
    $props['marge_brute']             = 'num';
    $props['caf']                     = 'num';
    $props['caf_nette']               = 'num';
    $props['duree_dette']             = 'num';
    $props['inde_finance']            = 'num';
    $props['intensite_invest']        = 'num';
    $props['vetuste_equip']           = 'num';
    $props['vetuste_bat']             = 'num';
    $props['besoin_fonds_roulement']  = 'num';
    $props['fond_roulement_net']      = 'num';
    $props['creances_non_recouvrees'] = 'num';
    $props['dette_fournisseur']       = 'num';
    $props['hd_etablissement_id']     .= ' back|finances';

    return $props;
  }
}
