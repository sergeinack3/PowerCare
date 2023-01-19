<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\ResourceLoaders\CHTMLResourceLoader;

/**
 * Class CSmartyDP
 */
class CSmartyDP extends CSmartyMB
{

    /**
     * Constructor
     *
     * @param string $dir Directory
     */
    function __construct($dir = null)
    {
        parent::__construct($dir);

        $this->assign("IS_MEDIBOARD_EXT_DARK", CAppUI::isMediboardExtDark());

        $this->register_block("mb_form", [$this, "mb_form"]);
        $this->register_block("vertical", [$this, "vertical"]);
        $this->register_block("th_vertical", [$this, "th_vertical"]);
        $this->register_block("th_rotate", [$this, "th_rotate"]);
        $this->register_block("me_form_field", [$this, "me_form_field"]);
        $this->register_block("me_form_bool", [$this, "me_form_bool"]);
        $this->register_block("me_img_title", [$this, "me_img_content"]);
        $this->register_block("me_scroller", [$this, "me_scroller"]);

        $this->register_function("mb_field", [$this, "mb_field"]);
        $this->register_function("mb_password", [$this, "mb_password"]);
        $this->register_function("mb_key", [$this, "mb_key"]);
        $this->register_function("mb_label", [$this, "mb_label"]);
        $this->register_function("mb_title", [$this, "mb_title"]);
        $this->register_function("mb_ternary", [$this, "mb_ternary"]);
        $this->register_function("mb_colonne", [$this, "mb_colonne"]);
        $this->register_function("mb_module_icon", [$this, "mb_module_icon"]);

        $this->register_function("me_button", [$this, "me_button"]);
        $this->register_function("me_dropdown_button", [$this, "me_dropdown_button"]);
        $this->register_function("me_img", [$this, "me_img"]);
    }

    /**
     * Mb_form
     *
     * @return string
     */
    function mb_form($params, $content, &$smarty, &$repeat)
    {
        $fields = [
            "m"     => CMbArray::extract($params, "m", null, true),
            "dosql" => CMbArray::extract($params, "dosql"),
            "tab"   => CMbArray::extract($params, "tab"),
            "a"     => CMbArray::extract($params, "a"),
        ];

        $attributes = [
            "name"   => CMbArray::extract($params, "name", null, true),
            "method" => CMbArray::extract($params, "method", "get"),
            "action" => CMbArray::extract($params, "action", "?"),
            "class"  => CMbArray::extract($params, "className", ""),
        ];

        $attributes += $params;

        $fields = array_filter($fields);

        $_content = "";
        foreach ($fields as $name => $value) {
            $_content .= "\n" . CHTMLResourceLoader::getTag(
                    "input", [
                               "type"  => "hidden",
                               "name"  => $name,
                               "value" => $value,
                           ]
                );
        }

        $_content .= $content;

        return CHTMLResourceLoader::getTag("form", $attributes, $_content);
    }

    /**
     * Diplays veritcal text
     *
     * @return string
     */
    function vertical($params, $content, &$smarty, &$repeat)
    {
        if (isset($content)) {
            $content = trim($content);
            $content = preg_replace("/\s+/", " ", $content);
            $orig    = $content;
            $content = strip_tags($content);
            $content = preg_replace("/\s+/", chr(0xA0), $content); // == nbsp

            $letters = str_split($content);

            $html = "";
            foreach ($letters as $_letter) {
                $html .= "<i>$_letter</i>";
            }

            return "<span class=\"vertical\"><span class=\"nowm\">$html</span><span class=\"orig\">$orig</span></span>";
        }
    }

    /**
     * Display vertical text in a th element
     *
     * @return string
     */
    function th_vertical($params, $content, &$smarty, &$repeat)
    {
        if (isset($content)) {
            $extra = CMbArray::makeXmlAttributes($params);

            return "<th $extra>" . $this->vertical($params, $content, $smarty, $repeat) . "</th>";
        }
    }

    /**
     * Diplays rotated table header cell
     *
     * @return string
     */
    function th_rotate($params, $content, &$smarty, &$repeat)
    {
        if (isset($content)) {
            $class = CMbArray::extract($params, "class");

            return "<th class='rotate-45 $class'><div><span>$content</span></div></th>";
        }
    }

