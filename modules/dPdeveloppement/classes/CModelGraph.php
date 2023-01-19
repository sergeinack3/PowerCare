<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CRefSpec;

/**
 * Description
 */
class CModelGraph extends CModelObject {
  public $class_select;     // Nom de la classe sélectionnées par l'utilisateur
  public $show_backprops;   // Quelles backprops veut-on afficher (aucune, celles propres à la classe ou toutes)
  public $show_props;       // Faut-il afficher les classes liées
  public $hierarchy_sort;   // Algorithme de positionnement des noeuds
  public $number;           // Profondeur du graph
  public $show_hover;       // Affichage d'informations au survol du graph ou non
  // Options pour la modale de détails
  public $show_properties;  // Afficher les propriétés de la classe
  public $show_backs;       // Afficher les backprops de la classe
  public $show_formfields;  // Afficher les champs calculés de la classe
  public $show_heritage;    // Afficher les champs hérités de la classe
  public $show_refs;        // Afficher les références de la classe

  static $backprops_list = array(
    'none', 'all', 'own'
  );

  static $hierarchy_list = array(
    'basic', 'hierarchy', 'hubsize', 'directed', 'none'
  );

  static $mb_classes = array();

  /**
   * Fonction d'initialisation de la classe à appeler après son instanciation
   *
   * @param array $properties Propriétés d'initialisation
   *
   * @return void
   */
  function init($properties = array()) {
    $instances = array();
    static::$mb_classes           = CApp::getMbClasses($instances, true);
    $this->_props['class_select'] = 'enum list|' . implode('|', static::$mb_classes) . ' notNull';
    $this->_specs                 = $this->getSpecs();

    foreach ($this->_specs['class_select']->_locales as $_class => &$_locale) {
      $_locale = $_class . " (" . CAppUI::tr($_class) . ")";
    }

    foreach ($properties as $_field => $_value) {
      $this->{$_field} = $_value;
    }
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["class_select"]    = "enum list|CMbObject notNull";
    $props["show_backprops"]  = "enum list|" . implode('|', static::$backprops_list) . " default|none";
    $props["hierarchy_sort"]  = "enum list|" . implode('|', static::$hierarchy_list) . " default|hierarchy";
    $props["show_props"]      = "bool default|1";
    $props["number"]          = "num default|1";
    $props["show_hover"]      = "bool default|1";
    $props["show_properties"] = "bool default|1";
    $props["show_backs"]      = "bool default|1";
    $props["show_formfields"] = "bool default|1";
    $props["show_heritage"]   = "bool default|1";
    $props["show_refs"]       = "bool default|1";

    return $props;
  }

  /**
   * Récupère les informations nécessaire à la création d'un graph avec les options choisis
   *
   * @param string|null $class Nom de la classe sélectionnée
   *
   * @return null|array
   */
  function getGraph($class = null) {
    if (!$class) {
      $class = $this->class_select;
    }
    if (!$class) {
      return null;
    }
    /** @var CMbObject $class */
    $object = new $class();
    // Level permet de répartir les noeuds suivant une hiérarchie si le type d'algo choisis pour l'affichage est "hierarchique"
    $level = 2;

    // Contient l'ensemble des informations pour créer le graph
    $nodes = array();
    // Récupération des informations pour créer le noeud principal
    $node    = $this->getNode($object, $level);
    $nodes[] = $node;
    if (!$this->show_props) {
      return $nodes;
    }
    // Création du noeud du parent (héritage)
    if ($parent = get_parent_class($object)) {
      $parent = new $parent;
      if ($this->hierarchy_sort == "hierarchy") {
        $nodes[] = $this->getNode($parent, 1, true, false);
      }
      elseif ($this->hierarchy_sort == "basic") {
        $nodes[] = $this->getNode($parent, $level, true, false);
      }

    }

    // Tableau contenant le nom des classes déjà traitées
    $classes_traites   = array();
    $classes_failed = array();
    $classes_traites[] = $object->_class;
    // Si number = 0 alors on n'affiche que la classe choisie et sa classe parent
    if ($this->number > 0) {
      // Itération jusqu'à la profondeur choisie
      for ($i = 0; $i < $this->number; $i++) {
        $level++;
        $level_count = 0;
        // Pour chaque classe qui a un lien avec un noeud déjà créé on lui crée un noeud
        foreach ($node["links"] as $_key => $_link) {
          if (!in_array($_link, $classes_traites)) {
            // Nombre maximum de classe par ligne
            if ($this->hierarchy_sort != "basic") {
              if ($level_count > 3) {
                $level++;
                $level_count = 0;
              }
            }
            $classes_traites[] = $_link;
            if (!class_exists($_link)) {
              $classes_failed[] = $_link;
              continue;
            }
            if ($this->number > 1) {
              $node = $this->getNode(new $_link, $level, false, true);
            }
            else {
              $node = $this->getNode(new $_link, $level, false, false);
            }
            $nodes[] = $node;
            $level_count++;
          }
        }
      }
    }
    if (count($classes_failed) > 0) {
      CAppUI::stepAjax("Classes inexistantes : ". implode(", ", $classes_failed), UI_MSG_WARNING);
    }
    return $nodes;
  }

