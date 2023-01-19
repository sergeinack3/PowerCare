<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use DateTime;
use DateTimeZone;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Core\CSmartyDP;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Levels\Level3\ANS\CCDAVsm;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;
use XSLTProcessor;

/**
 * Outils pour le CDA
 */
class CCdaTools implements IShortNameAutoloadable
{

    /** @var string[] Tableau � utiliser si on veut cr�er nos propres feuilles de style */
    public const TYPE_DOC_DMP_STYLESHEET = [];

    /**
     * Permet de r�cup�rer les attributs d'un noeud xml sous forme de tableau
     *
     * @param DOMNode $node Node
     *
     * @return array[nom_attribut]
     */
    static function parseattribute($node)
    {
        $tabAttribute = array();
        foreach ($node->attributes as $_attribute) {
            $tabAttribute[$_attribute->nodeName] = utf8_decode($_attribute->nodeValue);
        }
        return $tabAttribute;
    }

    /**
     * Retourne le code associ� au sexe de la personne
     *
     * @param String $sexe String
     *
     * @return CCDACE
     */
    public static function getAdministrativeGenderCode(string $sexe): CCDACE
    {
        $ce = new CCDACE();
        $ce->setCode(mb_strtoupper($sexe));
        $ce->setCodeSystem("2.16.840.1.113883.5.1");
        switch ($sexe) {
            case "f":
                $ce->setDisplayName("F�minin");
                break;
            case "m":
                $ce->setDisplayName("Masculin");
                break;
            default:
                $ce->setCode("U");
                $ce->setDisplayName("Inconnu");
        }

        return $ce;
    }

    /**
     * Transforme une chaine date au format time CDA
     *
     * @param String $date String
     * @param bool $naissance false
     *
     * @return string
     */
    public static function getTimeToUtc(string $date, bool $naissance = false)
    {
        if (!$date) {
            return null;
        }
        if ($naissance) {
            $date = Datetime::createFromFormat("Y-m-d", $date);

            return $date->format("Ymd");
        }
        $timezone = new DateTimeZone(CAppUI::conf("timezone"));
        $date = new DateTime($date, $timezone);

        return $date->format("YmdHisO");
    }

    /**
     * Retourne le code associ� � la situation familiale
     *
     * @param String|null $status String
     *
     * @return CCDACE
     */
    public static function getMaritalStatus(?string $status): CCDACE
    {
        $ce = new CCDACE();
        $ce->setCodeSystem("1.3.6.1.4.1.21367.100.1");
        switch ($status) {
            case "S":
                $ce->setCode("S");
                $ce->setDisplayName("C�libataire");
                break;
            case "M":
                $ce->setCode("M");
                $ce->setDisplayName("Mari�");
                break;
            case "G":
                $ce->setCode("G");
                $ce->setDisplayName("Concubin");
                break;
            case "D":
                $ce->setCode("D");
                $ce->setDisplayName("Divorc�");
                break;
            case "W":
                $ce->setCode("W");
                $ce->setDisplayName("Veuf/Veuve");
                break;
            case "A":
                $ce->setCode("A");
                $ce->setDisplayName("S�par�");
                break;
            case "P":
                $ce->setCode("P");
                $ce->setDisplayName("Pacte civil de solidarit� (PACS)");
                break;
            default:
                $ce->setCode("U");
                $ce->setDisplayName("Inconnu");
        }

        return $ce;
    }

    /**
     * Permet de faire un parcours en profondeur du document
     * et renvoi le document sous forme d'un tableau
     *
     * @param DOMNode $node Node
     *
     * @return array
     */
    static function parsedeep($node)
    {
        /**
         * On renseigne les informations de notre noeud dans un tableau
         */
        $tabNode = array("name" => $node->localName,
            "child" => array(),
            "data" => utf8_decode($node->nodeValue),
            "attribute" => self::parseattribute($node));
        /**
         * On v�rifie que l'�l�ment est un DOMElement pour �viter les noeuds Text et autres
         */
        if ($node instanceof DOMElement) {
            /**
             * On parcours les fils de notre noeud courant
             */
            foreach ($node->childNodes as $_childNode) {
                /**
                 * On v�rifie que notre noeud poss�dent un nom pour �viter les noeud contenant
                 * des commentaires
                 */
                if ($_childNode->localName) {
                    /**
                     * On affecte � notre tableau ces fils en appelant notre fonction
                     */
                    $tabNode["child"][] = self::parsedeep($_childNode);
                }
            }
        }
        /**
         * Retourne le tableau de notre noeud courant dans le child du noeud parent
         * ou retourne le tableau complet � la fin du processus
         */
        return $tabNode;
    }

