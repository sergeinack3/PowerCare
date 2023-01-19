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
        $extension->description = "Extension de connectivit� aux bases de donn�es";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Acc�s � la base de donn�e de principale Mediboard";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "PDO_ODBC";
        $extension->description = "Pilote ODBC pour PDO";
        $extension->reasons[]   = "Interop�rabilit� avec des syst�mes tiers";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "GD";
        $extension->description = "Extension de manipulation d'image.";
        $extension->mandatory   = true;
        $extension->reasons[]   = "GD version 2 est recommand�e car elle permet un meilleur rendu";
        $extension->reasons[]   = "Module de statistiques graphiques";
        $extension->reasons[]   = "Fonction d'audiogrammes";
        $extension->reasons[]   = "Affichage des aper�us d'images";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "MBString";
        $extension->description = "Extension de gestion des cha�nes de caract�res multi-octets";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Internationalisation de Mediboard";
        $extension->reasons[]   = "Interop�rabilit� Unicode";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "ZLib";
        $extension->description = "Extension de compression au format GNU ZIP (gz)";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Installation de Mediboard";
        $extension->reasons[]   = "Accel�ration substancielle de l'application via une communication web compress�e";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "Zip";
        $extension->description = "Extension de compression au format zip";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Installation de Mediboard";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "JSON";
        $extension->description = "Extension de manipulation de donn�es au format JSON. Inclus par d�faut avec PHP 5.2+";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Passage de donn�es de PHP vers Javascript.";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "DOM";
        $extension->description = "Extension de manipulation de fichier XML avec l'API DOM";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Import de base de donn�es m�decin";
        $extension->reasons[]   = "Interop�rabilit� HPRIM XML, notamment pour le PMSI";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "CURL";
        $extension->description =
            "Extension permettant de communiquer avec des serveurs distants, gr�ce � de nombreux protocoles";
        $extension->mandatory   = true;
        $extension->reasons[]   = "Connexion au site web du Conseil National l'Ordre des M�decins";
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
        $extension->description = "Extension d'acc�s aux serveur FTP";
        $extension->reasons[]   = "D�p�t et lecture fichiers distants pour l'interop�rabilit�";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "BCMath";
        $extension->description = "Extension de calculs sur des nombres de pr�cision arbitraire";
        $extension->reasons[]   = "Validation des codes INSEE et ADELI";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "APCu";
        $extension->mandatory   = true;
        $extension->description = "Extension d'optimsation d'OPCODE et de m�moire partag�e";
        $extension->reasons[]   = "Acc�l�ration globale du syst�me";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "Zend OPcache";
        $extension->mandatory   = true;
        $extension->description = "Am�liore les performances de PHP en stockant le bytecode des scripts pr�-compil�s en m�moire";
        $extension->reasons[]   = "Acc�l�ration globale du syst�me";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "LDAP";
        $extension->description = "Protocole l�ger d'acc�s aux annuaires";
        $extension->reasons[]   = "Protocole utilis� pour acc�der aux serveurs de dossiers.";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "GnuPG";
        $extension->description = "GNU Privacy Guard (GPG ou GnuPG)";
        $extension->reasons[]   = "Transmettre des messages sign�s et/ou chiffr�s";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "OpenSSL";
        $extension->description = "Chiffrage des donn�es";
        $extension->reasons[]   = "Interop�rabilit� avec syst�mes tiers.";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "Imagick";
        $extension->description = "Manipulation d'images";
        $extension->reasons[]   = "Cr�ation de miniatures";
        $extensions[]           = $extension;

        $extension              = new PHPExtension();
        $extension->name        = "XSL";
        $extension->description = "Impl�mentation du standard XSL";
        $extension->reasons[]   = "Permet d'utiliser les transformations XSLT";
        $extensions[]           = $extension;

        return $extensions;
    }
}
