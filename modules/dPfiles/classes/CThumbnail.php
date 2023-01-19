<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Imagine\Gd;
use Imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Imagick;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\Module\CModule;
use Ox\Import\Osoft\COsoftDossier;
use Ox\Import\Osoft\COsoftHistorique;
use Ox\Interop\Cda\CCdaTools;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use RuntimeException;
use Throwable;

/**
 * Image manipulation class using Imagine with Imagick or GD
 */
abstract class CThumbnail implements IShortNameAutoloadable
{
    public const IMG_NOT_FOUND     = 'images/pictures/notfound.png';
    public const IMG_ACCESS_DENIED = 'images/pictures/accessdenied.png';
    public const IMG_DRAFT         = 'modules/drawing/images/draft.png';
    public const IMG_DEFAULT       = 'images/pictures/medifile.png';

    public const PROFILE_SMALL  = 'small';
    public const PROFILE_MEDIUM = 'medium';
    public const PROFILE_LARGE  = 'large';

    public const DISPLAY_WIDTH  = 'display_w';
    public const DISPLAY_HEIGHT = 'display_h';

    public const PROFILES = [
        self::PROFILE_SMALL  => [
            'w'                  => 120,
            'h'                  => 120,
            self::DISPLAY_WIDTH  => 60,
            self::DISPLAY_HEIGHT => 60,
            'dpi'                => 25,
        ],
        self::PROFILE_MEDIUM => [
            'w'                  => 400,
            'h'                  => 400,
            self::DISPLAY_WIDTH  => 200,
            self::DISPLAY_HEIGHT => 200,
            'dpi'                => 75,
        ],
        self::PROFILE_LARGE  => [
            'w'                  => 1200,
            'h'                  => 1200,
            self::DISPLAY_WIDTH  => 600,
            self::DISPLAY_HEIGHT => 600,
            'dpi'                => 200,
        ],
    ];

    public const QUALITY = [
        "jpeg" => [
            'low'    => 20,
            'medium' => 50,
            'high'   => 80,
            'full'   => 100,
        ],
        "png"  => [
            'low'    => 2,
            'medium' => 5,
            'high'   => 7,
            'full'   => 9,
        ],
    ];

    /** @var string */
    public static string $img_default = self::IMG_DEFAULT;

    /** @var array */
    protected static array $tmp_files = [];

    /**
     * Get a thumbnail path
     *
     * @param CFile    $file    The file we want a thumb of
     * @param string   $engine  The engine to use : true = Imagick, false = Gd
     * @param string   $profile CThumbnail::$profiles to use
     * @param int|null $page    Page to get thumbnail of
     * @param bool     $crop    Crop the thumbnail to fit the size
     * @param int      $quality Image quality
     *
     * @return string Thumbnail path
     * @throws Exception
     */
    public static function getThumbnailPath(
        CFile $file,
        string $engine,
        string $profile = self::PROFILE_MEDIUM,
        ?int $page = 1,
        bool $crop = false,
        int $quality = 80,
        bool $show = true
    ): string {
        $thumbpath = CFile::getThumbnailDir();

        if ($page) {
            $page -= 1;
        }

        // Path of the thumbnail
        $tmp_path = self::getFileTmpPath($file, $thumbpath, $profile);

        $tmp_file_path = "{$tmp_path}p{$page}-c{$crop}-q{$quality}";

        $ext = 'jpeg';

        $tmp_file_path .= ".{$ext}";

        // If thumbnail doesn't exist create it
        if (!file_exists($tmp_file_path) || filemtime($file->_file_path) >= filemtime($tmp_file_path)) {
            $success = self::createThumb($engine, $tmp_file_path, $file, $profile, $page, $crop, $quality, $show);
            if (!$success) {
                // If thumbnail can't be show, display the default image instead
                throw new CMbException('CThumbnail-Error-Unable to create thumbnail');
            }
        }

        return $tmp_file_path;
    }