    /**
     * Fonction d'écriture des champs des objets
     *
     * @param array $params Tableau des parametres
     *                      - object          : Objet
     *                      - field           : Nom du champ a afficher (le champs doit avoir des specs sinon "spec"
     *                      non optionnel)
     *                      - prop            : {optionnel} Specification du champs, par defaut, celle de la classe
     *                      - separator       : {optionnel} Séparation entre les champs de type "radio" [default: ""]
     *                      - cycle           : {optionnel} Cycle de répétition du séparateur (pour les enums en type
     *                      radio) [default:
     *                      "1"]
     *                      - typeEnum        : {optionnel} Type d'affichage des enums (values : "select", "radio")
     *                      [default: "select"]
     *                      - emptyLabel      : {optionnel} Ajout d'un "option" en amont des valeurs ayant pour value
     *                      ""
     *                      - class           : {optionnel} Permet de donner une classe aux champs
     *                      - hidden          : {optionnel} Permet de forcer le type "hidden"
     *                      - canNull         : {optionnel} Permet de passer outre le notNull de la spécification
     * @param self  $smarty The Smarty object
     *
     * @return void
     * @throws Exception
     */
    function mb_field($params, &$smarty)
    {
        require_once $smarty->_get_plugin_filepath('shared', 'escape_special_chars');

        if (null == $object = CMbArray::extract($params, "object")) {
            $class  = CMbArray::extract($params, "class", null, true);
            $object = new $class;
        }

        $field   = CMbArray::extract($params, "field", null, true);
        $prop    = CMbArray::extract($params, "prop");
        $canNull = CMbArray::extract($params, "canNull");

        if (null !== $value = CMbArray::extract($params, "value")) {
            $object->$field = $value;
        }

        // Get spec, may create it
        $spec = $prop !== null ?
            CMbFieldSpecFact::getSpec($object, $field, $prop) :
            $object->_specs[$field];

        if ($canNull === "true" || $canNull === true) {
            $spec->notNull = 0;
            $tabSpec       = explode(" ", $spec->prop);
            CMbArray::removeValue("notNull", $tabSpec);
            $spec->prop = implode(" ", $tabSpec);
        }

        if ($canNull === "false" || $canNull === false) {
            $spec->notNull = 1;
            $spec->prop    = "canNull notNull $spec->prop";
        }

        return $spec->getFormElement($object, $params);
    }

    /**
     * Affichage d'un champ de password avec un bouton associé pour changer le type de champ [password / text]
     *
     * @param array $params   Tableau de paramètres
     *                        - input_class : Classes appliquées au champ
     *                        - size : Taille de champ
     *                        - maxlenght : Longueur maximum du champ
     *                        - name : Nom du champ (défaut : password)
     *                        - field_id : Identifiant du champ (défaut : Aléatoire)
     * @param self  $smarty   The Smarty object
     *
     * @return string
     * @throws Exception
     */
    public function mb_password($params, &$smarty)
    {
        $field_class = CMbArray::extract($params, "input_class");
        $size        = CMbArray::extract($params, "size");
        $maxlength   = CMbArray::extract($params, "maxlength");
        $field_name  = CMbArray::extract($params, "name", "password");
        $field_id    = CMbArray::extract($params, "field_id", uniqid("", true));

        $return = "<input id='$field_id' type='password' class='str $field_class' size='$size' maxlength='$maxlength' name='$field_name' />";
        $return .= "
      <div id='$field_id-on' class='mb-pwd-icon mb-pwd-icon-on'></div>
      <div id='$field_id-off' class='mb-pwd-icon mb-pwd-icon-off displayed'></div>
      <script>
        Main.add(MediboardExt.MeFormField.prepareMbPassword.curry('$field_id'));
      </script>
    ";

        return $return;
    }

    /**
     * Fonction d'écriture des labels
     *
     * @param array $params Tableau des parametres
     *                      - object      : Objet
     *                      - field       : Nom du champ a afficher (le champs doit avoir des specs sinon "spec" non
     *                      optionnel)
     *                      - defaultFor  : {optionnel} Ajout d'une valeur à cibler pour "select" ou "radio"
     *                      - typeEnum    : {optionnel} Type d'affichage des enums à cibler (values : "select",
     *                      "radio") [default:
     *                      "select"]
     * @param self  $smarty The Smarty object
     *
     * @return string
     * @throws Exception
     */
    function mb_label($params, &$smarty)
    {
        /** @var CMbObject $object */
        if (null == $object = CMbArray::extract($params, "object")) {
            $class  = CMbArray::extract($params, "class", null, true);
            $object = new $class;
        }

        $field = CMbArray::extract($params, "field", null, true);

        if (!array_key_exists($field, $object->_specs)) {
            $object->_specs[$field] = CMbFieldSpecFact::getSpec($object, $field, "");
            trigger_error("Spec missing for class '$object->_class' field '$field'", E_USER_WARNING);
        }

        return $object->_specs[$field]->getLabelElement($object, $params);
    }

