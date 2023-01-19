<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\CAppUI;

/**
 * Description
 */
class CCompteRenduService
{
    /** @var CCompteRendu */
    protected $compte_rendu;

    protected const DIV_BODY = '<div id="body">';

    /**
     * @param CCompteRendu $compte_rendu
     */
    public function __construct(CCompteRendu $compte_rendu)
    {
        $this->compte_rendu = $compte_rendu;
    }

    /**
     * @return void
     */
    public function manageCancelAndReplaceMotion(): void
    {
        if (!$this->compte_rendu->object_id || ($this->compte_rendu->version <= 1)) {
            return;
        }

        $cancel_and_replace = CAppUI::gconf('dPcompteRendu CCompteRendu cancel_and_replace');

        if (!$cancel_and_replace) {
            return;
        }

        $pos_cancel_and_replace = strpos($this->compte_rendu->_source, $cancel_and_replace);

        $pos_body = strpos($this->compte_rendu->_source, static::DIV_BODY);

        // Motion already present
        if ($pos_cancel_and_replace !== false) {
            $this->compte_rendu->_source = preg_replace(
                "#{$cancel_and_replace}\s*[0-9]*#",
                $cancel_and_replace . ' ' . ($this->compte_rendu->version - 1),
                $this->compte_rendu->_source
            );
        } elseif ($pos_body !== false) {
            // Header / footer case
            $this->compte_rendu->_source =
                substr($this->compte_rendu->_source, 0, $pos_body + strlen(static::DIV_BODY))
                . '<div>' . $cancel_and_replace . '</div>'
                . substr(
                    $this->compte_rendu->_source,
                    $pos_body + strlen(static::DIV_BODY)
                );
        } else {
            $this->compte_rendu->_source = '<div>' . $cancel_and_replace . '</div>' . $this->compte_rendu->_source;
        }
    }
}