    /**
     * Create a thumbnail from a file.
     *
     * @param string   $engine        The engine to use for the render : true = Imagick, false = Gd
     * @param string   $tmp_file_path The location of the resulting file
     * @param CFile    $file          File to convert to thumb
     * @param string   $profile       Image profile to use
     * @param int|null $page          For PDF the page number to convert
     * @param bool     $crop          Crop the thumbnail to fit the size
     * @param int      $quality       Image quality
     *
     * @return bool
     * @throws Exception
     */
    public static function createThumb(
        string $engine,
        string $tmp_file_path,
        CFile $file,
        string $profile,
        ?int $page = 0,
        bool $crop = false,
        int $quality = 80,
        bool $show = true
    ): bool {
        // Purge thumbnails
        CApp::doProbably(
            100,
            function (): void {
                CFile::purgeThumbnails(100);
            }
        );

        $imagine = null;
        try {
            $imagine = ($engine) ? new Imagick\Imagine() : new Gd\Imagine();

            // Put the page number at the end of the file path
            $file_path = ($page !== null) ? "{$file->_file_path}[$page]" : $file->_file_path;
            $image     = self::openFile($imagine, $file, $file_path, $tmp_file_path, $page, $profile);

            $mode = ImageInterface::THUMBNAIL_INSET;

            $width  = self::PROFILES[$profile]["w"];
            $height = self::PROFILES[$profile]["h"];

            $options = [
                'jpeg_quality' => $quality,
                'format'       => 'jpeg',
            ];

            $size     = new Box($width, $height);
            $src_size = $image->getSize();

            $palette    = new Image\Palette\RGB();
            $background = new Image\Palette\Color\RGB($palette, [255, 255, 255], 100);
            $canvas     = $imagine->create(new Box($src_size->getWidth(), $src_size->getHeight()), $background);

            if ($crop) {
                $crop_infos = self::getCropPoint($src_size->getWidth(), $src_size->getHeight());
                $image      = $image->crop($crop_infos['start_point'], $crop_infos['crop_box']);

                $mode   = ImageInterface::THUMBNAIL_OUTBOUND;
                $canvas = $imagine->create($crop_infos['crop_box'], $background);
            }

            if (!$engine && version_compare(PHP_VERSION, '7.0.0') >= 0) {
                $size = self::getSize($src_size, $width, $height);

                // GD is having a bug with the thumbnail function, using resize instead
                $canvas->paste($image, new Image\Point(0, 0))
                    ->resize($size)
                    ->interlace(ImageInterface::INTERLACE_PLANE)
                    ->save($tmp_file_path, $options);
            } else {
                if ($file->file_type === 'image/png') {
                    $canvas->paste($image, new Image\Point(0, 0))
                        ->thumbnail($size, $mode)
                        ->interlace(ImageInterface::INTERLACE_PLANE)
                        ->save($tmp_file_path, $options);
                    $canvas->thumbnail($size, $mode)->interlace(ImageInterface::INTERLACE_PLANE)->save($tmp_file_path);
                } else {
                    // Jpeg images can have errors with the use of canvas (color inversion)
                    $image->thumbnail($size, $mode)->interlace(ImageInterface::INTERLACE_PLANE)->save($tmp_file_path);
                }
            }
        } catch (Throwable $e) {
            if ($show) {
                self::buildHeaders();
                // If an error occure display the medifile image
                $imagine->open(CAppUI::conf('root_dir') . '/' . self::$img_default)->show('png');
            }

            return false;
        } finally {
            static::removeTmpFiles();
        }

        return true;
    }

    /**
     * Get the crop starting point
     *
     * @param int $src_width  Width of the source image
     * @param int $src_height Height of the source image
     *
     * @return array
     */
    public static function getCropPoint(int $src_width, int $src_height): array
    {
        if ($src_width > $src_height) {
            $start_y   = 0;
            $diff      = $src_width - $src_height;
            $start_x   = max($diff / 2, 0);
            $src_width -= $diff;
        } elseif ($src_height > $src_width) {
            $start_x    = 0;
            $diff       = $src_height - $src_width;
            $start_y    = max($diff / 2, 0);
            $src_height -= $diff;
        } else {
            $start_x = 0;
            $start_y = 0;
        }

        return [
            'start_point' => new Image\Point($start_x, $start_y),
            'crop_box'    => new Box($src_width, $src_height),
        ];
    }

    /**
     * Create the path for the thumbnail
     *
     * @param CFile  $file    Hash of the file
     * @param string $path    Path to Mediboard tmp dir
     * @param string $profile Profile used
     *
     * @return string
     */
    public static function getFileTmpPath(CFile $file, string $path, string $profile): string
    {
        $path .= intval($file->_id / 1000) . "/{$file->_id}/{$profile}/";
        CMbPath::forceDir($path);

        return $path;
    }

