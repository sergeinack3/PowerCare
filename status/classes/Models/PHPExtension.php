<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Models;

/**
 * PHP extension prerequisite
 */
class PHPExtension extends Prerequisite
{
    /**
     * @inheritdoc
     */
    function check($strict = true)
    {
        if ($strict) {
            return extension_loaded(strtolower($this->name));
        }

        return !$this->mandatory || extension_loaded(strtolower($this->name));
    }

    /**
     * @see parent::getAll()
     */
    function getAll()
    {
        $extensions = [];

        $extension              = new PHPExtension();
        $extension->name        = "PDO";
        $extension->description = "Extension de connectivité aux bases de données";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Accès à la base de donnée de principale Mediboard";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "PDO_ODBC";
        $extension->description = "Pilote ODBC pour PDO";
        $extension->reasons[]   = "Interopérabilité avec des systèmes tiers";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "GD";
        $extension->description = "Extension de manipulation d'image.";
        $extension->mandatory   = true;
        $extension->reasons[]   = "GD version 2 est recommandée car elle permet un meilleur rendu";
        $extension->reasons[]   = "Module de statistiques graphiques";
        $extension->reasons[]   = "Fonction d'audiogrammes";
        $extension->reasons[]   = "Affichage des aperçus d'images";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "MBString";
        $extension->description = "Extension de gestion des chaînes de caractères multi-octets";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Internationalisation de Mediboard";
        $extension->reasons[]   = "Interopérabilité Unicode";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "ZLib";
        $extension->description = "Extension de compression au format GNU ZIP (gz)";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Installation de Mediboard";
        $extension->reasons[]   = "Accelération substancielle de l'application via une communication web compressée";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "Zip";
        $extension->description = "Extension de compression au format zip";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Installation de Mediboard";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "JSON";
        $extension->description = "Extension de manipulation de données au format JSON. Inclus par défaut avec PHP 5.2+";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Passage de données de PHP vers Javascript.";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "DOM";
        $extension->description = "Extension de manipulation de fichier XML avec l'API DOM";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Import de base de données médecin";
        $extension->reasons[]   = "Interopérabilité HPRIM XML, notamment pour le PMSI";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "CURL";
        $extension->description =
            "Extension permettant de communiquer avec des serveurs distants, grâce à de nombreux protocoles";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Connexion au site web du Conseil National l'Ordre des Médecins";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "SOAP";
        $extension->mandatory   = true;
        $extension->description = "Extension permettant d'effectuer des requetes";
        $extension->reasons[]   = "Requetes vers des services web et exposition de service web";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "FTP";
        $extension->mandatory   = true;
        $extension->description = "Extension d'accès aux serveur FTP";
        $extension->reasons[]   = "Dépôt et lecture fichiers distants pour l'interopérabilité";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "BCMath";
        $extension->description = "Extension de calculs sur des nombres de précision arbitraire";
        $extension->reasons[]   = "Validation des codes INSEE et ADELI";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "APCu";
        $extension->mandatory   = true;
        $extension->description = "Extension d'optimsation d'OPCODE et de mémoire partagée";
        $extension->reasons[]   = "Accélération globale du système";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "Zend OPcache";
        $extension->mandatory   = true;
        $extension->description = "Améliore les performances de PHP en stockant le bytecode des scripts pré-compilés en mémoire";
        $extension->reasons[]   = "Accélération globale du système";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "LDAP";
        $extension->description = "Protocole léger d'accès aux annuaires";
        $extension->reasons[]   = "Protocole utilisé pour accéder aux serveurs de dossiers.";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "GnuPG";
        $extension->description = "GNU Privacy Guard (GPG ou GnuPG)";
        $extension->reasons[]   = "Transmettre des messages signés et/ou chiffrés";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "OpenSSL";
        $extension->description = "Chiffrage des données";
        $extension->reasons[]   = "Interopérabilité avec systèmes tiers.";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "Imagick";
        $extension->description = "Manipulation d'images";
        $extension->reasons[]   = "Création de miniatures";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "XSL";
        $extension->description = "Implémentation du standard XSL";
        $extension->reasons[]   = "Permet d'utiliser les transformations XSLT";
        $extensions[]           = $extension;

        return $extensions;
    }
}