    /**
     * Permet de remplir la variable $_contain avec la structure du document
     *
     * @param String $message message
     *
     * @return void|array
     * @deprecated
     */
    static function parse($message)
    {
        $result = array();
        $dom = new CMbXMLDocument("UTF-8");

        $returnErrors = $dom->loadXML(utf8_encode($message), null, true);
        $tabErrors = array_filter(explode("\n", $returnErrors));
        $returnErrors = $dom->schemaValidate("modules/cda/resources/CDA.xsd", true, false);
        $tabErrors = array_merge($tabErrors, array_filter(explode("\n", $returnErrors)));
        $validate = array_unique($tabErrors);

        if ($validate[0] != "1") {
            $contain = null;
            return;
        }

        $validateSchematron = self::validateSchematron($message);

        if ($validate[0] === "1" && !CMbArray::get($validate, 1)) {
            $validate = array();
        }

        $contain = self::parsedeep($dom->documentElement);
        $result["validate"] = $validate;
        $result["validateSchematron"] = $validateSchematron;
        $result["contain"] = $contain;

        return $result;
    }

    /**
     * Valide le CDA
     *
     * @param String $cda CDA
     *
     * @return void
     * @throws CMbException
     */
    static function validateCDA($cda)
    {
        $dom = new CMbXMLDocument();
        if ($dom->loadXML($cda, null, true) !== true) {
            throw new CMbException("Erreur lors de la conception du document");
        }
    }

    /**
     * Affiche le message au format xml
     *
     * @param String $message message
     *
     * @return String
     */
    static function showxml($message)
    {
        return CMbString::highlightCode("xml", $message);
    }

    /**
     * Fonction de cr�ation des classes voc
     *
     * @return string
     */
    static function createClass()
    {
        //On charge le XSD contenant le vocabulaire
        $dom = new CMbXMLDocument("UTF-8");
        $dom->load("modules/cda/resources/voc.xsd");

        //On enregistre le namespace utiliser dans le XSD
        $xpath = new CMbXPath($dom);
        $xpath->registerNamespace("xs", "http://www.w3.org/2001/XMLSchema");

        //On recherche tous les simpleTypes et les complexTypes
        $nodeList = $xpath->query("//xs:complexType|xs:simpleType");
        $listvoc = array();

        //On parcours la liste que retourne la requ�te XPATH
        foreach ($nodeList as $_node) {
            //On r�cup�re le nom du type
            $name = $xpath->queryAttributNode(".", $_node, "name");

            //On r�cup�re la documentation li� au type
            $documentation = $xpath->queryTextNode(".//xs:documentation", $_node);

            //On r�cup�re les unions du type
            $union = $xpath->queryUniqueNode(".//xs:union", $_node);

            //On v�rifie l'existence d'union
            if ($union) {
                $union = $xpath->queryAttributNode(".", $union, "memberTypes");
            }
            //on r�cup�re les �num�rations
            $enumeration = $xpath->query(".//xs:enumeration", $_node);
            $listEnumeration = array();

            //on met chaque enumeration dans un tableau
            foreach ($enumeration as $_enumeration) {
                array_push($listEnumeration, $xpath->queryAttributNode(".", $_enumeration, "value"));
            }
            //On cr�� un tableau rassemblant toutes les informations concernant un voc
            $listvoc[] = array("name" => $name,
                "documentation" => $documentation,
                "union" => $union,
                "enumeration" => $listEnumeration);
        }

        //On met le lien du dossier contenant les voc
        $cheminBase = "modules/cda/classes/Datatypes/Voc/";

        //On parcours les voc
        foreach ($listvoc as $_voc) {
            //On affecte comme nom de fichier CCDA et le nom du voc
            $nameFichier = "CCDA" . $_voc["name"] . ".php";
            $smarty = new CSmartyDP();
            $smarty->assign("documentation", $_voc["documentation"]);
            $smarty->assign("name", $_voc["name"]);
            $smarty->assign("enumeration", self::formatArray($_voc["enumeration"]));
            $union = self::formatArray(array());
            //on v�rifie la pr�sence d'union
            if (CMbArray::get($_voc, "union")) {
                $union = self::formatArray(explode(" ", $_voc["union"]));
            }
            $smarty->assign("union", $union);
            //on r�cup�re la classe former
            $data = $smarty->fetch("defaultClassVoc.tpl");

            //on cr�� le fichier
            file_put_contents($cheminBase . $nameFichier, str_replace("\r\n", "\n", $data));
        }

        return true;
    }