    /**
     * Make a thumbnail from a CFile or a CCompteRendu
     *
     * @param int      $document_id    CDocumentItem ID of the document
     * @param string   $document_class Class of the document (CFile|CCompteRendu)
     * @param string   $profile        Profile to use for the thumbnail
     * @param int|null $page           Page of the document to display a thumbnail of
     * @param int      $rotate         Rotation of the document
     * @param bool     $crop           Crop the thumbnail to fit
     * @param string   $quality        JPEG quality to display
     * @param string   $perm_callback  Function to check perms
     * @param bool     $show           Display thumbnail or get the thumbnail
     *
     * @return mixed|void
     * @throws Exception
     */
    public static function makeThumbnail(
        int $document_id,
        string $document_class = 'CFile',
        string $profile = self::PROFILE_MEDIUM,
        ?int $page = null,
        int $rotate = 0,
        bool $crop = false,
        string $quality = 'high',
        string $perm_callback = null,
        bool $show = true
    ) {
        $root_dir   = CAppUI::conf('root_dir');
        $error_path = '';

        try {
            if (!static::checkImagineExists()) {
                throw new Exception();
            }

            $imagine = static::getEngineInstance();

            $file = self::createFileForThumb($document_id, $document_class);

            if (is_string($file)) {
                $error_path = $file;
                $file       = null;
            }

            // Check the rights, file_exists and if the file is a draft
            // Show image and call CApp::rip() if a condition is validated.
            if ($error_path = self::handleFileErrors($file, $error_path, $perm_callback)) {
                if ($show) {
                    ob_clean();
                    self::buildHeaders();
                    $imagine->open($root_dir . '/' . $error_path)->show('png');
                    CApp::rip();
                } else {
                    return $imagine->open($root_dir . '/' . $error_path)->get('png');
                }
            }

            // If the file is a svg display it
            if ($file && strpos($file->file_type, 'svg') !== false) {
                if ($show) {
                    header('Content-type: image/svg+xml');
                    $last_modify = filemtime($file->_file_path);
                    self::buildHeaders($file->_file_path, $last_modify);
                    readfile($file->_file_path);
                    CApp::rip();
                } else {
                    return readfile($file->_file_path);
                }
            }

            // If the file is not an image or a pdf try convert it to pdf
            if (
                strpos($file->file_type, 'image') === false
                && strpos($file->file_type, 'pdf') === false
                && $file->isPDFconvertible()
            ) {
                $file = self::convertFileToPdf($file);
            }

            // Display the image
            $last_modify = filemtime($file->_file_path);

            $engine = ($imagine instanceof Imagick\Imagine);

            $quality       = self::QUALITY['jpeg'][$quality];
            $tmp_file_path = self::getThumbnailPath($file, $engine, $profile, $page, $crop, $quality, $show);

            $ext   = 'jpeg';
            $image = $imagine->open($tmp_file_path)->interlace(ImageInterface::INTERLACE_PLANE);

            $rotate = $rotate ?: $file->rotation;
            if ($rotate != 0) {
                // If rotation reset the last modified time
                $image->rotate($rotate);
                $last_modify = time();
            } elseif ($file->date_rotation && strtotime($file->date_rotation) > $last_modify) {
                // Vérification de la date de modification du champ "roration"
                $last_modify = time();
            }

            if ($show) {
                self::buildHeaders($tmp_file_path, $last_modify);
            }

            $options = ['jpeg_quality' => $quality];

            if ($show) {
                $image->show($ext, $options);
            } else {
                return $image->get($ext, $options);
            }

            CApp::rip();
        } catch (Exception $e) {
            if ($show) {
                header('Content-type: image/jpg');
                readfile($root_dir . '/' . self::$img_default);

                CApp::rip();
            }

            throw $e;
        }
    }

