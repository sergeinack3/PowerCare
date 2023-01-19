<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CFile;

/**
 * A service for generating a printable PDF file from the given CFacture
 */
class FacturePrintService
{
    /** @var CFacture */
    protected CFacture $facture;

    protected string $title;

    /** @var CTemplateManager */
    protected CTemplateManager $header_template;

    protected int $header_height = 100;

    /** @var CTemplateManager */
    protected CTemplateManager $footer_template;

    protected int $footer_height = 100;

    /** @var mixed */
    protected $content;

    protected CFile $file;

    /**
     * @param CFacture    $facture
     * @param string|null $title The title of the pdf file that will be generated
     */
    public function __construct(CFacture $facture, string $title = null)
    {
        $this->facture         = $facture;
        $this->header_template = new CTemplateManager();
        $this->footer_template = new CTemplateManager();

        if (!$title) {
            $title = CAppUI::tr('CFactureEtablissement-Bill number %s-court', $this->facture->_view);
        }

        $this->title = $title;
    }

    /**
     * Generate the PDF file and returns it.
     * The CFile is not stored in the service
     *
     *
     * @param bool $stream     If true, the PDF will be streamed
     * @param bool $auto_print If true, the pdf will contain an auto print flag
     *
     * @return CFile
     * @throws Exception
     * @throws CMbException
     */
    public function generatePdfFile(bool $stream = false, bool $auto_print = true): CFile
    {
        $this->facture->loadRefPatient();
        $this->facture->loadRefPraticien();
        $this->facture->loadRefsObjects();
        $this->facture->loadRefsItems();
        $this->facture->loadRefsReglements();

        $this->setHeaderTemplate();
        $this->setFooterTemplate();
        $this->setCcamActsTooth();
        $this->fetchContent();
        $this->setFile();

        $htmltopdf        = new CHtmlToPDF('CDomPDFConverter');
        $cr               = new CCompteRendu();
        $cr->_page_format = 'a4';
        $cr->_orientation = 'portrait';

        if ($htmltopdf->generatePDF($this->content, $stream, $cr, $this->file, $auto_print) === null) {
            throw new CMbException('CFacture-error-pdf_generation');
        }

        return $this->file;
    }

    /**
     * Get the special model for the header of the document
     */
    protected function setHeaderTemplate(): void
    {
        $title  = ($this->facture->_class === 'CFactureCabinet') ?
            '[ENTETE FACTURE CABINET]' : '[ENTETE FACTURE ETAB]';
        $header = CCompteRendu::getSpecialModel($this->facture->_ref_praticien, $this->facture->_class, $title);

        if ($header->_id) {
            $header->loadContent();
            $this->facture->fillTemplate($this->header_template);
            $this->header_template->renderDocument($header->_source);
            if ($header->height) {
                $this->header_height = $header->height + 75;
            }
        }
    }

    /**
     * Get the special model for the footer of the document
     */
    protected function setFooterTemplate(): void
    {
        $title  = ($this->facture->_class === 'CFactureCabinet') ?
            '[PIED DE PAGE FACT CABINET]' : '[PIED DE PAGE FACT ETAB]';
        $footer = CCompteRendu::getSpecialModel($this->facture->_ref_praticien, $this->facture->_class, $title);

        if ($footer->_id) {
            $footer->loadContent();
            $this->facture->fillTemplate($this->footer_template);
            $this->footer_template->renderDocument($footer->_source);
            if ($footer->height) {
                $this->footer_height = $footer->height;
            }
        }
    }

    /**
     * Add the tooth number to the items of the CFacture (if any)
     */
    protected function setCcamActsTooth(): void
    {
        $teeth_by_codes = [];
        foreach ($this->facture->_ref_consults as $_ref_consult) {
            foreach (explode('|', $_ref_consult->_tokens_ccam) as $_token) {
                $explode_token = explode('-', $_token);
                /* If there are tooth on the Ccam act */
                if (isset($explode_token[11]) && $explode_token[11] != '') {
                    $code = $explode_token[0];

                    /* We use an array with 2 levels to handle the case were a ccam code has been coded several times */
                    if (!array_key_exists($code, $teeth_by_codes)) {
                        $teeth_by_codes[$code] = [];
                    }

                    $teeth_by_codes[$code][] = explode('+', $explode_token[11]);
                }
            }
        }

        if (count($teeth_by_codes)) {
            foreach ($this->facture->_ref_items as $item) {
                if (array_key_exists($item->code, $teeth_by_codes)) {
                    $item->libelle .= ' - Dents : ';
                    $index         = array_key_first($teeth_by_codes[$item->code]);
                    $teeth         = $teeth_by_codes[$item->code][$index];

                    foreach ($teeth as $tooth) {
                        $item->libelle .= ' n°' . $tooth;
                    }

                    unset($teeth_by_codes[$item->code][$index]);
                }
            }
        }
    }

    /**
     * Fetch the content of the Pdf file from the smarty template
     */
    protected function fetchContent(): void
    {
        $style  = file_get_contents('style/mediboard_ext/tables.css');
        $smarty = new CSmartyDP();

        $smarty->assign('style', $style);
        $smarty->assign('facture', $this->facture);
        $smarty->assign('header_height', $this->header_height);
        $smarty->assign('footer_height', $this->footer_height);
        $smarty->assign('header', $this->header_template->document);
        $smarty->assign('footer', $this->footer_template->document);
        $smarty->assign('body_height', 980 - $this->header_height - $this->footer_height);

        $this->content = $smarty->fetch('print_facture.tpl');
    }

    /**
     * Initialize the CFile
     *
     * @throws Exception
     */
    protected function setFile(): void
    {
        $this->file            = new CFile();
        $this->file->file_name = $this->title . '.pdf';
        $this->file->setObject($this->facture);
        $this->file->file_type = 'application/pdf';
        $this->file->author_id = $this->facture->praticien_id;
        $this->file->loadMatchingObject();

        $this->file->fillFields();
        $this->file->updateFormFields();
        $this->file->forceDir();
    }
}
