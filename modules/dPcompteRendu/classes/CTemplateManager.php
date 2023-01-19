<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use CMb128BObject;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Gestion avancée de documents (destinataires, listes de choix, etc.)
 */
class CTemplateManager implements IShortNameAutoloadable
{
    private const SEPARATOR = ' - ';

    private static $barcodeCache = [];

    public $editor        = "ckeditor";
    public $sections      = [];
    public $helpers       = [];
    public $allLists      = [];
    public $lists         = [];
    public $graphs        = [];
    public $textes_libres = [];
    public $template;
    public $document;
    public $usedLists     = [];
    public $isCourrier; // @todo : changer en applyMode
    public $valueMode     = true;
    public $isModele      = true;
    public $printMode     = false;
    public $simplifyMode  = false;
    public $messageMode   = false;
    public $parameters    = [];
    public $font;
    public $size;
    public $destinataires = [];
    public $max_sections = 0;
    /** @var bool */
    public $include_forms = true;

    /**
     * Constructeur
     *
     * @param array $parameters [optional]
     * @param bool  $valueMode  Fill the fields with value
     */
    function __construct($parameters = [], $valueMode = true)
    {
        $this->valueMode = $valueMode;
        $user            = CMediusers::get();
        $user->loadRefSpecCPAM();
        $user->loadRefDiscipline();

        $this->parameters = $parameters;

        $courrier_section = CAppUI::tr('common-Mail');
        $formule_subItem  = CAppUI::tr('CSalutation');
        $copy_subItem     = CAppUI::tr('common-copy to');

        if ($this->getParameter("isBody", 1)) {
            $properties = [
                "$formule_subItem - Début",
                "$formule_subItem - Fin",
                "$formule_subItem - " . CAppUI::tr('CSalutation-vous-te'),
                "$formule_subItem - " . CAppUI::tr('CSalutation-vous-t'),
                "$formule_subItem - " . CAppUI::tr('CSalutation-votre-ton'),
                "$formule_subItem - " . CAppUI::tr('CSalutation-votre-ta'),
                "$formule_subItem - " . CAppUI::tr('CSalutation-votre-accord genre patient'),
                CAppUI::tr('common-recipient name'),
                CAppUI::tr('common-recipient address'),
                CAppUI::tr('common-recipient cp city'),
                CAppUI::tr('common-brotherhood'),
                "$copy_subItem - " . CAppUI::tr('common-simple'),
                "$copy_subItem - " . CAppUI::tr('common-simple (multiline)'),
                "$copy_subItem - " . CAppUI::tr('common-full'),
                "$copy_subItem - " . CAppUI::tr('common-full (multiline)'),
            ];

            foreach ($properties as $_property) {
                $this->addProperty("$courrier_section - $_property", "[$courrier_section - $_property]");
            }
        }

        $general_section = CAppUI::tr('General');
        $now             = CMbDT::dateTime();
        $this->addDateProperty("$general_section - " . CAppUI::tr('common-day date'), $now);
        $this->addLongDateProperty("$general_section - " . CAppUI::tr('common-date of the day (long)'), $now);
        $this->addLongDateProperty(
            "$general_section - " . CAppUI::tr('common-date of the day (long, lowercase)'),
            $now,
            true
        );
        $this->addTimeProperty("$general_section - " . CAppUI::tr('common-current time'), $now);
        $this->addDateProperty(
            "$general_section - " . CAppUI::tr('common-last day of the calendar year'),
            substr($now, 0, 4) . "-12-31"
        );

        $meta_section   = CAppUI::tr('common-Meta Data|pl');
        $lock_subItem   = CAppUI::tr('common-Lock date');
        $locker_subItem = CAppUI::tr('common-Locker');

        if ($this->getParameter("isModele")) {
            $this->addProperty("$meta_section - $lock_subItem - " . CAppUI::tr('common-Date'));
            $this->addProperty("$meta_section - $lock_subItem - " . CAppUI::tr('common-Hour'));
            $this->addProperty("$meta_section - $locker_subItem - " . CAppUI::tr('common-Name'));
            $this->addProperty("$meta_section - $locker_subItem - " . CAppUI::tr('CMediusers-_p_first_name'));
            $this->addProperty("$meta_section - $locker_subItem - " . CAppUI::tr('common-Initial|pl'));
        }

        // Connected user
        $user_complete = $user->_view;
        if ($user->isPraticien()) {
            if ($user->titres) {
                $user_complete .= "\n" . $user->titres;
            }
            if ($user->spec_cpam_id) {
                $spec_cpam     = $user->loadRefSpecCPAM();
                $user_complete .= "\n" . $spec_cpam->text;
            }
            if ($user->adeli) {
                $user_complete .= "\nAdeli : " . $user->adeli;
            }
            if ($user->rpps) {
                $user_complete .= "\nRPPS : " . $user->rpps;
            }
            if ($user->_user_email) {
                $user_complete .= "\nE-mail : " . $user->_user_email;
            }
        }

        // Initials
        $elements_first_name = preg_split("/[ -]/", $user->_user_first_name);
        $initials_first_name = "";

        foreach ($elements_first_name as $_element) {
            $initials_first_name .= strtoupper(substr($_element, 0, 1));
        }

        $elements_last_name = preg_split("/[ -]/", $user->_user_last_name);
        $initials_last_name = "";

        foreach ($elements_last_name as $_element) {
            $initials_last_name .= strtoupper(substr($_element, 0, 1));
        }

        $redacteur_subItem      = CAppUI::tr('common-editor');
        $redacteur_init_subItem = CAppUI::tr('common-editor (initial)');
        $this->addProperty("$general_section - $redacteur_subItem", $user->_shortview);
        $this->addProperty(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('CMediusers-_p_first_name'),
            $user->_user_first_name
        );
        $this->addProperty(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('common-name'),
            $user->_user_last_name
        );
        $this->addProperty("$general_section - $redacteur_subItem - " . CAppUI::tr('CMedecin-titre'), $user->titres);
        $this->addProperty("$general_section - $redacteur_subItem " . CAppUI::tr('common-full'), $user_complete);
        $this->addProperty(
            "$general_section - $redacteur_init_subItem - " . CAppUI::tr('CMediusers-_p_first_name'),
            $initials_first_name
        );
        $this->addProperty(
            "$general_section - $redacteur_init_subItem - " . CAppUI::tr('common-name'),
            $initials_last_name
        );
        $this->addProperty(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('CMediusers-discipline_id'),
            $user->_ref_discipline->_view
        );
        $this->addProperty(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('common-Speciality'),
            $user->_ref_spec_cpam->_view
        );
        $this->addProperty("$general_section - $redacteur_subItem - " . CAppUI::tr('CMedecin-adeli'), $user->adeli);
        $this->addProperty(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('CMediusers-Gender Agreement'),
            $user->_user_sexe == "f" ? "e" : ""
        );
        $this->addBarcode(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('CMediusers-ADELI bar code'),
            $user->adeli,
            [
                "barcode" => [
                    "title" => CAppUI::tr("{$user->_class}-adeli"),
                ],
            ]
        );
        $this->addProperty("$general_section - $redacteur_subItem - " . CAppUI::tr('CMedecin-rpps'), $user->rpps);
        $this->addBarcode(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('CMedecin-RPPS bar code'),
            $user->rpps,
            [
                "barcode" => [
                    "title" => CAppUI::tr("{$user->_class}-rpps"),
                ],
            ]
        );
        $signature = $user->loadRefSignature();
        $this->addImageProperty(
            "$general_section - $redacteur_subItem - " . CAppUI::tr('common-Signature'),
            $signature->_id,
            ["title" => "$general_section - $redacteur_subItem - " . CAppUI::tr('common-Signature')]
        );

        if (CAppUI::pref("pdf_and_thumbs")) {
            $this->addProperty(
                "$general_section - " . CAppUI::tr('common-page number'),
                "[$general_section - " . CAppUI::tr('common-page number') . "]"
            );
            $this->addProperty(
                "$general_section - " . CAppUI::tr('common-number of page|pl'),
                "[$general_section - " . CAppUI::tr('common-number of page|pl') . "]"
            );
        }
    }