  /**
   * A partir d'une classe, renvoie un tableau associatif qui contient les informations nécessaires à la création
   * d'un graphe.
   *
   * @param CMbObject|string $object     Nom ou instance de la classe
   * @param int              $level      Desc
   * @param bool             $parent     La classe traité est-elle le parent de la classe sélectionnée
   * @param bool             $links_show Permet d'affihcer uniquement les liens de la lcasse séléctionnées
   *
   * @return array|bool Tableau associatif
   */
  function getNode($object, $level, $parent = false, $links_show = true) {
    // Récupération des propriétés qui sont une référence vers une autre classe
    $links = array();
    if ($links_show) {
      $links = $this->showLinks($object);
    }
    // Récupération des propriétés qui ne sont pas une référence
    $props = $this->showProps($object);
    // Création du noeud que l'ont va renvoyer
    $node = array(
      'class'    => $object->_class,
      'options'  => array(
        'parent' => $parent,
        'level'  => $level
      ),
      'class_db' => ($object->_spec && $object->_spec->table) ? $object->_spec->table : 'Classe abstraite',
      'props'    => $props,
      'links'    => $links
    );
    // On supprime les valeurs en double
    $tmp           = array_flip($node['links']);
    $node['links'] = array_flip($tmp);

    return $node;
  }

  /**
   * Renvoie les classes liées à l'objet passé en paramètre
   *
   * @param CMbObject $object Objet dont on cherche les classes liées
   *
   * @return array
   */
  function showLinks($object) {
    // Récupération des classes référencées par les propriétés de la classe choisie
    $ref_specs = array_filter(
      $object->_specs,
      function ($spec) use ($object) {
        return ($spec instanceof CRefSpec && $spec->class !== $object->_class);
      }
    );

    $links = array();
    foreach ($ref_specs as $_spec) {
      $links[$_spec->fieldName] = $_spec->class;
    }

    return $links;
  }

  /**
   * Renvoie les propriétés de l'objet qui ne sont pas des références à des classes
   *
   * @param CMbObject $object Object dont on cherche les propriétés qui ne sont pas des références à des classes
   *
   * @return array
   */
  function showProps($object) {
    // Récupération des propriétés qui ne sont pas des références vers une classe et qui n'ont pas l'attribut 'show|0'
    $ref_specs = array_filter(
      $object->_specs,
      function ($spec) use ($object) {
        if (!$spec instanceof CRefSpec && strpos($spec->fieldName, "_") !== 0 && !strstr($spec->prop, "show|0")) {
          return $spec;
        }

        return null;
      }
    );
    $props     = array();
    $idx       = 0;
    // On affiche uniquement les 5 premières propriétés
    foreach ($ref_specs as $_spec) {
      if ($idx > 4) {
        $props[] = "...";
        break;
      }
      $props[] = $_spec->fieldName . " : " . $_spec->prop;
      $idx++;
    }

    return $props;
  }