    /**
     * Make a thumbnail from a CFile or a CCompteRendu
     *
     * @param int      $document_id    CDocumentItem ID of the document
     * @param string   $document_class Class of the document (CFile|CCompteRendu)
     * @param int|null $page           Page of the document to display a thumbnail of
     * @param int      $disposition    Force file download (1) or not (0) only used with $thumb=0
     * @param bool     $download_raw   Download the raw file (for Osoft)
     * @param int|null $length         Number of pages to slice
     *
     * @return void
     * @throws Exception
     */
    public static function makePreview(
        int $document_id,
        string $document_class = 'CFile',
        ?int $page = null,
        int $disposition = 0,
        bool $download_raw = false,
        ?int $length = null
    ): void {
        // Direct download of the file
        // BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
        // [http://bugs.php.net/bug.php?id=16173]
        header("Pragma: ");
        header("Cache-Control: ");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        // END extra headers to resolve IE caching bug

        header("MIME-Version: 1.0");
        if ($document_class == 'CFile') {
            $file = new CFile();
            $file->load($document_id);

            if (!$file->getPerm(CPermObject::READ)) {
                CApp::rip();
            }

            $disposition = ($disposition) ? 'attachment' : 'inline';

            if (
                $download_raw
                || ($file->file_type != 'application/osoft' && $file->file_type != 'text/osoft')
                || !CModule::getInstalled('osoft')
            ) {
                header("Content-disposition: $disposition; filename=\"{$file->file_name}\";");

                if ($file->type_doc_dmp && $file->file_type == 'application/xml' && CModule::getInstalled('cda')) {
                    $content = CCdaTools::display($file->getBinaryContent(), $file);
                } else {
                    header("Content-type: {$file->file_type}");
                    $content = $file->getBinaryContent();
                }

                if ($page && $page > 0) {
                    $content = CFile::slicePDF($file, $page, $length);
                    header("Content-length: " . strlen($content));
                    echo $content;
                } else {
                    header("Content-length: {$file->doc_size}");
                    echo $content;
                }
            } else {
                if ($file->file_type == 'application/osoft') {
                    $doc     = new COsoftDossier(false);
                    $content = $doc->toHTML($file->getBinaryContent(), false);
                } else {
                    $doc     = new COsoftHistorique(false);
                    $content = $doc->toHTML($file->getBinaryContent());
                }

                $file_name = str_replace('.osoft', '.txt', $file->file_name);
                header("Content-disposition: $disposition; filename=\"{$file_name}\";");
                header("Content-length: " . strlen($content));
                header("Content-type: application/msword");

                $tmp_file = tempnam('', 'osoft_');
                file_put_contents($tmp_file, $content);
                readfile($tmp_file);
                unlink($tmp_file);
            }
        } else {
            readfile(CAppUI::conf('root_dir') . '/' . self::$img_default);
        }
        CApp::rip();
    }

    /**
     * Instanciate and create a CFile from $document_id and $document_class
     *
     * @param int    $document_id    ID of CDocumentItem to get
     * @param string $document_class Class of the object to get (CFile|CCompteRendu)
     *
     * @return CFile|string
     */
    public static function createFileForThumb(int $document_id, string $document_class = 'CFile')
    {
        // If document is a CCompteRendu create the preview image.
        // Check the perms on the object before creating preview
        if ($document_class == 'CCompteRendu') {
            $cr = new CCompteRendu();
            try {
                $cr->load($document_id);
            } catch (Exception $e) {
                return self::$img_default;
            }

            if ($cr->_id) {
                $cr->loadRefsFwd();
                $file = $cr->loadFile();

                if (!$file || !$file->_id) {
                    $cr->makePDFpreview();
                    $file = $cr->_ref_file;

                    if (!$file || !$file->_id) {
                        return self::$img_default;
                    }
                }

                return $file;
            } else {
                return self::$img_default;
            }
        } elseif ($document_class == 'CFile') {
            $file = new CFile();
            try {
                $file->load($document_id);
            } catch (Exception $e) {
                return self::IMG_NOT_FOUND;
            }

            if ($file->_id) {
                return $file;
            } else {
                return self::IMG_NOT_FOUND;
            }
        } else {
            return self::IMG_NOT_FOUND;
        }
    }

    /**
     * Check the perms and existence of a file. Also check if the file is a draft or not
     * Display the appropriate image if one of the conditions are false
     *
     * @param CFile       $file          File to check errors for
     * @param string|null $error_path    Path of the error if there is one
     * @param string|null $perm_callback Function to check perms
     *
     * @return string|null
     */
    public static function handleFileErrors(
        CFile $file = null,
        ?string $error_path = null,
        ?string $perm_callback = null
    ): ?string {
        // Check perms on the file
        if ($perm_callback) {
            if ($file && $file->_id && !forward_static_call($perm_callback, $file)) {
                $error_path = self::IMG_ACCESS_DENIED;
            }
        } elseif ($file && $file->_id && !$file->getPerm(CPermObject::READ)) {
            $error_path = self::IMG_ACCESS_DENIED;
        }


        // Check if the file exists
        if (!$file || !$file->_id || !file_exists($file->_file_path)) {
            $error_path = self::IMG_NOT_FOUND;
        }

        // If the file is a draft display the draft image
        if ($file && $file->file_type == "image/fabricjs") {
            $error_path = self::IMG_DRAFT;
        }

        // If an error occured display the corresponding image
        if ($error_path) {
            return $error_path;
        }

        return null;
    }