    /**
     * Retrouve un paramètre dans un tableau
     *
     * @param string $name    nom du paramètre
     * @param object $default [optional] valeur par défaut, si non retrouvé
     *
     * @return mixed
     */
    function getParameter($name, $default = null)
    {
        return CValue::read($this->parameters, $name, $default);
    }

    /**
     * Ajoute un champ
     *
     * @param string  $field      nom du champ
     * @param string  $value      [optional]
     * @param array   $options    [optional]
     * @param boolean $htmlescape [optional]
     *
     * @return void
     */
    function addProperty($field, $value = null, $options = [], $htmlescape = true)
    {
        if ($htmlescape) {
            $value = CMbString::htmlSpecialChars($value);
        }

        $sec = explode(self::SEPARATOR, $field);

        $actual_section =& $this->sections;

        $this->max_sections = max($this->max_sections, count($sec) - 1);

        $last_section = end($sec);
        $save_sec = null;

        foreach ($sec as $sub_sec) {
            $name_sub_sec = $sub_sec;

            if ($sub_sec === $last_section) {
                $name_sub_sec = "$save_sec - $sub_sec";
            }

            if (!array_key_exists($name_sub_sec, $actual_section)) {
                $actual_section[$name_sub_sec] = [];
            }

            $actual_section =& $actual_section[$name_sub_sec];
            $save_sec = $sub_sec;
        }

        $structure = [
            "field"     => $field,
            "value"     => $value,
            "fieldHTML" => CMbString::htmlEntities("[{$field}]", ENT_QUOTES),
            "valueHTML" => $value,
            "options"   => $options,
        ];

        $actual_section = $structure;

        // Barcode
        if (isset($options["barcode"])) {
            if ($this->valueMode) {
                $src = self::getBarcodeDataUri($actual_section['value'], $options["barcode"]);
            } else {
                $src = $actual_section['fieldHTML'];
            }

            $actual_section["valueHTML"] = "";

            if ($options["barcode"]["title"]) {
                $actual_section["valueHTML"] .= $options["barcode"]["title"] . "<br />";
            }

            $actual_section["valueHTML"] .= "<img alt=\"$field\" src=\"$src\" ";

            foreach ($options["barcode"] as $name => $attribute) {
                $actual_section["valueHTML"] .= " $name=\"$attribute\"";
            }

            $actual_section["valueHTML"] .= "/>";
            $actual_section["fieldHTML"] = $actual_section["valueHTML"];
        }

        // Custom data
        if (isset($options["data"]) && empty($options["image"])) {
            $data   = $options["data"];

            if ($this->valueMode) {
                $view = $actual_section['value'];
            } else {
                $view = $actual_section['field'];
            }

            $actual_section["valueHTML"] = "[<span data-data='$data'>$view</span>]";
            $actual_section["fieldHTML"] = $actual_section["valueHTML"];
        }

        // Image (from a CFile object)
        if (isset($options["image"])) {
            if ($this->valueMode) {
                $file = new CFile();
                $src  = $actual_section['value'];
                // Ne charger le fichier que si c'est un id numérique
                if (is_numeric($actual_section['value'])) {
                    $file->load($actual_section['value']);
                    $src = $file->getThumbnailDataURI();
                }
            } elseif (isset($options["data"])) {
                $src = $options["data"];
            } else {
                $src = $actual_section['fieldHTML'];
            }

            $attribute_names = ["width", "height", "title"];

            $attributes = "";
            foreach ($attribute_names as $_name) {
                if (isset($options[$_name])) {
                    $attributes .= " $_name=\"{$options[$_name]}\"";
                }
            }

            $actual_section["valueHTML"] = "<img src=\"" . $src . "\" $attributes/>";
            $actual_section["fieldHTML"] = $actual_section["valueHTML"];
        }

        if (isset($options["datamatrix"])) {
            $actual_section["valueHTML"] = "";

            if ($this->valueMode) {
                $src = $actual_section['value'];
            } else {
                $src = $actual_section['fieldHTML'];
            }

            $actual_section["valueHTML"] .= "<img alt=\"$field\" src=\"$src\" ";

            foreach ($options["datamatrix"] as $name => $attribute) {
                $actual_section["valueHTML"] .= " $name=\"$attribute\"";
            }

            $actual_section["valueHTML"] .= "/>";

            if ($options["datamatrix"]["title"]) {
                $actual_section["valueHTML"] .= "<br />" . $options["datamatrix"]["title"];
            }

            $actual_section["fieldHTML"] = $actual_section["valueHTML"];
            $actual_section["uri"]       = $src;
        }
    }