    /**
     * Permet de formater le tableau en entr�
     *
     * @param array $array array
     *
     * @return mixed
     */
    static function formatArray($array)
    {
        return preg_replace('/\)$/', "  )", preg_replace("/\d+ => /", "  ", var_export($array, true)));
    }

    /**
     * Valide le xml avec le sch�matron
     *
     * @param String $xml String
     *
     * @return String[]
     */
    static function validateSchematron($xml)
    {
        // TODO : Pouvoir jouer la validation du schematron dans les TU. Pas possible en prod parce que trop long � jouer
        /*$baseDir = __DIR__."/../resources";
        $cmd     = escapeshellarg("java");

        $styleSheet = "$baseDir/schematron/CI-SIS_StructurationCommuneCDAr2.xsl";

        $temp = tempnam("temp", "xml");
        file_put_contents($temp, $xml);

        $cmd = $cmd." -jar $baseDir/saxon9he.jar -s:$temp -xsl:$styleSheet";

        $processorInstance = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        $processorResult = stream_get_contents($pipes[1]);
        $processorErrors = stream_get_contents($pipes[2]);
        proc_close($processorInstance);

        unlink($temp);

        $dom = new CMbXMLDocument();
        $dom->loadXML($processorResult);
        $xpath = new CMbXPath($dom);
        $xpath->registerNamespace("svrl", "http://purl.oclc.org/dsdl/svrl");
        $nodeList = $xpath->query("//svrl:failed-assert");

        $tabErrors = array();
        if ($processorErrors) {
            $tabErrors[] = array("error" => $processorErrors,
                                 "location" => "System");
        }

        foreach ($nodeList as $_node) {
            $tabErrors[] = array("error" => utf8_decode($_node->textContent),
                                 "location" => $xpath->queryAttributNode(".", $_node, "location"));
        }*/

        $tabErrors = array();
        return $tabErrors;
    }

