<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbArray;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;

/**
 * Contenu HTML
 */
class CContentHTML extends CMbObject
{
    // DB Table key
    /** @var int */
    public $content_id;

    // DB Fields
    /** @var string */
    public $content;
    /** @var string */
    public $last_modified;

    // Form fields
    /** @var array */
    public $_list_classes;
    /** @var string */
    public $_image_status;
    /** @var array */
    public $_images = [];

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'content_html';
        $spec->key   = 'content_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                  = parent::getProps();
        $props["_list_classes"] = "enum list|" . implode("|", array_keys(CCompteRendu::getTemplatedClasses()));
        $props["content"]       = "html helped|_list_classes";
        $props["last_modified"] = "dateTime";
        $props["_image_status"] = "bool";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function check(): ?string
    {
        if ($this->fieldModified("content", "")) {
            return "CContentHTML-failed-emptytext";
        }

        return parent::check();
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        if ($this->fieldModified("content") || !$this->last_modified) {
            $this->last_modified = CMbDT::dateTime();
        }

        // Suppression des caractères de contrôle
        $this->content = preg_replace('/[\x00-\x1F\x7F]/', '', $this->content);

        return parent::store();
    }

    public function updatePlainFields(): void
    {
        parent::updatePlainFields();
        // Ne pas supprimer cette expression régulière, elle contient un caractère de contrôle entre les slashes
        $this->content = preg_replace('//', '', $this->content);
        // Ne pas décommenter, cette purification supprime du contenu et des balises inoffensives
        // ou encore des id d'éléments (nécessaires au bon fonctionnement des documents)
        //$this->content = CMbString::purifyHTML($this->content);
    }

    /**
     * Retourne le statut des images dans la source html
     *
     * @return boolean
     */
    public function getImageStatus(): bool
    {
        preg_match_all("/(<img.*editor.*>)|(<img.*http.*>)|(<img.*base64.*>)/U", $this->content, $matches);

        foreach ($matches as &$_matches) {
            CMbArray::removeValue("", $_matches);
        }

        // Images dans le dossier editor
        if (isset($matches[1]) && count($matches[1])) {
            $this->_images["local"] = count($matches[1]);
        }

        // Images en url distantes
        if (isset($matches[2]) && count($matches[2])) {
            $this->_images["distant"] = count($matches[2]);
        }

        // Images en base64
        if (isset($matches[3]) && count($matches[3])) {
            $this->_images["base64"] = count($matches[3]);
        }

        return $this->_image_status = !array_sum($this->_images);
    }
}
