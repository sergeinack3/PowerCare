<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CAppUI;
use Ox\Mediboard\Files\CFile;

/**
 * Description
 */
class CCommuneFranceImportV2013 extends CCommuneImport {
  protected $file_url = "http://public.opendatasoft.com/explore/dataset/correspondance-code-insee-code-postal/download/?format=csv";
  protected $zip_name = 'communes_france_2013.zip';

  protected $_class = 'CCommuneFrance';

  protected $map = array(
    'insee_com'    => 'INSEE',
    'nom_comm'     => 'commune',
    'nom_dept'     => 'departement',
    'nom_region'   => 'region',
    'statut'       => 'statut',
    'superficie'   => 'superficie',
    'geo_point_2d' => 'point_geographique',
    'geo_shape'    => 'forme_geographique',
  );

  protected $communes_types = array(
    'Commune simple'       => 'comm',
    'Chef-lieu canton'     => 'cheflieu',
    'Sous-préfecture'      => 'souspref',
    'Préfecture'           => 'pref',
    'Préfecture de région' => 'prefregion',
    "Capitale d'état"      => 'capital',
  );

  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct($this->file_url);
    $this->file_path = rtrim(CFile::getDirectory(), '/\\') . '/upload/communes/communes_france_2013.csv';
  }

  /**
   * @param int    $commune_id ID of the commune for the CP
   * @param string $cp         Postal code to import
   * @param int    $num_line   Line number
   * @param bool   $update     Update existing postal code
   *
   * @return void
   */
  function importCp($commune_id, $cp, $num_line, $update = false) {
    $commune_cp              = new CCommuneCp();
    $commune_cp->commune_id  = $commune_id;
    $commune_cp->code_postal = $cp;

    $commune_cp->loadMatchingObjectEsc();
    if ($commune_cp && $commune_cp->_id && $update) {
      CAppUI::setMsg('CCommuneCp-cp-retrieved', UI_MSG_OK);

      return;
    }

    if ($msg = $commune_cp->store()) {
      CAppUI::setMsg('CCommuneCp-cp-error-store', UI_MSG_WARNING, $num_line, $msg);

      return;
    }

    CAppUI::setMsg('CCommuneCp-msg-create', UI_MSG_OK);
  }

  /**
   *
   * @inheritdoc
   *
   * @param CCommuneFrance $commune
   *
   * @return CCommuneFrance
   */
  function sanitizeCommune($commune) {
    $commune->statut     = $this->communes_types[$commune->statut];
    $commune->population = $commune->population * 1000;
    $count               = preg_match_all('/\-[0-9]|\-\-/', $commune->commune, $matches);
    if ($count) {
      foreach ($matches as $_match) {
        $replace          = str_replace('-', ' ', $_match);
        $commune->commune = str_replace($_match, $replace, $commune->commune);
      }
    }

    return $commune;
  }

  /**
   * @inheritdoc
   *
   * @param CCommuneFrance $commune
   */
  function handleImportCp($line, $commune, $num_line, $update = false) {
    $cps = explode('/', $line['postal_code']);
    foreach ($cps as $_cp) {
      $this->importCp($commune->_id, $_cp, $num_line, $update);
    }
  }

  /**
   * @inheritdoc
   * @return CCommuneFrance
   */
  function getCommuneFromFields($_commune) {
    $commune = new CCommuneFrance();
    $commune->loadByInsee($_commune['INSEE']);

    return $commune;
  }
}
