<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use DirectoryIterator;
use Exception;
use Ox\Core\CHTTPClient;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Throwable;
use ZipArchive;

/**
 * Description
 */
class CRppsFileDownloader
{
    public const DOWNLOAD_RPPS_FILE_URL
        = 'https://service.annuaire.sante.fr/annuaire-sante-webservices/V300/services/extraction/PS_LibreAcces';

    public const DOWNLOAD_MSSANTE_URL
        = 'https://service.annuaire.sante.fr/annuaire-sante-webservices/V300/services/extraction/Extraction_Correspondance_MSSante';

    public const DOWNLOAD_CPS_FIL_URL
        = 'https://service.annuaire.sante.fr/annuaire-sante-webservices/V300/services/extraction/Porteurs_CPS_CPF';

    public const FILE_DIPLOME_EXERCICE  = 'PS_LibreAcces_Dipl_AutExerc';
    public const FILE_SAVOIR_FAIRE      = 'PS_LibreAcces_SavoirFaire';
    public const FILE_PERSONNE_EXERCICE = 'PS_LibreAcces_Personne_activite';
    public const FILE_MSSANTE           = 'Extraction_Correspondance_MSSante';

    /**
     * @return string
     * @throws CImportMedecinException
     */
    public function downloadRppsFile(string $url): bool
    {
        $tmp_file = tempnam(dirname(__DIR__, 3) . '/tmp', 'med_');

        $fp = fopen($tmp_file, 'w+');

        $result = $this->getFile($fp, $url);

        fclose($fp);

        if (!$result) {
            unlink($tmp_file);
            throw new CImportMedecinException('CRppsFileDownloader-msg-Error-File download failed');
        }

        if (!$this->extractFilesFromArchive($tmp_file)) {
            unlink($tmp_file);
            throw new CImportMedecinException('CRppsFileDownloader-msg-Error-Error while extracting files');
        }

        $this->renameExtractedFiles();

        unlink($tmp_file);

        return true;
    }

    /**
     * @param resource $fs
     *
     * @return CHTTPClient
     */
    protected function initHttpClient(string $url, $fs = null): CHTTPClient
    {
        $http_client = new CHTTPClient($url);

        if ($fs) {
            $http_client->setOption(CURLOPT_FILE, $fs);
        }

        // Pas de vérification de certificat car problème avec le certificat de service.annuaire.sante.fr
        $http_client->setOption(CURLOPT_SSL_VERIFYPEER, false);

        return $http_client;
    }

    public function isRppsFileDownloadable(): bool
    {
        $http_client = $this->initHttpClient(self::DOWNLOAD_RPPS_FILE_URL);

        try {
            $http_client->head(false);
            $is_downloadable = (bool)($http_client->getInfo(CURLINFO_HTTP_CODE) === 200);
        } catch (Throwable $e) {
            return false;
        }

        $http_client->closeConnection();


        return $is_downloadable;
    }

    /**
     * Method is protected to enable mocking
     *
     * @param resource $fp
     *
     * @return bool
     * @throws Exception
     */
    protected function getFile($fp, string $url): bool
    {
        $http_client = $this->initHttpClient($url, $fp);

        return $http_client->get(true);
    }

    /**
     * @param string $file_path
     *
     * @return bool
     * @throws Exception
     */
    protected function extractFilesFromArchive(string $file_path): bool
    {
        $zip = new ZipArchive();
        $zip->open($file_path);
        $success = $zip->extractTo($this->getUploadDirectory());
        $zip->close();

        return $success;
    }

    /**
     * Rename files from uploadDirectories
     *
     * @throws Exception
     */
    private function renameExtractedFiles(): void
    {
        $dir_it = new DirectoryIterator($this->getUploadDirectory());

        while ($dir_it->valid()) {
            if (!$dir_it->isDot()) {
                $file_name = $dir_it->getFilename();

                if (str_starts_with($file_name, self::FILE_DIPLOME_EXERCICE)) {
                    rename(
                        $dir_it->getPathname(),
                        $dir_it->getPath() . DIRECTORY_SEPARATOR
                        . CExternalMedecinBulkImport::FILE_NAME_DIPLOME_AUTORISATION
                    );
                } elseif (str_starts_with($file_name, self::FILE_SAVOIR_FAIRE)) {
                    rename(
                        $dir_it->getPathname(),
                        $dir_it->getPath() . DIRECTORY_SEPARATOR
                        . CExternalMedecinBulkImport::FILE_NAME_SAVOIR_FAIRE
                    );
                } elseif (str_starts_with($file_name, self::FILE_PERSONNE_EXERCICE)) {
                    rename(
                        $dir_it->getPathname(),
                        $dir_it->getPath() . DIRECTORY_SEPARATOR
                        . CExternalMedecinBulkImport::FILE_NAME_PERSONNE_EXERCICE
                    );
                } elseif (str_starts_with($file_name, self::FILE_MSSANTE)) {
                    // TODO Correct file instead of renaming it
                    rename(
                        $dir_it->getPathname(),
                        $dir_it->getPath() . DIRECTORY_SEPARATOR
                        . CExternalMedecinBulkImport::FILE_NAME_MSSANTE
                    );
                }
            }

            $dir_it->next();
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getUploadDirectory(): string
    {
        return CExternalMedecinBulkImport::getUploadDirectory();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPersonneExerciceFilePath(): string
    {
        return $this->getUploadDirectory() . DIRECTORY_SEPARATOR
            . CExternalMedecinBulkImport::FILE_NAME_PERSONNE_EXERCICE;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSavoirFaireFilePath(): string
    {
        return $this->getUploadDirectory() . DIRECTORY_SEPARATOR . CExternalMedecinBulkImport::FILE_NAME_SAVOIR_FAIRE;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getDiplomeExerciceFilePath(): string
    {
        return $this->getUploadDirectory() . DIRECTORY_SEPARATOR
            . CExternalMedecinBulkImport::FILE_NAME_DIPLOME_AUTORISATION;
    }
}
