<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;
use Ox\Core\CMbXPath;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype;

/**
 * CCDAClasseBase Class
 */
class CCDAClasseBase implements IShortNameAutoloadable
{

    /**
     * Appelle la méthode validate et retourne un tableau avec le résultat
     *
     * @param String $description   String
     * @param String $resultAttendu String
     *
     * @return array
     */
    function sample($description, $resultAttendu)
    {
        $arrayReturn = [
            "description"     => $description,
            "resultatAttendu" => $resultAttendu,
            "resultat"        => "",
        ];
        $result      = $this->validate();

        if ($result) {
            $arrayReturn["resultat"] = "Document valide";
        } else {
            $arrayReturn["resultat"] = "Document invalide";
        }

        return $arrayReturn;
    }

    /**
     * Retourne le résultat de la validation par le xsd de la classe appellée
     *
     * @return bool
     */
    function validate()
    {
        $domDataType = $this->toXML(null, "urn:hl7-org:v3");

        return $domDataType->schemaValidate("modules/cda/resources/TestClassesCDA.xsd", false, false);
    }

    /**
     * Transforme la classe en document XML
     *
     * @param null $nameParent String
     * @param null $namespace  String
     *
     * @return CCDADomDocument
     */
    function toXML($nameParent = null, $namespace = null)
    {
        $dom = new CCDADomDocument();
        //on affecte le nom de la classe comme noeud racine
        $name = $this->getNameClass();
        /**
         * Si le nom parent est spécifié, on utilisera ce nom pour le noeud racine
         */
        if (!empty($nameParent)) {
            $name = $nameParent;
        }

        //on créé le nom racine
        $baseXML = $dom->addElement($dom, $name, null, $namespace);

        //on récupère les specifications définie dans les props
        $spec = $this->getSpecs();

        //On parcours les specs
        foreach ($spec as $key => $value) {
            //on récupère une instance d'une classe stocké dans la variable
            /** @var CCDA_Datatype $classInstance */
            $classInstance = $this->$key;
            //on effectue différente action selon ce qui est définir dans la prop XML
            switch ($value["xml"]) {
                case "attribute":
                    //On vérifie la présence d'une instance
                    if (empty($classInstance)) {
                        break;
                    }
                    if ($key === "identifier") {
                        $key = "ID";
                    }
                    //On créé l'attribut
                    $dom->addAttribute($baseXML, $key, $classInstance->getData());
                    break;
                case "data":
                    //on insert la donnée avant tous les éléments
                    // Pour CDA, on force à true
                    $dom->insertTextFirst($baseXML, $this->getData(), true);
                    break;
                case "element":
                    //on vérifie l'existence d'une instance
                    if (empty($classInstance)) {
                        break;
                    }

                    if (!is_array($classInstance)) {
                        $classInstance = [$classInstance];
                    }

                    //on parcours les différentes instance
                    /** @var CCDA_Datatype[] $classInstance */
                    foreach ($classInstance as $_class) {
                        if (!$_class) {
                            continue;
                        }
                        //on récupère le code xml de l'instance en spécifiant le nom du noeud racine
                        $xmlClass = $_class->toXML($key, $namespace);
                        //on ajoute à notre document notre instance
                        $dom->importDOMDocument($baseXML, $xmlClass);
                    }
                    break;
            }
            //si la propriété abstract est spécifié
            if (CMbArray::get($value, "abstract")) {
                //on vérifie l'existence d'une instance
                if (empty($classInstance)) {
                    continue;
                }
                //on cherche le noeud XML dans notre document
                $xpath = new CMbXPath($dom);
                /*if (!empty($namespace)) {
                  $xpath->registerNamespace("cda", $namespace);
                  //$nodeKey = $xpath->queryUniqueNode("//cda:".$key);
                  $nodeKey = $xpath->query("//cda:".$key);
                  $nodeKey = $nodeKey->item(0);
                }
                else {
                  //$nodeKey = $xpath->queryUniqueNode("//".$key);
                  $nodeKey = $xpath->query("//".$key);
                  $nodeKey = $nodeKey->item(0);
                }*/

                if (!is_array($classInstance)) {
                    $classInstance = [$classInstance];
                }

                foreach ($classInstance as $_class) {
                    //on cherche le noeud XML dans notre document
                    if (!empty($namespace)) {
                        $xpath->registerNamespace("cda", $namespace);
                        $nodeKey = $xpath->query("//cda:" . $key);
                    } else {
                        $nodeKey = $xpath->query("//" . $key);
                    }
                    $nodeKey = $nodeKey->item(isset($_class->position) ? $_class->position : 0);

                    // on spécifie le type de l'élément (on cast)
                    $dom->castElement($nodeKey, $_class->getNameClass());
                }
            }
        }

        return $dom;
    }

    /**
     * Récupère le nom de la classe
     *
     * @return String
     */
    function getNameClass()
    {
    }

    /**
     * retourne les props sous la forme d'un tableau
     *
     * @return array
     */
    function getSpecs()
    {
        $specs = [];
        foreach ($this->getProps() as $_field => $_prop) {
            $parts = explode(" ", $_prop);
            $_type = array_shift($parts);

            $spec_options = [
                "type" => $_type,
            ];
            foreach ($parts as $_part) {
                $options                             = explode("|", $_part);
                $spec_options[array_shift($options)] = count($options) ? implode("|", $options) : true;
            }

            $specs[$_field] = $spec_options;
        }

        return $specs;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
    }

    /**
     * Retourne la données
     *
     * @return String
     */
    function getData()
    {
    }
}