    /**
     * Fonction d'écriture  des labels de titre
     *
     * @param array $params Tableau des parametres
     *                      - object      : Objet
     *                      - field       : Nom du champ a afficher (le champs doit avoir des specs sinon "spec" non
     *                      optionnel)
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function mb_title($params, &$smarty)
    {
        /** @var CMbObject $object */
        if (null == $object = CMbArray::extract($params, "object")) {
            $class  = CMbArray::extract($params, "class", null, true);
            $object = new $class;
        }

        $field = CMbArray::extract($params, "field", null, true);

        return $object->_specs[$field]->getTitleElement($object, $params);
    }

    /**
     * Fonction d'écriture des labels
     *
     * @param array $params Tableau des parametres
     *                      - var   : Name of the new variable
     *                      - test  : Test for ternary operator
     *                      - value : Value if test is true
     *                      - other : Value if test is false
     * @param self  $smarty The Smarty object
     *
     * @return mixed
     */
    function mb_ternary($params, &$smarty)
    {
        $test  = CMbArray::extract($params, "test", null, true);
        $value = CMbArray::extract($params, "value", null, true);
        $other = CMbArray::extract($params, "other", null, true);

        $result = $test ? $value : $other;

        if ($var = CMbArray::extract($params, "var", null)) {
            $smarty->assign($var, $result);
        } else {
            return $result;
        }
    }

    /**
     * Fonction de trie des colonnes
     *
     * @param array $params Tableau des parametres
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function mb_colonne($params, &$smarty)
    {
        $class         = CMbArray::extract($params, "class", null, true);
        $field         = CMbArray::extract($params, "field", null, true);
        $order_col     = CMbArray::extract($params, "order_col", null, true);
        $order_way     = CMbArray::extract($params, "order_way", null, true);
        $order_suffixe = CMbArray::extract($params, "order_suffixe", "", false);
        $url           = CMbArray::extract($params, "url", null, false);
        $function      = CMbArray::extract($params, "function", null, false);
        $label         = CMbArray::extract($params, "label", null, false);

        $sHtml = "<label for=\"$field\" title=\"" . CAppUI::tr("$class-$field-desc") . "\">";
        $sHtml .= ($label) ? $label : CAppUI::tr("$class-$field-court");
        $sHtml .= "</label>";

        $css_class     = ($order_col == $field) ? "sorted" : "sortable";
        $order_way_inv = ($order_way == "ASC") ? "DESC" : "ASC";

        if ($url) {
            if ($css_class == "sorted") {
                return "<a class='$css_class $order_way'"
                    . "href='$url&amp;order_col$order_suffixe=$order_col&amp;order_way$order_suffixe=$order_way_inv'>$sHtml</a>";
            }
            if ($css_class == "sortable") {
                return "<a class='$css_class' href='$url&amp;order_col$order_suffixe=$field&amp;order_way$order_suffixe=ASC'>$sHtml</a>";
            }
        }

        if ($function) {
            if ($css_class == "sorted") {
                return "<a class='$css_class $order_way' onclick=\"$function('$order_col','$order_way_inv');\">$sHtml</a>";
            }
            if ($css_class == "sortable") {
                return "<a class='$css_class' onclick=\"$function('$field','ASC');\">$sHtml</a>";
            }
        }
    }

    function mb_module_icon($params, &$smarty)
    {
        $mod_name     = CMbArray::extract($params, "mod_name");
        $mod_category = CMbArray::extract($params, "mod_category");
        $svg_icon     = CAppUI::conf('root_dir') . '/modules/' . $mod_name . '/images/icon.svg';
        $dark_class   = CAppUI::isMediboardExtDark() ? "dark" : "";

        if (file_exists($svg_icon)) {
            $svg_icon_content = file_get_contents($svg_icon);

            return '<div class="module-icon ' . $dark_class . '">' . $svg_icon_content . '</div>';
        }

        $icon_name = $this->getIconFromModuleCategory($mod_category);

        $icon_content = '<i class="mdi mdi-18px ' . $icon_name . '" style="float: right"></i>';

        return '<div class="module-icon ' . $dark_class . '">' . $icon_content . '</div>';
    }

    // TODO : Delete this function with Vue Components ref for System & Preferences
    protected function getIconFromModuleCategory($mod_category): string
    {
        switch ($mod_category) {
            case "interoperabilite":
                return "mdi-share-variant";
            case "import":
                return "mdi-database-import";
            case "dossier_patient":
                return "mdi-badge-account-horizontal";
            case "circuit_patient":
                return "mdi-hospital-building";
            case "erp":
                return "mdi-package-variant";
            case "administratif":
                return "mdi-tune";
            case "referentiel":
                return "mdi-compass";
            case "plateau_technique":
                return "mdi-heart-pulse";
            case "systeme":
                return "mdi-tools";
            case "parametrage":
                return "mdi-cogs";
            case "reporting":
                return "mdi-chart-pie";
            default:
                return "mdi-bookmark";
        }
    }

    /**
     * Affiche un bouton en ancien style, l'ajoute à la liste du dropdownbutton dans le thème Mediboard Etendu
     *
     * @param array $params Parameters array
     *                      - label     Label du bouton
     *                      - label_suf Suffix du label du bouton
     *                      - onclick   Javascript à executer au click
     *                      - icon      Icone du bouton
     *                      - link      Lien du bouton (opt : Remplace le onclick par un lien)
     *                      - old_class Class à appliquer dans l'ancien style (opt)
     *                      - title     Contenu de l'attribut title du bouton (opt)
     *                      - attr      Attribut supplémentaire au bouton (exemple : disabled)
     *                      - id        Identifiant du bouton (opt)
     * @param       $smarty
     *
     * @return string
     */
    function me_button($params, &$smarty)
    {
        $label        = CMbArray::extract($params, "label", null);
        $label_suf    = CMbArray::extract($params, "label_suf", null);
        $callback     = CMbArray::extract($params, "onclick", null);
        $icon         = CMbArray::extract($params, "icon", null);
        $link         = CMbArray::extract($params, "link", null);
        $old_class    = CMbArray::extract($params, "old_class", null);
        $title        = CMbArray::extract($params, "title", null);
        $title_tr     = CMbArray::extract($params, "title_tr", null);
        $attr         = CMbArray::extract($params, "attr", null);
        $id           = CMbArray::extract($params, "id", null);
        $button_group = CMbArray::extract($params, "button_group", "default");

        /* Reserved usage*/
        $use_from_dropdown = CMbArray::extract($params, "use_from_dropdown", false);

        if ($title_tr && !$title && !$use_from_dropdown) {
            $title = CAppUI::tr($title_tr);
        }

        $js_action = html_entity_decode(
            $callback ? $callback : "document.location.href = '$link';",
            ENT_QUOTES
        );
        if ($use_from_dropdown) {
            return "
        <button type=\"button\" class=\"$icon $old_class\" title=\"$title\" onclick=\"$js_action\" $attr>
          " . ($label ? CAppUI::tr($label) : "") . " $label_suf
        </button>
      ";
        }

        $buttons_list = $this->extractVar("buttons_list_$button_group", false, $params, $smarty);
        if (!$buttons_list) {
            $current_id = "1";
        } else {
            $buttons    = explode(",", $buttons_list);
            $current_id = trim($buttons[count($buttons) - 1]) + 1;
        }
        $smarty->_tpl_vars = array_merge(
            $smarty->_tpl_vars,
            [
                "button_" . $current_id . "_" . $button_group . "_label"     => $label,
                "button_" . $current_id . "_" . $button_group . "_label_suf" => $label_suf,
                "button_" . $current_id . "_" . $button_group . "_callback"  => $callback,
                "button_" . $current_id . "_" . $button_group . "_icon"      => $icon,
                "button_" . $current_id . "_" . $button_group . "_link"      => $link,
                "button_" . $current_id . "_" . $button_group . "_title"     => $title,
                "button_" . $current_id . "_" . $button_group . "_attr"      => $attr,
                "button_" . $current_id . "_" . $button_group . "_id"        => $id,
                "buttons_list_$button_group"                                 => "$buttons_list, $current_id",
            ]
        );
    }

    /**
     * Affichage de boutons en drop-down
     *
     * @param array $params Parameters array
     *                      - buttons_list : Buttons identifier list, separated by "," : 1,2,3 (spaces will be ignored)
     *                      - container_class : Main container additional classes
     *                      - container_style : Main container additional style
     *                      - use_anim : Use rotate anim on hover
     *                      - button_icon : Main button icon
     *                      - button_class : Main button additionals classes
     *                      - button_label : Main button label
     *                      - button_X_label : Label for the button with the "X" id
     *                      - button_X_label_suf : Label suffix for the button with the "X" id
     *                      - button_X_callback : Callback for the button with the "X" id (use Javascript Callback)
     *                      - button_X_link : Link for the button with the "X" id (use direct Link)
     *                      - button_X_icon : Icon for the button with the "X" id
     *                      - button_X_old_class : Class to apply for each button on the old themes
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function me_dropdown_button($params, &$smarty)
    {
        $button_group = CMbArray::extract($params, "button_group", "default");
        $buttons_list = $this->extractVar("buttons_list_$button_group", false, $params, $smarty);
        if (!$buttons_list) {
            return "";
        }
        $use_anim        = CMbArray::extract($params, "use_anim", true);
        $container_class = CMbArray::extract($params, "container_class", null);
        $container_style = CMbArray::extract($params, "container_style", null);
        $button_icon     = CMbArray::extract($params, "button_icon", null);
        $button_class    = CMbArray::extract($params, "button_class", null);
        $button_label    = CMbArray::extract($params, "button_label", null);
        $button_id       = CMbArray::extract($params, "button_id", null);

        $button_id    = $button_id ?: uniqid();
        $buttons_list = explode(",", $buttons_list);
        $button_class .= !$use_anim ? ' no-anim' : '';
        if ($button_label) {
            $button_label = CAppUI::tr($button_label);
        }
        $html = "
      <div class='me-dropdown-button $container_class' style='$container_style'>
        <button id='$button_id' type='button' class='$button_icon $button_class'>
          $button_label
        </button>
        <div class='me-dropdown-content'>";
        foreach ($buttons_list as $_button) {
            $_button = trim($_button);
            if ($_button === "") {
                continue;
            }
            $_label     = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_label",
                "",
                $params,
                $smarty
            );
            $_label_suf = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_label_suf",
                "",
                $params,
                $smarty
            );
            $_callback  = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_callback",
                "",
                $params,
                $smarty
            );
            $_link      = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_link",
                "",
                $params,
                $smarty
            );
            $_attr      = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_attr",
                "",
                $params,
                $smarty
            );
            $_icon      = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_icon",
                "",
                $params,
                $smarty
            );
            $_old_class = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_old_class",
                "",
                $params,
                $smarty
            );
            $_id        = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_id",
                "",
                $params,
                $smarty
            );
            $_title     = $this->extractVar(
                "button_" . $_button . "_" . $button_group . "_title",
                "",
                $params,
                $smarty
            );
            $_icon      = str_replace("notext", "", $_icon);

            $html .= $this->me_button(
                [
                    "label"             => $_label,
                    "label_suf"         => $_label_suf,
                    "onclick"           => $_callback,
                    "link"              => $_link,
                    "attr"              => $_attr,
                    "icon"              => "$_icon secondary",
                    "old_class"         => $_old_class,
                    "id"                => $_id,
                    "title"             => $_title,
                    "use_from_dropdown" => true,
                ],
                $smarty
            );
        }
        $html .= "
        </div>
      </div>
      <script>
        MediboardExt.addTogglableElement($('$button_id'));
      </script>
      ";

        return $html;
    }

    /**
     * Affiche une image ou une icone en fonction du thème sélectionné
     *
     * @param array $params
     *                      - src         : Image file
     *                      - width       : Image width
     *                      - height      : Image height
     *                      - style       : Image additional style
     *                      - title       : Image title (with traduction)
     *                      - onmouseover : Image OnMouseOver event callback
     *                      - onclick     : Image onclick event callback
     *                      - alt         : Image Alt (without traduction)
     *                      - alt_tr      : Image Alt (with traduction)
     *                      - attr        : Image additionals attributes
     *                      - class       : Image classes
     *                      - icon        : Icone class name
     *                      - image_url   : Image folder url
     * @param self  $smarty The Smarty object
     *
     * @return string
     */
    function me_img($params, &$smarty)
    {
        return $this->me_img_content($params, null, $smarty);
    }

    /**
     * Affiche une image ou une icone en fonction du thème sélectionné
     *
     * @param array  $params
     *                        - src         : Image file
     *                        - width       : Image width
     *                        - height      : Image height
     *                        - style       : Image additional style
     *                        - title       : Image title (with traduction)
     *                        - onmouseover : Image OnMouseOver event callback
     *                        - onclick     : Image onclick event callback
     *                        - alt         : Image Alt (without traduction)
     *                        - alt_tr      : Image Alt (with traduction)
     *                        - attr        : Image additionals attributes
     *                        - class       : Image classes
     *                        - icon        : Icone class name
     *                        - image_url   : Image folder url
     *                        - auto_tr     : Automatic translation
     * @param string $content Image title (without traduction)
     * @param self   $smarty  The Smarty object
     *
     * @return string
     */
    function me_img_content($params, $content, &$smarty)
    {
        $image_name  = CMbArray::extract($params, "src", null);
        $width       = CMbArray::extract($params, "width", false);
        $height      = CMbArray::extract($params, "height", false);
        $style       = CMbArray::extract($params, "style", false);
        $onmouseover = CMbArray::extract($params, "onmouseover", false);
        $onclick     = CMbArray::extract($params, "onclick", false);
        $title       = CMbArray::extract($params, "title", false);
        $alt         = CMbArray::extract($params, "alt", false);
        $alt_tr      = CMbArray::extract($params, "alt_tr", false);
        $attr        = CMbArray::extract($params, "attr", false);
        $class       = CMbArray::extract($params, "class", false);
        $icon_name   = CMbArray::extract($params, "icon", null);
        $auto_tr     = CMbArray::extract($params, "auto_tr", false);

        if ($icon_name) {
            $icon = "<i class='me-icon $icon_name ";
            $icon .= $class ? "$class'" : "'";
            $icon .= $width ? " width='$width'" : "";
            $icon .= $height ? " height='$height'" : "";
            $icon .= $style ? " style='$style'" : "";
            $icon .= $onmouseover ? " onmouseover=\"$onmouseover\"" : "";
            $icon .= $onclick ? " onclick=\"$onclick\"" : "";
            $icon .= $title ? " title='" . htmlspecialchars(
                    CAppUI::tr($title),
                    ENT_QUOTES
                ) . "'" : ($content ? " title='"
                . ($auto_tr ? htmlspecialchars(CAppUI::tr($content), ENT_QUOTES) : htmlspecialchars(
                    $content,
                    ENT_QUOTES
                )) . "'" : "");
            $icon .= $alt ? " alt='$alt'" : ($alt_tr ? " alt='" . CAppUI::tr($alt_tr) . "'" : "");
            $icon .= $attr ? " $attr" : "";
            $icon .= "></i>";

            return $icon;
        }

        $image_url = "./style/mediboard_ext/images/icons";
        $img       = "<img src='$image_url/$image_name'";
        $img       .= $width ? " width='$width'" : "";
        $img       .= $height ? " height='$height'" : "";
        $img       .= $style ? " style='$style'" : "";
        $img       .= $onmouseover ? " onmouseover=\"$onmouseover\"" : "";
        $img       .= $onclick ? " onclick=\"$onclick\"" : "";
        $img       .= $title ? " title='" . htmlspecialchars(
                CAppUI::tr($title),
                ENT_QUOTES
            ) . "'" : ($content ? " title='"
            . ($auto_tr ? htmlspecialchars(CAppUI::tr($content), ENT_QUOTES) : htmlspecialchars(
                $content,
                ENT_QUOTES
            )) . "'" : "");
        $img       .= $alt ? " alt='$alt'" : ($alt_tr ? " alt='" . CAppUI::tr($alt_tr) . "'" : "");
        $img       .= $attr ? " $attr" : "";
        $img       .= $class ? " class='$class'" : "";
        $img       .= "/>";

        return $img;
    }

    /**
     * Ajoute la surcouche pour les form fields du thème mediboard_ext
     *
     * @param array  $params                             Tableau des parametres
     *                                                   - {string} label         : Label du champ
     *                                                   - {string} label_prefix  : Prefix du label du champ
     *                                                   - {string} label_suffix  : Suffix du label du champ
     *                                                   - {string} id            : ID du container parent de l'input
     *                                                   - {int}    nb_cells      : Taille de l'input dans le tableau
     *                                                   (0 si hors d'un tableau)
     *                                                   - {int}    rowspan       : Attribut rowspan du td contenant le
     *                                                   champ
     *                                                   - {string} class         : Class du groupe
     *                                                   - {string} style_css     : Style initial du groupe
     *                                                   - {string} mb_class      : Classe PHP
     *                                                   - {string} mb_object     : Objet PHP
     *                                                   - {string} mb_field      : Champs de la classe
     *                                                   - {bool}   animated      : Le label de l'input doit il être
     *                                                   animé ?
     *                                                   - {string} null_chars    : Caractères considérés comme null
     *                                                   (séparés par un | )
     *                                                   - {string} title_label   : Attribut title du label
     *                                                   - {string} field_class   : Classe(s) appliquées au champ
     *                                                   - {bool}   layout        : Génère seulement un template de
     *                                                   me_form_field sans modifier le style des inputs
     *                                                   - {bool}   readonly      : Applique le style readonly sur la
     *                                                   border (dotted)
     * @param string $content                            The content to put between
     * @param self   $smarty                             The Smarty object
     *
     * @return string
     * @throws Exception
     */
    public function me_form_field(array $params, $content, &$smarty, &$repeat): string
    {
        if ($repeat) {
            return "";
        }
        $label        = CMbArray::extract($params, 'label', null);
        $label_prefix = CMbArray::extract($params, 'label_prefix', null);
        $label_suffix = CMbArray::extract($params, 'label_suffix', null);
        $id           = CMbArray::extract($params, 'id', null);
        $nb_cells     = intval(CMbArray::extract($params, 'nb_cells', 0));
        $rowspan      = intval(CMbArray::extract($params, 'rowspan', 1));
        $class        = CMbArray::extract($params, 'class', null);
        $style_css    = CMbArray::extract($params, 'style_css', null);
        $mb_class     = CMbArray::extract($params, 'mb_class', null);
        $mb_object    = CMbArray::extract($params, 'mb_object', null);
        $mb_field     = CMbArray::extract($params, 'mb_field', null);
        $animated     = CMbArray::extract($params, 'animated', true);
        $null_chars   = CMbArray::extract($params, 'null_chars', null);
        $title_label  = CMbArray::extract($params, 'title_label', null);
        $field_class  = CMbArray::extract($params, 'field_class', null);
        $layout       = CMbArray::extract($params, 'layout', false);
        $readonly     = CMbArray::extract($params, 'readonly', false);
        $input_id     = uniqid();

        $render = "";

        $field_class .= ($layout) ? " me-form-group-layout" : " me-form-group";

        $field_class .= ($readonly) ? " readonly" : "";

        if (!$animated || $layout) {
            $field_class .= " inanimate";
        }

        $render_label = $this->getFormFieldLabel(
            $mb_object,
            $mb_class,
            $mb_field,
            $label,
            $title_label,
            $style_css,
            $class,
            $nb_cells,
            $label_prefix,
            $label_suffix,
            $rowspan
        );

        if ($nb_cells > 0) {
            $render .= "<td colspan='$nb_cells' rowspan='$rowspan' 
                            style='$style_css' class='$class me-form-group-container'>";
        }
        $render .= "<div id='$input_id' class='$field_class' ";
        $render .= ">";
        $render .= ($layout || $id) ? "<div " . ($id ? "id='$id'" : "")
            . " style='display: flex; flex-grow: 1;align-items: center; flex-wrap: wrap;'>" : "";
        $render .= $content;
        $render .= ($layout || $id) ? "</div>" : "";

        $render .= $render_label;
        if ($animated && !$layout) {
            $render .= "<script>
                        Main.add(function() {
                          var chars_null_string = '$null_chars';
                          var chars_null = chars_null_string.split('|');
                          MediboardExt.MeFormField.prepareFormField($('$input_id'), chars_null);
                        });
                      </script>";
        }
        $render .= "</div>";
        if ($nb_cells > 0) {
            $render .= "</td>";
        }

        return $render;
    }

    /**
     * Ajoute la surcouche pour les boutons radio et checkbox du thème mediboard_ext
     *
     * @param array  $params  Tableau des parametres
     *                        - {string} label         : Label du champ
     *                        - {string} label_prefix  : Prefix du label du champ
     *                        - {string} label_suffix  : Suffix du label du champ
     *                        - {int}    nb_cells      : Taille de l'input dans le tableau (0 si il n'est pas dans un
     *                        tableau)
     *                        - {int}    rowspan       : Attribut rowspan du td contenant le champ
     *                        - {string} class         : Class du groupe
     *                        - {string} style_css     : Style initial du groupe
     *                        - {string} mb_class      : Classe PHP
     *                        - {Object} mb_object     : Objet PHP
     *                        - {string} mb_field      : Champs de la classe
     *                        - {string} title_label   : Attribut title du label
     *                        - {string} var_true      : Attribut 'value' de l'input radio "oui"
     *                        - {string} var_false     : Attribut 'value' de l'input radio "non"
     *                        - {string} field_class   : Classe(s) appliquées au champ
     * @param string $content The content to put between
     * @param self   $smarty  The Smarty object
     *
     * @return string
     * @throws Exception
     */
    function me_form_bool($params, $content, &$smarty)
    {
        $label        = CMbArray::extract($params, 'label', null);
        $label_prefix = CMbArray::extract($params, 'label_prefix', null);
        $label_suffix = CMbArray::extract($params, 'label_suffix', null);
        $nb_cells     = intval(CMbArray::extract($params, 'nb_cells', 0));
        $rowspan      = intval(CMbArray::extract($params, 'rowspan', 1));
        $class        = CMbArray::extract($params, 'class', null);
        $style_css    = CMbArray::extract($params, 'style_css', null);
        $mb_class     = CMbArray::extract($params, 'mb_class', null);
        $mb_object    = CMbArray::extract($params, 'mb_object', null);
        $mb_field     = CMbArray::extract($params, 'mb_field', null);
        $title_label  = CMbArray::extract($params, 'title_label', null);
        $var_true     = CMbArray::extract($params, 'var_true', "1");
        $var_false    = CMbArray::extract($params, 'var_false', "0");
        $field_class  = CMbArray::extract($params, 'field_class', null);
        $input_id     = uniqid();

        $render = "";

        if ($nb_cells > 0) {
            $render .= "<td colspan='$nb_cells' rowspan='$rowspan' style='$style_css' class='$class me-form-group-container'>";
        }
        $render .= "<div id='$input_id' class='me-form-bool $field_class'>";
        $render .= "<input id='" . $input_id . "_input' type='checkbox' class='me-checkbox'>";
        $render .= $this->getFormFieldLabel(
            $mb_object,
            $mb_class,
            $mb_field,
            $label,
            $title_label,
            null,
            null,
            $nb_cells,
            $label_prefix,
            $label_suffix,
            $rowspan
        );

        $render .= "<div id='" . $input_id . "_old_input' class='me-old-input'>";
        $render .= $content;
        $render .= "</div>";
        $render .= "</div>";
        $render .= "<script>
                Main.add(function() {
                  MediboardExt.MeFormField.prepareFormBool('$input_id', '$var_true', '$var_false');
                });
              </script>";

        if ($nb_cells > 0) {
            $render .= "</td>";
        }

        return $render;
    }


    /**
     * Prend en charge la classe scroller et l'adapte au theme mediboard_ext
     *
     * @param array  $params  Tableau de parametres
     * @param string $content Contenu du bloc
     * @param self   $smarty  The Smarty object
     *
     * @return string
     */
    function me_scroller($params, $content, &$smarty)
    {
        return "<div class='me-scroller scroller'><div>$content</div></div>";
    }

    /**
     * Extrait une variable depuis les variables Smarty courantes.
     * Si la variable n'y existe pas, elle est alors extraite du tableau de paramètres.
     *
     * @param string         $var_lib       Nom de la variabel
     * @param string|boolean $default_value Valeur par défaut
     * @param array          $params        Tableau de parametres
     * @param self           $smarty        The Smarty object
     *
     * @return mixed
     * @todo   : Prévoir un ref pour réduire les effets de bord
     *         Effets possibles : Les variables définies dans des mb_includes peuvent interférer
     *         Les variables sont extraites, donc inutilisables par la suite
     *
     */
    private function extractVar($var_lib, $default_value, $params, &$smarty)
    {
        $var = CMbArray::extract($smarty->_tpl_vars, $var_lib, $default_value);
        if ($var === $default_value) {
            $var = CMbArray::extract($params, $var_lib, $default_value);
        }

        return $var;
    }

    /**
     * Retourne le bon label du me_form_field en fonction des valeur renseignées
     *
     * @param Object $mb_object    Objet PHP
     * @param string $mb_class     Classe PHP
     * @param string $mb_field     Nom du champ
     * @param string $label        Clé de traduction
     * @param string $title_label  Attribut titlte du label
     * @param string $style_css    Style css supplémentaire
     * @param string $class        Classes HTML supplémentaires
     * @param int    $nb_cells     Taille de l'input dans le tableau (0 si il n'est pas dans un tableau)
     * @param string $label_prefix Prefix du label du champ
     * @param string $label_suffix Suffix du label du champ
     * @param string $rowspan      Attribut rowspan du td contenant le champ
     *
     * @return string
     * @throws Exception
     * @todo: À garder seulement pour la v1 de Mediboard Design
     *
     */
    protected function getFormFieldLabel(
        $mb_object,
        $mb_class,
        $mb_field,
        $label,
        $title_label,
        $style_css,
        $class,
        $nb_cells,
        $label_prefix,
        $label_suffix,
        $rowspan
    ) {
        $render = "";

        $render .= "$label_prefix";
        if ($label) {
            $render .= "<label ";
            if ($title_label) {
                $render .= "title='" . htmlentities(CAppUI::tr($title_label), ENT_QUOTES) . "' ";
            }
            $render .= ">
                  $label_prefix" . CAppUI::tr($label) . "$label_suffix
                </label>";
        } elseif ($mb_class || $mb_object) {
            $params_mb_label = ["field" => $mb_field];
            if ($mb_class) {
                $params_mb_label["class"] = $mb_class;
            } else {
                $params_mb_label["object"] = $mb_object;
            }
            $render .= $this->mb_label($params_mb_label, $smarty);
            $render .= $label_suffix ? "<span style='margin-left: 8px'>$label_suffix</span>" : "";
        }

        return $render;
    }
}