    /**
     * Get the data URI of a barcode
     *
     * @param string $code    Code
     * @param array  $options Options
     *
     * @return null|string
     */
    public static function getBarcodeDataUri($code, $options)
    {
        if (!$code) {
            return null;
        }

        $with_text = CMbArray::get($options, "with_text", true);

        $size = "{$options['width']}x{$options['width']}";

        if (isset(self::$barcodeCache[$code][$size])) {
            return self::$barcodeCache[$code][$size];
        }

        CMb128BObject::init();
        $bc_options = ($with_text ? (BCD_DEFAULT_STYLE | BCS_DRAW_TEXT) : BCD_DEFAULT_STYLE) & ~BCS_BORDER;
        $barcode    = new CMb128BObject($options["width"] * 2, $options["height"] * 2, $bc_options, $code);

        $barcode->SetFont(7);
        $barcode->DrawObject(2);

        ob_start();
        $barcode->FlushObject();
        $image = ob_get_contents();
        ob_end_clean();

        $barcode->DestroyObject();

        $image = "data:image/png;base64," . urlencode(base64_encode($image));

        return self::$barcodeCache[$code][$size] = $image;
    }

    /**
     * Ajoute un champ de type date
     *
     * @param string $field nom du champ
     * @param string $value [optional]
     *
     * @return void
     */
    function addDateProperty($field, $value = null)
    {
        $value = $value ? CMbDT::format($value, CAppUI::conf("date")) : "";
        $this->addProperty($field, $value);
    }

