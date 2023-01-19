<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Rgpd;

/**
 * Description
 */
interface IRGPDCompliant {
  /**
   * Sets the RGPD consent
   *
   * @param CRGPDConsent|null $consent
   *
   * @return CRGPDConsent
   */
  public function setRGPDConsent(CRGPDConsent $consent = null);

  /**
   * Tells if we should ask consent
   *
   * @return bool
   */
  public function shouldAskConsent();

  /**
   * Tells if we technically can ask consent
   *
   * @return bool
   */
  public function canAskConsent();

  /**
   * Get the first name field name
   *
   * @return mixed
   */
  public function getFirstNameField();

  /**
   * Get the last name field name
   *
   * @return mixed
   */
  public function getLastNameField();

  /**
   * Get the birth date field name
   *
   * @return mixed
   */
  public function getBirthDateField();

  /**
   * Get the email
   *
   * @return string|null
   */
  public function getEmail();
}
