<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\System\CSourcePOP;

/**
 * Description
 */
class CSMimeKey extends CMbObject
{
    /** @var integer Primary key */
    public $s_mime_key_id;

    /** @var integer The CSourcePOP */
    public $source_id;

    /** @var string The path of the certificate */
    public $cert_path;

    /** @var string The passphrase associated to the certificate */
    public $passphrase;

    /** @var string The initialization vector used for cipher the passphrase */
    public $iv;

    /** @var CSourcePOP The linked CSourcePOP */
    public $_ref_source;

    /** @var string A form field for the passphrase */
    public $_passphrase;

    /** @var boolean True if the passphrase if changed */
    public $_modify = false;

    /** @var bool Indicate if the cert is set */
    public $_is_cert_set;

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "s_mime_key";
        $spec->key   = "s_mime_key_id";

        return $spec;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['source_id']   = 'ref class|CSourcePOP notNull show|0 loggable|0 back|smime_key cascade';
        $props['cert_path']   = 'str';
        $props['passphrase']  = 'str show|0 loggable|0';
        $props['iv']          = 'str show|0 loggable|0';
        $props['_passphrase'] = 'password show|0 loggable|0';
        $props['_cert_file']  = 'str';

        return $props;
    }

    /**
     * @see parent::check()
     */
    public function check()
    {
        $result = null;

        if (CSMimeHandler::getMasterKey() === false) {
            return CAppUI::tr('CSMimeKey-error-master_key_not_generated');
        }

        return parent::check();
    }

    /**
     * @see parent::store()
     */
    public function store()
    {
        if ((!$this->_id || $this->_modify) && $this->_passphrase) {
            $this->iv         = CMbSecurity::generateIV();
            $key              = CSMimeHandler::getMasterKey();
            $this->passphrase = CMbSecurity::encrypt(
                CMbSecurity::AES_COMPAT,
                CMbSecurity::CTR,
                $key,
                $this->_passphrase,
                $this->iv
            );
        }

        return parent::store();
    }

    /**
     * @see parent::delete()
     */
    public function delete()
    {
        if ($this->cert_path) {
            $this->loadRefSource();
            unlink($this->cert_path);
            rmdir(CSMimeHandler::getCertificateDirectoryPath($this->_ref_source));
        }

        return parent::delete();
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields()
    {
        if ($this->_id && $this->passphrase) {
            $this->_passphrase = '*************';
        }

        $this->isCertificateSet();

        parent::updateFormFields();
    }

    /**
     * Return the decrypted passphrase
     *
     * @return bool|string
     */
    public function getPassphrase()
    {
        $passphrase = null;
        if ($this->passphrase) {
            $passphrase = CMbSecurity::decrypt(
                CMbSecurity::AES_COMPAT,
                CMbSecurity::CTR,
                CSMimeHandler::getMasterKey(),
                $this->passphrase,
                $this->iv
            );
        }

        return $passphrase;
    }

    /**
     * Check if the certificate is set
     *
     * @return bool
     */
    public function isCertificateSet()
    {
        return $this->_is_cert_set = $this->cert_path && file_exists($this->cert_path);
    }

    /**
     * Load the linked CSourcePOP
     *
     * @param bool $cache If true, the cache will be used
     *
     * @return CSourcePOP
     */
    public function loadRefSource($cache = true)
    {
        if (!$this->_ref_source) {
            $this->_ref_source = $this->loadFwdRef('source_id', $cache);
        }

        return $this->_ref_source;
    }
}