    /**
     * Ajoute un champ de type date longue
     *
     * @param string  $field     Nom du champ
     * @param string  $value     Valeur du champ
     * @param boolean $lowercase Champ avec des minuscules
     *
     * @return void
     */
    function addLongDateProperty($field, $value, $lowercase = false)
    {
        $value = $value ? ucfirst(CMbDT::format($value, CAppUI::conf("longdate"))) : "";
        $this->addProperty($field, $lowercase ? CMbString::lower($value) : $value);
    }

    /**
     * Ajoute un champ de type heure
     *
     * @param string $field Nom du champ
     * @param string $value Valeur du champ
     *
     * @return void
     */
    function addTimeProperty($field, $value = null)
    {
        $value = $value ? CMbDT::format($value, CAppUI::conf("time")) : "";
        $this->addProperty($field, $value);
    }

    /**
     * Ajoute un champ de type code-barre
     *
     * @param string $field   Nom du champ
     * @param string $data    Code barre
     * @param array  $options Options
     *
     * @return void
     */
    function addBarcode($field, $data, $options = [])
    {
        $options = array_replace_recursive(
            [
                "barcode" => [
                    "width"  => 220,
                    "height" => 60,
                    "class"  => "barcode",
                    "title"  => "",
                ],
            ],
            $options
        );

        $this->addProperty($field, $data, $options, false);
    }

    /**
     * Ajoute un champ de type image
     *
     * @param string $field   Nom du champ
     * @param int    $file_id Identifiant du fichier
     *
     * @return void
     */
    function addImageProperty($field, $file_id, $options = [])
    {
        $options["image"] = 1;
        $this->addProperty($field, $file_id, $options, false);
    }

    /**
     * Construit l'élément html pour les champs, listes de choix et textes libres.
     *
     * @param string $spanClass classe de l'élément
     * @param string $text      contenu de l'élément
     *
     * @return string
     */
    function makeSpan($spanClass, $text)
    {
        // Escape entities cuz CKEditor does so
        $text = CMbString::htmlEntities($text);

        // Keep backslashed double quotes instead of quotes
        // cuz CKEditor creates double quoted attributes
        return "<span class=\"{$spanClass}\">{$text}</span>";
    }

