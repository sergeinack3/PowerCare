<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Libraries;

use Ox\Core\CMbException;
use Ox\Core\CMbPath;

/**
 * Vendor library
 */
class CLibrary
{
    /** @var self[] */
    static $all = [];

    /** @var array The URL of the different licences */
    public static $licences = [
        'agpl3'  => ['name' => 'GNU AGPL v3', 'link' => 'https://www.gnu.org/licenses/agpl-3.0-standalone.html'],
        'apache' => ['name' => 'Apache', 'link' => 'http://www.apache.org/licenses/LICENSE-2.0.html'],
        'bsd'    => ['name' => 'New BSD', 'link' => 'https://opensource.org/licenses/BSD-3-Clause'],
        'gpl2'   => ['name' => 'GPL v2', 'link' => 'https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html'],
        'gpl3'   => ['name' => 'GPL v3', 'link' => 'https://www.gnu.org/licenses/gpl-3.0-standalone.html'],
        'lgpl2'  => [
            'name' => 'LGPL v2.1',
            'link' => 'https://www.gnu.org/licenses/old-licenses/lgpl-2.1-standalone.html',
        ],
        'lgpl3'  => ['name' => 'LGPL v3', 'link' => 'https://www.gnu.org/licenses/lgpl-3.0-standalone.html'],
        'mit'    => ['name' => 'MIT', 'link' => 'https://mit-license.org/'],
    ];

    public $name           = "";
    public $url            = "";
    public $fileName       = "";
    public $extraDir       = "";
    public $description    = "";
    public $nbFiles        = 0;
    public $sourceDir      = null;
    public $targetDir      = null;
    public $versionFile    = "";
    public $versionString  = "";
    public $copyrightOwner = '';
    public $licence        = '';

    /** @var CLibraryPatch[] */
    public $patches = [];

    /**
     * Return application root path
     *
     * @return string
     */
    static function getRootPath()
    {
        return dirname(__DIR__, 3) . '/';
    }

    /**
     * Remove installed libraries
     *
     * @param string $libSel Library to clear
     *
     * @return void
     */
    function clearLibraries($libSel = null)
    {
        $mbpath  = $this->getRootPath();
        $libsDir = $mbpath . "lib";

        /// Clear out all libraries
        if (!$libSel) {
            foreach (glob("$libsDir/*") as $libDir) {
                CMbPath::remove($libDir, false);
            }

            return;
        }

        // Clear out selected lib
        $library = self::$all[$libSel];
        if ($targetDir = $library->targetDir) {
            @CMbPath::remove("$libsDir/$targetDir", false);
        }
    }

    /**
     * @return string
     * @throws CMbException
     * @uses by composer script post install
     */
    public static function installAll(): string
    {
        self::init();

        if (self::checkAll()) {
            return 'Front libraries (js) are up to date';
        }

        $time_start = microtime(true);
        $count      = 0;

        foreach (self::$all as $library) {
            if ($library->isInstalled() && $library->getUpdateState()) {
                continue;
            }
            $count++;

            $library->clearLibraries($library->name);
            $library->install();
            if (!$library->apply()) {
                throw new CMbException("Unable to apply library {$library->name}");
            }


            if (count($library->patches)) {
                foreach ($library->patches as $patch) {
                    if (!$patch->apply()) {
                        throw new CMbException("Unable to apply patch for library {$library->name}");
                    }
                }
            }
        }
        $time = round(microtime(true) - $time_start, 3);

        return "Install {$count} front libraries (js) during {$time} sec";
    }

    /**
     * Get update status of the libraries
     *
     * @return bool|null True if installed and up to date, null otherwise
     */
    function getUpdateState()
    {
        $mbpath = $this->getRootPath();
        $dir    = $mbpath . "lib/$this->targetDir";

        if ($this->versionFile && $this->versionString) {
            return (file_exists("$dir/$this->versionFile") &&
                strpos(file_get_contents("$dir/$this->versionFile"), $this->versionString) !== false);
        }

        return null;
    }

