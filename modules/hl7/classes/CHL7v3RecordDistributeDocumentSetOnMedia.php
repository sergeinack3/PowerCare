<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\Events\XDM\CHL7v3EventXDMException;

/**
 * Class CHL7v3RecordDistributeDocumentSetOnMedia
 * Record distribute document set on media
 */
class CHL7v3RecordDistributeDocumentSetOnMedia implements IShortNameAutoloadable
{
    const TMP_DIR    = "tmp/ihe_xdm";
    const EVENT_NAME = "CHL7v3EventXDMDistributeDocumentSetOnMedia";

    public $_ref_sender;

    public $codes   = [];
    public $dom_cda = null;
    public $xpath   = null;

    /**
     * Handle event
     *
     * @param CMbObject $object Object
     * @param array     $data   Nodes data
     *
     * @return null|string
     * @throws Exception
     */
    public function handle(CMbObject $object, $data)
    {
        $result = false;
        $file   = CMbArray::get($data, "file");

        dump("Handle");

        /** @todo On va le récupérer depuis le dispatcher */
        $senders = CMbArray::get(CInteropSender::getObjectsBySupportedEvents([self::EVENT_NAME]), self::EVENT_NAME);
        $sender  = reset($senders);

        /* Extract the archive and return the path */
        if (!$path = self::getArchiveFilePath($file)) {
            return null;
        }

        dump("0 - Extract the archive");
        // @todo foreach sur les subsets ??
        $subset = "IHE_XDM/SUBSET01";
        if (!$files = CMbPath::getFiles("$path/$subset")) {
            throw CHL7v3EventXDMException::emptyArchiveZIP();
        }

        dump("1 - Extract metadata");
        // 1 - Extract metadata
        $matches = preg_grep('/metadata.xml/i', $files);
        if (!$matches) {
            throw CHL7v3EventXDMException::emptyMetadataMissing();
        }

        dump("2 - Parse metadata");
        // 2 - Parse metadata : DocumentEntry, hash et size
        $file_medata_path = reset($matches);
        $metadata         = $this->handleMetadata($file_medata_path);

        foreach ($metadata as $_cda_name => $_metatada) {
            $cda_file = "$path/$subset/$_cda_name";
            dump("3 - Comparison of metadata with CDA metadata");

            // CDA document not found
            if (!"$path/$subset/$_cda_name") {
                throw CHL7v3EventXDMException::cdaMissing();
            }

            // Not CDA File
            if (!$this->isFileCDADocument($cda_file, $sender)) {
                throw CHL7v3EventXDMException::noCDAFile();
            }
            dump("CDA file OK");

            // 3 - Comparison of metadata with CDA metadata : hash + size
            $cda_content = file_get_contents($cda_file);

            $cda_size      = strlen($cda_content);
            $metadata_size = CMbArray::get($_metatada, "size");
            if ((CMbArray::get($_metatada, "size") != $cda_size)) {
                throw CHL7v3EventXDMException::metadataDifferentSize($metadata_size, $cda_size);
            }
            dump("Size OK");

            $cda_hash      = strtoupper(sha1($cda_content));
            $metadata_hash = strtoupper(CMbArray::get($_metatada, "hash"));
            if ($metadata_hash != $cda_hash) {
                throw CHL7v3EventXDMException::metadataDifferentHash($metadata_hash, $cda_hash);
            }
            dump("Hash OK");

            dump("4 - Settlement of the trace");
            // 4 - Settlement of the trace
            CEAIDispatcher::dispatch($cda_content, $sender);
        }

        self::cleanFiles($path);

        return $result;
    }

    /**
     * Extract the given attachment and return it's path
     *
     * @param array $file Upload file
     *
     * @return false|string False or the path of the extracted content
     */
    protected static function getArchiveFilePath($file)
    {
        /* Creates a temporary dir */
        $dir = self::TMP_DIR;

        CMbPath::forceDir($dir);
        $path    = "{$dir}/" . CMbArray::get($file, "name");
        $archive = "{$path}.zip";

        /* Must copy the file because the CMbPath::extract function uses the file extension to detect the type of compression */
        if (!copy($file["tmp_name"], $archive) || !CMbPath::extract($archive, $path)) {
            $path = false;
        }

        return $path;
    }

    /**
     * Handle metadata
     *
     * @param string $file_medata_path File
     *
     * @return array
     * @throws Exception
     */
    public function handleMetadata($file_medata_path)
    {
        // todo utiliser le CXDSXMLDocument
        $dom_metadata = new CMbXMLDocument('utf-8');
        $dom_metadata->load($file_medata_path);

        return $this->getMetadataNodes($dom_metadata);
    }