    /**
     * Ajoute un champ au format Markdown
     *
     * @param string $field nom du champ
     * @param string $value [optional]
     *
     * @return void
     */
    function addMarkdown($field, $value = null)
    {
        // Remplacement des sauts de ligne par br
        $value = preg_replace('(\r\n|\n|\n\r)', '<br />', $value);
        $value = $value ? CMbString::markdown($value) : "";
        if (preg_match_all("/<p>/", $value) === 1) {
            $value = $value ? preg_replace("/^<p>(.*)<\/p>$/ms", "$1", $value) : "";
        }
        $this->addProperty($field, $value, ["markdown" => true], false);
    }

    /**
     * Ajoute un champ de type durée
     *
     * @param string $field Nom du champ
     * @param string $value Valeur du champ
     *
     * @return void
     */
    function addDurationProperty($field, $value = null)
    {
        $value = $value ? CMbDT::formatDuration($value) : "";
        $this->addProperty($field, $value);
    }

    /**
     * Ajoute un champ de type date et heure
     *
     * @param string $field Nom du champ
     * @param string $value Valeur du champ
     *
     * @return void
     */
    function addDateTimeProperty($field, $value = null)
    {
        $value = $value ? CMbDT::format($value, CAppUI::conf("datetime")) : "";
        $this->addProperty($field, $value);
    }

    /**
     * Ajoute un champ de type liste
     *
     * @param string  $field      Nom du champ
     * @param array   $items      Liste de valeurs
     * @param boolean $htmlescape [optional]
     * @param boolean $markdown   [optional]
     *
     * @return void
     */
    function addListProperty($field, $items = null, $htmlescape = true, bool $markdown = false)
    {
        $this->addProperty($field, $this->makeList($items, $htmlescape, 0, $markdown), null, false);
    }

    /**
     * Génération de la source html pour la liste d'items
     *
     * @param array   $items       liste d'items
     * @param boolean $htmlescape  [optional]
     * @param integer $indentation Niveau d'indentation
     * @param boolean $markdown    [optional]
     *
     * @return string|null
     */
    function makeList($items, $htmlescape = true, $indentation = 0, $markdown = false)
    {
        if (!$items) {
            return null;
        }

        // Make a list out of a string
        if (!is_array($items)) {
            $items = [$items];
        }

        // Escape content
        if ($htmlescape) {
            $items = array_map("Ox\Core\CMbString::htmlEntities", $items);
        }

        if ($markdown) {
            foreach ($items as $_key => $_item) {
                $value = CMbString::markdown($_item);
                $value = preg_replace("/\n/", "", $value);

                if ($value && preg_match_all("/<p>/", $value) === 1) {
                    $value = preg_replace("/^<p>(.*)<\/p>$/ms", "$1", $value);
                }
                $items[$_key] = $value;
            }
        }

        $indent = '';
        if ($indentation) {
            $indent = str_repeat('&emsp;', $indentation);
        }

        // HTML production
        switch ($default = CAppUI::pref("listDefault")) {
            case "ulli":
                $html = "<ul>";
                foreach ($items as $item) {
                    $html .= "<li>$item</li>";
                }
                $html .= "</ul>";
                break;

            case "br":
                $html   = "";
                $prefix = CAppUI::pref("listBrPrefix");
                foreach ($items as $item) {
                    $html .= "<br />$indent$prefix $item";
                }
                break;

            case "inline":
                $separator = CAppUI::pref("listInlineSeparator");
                $html      = $indent . implode(" $separator ", $items);
                break;

            default:
                $html = "";
                trigger_error("Default style for list is unknown '$default'", E_USER_WARNING);
                break;
        }

        return $html;
    }

    function addDatamatrixProperty($field, $data, $options = [])
    {
        $options = array_replace_recursive(
            [
                "datamatrix" => [
                    "width"  => 70,
                    "height" => 70,
                    "class"  => "datamatrix",
                    "title"  => "",
                ],
            ],
            $options
        );
        $this->addProperty($field, $data, $options, false);
    }

    /**
     * Ajoute un champ de type graphique
     *
     * @param string $field   Champ
     * @param array  $data    Tableau de données
     * @param array  $options Options
     *
     * @return void
     */
    function addGraph($field, $data, $options = [])
    {
        $this->graphs[$field] = [
            "data"    => $data,
            "options" => $options,
            "name"    => $field,
        ];

        $this->addProperty($field, $field, null, false);
    }