  /**
   * Tri les backprops d'une classe en deux catégories : les backprops héritées et celles propres à la classe
   *
   * @param CMbObject $class Classe dont on veut les propriétés
   *
   * @return array
   */
  function showBackProps($class = null) {
    $backprops_show = $this->show_backprops;
    // Si ont ne veut pas afficher les backsprops on retourne un tableau vide
    if ($backprops_show != "all" && $backprops_show != "own" || $backprops_show == "none") {
      return array();
    }
    if (!$class) {
      $class = $this->class_select;
    }
    /** @var CMbObject $object */
    $object = new $class;
    // Récupération des backprops de l'objet
    $backs      = $object->getBackProps();
    $back_props = array();
    // Pour chaque backprop on vérifie si elle est hérité ou non
    foreach ($backs as $_back) {
      if ($this->heritageBacks($_back, $object)) {
        $back_props[] = $_back;
      }
      // Ajout des backprops héritées si l'utilisateur a choisis 'all'
      elseif ($backprops_show == "all") {
        $back_props[] = $_back;
      }
    }

    // Suppression des doublons et retour des backprops
    return array_unique($back_props);
  }

  /**
   * Permet de savoir si une backprops est héritée ou non.
   *
   * @param string    $back   Propriété à tester
   * @param CMbObject $object Classe d'où  vient la propriété
   *
   * @return bool
   */
  function heritageBacks($back, $object) {
    // Récupération de la classe parent
    $parent = get_parent_class($object);
    if ((!$parent || $parent == CModelObject::class || $parent == CStoredObject::class) && $back != "user_logs") {
      return true;
    }
    /** @var CMbObject $parent */
    $parent = new $parent;
    // Vérification de si le parent possède la backprop ou non
    foreach ($parent->getBackProps() as $_back) {
      if ($_back == $back) {
        return false;
      }
    }

    return true;
  }

  /**
   * Tri les champs d'une classe en trois catégories : les champs hérités, les champs calculés et les autres.
   *
   * @param string $class La classe dont on veut récupérer les propriétées
   *
   * @return array|bool
   */
  function getFields($class) {
    if (!$class) {
      $class = $this->class_select;
    }

    /** @var CMbObject $object */
    $object = new $class;
    // Tableaux pour contenir les champs, les champs calculés et les champs hérités de la classe passée en paramètre
    $plainfield         = array();
    $formfield          = array();
    $refs               = array();
    $heritage           = $this->getParentProperties($object);
    $heritage_formatted = array();

    foreach ($heritage as $_key => $_prop) {
      if (strpos($_prop, "ref ") !== false) {
        $refs[$_key] = $_prop;
        unset($heritage[$_key]);
      }
      else {
        $heritage_formatted[$_key] = self::makePropFormat($_prop);
      }
    }

    // Parcours de l'ensemble des propriétés et trie
    foreach ($object->getProps() as $_key => $_prop) {
      if (array_key_exists($_key, $heritage)) {
        continue;
      }
      $prop_formatted = self::makePropFormat($_prop);

      if (strpos($prop_formatted, "ref ") !== false) {
        $refs[$_key] = $prop_formatted;
      }
      else {
        // FormField
        if (strpos($_key, "_") === 0) {
          $formfield[$_key] = $prop_formatted;
        }
        // PlainField
        else {
          $plainfield[$_key] = $prop_formatted;
        }
      }
    }

    $res               = array();
    $res["plainfield"] = $plainfield;
    $res["formfield"]  = $formfield;
    $res["heritage"]   = $heritage_formatted;
    $res["refs"]       = $refs;

    // Renvoie un tableau associatif contenant des tableaux de propriétés
    return $res;
  }