    /**
     * Show CDA from XSLT
     *
     * @param string $message Message
     *
     * @return string
     */
    static function showXSLT($message)
    {
        $baseDir = __DIR__ . "/../resources";

        $xslDoc = new CMbXMLDocument();
        $xslDoc->load("$baseDir/xsl/cda_asip.xsl");

        $xmlDoc = new CMbXMLDocument();
        $xmlDoc->loadXML($message);

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xslDoc);
        return $proc->transformToXML($xmlDoc);
    }

    /**
     * Affiche le message au format xml
     *
     * @param String $html HTML page
     *
     * @return String
     */
    static function showHTML($html)
    {
        return CMbString::highlightCode("html", $html);
    }

    /**
     * Permet de de retourner la portion xml du noeud choisi par le nom dans le sch�ma sp�cifi�
     *
     * @param String $name String
     * @param String $schema String
     *
     * @return string
     */
    static function showNodeXSD($name, $schema)
    {
        $dom = new CMbXMLDocument();
        $dom->load($schema);

        $xpath = new CMbXPath($dom);
        $xpath->registerNamespace("xs", "http://www.w3.org/2001/XMLSchema");
        $node = $xpath->queryUniqueNode("//xs:simpleType[@name='" . $name . "']|//xs:complexType[@name='" . $name . "']");
        return $dom->saveXML($node);
    }


    /**
     * Permet de lancer toutes les cr�ations des sch�ma de test
     *
     * @return bool
     */
    static function createAllTestSchemaClasses()
    {
        //URI du fichier
        $nameFile = "modules/cda/resources/TestClasses.xsd";
        $glob = "modules/cda/classes/Datatypes/{Voc,Base,Datatype}/*.php";
        self::createTestSchemaClasses($nameFile, $glob);

        $nameFile = "modules/cda/resources/TestClassesCDA.xsd";
        $glob = "modules/cda/classes/Structure/*.php";
        self::createTestSchemaClasses($nameFile, $glob);

        return true;
    }

    /**
     * Permet de cr�er le xsd contenant la d�finition d'�l�ment pour tester les types
     *
     * @param String $nameFile String
     * @param String $glob String
     *
     * @return bool
     */
    static function createTestSchemaClasses($nameFile, $glob)
    {

        $dom = new CMbXMLDocument("UTF-8");
        //On enregistre pas les nodeText vide
        $dom->preserveWhiteSpace = false;
        $dom->load($nameFile);

        //on r�cup�re tous les �lements
        $xpath = new CMbXPath($dom);
        $nodeList = $xpath->query("//xs:element");

        //on supprime tous les �lements du fichier
        foreach ($nodeList as $_node) {
            $dom->documentElement->removeChild($_node);
        }

        //On sauvegarde le fichier sans �l�ment
        file_put_contents($nameFile, $dom->saveXML());

        //on r�cup�re tous les class existant dans les dossier voc, base, datatype
        $file = glob($glob, defined('GLOB_BRACE') ? GLOB_BRACE : 0);

        /**
         * Pour chacun des fichier on cr�� un �l�ment avec sont type correspondant
         */
        foreach ($file as $_file) {
            //on cr�� l'�l�ment
            $element = $dom->createElementNS("http://www.w3.org/2001/XMLSchema", "xs:element");
            //on formatte le nom du fichier
            $_file = CMbArray::get(explode(".", $_file), 0);
            $_file = substr($_file, strrpos($_file, "/") + 1);
            //on cr�� une instance de la classe
            /** @var CCDAClasseBase $instanceClass */
            $instanceClass = new $_file;
            //on r�cup�re le nom quisera �gale au type et au nom de l'�l�ment
            $_file = $instanceClass->getNameClass();
            //On ajoute les attribut type et nom
            $dom->addAttribute($element, "name", $_file);
            $dom->addAttribute($element, "type", $_file);
            //On ajoute un saut de ligne dans le sch�ma
            $dom->documentElement->appendChild($dom->createTextNode("\n"));
            // Ajout de l'�l�ment dans le dom
            $dom->documentElement->appendChild($element);
        }

        $dom->documentElement->appendChild($dom->createTextNode("\n"));
        //on sauvegarde le fichier
        file_put_contents($nameFile, $dom->saveXML());

        return true;
    }

    /**
     * Permet la cr�ation de la synth�se des tests
     *
     * @param array $result array
     *
     * @return array
     */
    static function syntheseTest($result)
    {
        /**
         * on cr�� le tableau qui contiendra le nombre total de test
         * le nombre de succ�s et les classes qui sont en erreur
         */
        $resultSynth = array("total" => 0,
            "succes" => 0,
            "erreur" => array());
        //on parcours le tableau des tests
        foreach ($result as $keyClass => $valueClass) {
            //on parcours les r�sultats et on compte les r�sultats
            foreach ($valueClass as $_test) {
                if ($_test["resultat"] === $_test["resultatAttendu"]) {
                    $resultSynth["succes"]++;
                } else {
                    array_push($resultSynth["erreur"], $keyClass);
                }
                $resultSynth["total"]++;
            }
        }
        $resultSynth["erreur"] = array_unique($resultSynth["erreur"]);
        return $resultSynth;
    }

    /**
     * Retourne tous les types pr�sent dans le sch�ma renseign�
     *
     * @param String $schema String
     *
     * @return array
     */
    static function returnType($schema)
    {
        $dom = new CMbXMLDocument("UTF-8");
        $dom->load($schema);

        $xpath = new CMbXPath($dom);
        $xpath->registerNamespace("xs", "http://www.w3.org/2001/XMLSchema");
        $nodelist = $xpath->query("//xs:simpleType[@name]|//xs:complexType[@name]");
        $listName = array();
        foreach ($nodelist as $_node) {
            array_push($listName, $xpath->queryAttributNode(".", $_node, "name"));
        }

        return $listName;
    }

    /**
     * Retourne les classes manqantes
     *
     * @return array
     */
    static function missclass()
    {
        /**
         * On r�cup�re les types des diff�rents XSD
         */
        $listAllType = self::returnType("modules/cda/resources/datatypes-base.xsd");
        $voc = self::returnType("modules/cda/resources/voc.xsd");
        $datatype = self::returnType("modules/cda/resources/datatypes.xsd");
        $listAllType = array_merge($listAllType, $voc, $datatype);
        $file = glob("modules/cda/classes/{Structure,Datatypes}/{Voc,Base,Datatype}/*.php", defined('GLOB_BRACE') ? GLOB_BRACE : 0);

        $result = array();
        /**
         * On parcours les classes existantes
         */
        foreach ($file as $_file) {
            $_file = CMbArray::get(explode(".", $_file), 0);
            $_file = substr($_file, strrpos($_file, "/") + 1);
            /** @var CCDAClasseBase $class */
            $class = new $_file;
            array_push($result, $class->getNameClass());
        }
        //on retourne la diff�rence entre le tableau des types XSd et le tableau des classes existantes
        return array_diff($listAllType, $result);
    }

    /**
     * Permet de nettoyer le XSD (suppression des minOccurs=0 maxOccurs=0 et des abtract)
     * libxml ne g�re pas la prohibition des �l�ment avec maxOccurs = 0;
     * les abtracts emp�che l'instanciation des classes
     *
     * @return bool
     */
    static function clearXSD()
    {
        $pathSource = "modules/cda/resources/datatypes-base_original.xsd";
        $pathDest = "modules/cda/resources/datatypes-base.xsd";

        $copyFile = copy($pathSource, $pathDest);

        if (!$copyFile) {
            return false;
        }

        $dom = new CMbXMLDocument("UTF-8");
        $dom->load($pathDest);

        $xpath = new CMbXPath($dom);
        $nodeList = $xpath->query("//xs:complexType[@abstract]|xs:simpleType[@abstract]");

        foreach ($nodeList as $_node) {
            /** @var DOMElement $_node */
            $_node->removeAttribute("abstract");
        }

        $nodeList = $xpath->query("//xs:element[@maxOccurs=\"0\"]");
        foreach ($nodeList as $_node) {
            $_node->parentNode->removeChild($_node);
        }
        file_put_contents($pathDest, $dom->saveXML());

        return true;
    }

    /**
     * Permet de cr�er les props pour une classe
     *
     * @param DOMNodeList $elements DOMNodeList
     * @param array $tabVariable Array
     * @param array $tabProps Array
     *
     * @return array
     */
    static function createPropsForElement($elements, $tabVariable, $tabProps)
    {
        $nameAttribute = "";
        $typeAttribute = "";
        foreach ($elements as $_element) {
            $attributes = $_element->attributes;
            $typeXML = "xml|element";

            if ($_element->nodeName == "xs:attribute") {
                $typeXML = "xml|attribute";
            }

            $elementProps = "";
            $maxOccurs = false;
            $minOccurs = false;
            foreach ($attributes as $_attribute) {
                switch ($_attribute->nodeName) {
                    case "name":
                        $nameAttribute = $_attribute->nodeValue;
                        break;
                    case "type":
                        $name = str_replace(".", "_", $_attribute->nodeValue);
                        if (ctype_lower($name)) {
                            $name = "_base_$name";
                        }
                        $typeAttribute = $name;
                        $elementProps .= "CCDA$name $typeXML";
                        break;
                    case "minOccurs":
                        $minOccurs = true;
                        if ($_attribute->nodeValue > 0) {
                            $minOccurs = false;
                            $elementProps .= " min|$_attribute->nodeValue";
                        }
                        break;
                    case "maxOccurs":
                        if ($_attribute->nodeValue == "unbounded") {
                            $maxOccurs = true;
                        } else {
                            if ($_attribute->nodeValue > 1) {
                                $maxOccurs = true;
                                $elementProps .= " max|$_attribute->nodeValue";
                            }
                        }
                        break;
                    case "default":
                        $elementProps .= " default|$_attribute->nodeValue";
                        break;
                    case "use":
                        if ($_attribute->nodeValue == "required") {
                            $elementProps .= " required";
                        }
                        break;
                    case "fixed":
                        $elementProps .= " fixed|$_attribute->nodeValue";
                        break;
                }
            }
            $tabVariable[$nameAttribute]["type"] = $typeAttribute;
            $tabVariable[$nameAttribute]["max"] = $maxOccurs;
            if (!$maxOccurs && $typeXML == "xml|element") {
                if ($minOccurs) {
                    $elementProps .= " max|1";
                } else {
                    $elementProps .= " required";
                }
            }
            $tabProps[$nameAttribute] = $elementProps;
        }
        return array($tabVariable, $tabProps);
    }

    /**
     * fonction permettant de cr��r la structure principal des classes d'un XSD
     *
     * @return bool
     */
    static function createClassFromXSD()
    {
        $pathXSD = "modules/cda/resources/POCD_MT000040.xsd";
        $pathDir = "modules/cda/classes/Structure/ClassesGenerate/";
        $dom = new CMbXMLDocument("UTF-8");
        $dom->load($pathXSD);

        $xpath = new CMbXPath($dom);
        $xpath->registerNamespace("xs", "http://www.w3.org/2001/XMLSchema");
        $nodeList = $xpath->query("//xs:complexType[@name] | //xs:simpleType[@name]");

        foreach ($nodeList as $_node) {
            $tabVariable = array();
            $tabProps = array();
            /** @var DOMElement $_node */
            $elements = $_node->getElementsByTagName("element");
            $nodeAttributes = $_node->getElementsByTagName("attribute");
            $nameNode = $xpath->queryAttributNode(".", $_node, "name");
            $nameNode = str_replace(".", "_", $nameNode);

            [$tabVariable, $tabProps] = self::createPropsForElement($elements, $tabVariable, $tabProps);
            [$tabVariable, $tabProps] = self::createPropsForElement($nodeAttributes, $tabVariable, $tabProps);

            $smarty = new CSmartyDP();
            $smarty->assign("name", $nameNode);
            $smarty->assign("variables", $tabVariable);
            $smarty->assign("props", $tabProps);

            $data = $smarty->fetch("defaultClassCDA.tpl");

            file_put_contents($pathDir . "CCDA" . $nameNode . ".php", $data);
        }
        return true;
    }

    /**
     * Generate a PDFA with a PDF
     *
     * @param String $path_input path
     *
     * @return String|null
     * @throws CMbException
     */
    static function generatePDFA($path_input)
    {
        $command_path = CAppUI::conf("cda path_ghostscript");
        $command_path = $command_path ? escapeshellarg($command_path) : "gs";
        $path_output = @tempnam("temp", "pdf");
        $cmd = "$command_path -dPDFA -dBATCH -dNOPAUSE -dUseCIEColor -sProcessColorModel=DeviceCMYK -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/printer -sOutputFile=$path_output -dPDFACompatibilityPolicy=1 $path_input";
        $processorInstance = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        $processorErrors = stream_get_contents($pipes[2]);
        proc_close($processorInstance);
        if ($processorErrors) {
            //throw new CMbException($processorErrors);
        }
        return $path_output;
    }

    /**
     * Detect the encoding of the content, and return an UTF-8 string
     *
     * @param string $content The XML string
     *
     * @return string
     */
    public static function encode($content)
    {
        if (strpos($content, 'UTF-8') !== false || strpos($content, 'utf-8') !== false) {
            $content = str_replace(array('UTF-8', 'utf-8'), 'ISO-8859-1', $content);
        }

        if (strpos($content, '<?xml') === false) {
            $content = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n{$content}";
        } else {
            $content = substr($content, strpos($content, '<?xml'));
        }

        return $content;
    }

    /**
     * Display the given file content by doing and XSL transformation
     *
     * @param string $content The content of the file to display
     * @param CFile $file The file
     *
     * @return string
     */
    public static function display($content, ?CFile $file = null)
    {
        $content = self::encode($content);
        $xml = new CMbXMLDocument('ISO-8859-1');
        $xml->loadXML($content);

        $xpath = new CMbXPath($xml);
        $xpath->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');

        /* Check if the document contains ans XSL Stylesheet, and use it to format the document */
        if (strpos($content, 'xsl:stylesheet') !== false) {
            $xsl = new CMbXMLDocument('ISO-8859-1');
            $xsl->loadXML($content);

            $processor = new XSLTProcessor();
            $processor->importStylesheet($xsl);
            $content = utf8_decode($processor->transformToXml($xml));
        } /* Use the MB stylesheet otherwise */
        else {
            $xsl = new CMbXMLDocument('ISO-8859-1');

            $stylesheet_type = 'cda_asip';

            // On va chercher la feuille de style correspondant au type doc DMP
            if ($file && $file->_id && $file->type_doc_dmp) {
                $stylesheet_type_specific = CMbArray::get(self::TYPE_DOC_DMP_STYLESHEET, $file->type_doc_dmp);

                if ($stylesheet_type_specific) {
                    $stylesheet_type = $stylesheet_type_specific;
                }
            }

            $xsl->loadXML((file_get_contents("modules/cda/resources/xsl/$stylesheet_type.xsl")));

            $processor = new XSLTProcessor();
            $processor->registerPHPFunctions();
            $processor->importStylesheet($xsl);
            $content = utf8_decode($processor->transformToXml($xml));
        }

        return $content;
    }

    /**
     * Generate PDF for VSM with stylesheet
     *
     * @param CMbObject $object
     * @param CFile $file
     */
    public static function generatePdfVSM(CMbObject $object, CFile $file)
    {
        $content = $file->getContent() ?: $file->getBinaryContent();
        $content = CCdaTools::encode($content);

        $xml = new CMbXMLDocument('ISO-8859-1');
        $xml->loadXML($content);

        $xpath = new CMbXPath($xml);
        $xpath->registerNamespace('xsl', 'http://www.w3.org/1999/XSL/Transform');

        // Check if the document contains ans XSL Stylesheet, and use it to format the document
        if (strpos($content, 'xsl:stylesheet') !== false) {
            $xsl = new CMbXMLDocument('ISO-8859-1');
            $xsl->loadXML($content);

            $processor = new XSLTProcessor();
            $processor->importStylesheet($xsl);
            $content = utf8_decode($processor->transformToXml($xml));
        } // Use the MB stylesheet otherwise
        else {
            $xsl = new CMbXMLDocument('ISO-8859-1');
            $xsl->loadXML(utf8_decode(file_get_contents('modules/cda/resources/xsl/cda_asip.xsl')));

            $processor = new XSLTProcessor();
            $processor->registerPHPFunctions();
            $processor->importStylesheet($xsl);
            $content = $processor->transformToXml($xml);
        }

        // Suppression des caract�res incorrects dans le content
        $content = preg_replace("/<!DOCTYPE.*>/", "", $content);
        $content = preg_replace("/<link.*>/", "", $content);
        $content = preg_replace("/<meta.*>/", "", $content);
        $content = preg_replace("/<br>/", "<br/>", $content);
        $content = preg_replace("/<hr(.*)>/", "<hr$1/>", $content);

        $file_pdf = new CFile();
        $file_pdf->setObject($object);
        $file_pdf->type_doc_dmp = CCDAVsm::TYPE_DOC;
        $file_pdf->file_name = "Volet Synth�se M�dicale " . CMbDT::dateTime();
        $file_pdf->author_id = CAppUI::$instance->user_id;
        $file_pdf->file_category_id = $file->file_category_id;
        $file_pdf->file_type = "application/pdf";
        $file_pdf->loadMatchingObject();
        $file_pdf->fillFields();
        $file_pdf->updateFormFields();

        $cr = new CCompteRendu();
        $cr->_page_format = "a4";
        $cr->_orientation = "portrait";

        $htmltopdf = new CHtmlToPDF();
        if ($htmltopdf->generatePDF($content, 0, $cr, $file_pdf, true, false)) {
            $file_pdf->store();
        }
    }
}
