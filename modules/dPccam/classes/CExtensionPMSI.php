<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CMbDT;

/**
 * Description
 */
class CExtensionPMSI extends CCCAM
{
    /** @var string The CCAM code */
    public $code;

    /** @var string The PMSI extension */
    public $extension;

    /** @var string The short name */
    public $name;

    /** @var string The long name of the extension */
    public $description;

    /** @var string The application date of the extension */
    public $date_begin;

    /** @var string The end date of the extension */
    public $date_end;

    /**
     * Map the data from the database
     *
     * @param array $row A row returned by a SQL query
     *
     * @return void
     */
    protected function map(array $row): void
    {
        $this->code        = $row['CODEACTE'];
        $this->extension   = $row['EXTENSIONPMSI'];
        $this->name        = ucfirst($row['LIBELLECOURT']);
        $this->description = $row['LIBELLELONG'];
        $this->date_begin  = self::convertDateToMb($row['DATEDEBUT']);
        $this->date_end    = null;
        if ($row['DATEFIN'] != '00000000') {
            $this->date_end = self::convertDateToMb($row['DATEFIN']);
        }
    }

    /**
     * Load the object for the given code and extension
     *
     * @param string $code      The CCAM code
     * @param string $extension The extension to load
     * @param string $date      The date of effect
     *
     * @return CExtensionPMSI|null
     */
    public static function load(string $code, string $extension, string $date = null): ?self
    {
        self::getSpec();
        $ds = self::$spec->ds;

        if (!$date) {
            $date = CMbDT::date();
        }

        $query = "SELECT * FROM p_acte_extension_pmsi
                  WHERE p_acte_extension_pmsi.CODEACTE = %1 AND p_acte_extension_pmsi.EXTENSIONPMSI = %2
                  AND p_acte_extension_pmsi.DATEDEBUT <= %3
                  AND (p_acte_extension_pmsi.DATEFIN >= %3 OR p_acte_extension_pmsi.DATEFIN = '00000000');";
        $query = $ds->prepare($query, $code, $extension, self::convertDateToCCAM($date));

        $result = $ds->exec($query);

        if (!$result) {
            return null;
        }

        if (!$ds->numRows($result)) {
            return null;
        }

        $extension = new self();
        $extension->map($ds->fetchAssoc($result));

        return $extension;
    }

    /**
     * Load the extensions for the given code
     *
     * @param string $code The CCAM code
     * @param string $date The date of effect
     *
     * @return CExtensionPMSI[]
     */
    public static function loadList(string $code, string $date = null): array
    {
        self::getSpec();
        $ds = self::$spec->ds;

        if (!$date) {
            $date = CMbDT::date();
        }

        $query = "SELECT * FROM p_acte_extension_pmsi
      WHERE p_acte_extension_pmsi.CODEACTE = %1 AND p_acte_extension_pmsi.DATEDEBUT <= %2
      AND (p_acte_extension_pmsi.DATEFIN >= %2 OR p_acte_extension_pmsi.DATEFIN = '00000000');";
        $query = $ds->prepare($query, $code, self::convertDateToCCAM($date));

        $result = $ds->exec($query);

        $extensions = [];

        if ($result) {
            while ($row = $ds->fetchAssoc($result)) {
                $_extension = new self();
                $_extension->map($row);
                $extensions[$_extension->extension] = $_extension;
            }
        }

        return $extensions;
    }

    /**
     * Convert a date in the CCAM format to the ISO format
     *
     * @param string $date The date in CCAM format
     *
     * @return string
     */
    public static function convertDateToMb(string $date): string
    {
        return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
    }

    /**
     * Convert a date in ISO format to the CCAM format
     *
     * @param string $date The date in ISO format
     *
     * @return string
     */
    public static function convertDateToCCAM(string $date): string
    {
        return CMbDT::format($date, "%Y%m%d");
    }
}