    /**
     * Ajoute une aide à la saisie au templateManager
     *
     * @param string $name Nom de l'aide à la saisie
     * @param string $text Texte de remplacement de l'aide
     *
     * @return void
     */
    function addHelper($name, $text)
    {
        $this->helpers[$name] = $text;
    }

    function addAdvancedData($name, $data, $value)
    {
        $options = [
            "data" => $data,
        ];

        $this->addProperty($name, $value, $options, false);
    }

    /**
     * Applique les champs variable sur un document
     *
     * @param CCompteRendu|CPack $template TemplateManager sur lequel s'applique le document
     *
     * @return void
     */
    function applyTemplate($template)
    {
        assert($template instanceof CCompteRendu || $template instanceof CPack);

        if ($template instanceof CCompteRendu) {
            $this->font = $template->font ? CCompteRendu::$fonts[$template->font] : "";
            $this->size = $template->size;

            if (!$this->valueMode) {
                $this->setFields($template->object_class);
            }
        }

        $this->renderDocument($template->_source);
    }

    /**
     * Applique les champs variable d'un objet
     *
     * @param string $modeleType classe de l'objet
     *
     * @return void
     */
    function setFields($modeleType)
    {
        if ($modeleType) {
            $object = new $modeleType;
            /** @var CMbObject $object */
            $object->fillTemplate($this);
        }
    }

    /**
     * Applique les champs variables sur une source html
     *
     * @param string $_source source html
     *
     * @return void
     */
    function renderDocument($_source)
    {
        $fields = [];
        $values = [];

        $fields_regex = [];
        $values_regex = [];

        $this->subRenderDocument($this->sections, $fields, $values, $fields_regex, $values_regex);

        if (count($fields_regex)) {
            $_source = preg_replace($fields_regex, $values_regex, $_source);
        }

        if (count($fields)) {
            $_source = str_ireplace($fields, $values, $_source);
        }

        if (count($fields_regex) || count($fields)) {
            $this->document = $_source;
        }
    }

    protected function subRenderDocument(
        array $sections,
        array &$fields,
        array &$values,
        array &$fields_regex,
        array &$values_regex
    ): void {
        if (!count($sections)) {
            return;
        }

        foreach ($sections as $type => $properties) {
            if (!isset($properties['field'])) {
                $this->subRenderDocument($properties, $fields, $values, $fields_regex, $values_regex);
                continue;
            }

            if ($properties["valueHTML"] && isset($properties["options"]["barcode"])) {
                $image    = self::getBarcodeDataUri($properties["value"], $properties["options"]["barcode"]);
                $fields[] = "src=\"[{$properties['field']}]\"";
                $values[] = "src=\"$image\"";
            } elseif (isset($properties["options"]["data"]) && empty($properties["options"]["image"])) {
                $data     = $properties["options"]["data"];
                $form_ctx = '';
                if (strpos($data, 'CExObject') !== false) {
                    $split_field = explode(' - ', $properties['field']);
                    $form_ctx = $split_field[0];
                }

                $fields_regex[] = $this->getDataRegex($data, $form_ctx);
                $values_regex[] = $properties["value"];
            } elseif ($properties["valueHTML"] && isset($properties["options"]["image"])) {
                $src_src  = $properties["options"]["data"] ?? "[{$properties['field']}]";
                $fields[] = "src=\"$src_src\"";

                if (is_numeric($properties['value'])) {
                    $file = new CFile();
                    $file->load($properties['value']);

                    $src      = $file->getDataURI();
                    $values[] = "src=\"$src\"";
                } else {
                    $values[] = "src=\"" . $properties['value'] . "\"";
                }
            } elseif ($properties["valueHTML"] && isset($properties["options"]["datamatrix"])) {
                $fields[] = "src=\"[{$properties['field']}]\"";
                $values[] = "src=\"{$properties['uri']}\"";
            } else {
                $properties["fieldHTML"] = preg_replace("/'/", "&#039;", $properties["fieldHTML"]);

                $field = $properties["fieldHTML"];
                $value = $properties["valueHTML"];

                // Le markdown génère déjà des <br />, n'en ajoutons pas plus qu'il n'en faut...
                if (!isset($_property["options"]["markdown"])) {
                    $value = nl2br($value);
                }

                $fields[] = $field;
                $values[] = $value;
            }
        }
    }