  /**
   * Prépare les propriétés pour les formatter pour l'affichage
   *
   * @param string $prop Propriété à formatter
   *
   * @return string
   */
  static function makePropFormat($prop) {
    $colors     = array(
      'enum'         => 'prop_enum',
      'notNull'      => 'prop_notNull',
      'index'        => 'prop_index',
      'confidential' => 'prop_confidential',
      'ref'          => 'prop_ref',
      'class'        => 'prop_class',
      'default'      => 'prop_default',
      'length'       => 'prop_length',
      'list'         => 'prop_list',
    );
    $result     = "";
    $prop_split = explode(" ", $prop);
    if (count($prop_split) == 1 || count($prop_split) === 0) {
      return "<strong>$prop</strong>";
    }
    $idx = 0;
    foreach ($prop_split as $_prop) {
      if ($idx === 0) {
        $result = "<strong>";
      }
      $choice = explode("|", $_prop);
      switch ($choice[0]) {
        case "enum":
          $result .= "<span class='" . $colors["enum"] . "'>$_prop </span>";
          break;
        case "notNull":
          $result .= "<span class='" . $colors["notNull"] . "'>$_prop </span>";
          break;
        case "index":
          $result .= "<span class='" . $colors["index"] . "'>$_prop </span>";
          break;
        case "confidential":
          $result .= "<span class='" . $colors["confidential"] . "'>$_prop </span>";
          break;
        case "ref":
          $result .= "<span class='" . $colors["ref"] . "'>$_prop </span>";
          break;
        case "default":
        case "min":
        case "max":
          $result .= "<span class='" . $colors["default"] . "'>$choice[0] : $choice[1] </span>";
          break;
        case "length":
        case "minLength":
        case "maxLength":
          $result .= "<span class='" . $colors["length"] . "'>$choice[0] : $choice[1] </span>";
          break;
        case "class":
          if (count($choice) == 1) {
            $result .= "<span class='" . $colors["class"] . "'>$choice[0] </span>";
          }
          else {
            $result .= "<span class='" . $colors["class"] . "'>$choice[0] : $choice[1] </span>";
          }
          break;
        case "list":
          $result .= "<span class='" . $colors["list"] . "'>";
          $list_idx = 0;
          foreach ($choice as $_split) {
            if ($list_idx === 0) {
              $result .= $_split . " : ";
            }
            elseif ($list_idx === count($choice) - 1) {
              $result .= $_split . " ";
            }
            else {
              $result .= $_split . ", ";
            }
            $list_idx++;
          }
          $result .= "</span>";
          break;
        default:
          $result .= $_prop . " ";
          break;
      }
      if ($idx === 0) {
        $result .= "</strong>";
      }
      $idx++;
    }

    return $result;
  }

  /**
   * Récupère les propriétés de la classe parent afin de connaitre les propriétés héritées de manière récursive
   *
   * @param CMbObject $object La classe dont on veut récupérer les propriétés du parent
   *
   * @return array
   */
  function getParentProperties($object) {
    $props  = array();
    $parent = get_parent_class($object);
    if (!$parent || $parent == CModelObject::class) {
      return $props;
    }

    /** @var CMbObject $parent */
    $parent = new $parent;
    $props  = $parent->getProps();
    if (get_parent_class($parent)) {
      $props = array_merge($this->getParentProperties($parent), $props);
    }

    return $props;
  }

  /**
   * @param array     $plainfields Propriétés sauvegardées en base de données
   * @param array     $refs        Références aux autres classes
   * @param CMbObject $object      Object dont on veut les types de propriétés
   *
   * @return array
   */
  function getDB_Specs($plainfields, $refs, $object) {
    $db_specs    = array();
    $plainfields = array_merge($plainfields, $refs);
    foreach ($plainfields as $_key => $_value) {
      $spec = $object->_specs[$_key];
      $spec = CMbFieldSpec::parseDBSpec($spec->getDBSpec());
      if ($spec["type"] == "ENUM") {
        $db_specs[$_key] = $spec["type"] . ' (';
        foreach ($spec["params"] as $_param) {
          $db_specs[$_key] .= $_param . ", ";
        }
        $db_specs[$_key] = rtrim($db_specs[$_key], ", ");
        $db_specs[$_key] .= ')';
      }
      else {
        $db_specs[$_key] = $spec["type"] . ' (' . $spec["params"][0] . ')';
      }
    }

    return $db_specs;
  }
}