    /**
     * Create a pdf file from a file. Throw an exception if an error occure
     *
     * @param CFile $file Object to convert to pdf
     *
     * @return CFile
     * @throws Exception
     */
    public static function convertFileToPdf(CFile $file): CFile
    {
        $fileconvert = $file->loadPDFconverted();
        $success     = 1;
        if (!$fileconvert || $fileconvert->_id) {
            $success = $file->convertToPDF();
        }
        if ($success) {
            $fileconvert = $file->loadPDFconverted();
        }
        if ($fileconvert && $fileconvert->_id) {
            return $fileconvert;
        } else {
            throw new Exception('Failed pdf convertion');
        }
    }

    /**
     * Build the http headers for the file
     *
     * @param string|null $tmp_file_path Path to the thumbnail
     * @param int|null    $last_modify   Last modification time of the file
     *
     * @return void
     */
    public static function buildHeaders(?string $tmp_file_path = null, ?int $last_modify = null): void
    {
        $week_time = 604800;

        header("Cache-Control: max-age=$week_time");
        header('Connection: keep-alive');

        if ($tmp_file_path && $last_modify) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modify) . " GMT");
        }

        header_remove('Pragma');
    }

    /**
     * Return the binaries data from the thumbnail of the file
     *
     * @param CFile    $file    Mediboard file to get binaries from
     * @param int|null $page    File page number to get
     * @param int      $width   Max-width for the thumbnail
     * @param int|null $height  Max-height for the thumbnail
     * @param string   $quality Quality to display the image in
     * @param int|null $rotate  Rotation of the document
     *
     * @return string
     * @throws Exception
     */
    public static function displayThumb(
        CFile $file,
        ?int $page = null,
        int $width = 120,
        ?int $height = null,
        string $quality = 'high',
        ?int $rotate = 0
    ): string {
        if (!static::checkImagineExists()) {
            return '';
        }

        $engine  = extension_loaded('Imagick');
        $imagine = ($engine) ? new Imagick\Imagine() : new Gd\Imagine();

        if (file_exists($file->_file_path)) {
            $height    = $height ?: $width;
            $temp_path = tempnam('tmp/', 'img_');
            try {
                $image = self::openFile($imagine, $file, $file->_file_path, $temp_path, $page);
            } catch (Throwable $e) {
                unlink($temp_path);

                return $imagine->open(rtrim(CAppUI::conf("root_dir"), '/\\') . '/' . self::IMG_NOT_FOUND)->get('png');
            }

            if ($rotate) {
                $image->rotate($rotate);
            }

            $type = 'jpeg';
            if (strpos($file->_file_type, 'png') !== false) {
                $type = 'png';
            }

            $src_size = $image->getSize();

            if (file_exists($temp_path)) {
                unlink($temp_path);
            }

            $options = ($type == 'jpeg') ? ['jpeg_quality' => self::QUALITY['jpeg'][$quality]] :
                ['png_compression_level' => self::QUALITY['png'][$quality]];

            if (!$engine && version_compare(PHP_VERSION, '7.0.0') >= 0) {
                $size = self::getSize($src_size, $width, $height);

                // GD is having a bug with the thumbnail function, using resize instead
                return $image->resize($size)->get($type, $options);
            } else {
                $size = new Box($width, $height);

                return $image->thumbnail($size)->get($type, $options);
            }
        } else {
            return $imagine->open(rtrim(CAppUI::conf("root_dir"), '/\\') . '/' . self::IMG_NOT_FOUND)->get('png');
        }
    }

    /**
     * @param Imagick\Imagine|Gd\Imagine $imagine   The Imagine instance
     * @param CFile                      $file      The CFile to get thumb of
     * @param string                     $file_path The path of the file to get thumb of
     * @param string                     $temp_file Temp file name
     * @param int|null                   $page      Page number to open
     * @param string                     $profile   Profile to use for the resolution
     *
     * @return ImageInterface
     * @throws Exception
     */
    public static function openFile(
        $imagine,
        CFile $file,
        string $file_path,
        string $temp_file,
        ?int $page = null,
        string $profile = self::PROFILE_MEDIUM
    ): ImageInterface {
        $gs   = CAppUI::conf('dPfiles CThumbnail gs_alias');
        $type = $file->file_type;

        if ($type == "application/zip") {
            $f = static::extractFileForPreview($file);

            if (!is_array($f) && is_file($f)) {
                if (!isset(static::$tmp_files[dirname($f)])) {
                    static::$tmp_files[dirname($f)] = [];
                }

                static::$tmp_files[dirname($f)][] = $file->_file_path = $file_path = $f;
                $type                             = CMbPath::getExtension($file->_file_path);
            } else {
                if (is_array($f)) {
                    $tmp_dir_name = dirname($f[0]);
                    foreach ($f as $_file) {
                        if (is_file($_file)) {
                            unlink($_file);
                        } elseif (is_dir($_file)) {
                            CMbPath::emptyDir($_file);
                        }
                    }

                    CMbPath::recursiveRmEmptyDir($tmp_dir_name);
                }

                throw new Exception("Multiple files in zip");
            }
        }

        $resolution = self::PROFILES[$profile]['dpi'];

        // Create tempfile and decode the file_content in it
        $file_path = tempnam('tmp/', 'thumb');
        $temp_path = null;
        try {
            file_put_contents($file_path, $file->getBinaryContent());

            if (strpos($type ?? '', 'pdf') !== false) {
                $escaped_path = escapeshellarg($file->_file_path);

                // Create the PDF with a good quality
                $command = "$gs -dDEVICEXRESOLUTION=$resolution -dDEVICEYRESOLUTION=$resolution -sDEVICE=jpeg";
                if ($page !== null) {
                    $command .= " -dFirstPage=" . ($page + 1) . " -dLastPage=" . ($page + 1);
                }
                $command .= " -o " . $temp_file . " " . $escaped_path;
                exec($command);

                $temp_path = $temp_file;
            }

            $data = $imagine->open($temp_path ?? $file_path);
        } finally {
            // Remove the temp file
            unlink($file_path);

            // Remove the second temp file if it exists
            if (is_file($temp_path ?? '')) {
                unlink($temp_path);
            }
        }

        return $data;
    }

    /**
     * Extract a zip file to preview its content
     *
     * @param CFile $file File to extract
     *
     * @return array|string
     * @throws Exception
     */
    public static function extractFileForPreview(CFile $file)
    {
        $thumbpath = CFile::getThumbnailDir();

        $extract_dir = $thumbpath . "/" . $file->file_real_filename;
        CMbPath::forceDir($extract_dir);

        CMbPath::extract($file->_file_path, $extract_dir, "zip");
        $f = glob($extract_dir . '/*');

        if (count($f) === 1) {
            return reset($f);
        }

        return $f;
    }

    /**
     * Check if hte Imagine library exists or not
     *
     * @return bool
     */
    public static function checkImagineExists(): bool
    {
        return class_exists("\Imagine\Gd\Imagine") || class_exists("\Imagine\Imagick\Imagine");
    }

    /**
     * Remove the temporary files created from extracting a zip
     *
     * @return void
     */
    public static function removeTmpFiles(): void
    {
        foreach (static::$tmp_files as $_dir => $_files) {
            foreach ($_files as $_file) {
                unlink($_file);
            }

            CMbPath::recursiveRmEmptyDir($_dir);
        }
    }

    /**
     * @return Gd\Imagine|Imagick\Imagine
     */
    protected static function getEngineInstance()
    {
        $engine = extension_loaded('Imagick');

        return ($engine) ? new Imagick\Imagine() : new Gd\Imagine();
    }

    /**
     * @param Image\BoxInterface $src_size
     * @param int|string         $width
     * @param int|string         $height
     */
    public static function getSize(Image\BoxInterface $src_size, $width, $height): Box
    {
        $width  = ($width <= $src_size->getWidth()) ? $width : $src_size->getWidth();
        $height = ($height <= $src_size->getHeight()) ? $height : $src_size->getHeight();

        if ($src_size->getWidth() > $src_size->getHeight()) {
            $ratio  = $src_size->getWidth() / $src_size->getHeight();
            $height = $width / $ratio;
        } elseif ($src_size->getWidth() < $src_size->getHeight()) {
            $ratio = $src_size->getHeight() / $src_size->getWidth();
            $width = $height / $ratio;
        }

        return new Box($width, $height);
    }
}