    /**
     * Is the library installed
     *
     * @return bool
     */
    function isInstalled()
    {
        $mbpath = $this->getRootPath();

        return is_dir($mbpath . "lib/$this->targetDir");
    }

    /**
     * Count installed libraries
     *
     * @return int
     */
    static function countLibraries()
    {
        $mbpath = self::getRootPath();

        return count(glob($mbpath . 'lib/*'));
    }

    /**
     * Install the library
     *
     * @return int The number of extracted files
     */
    function install()
    {
        $mbpath   = $this->getRootPath();
        $pkgsDir  = $mbpath . "libpkg";
        $libsDir  = $mbpath . "lib";
        $filePath = "$pkgsDir/$this->fileName";

        // For libraries archive not contained in directory
        if ($this->extraDir) {
            $libsDir .= "/$this->extraDir";
        }

        return CMbPath::extract($filePath, $libsDir);
    }

    /**
     * Apply the library patch
     *
     * @return bool
     */
    function apply()
    {
        $mbpath    = $this->getRootPath();
        $libsDir   = $mbpath . "lib";
        $sourceDir = "$libsDir/$this->sourceDir";
        $targetDir = "$libsDir/$this->targetDir";
        assert(is_dir($sourceDir));

        return rename($sourceDir, $targetDir);
    }

    /**
     * Return the licence infos
     *
     * @return array Empty array if unknown licence, or array containing the name and a link
     */
    public function getLicence()
    {
        $licence = [];

        if (array_key_exists($this->licence, self::$licences)) {
            $licence = self::$licences[$this->licence];
        }

        return $licence;
    }