    /**
     * Get data nodes
     *
     * @param CMbXMLDocument $dom DOM metadatas
     *
     * @return array Get nodes
     * @throws Exception
     *
     */
    function getMetadataNodes(CMbXMLDocument $dom)
    {
        // todo Utiliser le CXDSPath (pas besoind de register les namespase)
        $this->xpath = $xpath = new CMbXPath($dom);
        $xpath->registerNamespace("rs", "urn:oasis:names:tc:ebxml-regrep:xsd:rs:3.0");
        $xpath->registerNamespace("rim", "urn:oasis:names:tc:ebxml-regrep:xsd:rim:3.0");

        $extrinsicObjects = $xpath->query("//rim:ExtrinsicObject");
        $metadata         = [];
        if (!$extrinsicObjects) {
            return $metadata;
        }

        // todo XDSTransformer::parseNode (transformation en CXDSDocumentEntry
        foreach ($extrinsicObjects as $_extrinsicObject) {
            // URI
            $metadata[$URI]["uri"] = $URI = $xpath->queryTextNode("rim:Slot[@name='URI']", $_extrinsicObject);

            // Hash
            $metadata[$URI]["hash"] = $xpath->queryTextNode("rim:Slot[@name='hash']", $_extrinsicObject);

            // Creation Time
            $metadata[$URI]["creationTime"] = $xpath->queryTextNode(
                "rim:Slot[@name='creationTime']",
                $_extrinsicObject
            );

            // Size
            $metadata[$URI]["size"] = $xpath->queryTextNode("rim:Slot[@name='size']", $_extrinsicObject);

            // RepositoryUniqueId
            $metadata[$URI]["repositoryUniqueId"] = $xpath->queryTextNode(
                "rim:Slot[@name='repositoryUniqueId']",
                $_extrinsicObject
            );

            // EntryUniqueId
            if ($node_entryUniqueId = $xpath->getNode(
                "rim:ExternalIdentifier[@identificationScheme='urn:uuid:2e82c1f6-a085-4c72-9da3-8640a32e42ab']",
                $_extrinsicObject
            )) {
                $metadata[$URI]["entryUniqueId"] = $xpath->getValueAttributNode($node_entryUniqueId, "value");
            }

            // Version
            if ($node_version = $xpath->getNode("rim:VersionInfo", $_extrinsicObject)) {
                $metadata[$URI]["version"] = $xpath->getValueAttributNode($node_version, "versionName");
            }

            // extrinsicNode
            $metadata[$URI]["extrinsicNode"] = $_extrinsicObject;
        }

        return $metadata;
    }

    /**
     * Check if the given file is an exam report in XDM format, and if it is, handle it
     *
     * @param string $file The file path
     *
     * @return bool
     * @throws Exception
     */
    protected static function isFileCDADocument($file, CInteropSender $sender)
    {
        $sender->loadBackRefConfigCDA();
        $is_cda_document = false;
        $content         = self::encode(file_get_contents($file));

        if ($sender->_ref_config_cda->encoding == 'UTF-8') {
            $content = utf8_decode($content);
        }

        $xml  = new CMbXMLDocument('ISO-8859-1');

        $xml->loadXML($content);
        $xpath = new CMbXPath($xml);
        $xpath->registerNamespace("cda", "urn:hl7-org:v3");

        /* Check if the file is a cda document */
        if ($xpath->queryUniqueNode('//cda:ClinicalDocument')) {
            $is_cda_document = true;
        }

        return $is_cda_document;
    }

    /**
     * Detect the encoding of the content, and return an UTF-8 string
     *
     * @param string $content The XML string
     *
     * @return string
     */
    protected static function encode($content)
    {
        if (strpos($content, 'UTF-8') !== false || strpos($content, 'utf-8') !== false) {
            $content = str_replace(['UTF-8', 'utf-8'], 'ISO-8859-1', $content);
        }

        if (strpos($content, '<?xml') === false) {
            $content = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n{$content}";
        } else {
            $content = substr($content, strpos($content, '<?xml'));
        }

        return $content;
    }

    /**
     * Remove the extracted files and the archive
     *
     * @param string $path The file path
     *
     * @return void
     */
    protected static function cleanFiles($path)
    {
        CMbPath::remove($path, false);
        CMbPath::remove("{$path}.zip", false);
    }
}