    /**
     * Get the regex to replace data
     *
     * @param string $data     Data key
     * @param string $form_ctx Context for forms fields
     *
     * @return string
     */
    protected function getDataRegex($data, $form_ctx = '')
    {
        $data_re  = preg_quote($data, "/");
        $form_ctx = preg_quote(CMbString::htmlEntities($form_ctx), "/");

        return '/(\[<span data-data=["\']' . $data_re . '["\']>' . $form_ctx . '[^<]+<\/span>\])/ms';
    }

    /**
     * Affiche l'éditeur de texte avec le contenu du document
     *
     * @return void
     */
    function initHTMLArea()
    {
        CSessionHandler::start();

        // Don't use CValue::setSession which uses $m
        $_SESSION["dPcompteRendu"]["templateManager"] = gzcompress(serialize($this));

        CSessionHandler::writeClose();

        $smarty = new CSmartyDP("modules/dPcompteRendu");
        $smarty->assign("templateManager", $this);
        $smarty->display("init_htmlarea");
    }

    /**
     * Charge les listes de choix pour un utilisateur, ou la fonction et l'établissement de l'utilisateur connecté
     *
     * @param int  $user_id         identifiant de l'utilisateur
     * @param int  $compte_rendu_id identifiant du compte-rendu
     * @param bool $instance_mode   Flag pour ne prendre que les listes de choix d'instance
     *
     * @return void
     */
    function loadLists($user_id, $compte_rendu_id = 0, $instance_mode = false)
    {
        $where = [];
        $user  = CMediusers::get($user_id);
        $user->loadRefFunction();
        if ($user_id) {
            $curr_user           = CMediusers::get();
            $secondary_functions = array_keys($curr_user->loadRefsSecondaryFunctions());
            $secondary_functions = $secondary_functions ? "," . implode(',', $secondary_functions) : "";
            $where[]             = "(
        user_id IN ('$user->user_id', '$curr_user->_id') OR
        function_id IN ('$user->function_id', '$curr_user->function_id' $secondary_functions) OR
        group_id = '{$user->_ref_function->group_id}'
      ) OR (user_id IS NULL AND function_id IS NULL AND group_id IS NULL)";
        } elseif ($instance_mode) {
            $where[] = "user_id IS NULL AND function_id IS NULL AND group_id IS NULL";
        } else {
            $compte_rendu = new CCompteRendu();
            $compte_rendu->load($compte_rendu_id);
            $where[] = "(
        function_id IN('$user->function_id', '$compte_rendu->function_id') OR
        group_id IN('{$user->_ref_function->group_id}', '$compte_rendu->group_id')
      ) OR (user_id IS NULL AND function_id IS NULL AND group_id IS NULL)";
        }

        $where[]        = $user->getDS()->prepare("`compte_rendu_id` IS NULL OR compte_rendu_id = %", $compte_rendu_id);
        $order          = "user_id, function_id, group_id, nom ASC";
        $lists          = new CListeChoix();
        $this->allLists = $lists->loadList($where, $order);

        foreach ($this->allLists as $list) {
            //Escape double spaces
            $list->nom = str_replace("  ", " ", $list->nom);
            /** @var CListeChoix $list */
            $this->addList($list->nom);
        }
    }

    /**
     * Ajoute un champ de type liste
     *
     * @param string $name Nom de la liste
     *
     * @return void
     */
    function addList($name)
    {
        $this->lists[$name] = [
            "view" => $name,
            "item" => CMbString::htmlEntities("[Liste - {$name}]"),
        ];
    }

    /**
     * Charge les listes de choix d'une classe pour un utilisateur, sa fonction et son établissement
     *
     * @param int    $user_id           identifiant de l'utilisateur
     * @param string $modeleType        classe ciblée
     * @param string $other_function_id autre fonction
     *
     * @return void
     */
    function loadHelpers($user_id, $modeleType, $other_function_id = "")
    {
        $compte_rendu = new CCompteRendu();
        $ds           = $compte_rendu->getDS();

        // Chargement de l'utilisateur courant
        $currUser = CMediusers::get($user_id);

        $order = "name";

        // Where user_id
        $whereUser            = [];
        $whereUser["user_id"] = $ds->prepare("= %", $user_id);
        $whereUser["class"]   = $ds->prepare("= %", $compte_rendu->_class);

        // Where function_id
        $whereFunc                = [];
        $whereFunc["function_id"] = $other_function_id ?
            "IN ($currUser->function_id, $other_function_id)" : $ds->prepare("= %", $currUser->function_id);
        $whereFunc["class"]       = $ds->prepare("= %", $compte_rendu->_class);

        // Where group_id
        $whereGroup             = [];
        $group                  = CGroups::loadCurrent();
        $whereGroup["group_id"] = $ds->prepare("= %", $group->_id);
        $whereGroup["class"]    = $ds->prepare("= %", $compte_rendu->_class);

        // Chargement des aides
        $aide = new CAideSaisie();

        /** @var CAideSaisie $aidesUser */
        $aidesUser = $aide->loadList($whereUser, $order, null, "aide_id");

        /** @var CAideSaisie $aidesFunc */
        $aidesFunc = $aide->loadList($whereFunc, $order, null, "aide_id");

        /** @var CAideSaisie $aidesGroup */
        $aidesGroup = $aide->loadList($whereGroup, $order, null, "aide_id");

        $this->helpers["Aide de l'utilisateur"] = [];
        foreach ($aidesUser as $aideUser) {
            if ($aideUser->depend_value_1 == $modeleType || $aideUser->depend_value_1 == "") {
                $this->helpers["Aide de l'utilisateur"][CMbString::htmlEntities(
                    $aideUser->name
                )] = CMbString::htmlEntities($aideUser->text);
            }
        }
        $this->helpers["Aide de la fonction"] = [];
        foreach ($aidesFunc as $aideFunc) {
            if ($aideFunc->depend_value_1 == $modeleType || $aideFunc->depend_value_1 == "") {
                $this->helpers["Aide de la fonction"][CMbString::htmlEntities(
                    $aideFunc->name
                )] = CMbString::htmlEntities($aideFunc->text);
            }
        }
        $this->helpers["Aide de l'&eacute;tablissement"] = [];
        foreach ($aidesGroup as $aideGroup) {
            if ($aideGroup->depend_value_1 == $modeleType || $aideGroup->depend_value_1 == "") {
                $this->helpers["Aide de l'&eacute;tablissement"][CMbString::htmlEntities($aideGroup->name)] =
                    CMbString::htmlEntities($aideGroup->text);
            }
        }
    }

    /**
     * Obtention des listes utilisées dans le document
     *
     * @param CListeChoix[] $lists Listes de choix
     *
     * @return CListeChoix[]
     */
    function getUsedLists($lists)
    {
        $this->usedLists = [];

        // Les listes de choix peuvent contenir des caractères qui ne sont pas dans la table iso-8859-1
        // On change donc temporairement en windows-1252
        $actual_encoding = CApp::$encoding;
        CApp::$encoding  = "windows-1252";

        foreach ($lists as $value) {
            $nom = CMbString::htmlEntities(stripslashes("[Liste - $value->nom]"), ENT_QUOTES);
            $pos = strpos($this->document, $nom);
            if ($pos !== false) {
                $this->usedLists[$pos] = $value;
            }
        }

        CApp::$encoding = $actual_encoding;

        ksort($this->usedLists);

        return $this->usedLists;
    }

    /**
     * Vérification s'il s'agit d'un courrier
     *
     * @return bool
     */
    function isCourrier()
    {
        return $this->isCourrier = strpos($this->document, "[Courrier -") !== false;
    }

    function makeFields($prefix, $with_separator = true, $check_modele = true, $with_bracket = true)
    {
        if ($check_modele && $this->isModele) {
            return true;
        }

        $needle = CMbString::htmlEntities(($with_bracket ? "[" : "") . $prefix . ($with_separator ? " - " : ""));

        return strpos($this->document, $needle) !== false;
    }
}