    /**
     * Check update status of all the libraries
     *
     * @param bool $strict Use strict checking, not used
     *
     * @return bool
     */
    static function checkAll($strict = true)
    {
        foreach (CLibrary::$all as $library) {
            if (!$library->getUpdateState()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all target archive directories
     *
     * @return array
     */
    static function getLibraryDirectories()
    {
        $directories = [];

        foreach (CLibrary::$all as $library) {
            $directories[] = $library->targetDir;
        }

        return $directories;
    }

    /**
     * Get old libraries directories
     *
     * @return array
     */
    static function getOldLibraries()
    {
        $dir = self::getRootPath() . "lib/";

        $files     = glob($dir . "*");
        $libraries = self::getLibraryDirectories();

        $old = [];

        foreach ($files as $_file) {
            $name = basename($_file);

            if (!in_array($name, $libraries)) {
                $old[] = $name;
            }
        }

        return $old;
    }

    /**
     * Cleanup old directories
     *
     * @return int
     */
    static function cleanUpDirectories()
    {
        $dir = self::getRootPath() . "lib/";

        $old = self::getOldLibraries();

        $count = 0;

        foreach ($old as $_old) {
            CMbPath::remove($dir . $_old, false);
            $count++;
        }

        return $count;
    }

    static function init()
    {
        $library                 = new CLibrary;
        $library->name           = "Scriptaculous";
        $library->url            = "http://script.aculo.us/";
        $library->fileName       = "scriptaculous-js-1.9.0.zip";
        $library->description    = "Composant Javascript d'effets spéciaux, accompagné du framework prototype.js";
        $library->sourceDir      = "scriptaculous-js-1.9.0";
        $library->targetDir      = "scriptaculous";
        $library->versionFile    = "lib/prototype.js";
        $library->versionString  = "console.error(e.stack";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2005-2010 Thomas Fuchs';

        $patch              = new CLibraryPatch;
        $patch->dirName     = "scriptaculous";
        $patch->sourceName  = "src/scriptaculous.js";
        $library->patches[] = $patch;

        CLibrary::$all[$library->name] = $library;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "scriptaculous";
        $patch->sourceName  = "lib/prototype.js";
        $library->patches[] = $patch;

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "DatePicker";
        $library->url            = "http://home.jongsma.org/software/js/datepicker";
        $library->fileName       = "datepicker.tar.gz";
        $library->description    = "Composant Javascript de sélecteur de date/heure";
        $library->sourceDir      = "datepicker";
        $library->targetDir      = "datepicker";
        $library->versionFile    = "datepicker.js";
        $library->versionString  = "Test if icon is needed";
        $library->licence        = 'gpl3';
        $library->copyrightOwner = 'Jeremy Jongsma';

        $patch              = new CLibraryPatch;
        $patch->dirName     = "datepicker";
        $patch->sourceName  = "datepicker-locale-de_DE.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "datepicker";
        $patch->sourceName  = "datepicker-locale-fr_FR.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "CKEditor";
        $library->url            = "http://ckeditor.com/";
        $library->fileName       = "ckeditor_4.5.8.zip";
        $library->description    = "Composant Javascript d'édition de texte au format HTML";
        $library->sourceDir      = "ckeditor";
        $library->targetDir      = "ckeditor";
        $library->versionFile    = "plugins/forms/dialogs/radio.js";
        $library->versionString  = "function(c)";
        $library->licence        = 'lgpl3';
        $library->copyrightOwner = '2003-2017, CKSource - Frederico Knabben';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "Livepipe UI";
        $library->url            = "https://github.com/eastridge/livepipe-ui";
        $library->fileName       = "livepipe.tar.gz";
        $library->description    = "High Quality Controls & Widgets for Prototype";
        $library->extraDir       = "livepipe";
        $library->sourceDir      = "livepipe";
        $library->targetDir      = "livepipe";
        $library->versionFile    = "window.js";
        $library->versionString  = "'center', 'center_once'";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2008 PersonalGrid Corporation';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "Flotr plotting library";
        $library->url            = "https://github.com/PhenX/flotr";
        $library->fileName       = "flotr.zip";
        $library->description    = "Création de graphiques en JS";
        $library->sourceDir      = "flotr";
        $library->targetDir      = "flotr";
        $library->versionFile    = "flotr.js";
        $library->versionString  = '@patch download';
        $library->licence        = 'mit';
        $library->copyrightOwner = '2008 Bas Wenneker';

        CLibrary::$all[$library->name] = $library;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flotr";
        $patch->sourceName  = "flotr.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $library                 = new CLibrary;
        $library->name           = "Flot plotting library";
        $library->url            = "http://www.flotcharts.org/";
        $library->fileName       = "flot-0.8.1.zip";
        $library->description    = "Création de graphiques en JS";
        $library->sourceDir      = "flot";
        $library->targetDir      = "flot";
        $library->versionFile    = "jquery.flot.touch.js";
        $library->versionString  = 'jquery.flot.touch 3';
        $library->licence        = 'mit';
        $library->copyrightOwner = '2007-2014 IOLA and Ole Laursen';

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.flot.resize.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.flot.dashes.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.flot.curvedlines.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.flot.navigate.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.event.drag.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.flot.touch.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        $patch              = new CLibraryPatch;
        $patch->dirName     = "flot";
        $patch->sourceName  = "jquery.flot.pyramid.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "Prism.js";
        $library->url            = "http://prismjs.com";
        $library->fileName       = "prismjs.zip";
        $library->description    = "Prism is a lightweight, extensible syntax highlighter";
        $library->sourceDir      = "prismjs";
        $library->targetDir      = "prismjs";
        $library->versionFile    = "prism.js";
        $library->versionString  = "PrismJS 1.11.0";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2012 Lea Verou';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "jsExpressionEval";
        $library->url            = "https://github.com/silentmatt/js-expression-eval";
        $library->fileName       = "jsExpressionEval.tar.gz";
        $library->description    = "A JavaScript math expression evaluator";
        $library->sourceDir      = "jsExpressionEval";
        $library->targetDir      = "jsExpressionEval";
        $library->versionFile    = "parser.js";
        $library->versionString  = "\"if\": condition,";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2015 Matthew Crumley';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "Store.js";
        $library->url            = "https://github.com/marcuswestin/store.js";
        $library->fileName       = "store.js-master.zip";
        $library->description    = "localStorage wrapper for all browsers";
        $library->sourceDir      = "store.js-master";
        $library->targetDir      = "store.js";
        $library->versionFile    = "Changelog";
        $library->versionString  = 'v1.3.9';
        $library->licence        = 'mit';
        $library->copyrightOwner = '2010-2017 Marcus Westin';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "Fabricjs library";
        $library->url            = "http://fabricjs.com/";
        $library->fileName       = "fabricjs.zip";
        $library->description    = "Canvas javascript library";
        $library->sourceDir      = "fabricjs";
        $library->targetDir      = "fabricjs";
        $library->versionFile    = "fabric.min.js";
        $library->versionString  = 'version:"1.6.3"';
        $library->licence        = 'mit';
        $library->copyrightOwner = '2008-2015 Printio (Juriy Zaytsev, Maxim Chernyak)';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "d3";
        $library->url            = "https://github.com/mbostock/d3";
        $library->fileName       = "d3-3.5.16.tar.gz";
        $library->description    = "A JavaScript visualization library for HTML and SVG";
        $library->sourceDir      = "d3-3.5.16";
        $library->targetDir      = "d3-3.5.16";
        $library->versionFile    = "d3.min.js";
        $library->versionString  = "3.5.16";
        $library->licence        = 'bsd';
        $library->copyrightOwner = '2010-2017 Mike Bostock';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary;
        $library->name           = "dagre-d3";
        $library->url            = "https://github.com/cpettitt/dagre-d3";
        $library->fileName       = "dagre-d3-0.3.2.tar.gz";
        $library->description    = "A D3-based renderer for Dagre";
        $library->sourceDir      = "dagre-d3-0.3.2";
        $library->targetDir      = "dagre-d3-0.3.2";
        $library->versionFile    = "dagre-d3.js";
        $library->versionString  = "0.3.2";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2013 Chris Pettit';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "visjs";
        $library->url            = "http://visjs.org/";
        $library->fileName       = "vis.zip";
        $library->description    = "A visual interaction system";
        $library->sourceDir      = "vis";
        $library->targetDir      = "visjs";
        $library->versionFile    = "vis.js";
        $library->versionString  = "4.21.6";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2010-2017 Almende B.V. ';

        $patch              = new CLibraryPatch;
        $patch->dirName     = "visjs";
        $patch->sourceName  = "vis.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "favico.js";
        $library->url            = "http://lab.ejci.net/favico.js/";
        $library->fileName       = "favico.tar.gz";
        $library->description    = "Make a use of your favicon with badges, images or videos";
        $library->sourceDir      = "favico";
        $library->targetDir      = "favico";
        $library->versionFile    = "readme.md";
        $library->versionString  = "Version 0.3.6";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2011-2016 Miroslav Magda';

        $patch              = new CLibraryPatch();
        $patch->dirName     = "favico";
        $patch->sourceName  = "favico.js";
        $patch->targetDir   = "";
        $library->patches[] = $patch;

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "requirejs";
        $library->url            = "http://requirejs.org/";
        $library->fileName       = "requirejs.zip";
        $library->description    = "JavaScript file and module loader";
        $library->sourceDir      = "requirejs";
        $library->targetDir      = "requirejs";
        $library->versionFile    = "require.js";
        $library->versionString  = "RequireJS 2.1.16";
        $library->licence        = 'mit';
        $library->copyrightOwner = 'jQuery Foundation';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "PapaParse";
        $library->url            = "https://github.com/mholt/PapaParse";
        $library->fileName       = "PapaParse-4.1.2.zip";
        $library->description    = "Bibliothèque d'analyse syntaxique de fichiers CSV";
        $library->sourceDir      = "PapaParse-4.1.2";
        $library->targetDir      = "PapaParse";
        $library->versionFile    = "papaparse.min.js";
        $library->versionString  = "v4.1.2";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2015 Matthew Holt';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "dc.js";
        $library->url            = "https://github.com/dc-js/dc.js";
        $library->fileName       = "dc.js-2.0.0-beta.31.tar.gz";
        $library->description    = "Multi-Dimensional charting built to work natively with crossfilter rendered with d3.js";
        $library->sourceDir      = "dc.js-2.0.0-beta.31";
        $library->targetDir      = "dc";
        $library->versionFile    = "dc.min.js";
        $library->versionString  = "2.0.0-beta.31";
        $library->licence        = 'apache';
        $library->copyrightOwner = '2012-2016 Nick Zhu & the dc.js Developers';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Crossfilter";
        $library->url            = "https://github.com/crossfilter/crossfilter";
        $library->fileName       = "crossfilter-1.3.13.tar.gz";
        $library->description    = "Fast n-dimensional filtering and grouping of records";
        $library->sourceDir      = "crossfilter-1.3.13";
        $library->targetDir      = "crossfilter";
        $library->versionFile    = "crossfilter.min.js";
        $library->versionString  = "1.3.13";
        $library->licence        = 'apache';
        $library->copyrightOwner = '2012-2016 Square, Inc';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "paste.js";
        $library->url            = "https://github.com/layerssss/paste.js";
        $library->fileName       = "paste.js-0.0.21.zip";
        $library->description    = "Read image/text data from clipboard (cross-browser)";
        $library->sourceDir      = "paste.js-0.0.21";
        $library->targetDir      = "paste.js";
        $library->versionFile    = "paste.js";
        $library->versionString  = "Generated by CoffeeScript 1.10.0";
        $library->licence        = 'mit';
        $library->copyrightOwner = '2015 Michael Yin';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "spectrum.js";
        $library->url            = "http://bgrins.github.io/spectrum/";
        $library->fileName       = "spectrum-1.8.0.zip";
        $library->description    = "The No Hassle jQuery Colorpicker";
        $library->sourceDir      = "spectrum-1.8.0";
        $library->targetDir      = "spectrum";
        $library->versionFile    = "spectrum.js";
        $library->versionString  = "// Spectrum Colorpicker v1.8.0";
        $library->licence        = 'mit';
        $library->copyrightOwner = 'Brian Grinstead';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Leaflet";
        $library->url            = "http://leafletjs.com";
        $library->fileName       = "leaflet.zip";
        $library->description    = "a JS library for interactive maps";
        $library->sourceDir      = "leaflet";
        $library->targetDir      = "leaflet";
        $library->versionFile    = "leaflet.js";
        $library->versionString  = 'Leaflet 1.3.1';
        $library->licence        = 'TO SET';
        $library->copyrightOwner = '(c) 2010-2017 Vladimir Agafonkin, (c) 2010-2011 CloudMade';

        CLibrary::$all[$library->name] = $library;


        $library                 = new CLibrary();
        $library->name           = "Leaflet.AwesomeMarkers";
        $library->url            = "https://github.com/lvoogdt";
        $library->fileName       = "leaflet-awesome-markers-2.0.zip";
        $library->description    = "a plugin that adds colorful iconic markers for Leaflet, based on the Font Awesome icons";
        $library->sourceDir      = "leaflet-awesome-markers-2.0";
        $library->targetDir      = "leaflet-awesome-markers-2.0";
        $library->versionFile    = "leaflet.awesome-markers.js";
        $library->versionString  = "L.AwesomeMarkers.version = '2.0.1';";
        $library->licence        = 'TO SET';
        $library->copyrightOwner = '(c) 2012-2013, Lennard Voogdt';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Leaflet.curve";
        $library->url            = "https://github.com/elfalem/Leaflet.curve";
        $library->fileName       = "leaflet.curve-gh-pages.zip";
        $library->description    = "a plugin for Leaflet mapping library";
        $library->sourceDir      = "Leaflet.curve-gh-pages";
        $library->targetDir      = "Leaflet.curve-gh-pages";
        $library->versionFile    = "leaflet.curve.js";
        $library->versionString  = "Leaflet.curve v0.1.0";
        $library->licence        = 'TO SET';
        $library->copyrightOwner = '(c) elfalem 2015';

        CLibrary::$all[$library->name] = $library;


        $library                 = new CLibrary();
        $library->name           = "Leaflet.markercluster";
        $library->url            = "https://github.com/Leaflet/Leaflet.markercluster";
        $library->fileName       = "Leaflet.markercluster-1.3.0.zip";
        $library->description    = "Provides Beautiful Animated Marker Clustering functionality for Leaflet, a JS library for interactive maps";
        $library->sourceDir      = "Leaflet.markercluster-1.3.0";
        $library->targetDir      = "Leaflet.markercluster-1.3.0";
        $library->versionFile    = "leaflet.markercluster-src.js";
        $library->versionString  = "Leaflet.markercluster 1.3.0";
        $library->licence        = 'TO SET';
        $library->copyrightOwner = '(c) 2012-2017, Dave Leaver, smartrak';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Leaflet.TileLayer.HERE";
        $library->url            = "https://gitlab.com/IvanSanchez/Leaflet.TileLayer.HERE";
        $library->fileName       = "Leaflet.TileLayer.HERE.zip";
        $library->description    = "Displays map tiles from HERE maps in your Leaflet map.";
        $library->sourceDir      = "Leaflet.TileLayer.HERE";
        $library->targetDir      = "Leaflet.TileLayer.HERE";
        $library->versionFile    = "leaflet-tilelayer-here.js";
        $library->versionString  = "class TileLayer.HERE";
        $library->licence        = 'THE BEER-WARE LICENSE';
        $library->copyrightOwner = '(c) 2017, Iván Sánchez Ortega';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Leaflet.TextPath";
        $library->url            = "https://github.com/makinacorpus/Leaflet.TextPath";
        $library->fileName       = "leaflet.TextPath.zip";
        $library->description    = "Show text along Polyline with Leaflet";
        $library->sourceDir      = "leaflet.TextPath";
        $library->targetDir      = "leaflet.TextPath";
        $library->versionFile    = "leaflet.textpath.js";
        $library->versionString  = "Leaflet.TextPath - Shows text along a polyline";
        $library->licence        = 'MIT';
        $library->copyrightOwner = '(c) 2018, Makina Corpus';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Jitsi Meet";
        $library->url            = "https://meet.jit.si/";
        $library->fileName       = "lib-jitsi-meet.min.zip";
        $library->description    = "Outil de conférence javascript";
        $library->sourceDir      = "lib-jitsi-meet";
        $library->targetDir      = "lib-jitsi-meet";
        $library->versionFile    = "lib-jitsi-meet.min.js";
        $library->versionString  = "@VERSION@";
        $library->licence        = 'apache';
        $library->copyrightOwner = '(c) 2021, 8x8';

        CLibrary::$all[$library->name] = $library;

        $library                 = new CLibrary();
        $library->name           = "Jquery";
        $library->url            = "https://jquery.com/";
        $library->fileName       = "jquery-3.6.0.min.zip";
        $library->description    = "Javascript framework";
        $library->sourceDir      = "jquery-3.6.0";
        $library->targetDir      = "jquery-3.6.0";
        $library->versionFile    = "jquery-3.6.0.min.js";
        $library->versionString  = "v3.6.0";
        $library->licence        = 'MIT';
        $library->copyrightOwner = '(c) 2021 OpenJS Foundation and jQuery contributors';

        CLibrary::$all[$library->name] = $library;

        return CLibrary::$all;
    }
}
