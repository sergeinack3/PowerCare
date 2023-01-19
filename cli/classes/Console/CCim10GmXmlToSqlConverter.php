<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;


/**
 * Convert the CIM10 GM from XML (CLaML) format to SQL
 */
class CCim10GmXmlToSqlConverter extends Command {

  /** @var OutputInterface */
  protected $output;

  /** @var InputInterface */
  protected $input;

  /** @var string The path of the DRC database archive or directory */
  protected $input_base_path;

  /** @var string The path of the archive containing the base file in SQL */
  protected $output_base_path;

  /** @var string The path of the SQL import file */
  protected $import_file;

  /** @var DOMDocument The DOMDocument instance */
  protected $document;

  /** @var DOMNode The root node of the document */
  protected $root_node;

  /** @var DOMXPath The XPath instance */
  protected $xpath;

  /** @var array An array containing the modifiers of the codes
   * (AKA a lazy way for defining several subcodes for the codes of a particular block)
   */
  protected $modifiers = array();

  /** @var array An array containing the data of the different chapters and block */
  protected $chapters = array();

  /** @var array An array for getting the chapter's id from it's code */
  protected $chapter_codes_to_id = array();

  /** @var integer The last chapter id */
  protected $chapter_id = 1;

  /** @var array An array containing the data of the codes */
  protected $codes = array();

  /** @var array An array for getting the CIM codes's id from it's code */
  protected $codes_to_id = array();

  /** @var integer The last code id */
  protected $code_id = 1;

  /** @var array An array containing the data of the notes */
  protected $notes = array();

  /** @var integer The last code id */
  protected $note_id = 1;

  /** @var array An array containing the data of the references */
  protected $references = array();

  /** @var integer The last reference id */
  protected $reference_id = 1;

  /** @var array An array linking the meta attributes to the database fields */
  protected static $meta_to_fields = array(
    'Para295'     => 'para_295',
    'Para301'     => 'para_301',
    'MortL1Code'  => 'mortality_level1',
    'MortL2Code'  => 'mortality_level2',
    'MortL3Code'  => 'mortality_level3',
    'MortL4Code'  => 'mortality_level4',
    'MortBCode'   => 'morbidity',
    'SexCode'     => 'sex_code',
    'SexReject'   => 'sex_reject',
    'AgeLowDiff'  => 'age_low',
    'AgeHighDiff' => 'age_high',
    'AgeReject'   => 'age_reject',
    'RareDisease' => 'rare_disease',
    'Content'     => 'content',
    'Infectious'  => 'infectious',
    'EBMLabor'    => 'ebm_labour',
  );

  /**
   * @inheritdoc
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $style = new OutputFormatterStyle('blue', null, array('bold'));
    $output->getFormatter()->setStyle('b', $style);

    $style = new OutputFormatterStyle(null, 'red', array('bold'));
    $output->getFormatter()->setStyle('error', $style);
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('ox-convert:cim10gm')
      ->setDescription('Convert the CIM10 GM xml file to a MySQL dump')
      ->setHelp('CIM10 basefile to SQL converter')
      ->addOption(
        'input',
        'i',
        InputOption::VALUE_REQUIRED,
        'The CIM10 GM CLaML file'
      )
      ->addOption(
        'output',
        'o',
        InputOption::VALUE_OPTIONAL,
        'The output archive path',
        __DIR__ . '/../../../modules/dPcim10/base/cim10_gm.tar.gz'
      );
  }

  /**
   * @throws Exception
   *
   * @return void
   */
  protected function getParams() {
    $this->input_base_path = $this->input->getOption('input');
    $this->output_base_path = $this->input->getOption('output');

    if (!is_file($this->input_base_path) || !is_readable($this->input_base_path)) {
      throw new Exception("Cannot read file {$this->input_base_path}");
    }

    if (pathinfo($this->input_base_path, PATHINFO_EXTENSION) != 'xml') {
      throw new Exception("File {$this->input_base_path} is not an XML file");
    }

    if ((!is_file($this->output_base_path) && !is_dir($this->output_base_path)) || !is_readable($this->output_base_path)) {
      $type = is_file($this->output_base_path) ? 'file' : 'dir';
      throw new Exception("Cannot read {$type} {$this->output_base_path}");
    }
  }

  /**
   * Output timed text
   *
   * @param string $text Text to print
   *
   * @return void
   */
    protected function out($text)
    {
        $this->output->writeln(CMbDT::strftime("[%Y-%m-%d %H:%M:%S]") . " - $text");
    }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->input  = $input;
    $this->output = $output;

    $this->getParams();

    set_error_handler(array($this, 'handleFileSystemError'));

    if (is_file($this->output_base_path)) {
      $this->out('Extracting the Mediboard CIM10 GM database archive.');

      $path = __DIR__ . '/../../../tmp/cim10/gm';
      if (!CMbPath::extract($this->output_base_path, $path)) {
        throw new Exception('Unable to extract the CIM10 GM archive');
      }
      $this->out('Mediboard CIM10 GM database archive extracted');

      $this->output_base_path = $path;
    }

    if (is_dir($this->output_base_path) && substr($this->output_base_path, -1, 1) == '/') {
      $this->output_base_path = substr($this->output_base_path, 0, -1);
    }

    $this->import_file = "{$this->output_base_path}/data.sql";

    if (file_exists($this->import_file)) {
      file_put_contents($this->import_file, '');
    }

    $this->import();
    $this->createArchive();

    return self::SUCCESS;
  }

  /**
   * The main import function, that parse the XML file and forge the SQL queries
   *
   * @throws Exception
   *
   * @return void
   */
  protected function import() {
        $this->document = new DOMDocument('1.0', 'UTF-8');
    $this->document->load($this->input_base_path);

    $this->xpath = new DOMXPath($this->document);
    $node = $this->xpath->query('/ClaML');
    if (!$node->length || $node->length > 1) {
      throw new Exception('The node ClaML has not been found in the XML file');
    }

    $this->root_node = $node->item(0);

    $this->parseModifiers();
    $this->parseClasses();

    file_put_contents("{$this->output_base_path}/missing_ref.txt", "");

    $this->insertChapters();
    $this->insertCodes();
    $this->insertNotes();
  }

  /**
   * Parse the modifiers
   *
   * @throws Exception
   *
   * @return void
   */
  protected function parseModifiers() {
    $this->out('Parsing the code modifiers');
    $modifiers = $this->xpath->query('Modifier', $this->root_node);

    if (!$modifiers->length) {
      throw new Exception('No Modifier nodes found in the XML file');
    }

    foreach ($modifiers as $modifier) {
      $modifier_code = $modifier->getAttribute('code');
      $sub_classes = array();

      $nodes = $this->xpath->query("ModifierClass[@modifier=\"$modifier_code\"]", $this->root_node);
      /** @var DOMElement $node */
      foreach ($nodes as $node) {
        $code = $node->getAttribute('code');
        $sub_class = array(
          'code'              => $code,
          'notes'             => array(),
          'usage'             => null,
          'sex_code'          => null,
          'sex_reject'        => null,
          'age_low'           => null,
          'age_high'          => null,
          'age_reject'        => null,
          'rare_disease'      => null,
          'content'           => null,
          'infectious'        => null,
          'ebm_labour'       => null,
          'mortality_level1'  => null,
          'mortality_level2'  => null,
          'mortality_level3'  => null,
          'mortality_level4'  => null,
          'morbidity'         => null,
          'para_295'          => null,
          'para_301'          => null,
          'exclusions'        => array()
        );

        $sub_class['notes'] = $this->getNotesFromElement($node);

        $meta_fields = $this->xpath->query('Meta', $node);
        foreach ($meta_fields as $meta_field) {
          $field = self::getMetaFieldName($meta_field);

          if ($field) {
            $value = self::getMetaValue($meta_field);

            $sub_class[$field] = $value;
          }
          elseif ($meta_field->getAttribute('name') == 'excludeOnPrecedingModifier') {
            $excluded_code = substr($meta_field->getAttribute('value'), -1, 1);
            $sub_class['exclusions'][$excluded_code] = $meta_field->getAttribute('value');
          }
        }

        $sub_classes[$code] = $sub_class;
      }

      $this->modifiers[$modifier_code] = $sub_classes;
    }
  }

  /**
   * Handle the Class elements, and parse them into chapter or codes
   *
   * @throws Exception
   *
   * @return void
   */
  protected function parseClasses() {
    $this->out('Parsing the classes (chapters and codes)');
    $classes = $this->xpath->query('Class', $this->root_node);

    if (!$classes->length) {
      throw new Exception('No Class nodes found in the XML file');
    }

    /** @var DOMElement $class */
    foreach ($classes as $class) {
      $type = $class->getAttribute('kind');

      switch ($type) {
        case 'block':
        case 'chapter':
          $this->parseChapter($class);
          break;
        case 'category':
          $this->parseCode($class);
          break;
        default:
      }
    }
  }

  /**
   * Parse the chapter data from the given DOMElement
   *
   * @param DOMElement $chapter The chapter element
   *
   * @return void
   */
  protected function parseChapter($chapter) {
    $code = $chapter->getAttribute('code');
    $code_long = $code;
    if (strpos($code, '-') !== false) {
      $code_long = "({$code})";
    }

    $chapter_id = $this->chapter_id++;

    $parent_id = null;
    $parent_class = $this->xpath->query('SuperClass', $chapter);
    if ($parent_class->length == 1) {
      $parent_code = $parent_class->item(0)->getAttribute('code');
      if (array_key_exists($parent_code, $this->chapter_codes_to_id)) {
        $parent_id = $this->chapter_codes_to_id[$parent_code];
      }
    }

    $notes = $this->getNotesFromElement($chapter);

    $this->chapter_codes_to_id[$code] = $chapter_id;
    $this->chapters[$chapter_id] = array(
      'chapter_id'  => $chapter_id,
      'code'        => $code,
      'code_long'   => $code_long,
      'parent_id'   => $parent_id,
      'notes'       => $notes
    );
  }

  /**
   * Format the SQL query for inserting the data of the chapters
   *
   * @return void
   */
  protected function insertChapters() {
    $this->out('Forging the SQL queries for the chapters');
    $query = "INSERT INTO `chapters_gm` (`id`, `code`, `code_long`, `parent_id`) VALUES\n";

    $n = 1;
    $total = count($this->chapters);
    foreach ($this->chapters as $chapter) {
      $values = array();
      foreach ($chapter as $field => $value) {
        if ($field == 'notes') {
          $this->parseNotesFor($chapter['chapter_id'], 'chapter', $value);
          $str = "Notes {$chapter['code_long']}:\n";
          foreach ($value as $note) {
            $str .= "  type: {$note['type']}\n";
            $doc = new DOMDocument('1.0', 'UTF-8');
            $node = $doc->importNode($note['content'], true);
            $doc->appendChild($node);
            $str .= "  content : " . utf8_decode($doc->saveXML()) . "\n\n";
          }

          file_put_contents("{$this->output_base_path}/notes.txt", $str, FILE_APPEND);
        }
        else {
          $values[] = self::prepareValue($value);
        }
      }

      $query .= '  (' . implode(', ', $values) . ')';
      $n++;

      if ($n <= $total) {
        $query .= ",\n";
      }
      else {
        $query .= ";\n";
      }
    }

    file_put_contents($this->import_file, "{$query}\n", FILE_APPEND);
  }
  /**
   * Parse the code data from the given element
   *
   * @param DOMElement $element The class element
   *
   * @return void
   */
  protected function parseCode($element) {
    $code_long = $element->getAttribute('code');
    $code = str_replace('.', '', $code_long);

    $code_id = $this->code_id++;

    $parent_id = null;
    $chapter_id = null;
    $parent_class = $this->xpath->query('SuperClass', $element);
    if ($parent_class->length == 1) {
      $parent_code = $parent_class->item(0)->getAttribute('code');
      if (array_key_exists($parent_code, $this->chapter_codes_to_id)) {
        $chapter_id = $this->chapter_codes_to_id[$parent_code];
      }
      elseif (array_key_exists($parent_code, $this->codes_to_id)) {
        $parent_id = $this->codes_to_id[$parent_code];
        $chapter_id = $this->codes[$parent_id]['chapter_id'];
      }
    }

    $usage = self::getUsage($element);

    $notes = $this->getNotesFromElement($element);

    $this->codes_to_id[$code_long] = $code_id;
    $data = array(
      'code_id'           => $code_id,
      'code'              => $code,
      'code_long'         => $code_long,
      'chapter_id'        => $chapter_id,
      'parent_id'         => $parent_id,
      'usage'             => $usage,
      'sex_code'          => null,
      'sex_reject'        => null,
      'age_low'           => null,
      'age_high'          => null,
      'age_reject'        => null,
      'rare_disease'      => null,
      'content'           => null,
      'infectious'        => null,
      'ebm_labour'       => null,
      'mortality_level1'  => null,
      'mortality_level2'  => null,
      'mortality_level3'  => null,
      'mortality_level4'  => null,
      'morbidity'         => null,
      'para_295'          => null,
      'para_301'          => null,
      'notes'             => $notes
    );

    $meta_fields = $this->xpath->query('Meta', $element);
    foreach ($meta_fields as $meta_field) {
      $field = self::getMetaFieldName($meta_field);

      if ($field) {
        $value = self::getMetaValue($meta_field);

        $data[$field] = $value;
      }
    }

    $this->codes_to_id[$code_long] = $code_id;
    $this->codes[$code_id] = $data;

    $this->applyModifiersTo($element, $data);
  }

  /**
   * Format the SQL query for inserting the data of the codes
   *
   * @return void
   */
  protected function insertCodes() {
    $this->out('Forging the SQL queries for the codes');
    $query = '';
    $n = 1;
    $line = 1;
    $total = count($this->codes);
    foreach ($this->codes as $code) {
      if ($n === 1) {
        $query .= "INSERT INTO `codes_gm` (`id`, `code`, `code_long`, `chapter_id`, `parent_id`, `usage`, `sex_code`,"
          . " `sex_reject`, `age_low`, `age_high`, `age_reject`, `rare_disease`, `content`, `infectious`, `ebm_labour`,"
          . " `mortality_level1`, `mortality_level2`, `mortality_level3`, `mortality_level4`, `morbidity`, `para_295`,"
          . " `para_301`) VALUES\n";
      }

      $values = array();
      foreach ($code as $field => $value) {
        if ($field == 'notes') {
          $this->parseNotesFor($code['code_id'], 'code', $value);
          $str = "Notes {$code['code_long']}:\n";
          foreach ($value as $note) {
            $str .= "  type: {$note['type']}\n";
            $doc = new DOMDocument('1.0', 'UTF-8');
            $node = $doc->importNode($note['content'], true);
            $doc->appendChild($node);
            $str .= "  content : " . utf8_decode($doc->saveXML()) . "\n\n";
          }

          file_put_contents("{$this->output_base_path}/notes.txt", $str, FILE_APPEND);
        }
        else {
          if (($field == 'sex_reject' && $code['sex_code'] === null) || ($field == 'age_reject' && $code['age_low'] === null)) {
            $values[] = 'NULL';
          }
          else {
            $values[] = self::prepareValue($value);
          }
        }
      }

      $query .= '  (' . implode(', ', $values) . ')';
      $n++;
      $line++;

      if ($n < 1000 && $line < $total) {
        $query .= ",\n";
      }
      else {
        $query .= ";\n";
        $n = 1;
      }
    }

    file_put_contents($this->import_file, "{$query}\n", FILE_APPEND);
  }

  /**
   * Parse the notes of the given chapter or code
   *
   * @param integer $owner_id   The owner id
   * @param string  $owner_type The owner type (chapter or code)
   * @param array   $notes      The notes
   *
   * @return void
   */
  protected function parseNotesFor($owner_id, $owner_type, $notes) {
    $fragments = array();
    foreach ($notes as $note) {
      if ($note['type'] == 'modifierlink') {
        continue;
      }

      $content = false;
      /** @var DOMElement $node */
      $node = $note['content'];
      if ($node->hasChildNodes()) {
        $lists = $this->xpath->query('descendant::Fragment[@type="list"]', $node);
        if ($lists->length) {
          $this->parseFragmentListElements($note, $lists, $fragments);
        }
        else {
          $note_id = $this->note_id++;

          $data = array(
            'note_id'    => $note_id,
            'owner_id'   => $owner_id,
            'owner_type' => $owner_type,
            'type'       => $note['type'],
            'content'    => null,
            'parent_id'  => null
          );

          $items = $this->xpath->query('descendant::Fragment[@type="item"]', $node);
          if ($items->length) {
            $data['content'] = $this->parseFragmentItemsElements($data, $items);
          }
          else {
            $note_id = $this->note_id++;
            $label   = $this->xpath->query('Label', $node)->item(0);

            $data['content'] = $this->parseNoteContent($data, $label);
          }

          $this->notes[$note_id] = $data;
        }
      }
    }

    $this->addFragments($fragments, $owner_type, $owner_id);
  }

  /**
   * Forge the SQL queries for the notes and references
   *
   * @return void
   */
  protected function insertNotes() {
    $this->out('Forging the SQL queries for the notes');
    $query = '';
    $n = 1;
    $line = 1;
    $total = count($this->notes);
    foreach ($this->notes as $note) {
      if ($n === 1) {
        $query .= "INSERT INTO `notes_gm` (`id`, `owner_id`, `owner_type`, `type`, `content`, `parent_id`) VALUES\n";
      }

      $values = array();
      foreach ($note as $field => $value) {
        $values[] = self::prepareValue($value);
      }

      $query .= '  (' . implode(', ', $values) . ')';
      $n++;
      $line++;

      if ($n < 1000 && $line < $total) {
        $query .= ",\n";
      }
      else {
        $query .= ";\n";
        $n = 1;
      }
    }

    file_put_contents($this->import_file, "{$query}\n", FILE_APPEND);

    $this->out('Forging the SQL queries for the references');
    $query = '';
    $n = 1;
    $line = 1;
    $total = count($this->references);
    foreach ($this->references as $reference) {
      if ($n === 1) {
        $query .= "INSERT INTO `references_gm` (`id`, `note_id`, `code_id`, `code_type`, `text`, `usage`) VALUES\n";
      }

      $values = array();
      foreach ($reference as $field => $value) {
        $values[] = self::prepareValue($value);
      }

      $query .= '  (' . implode(', ', $values) . ')';
      $n++;
      $line++;

      if ($n < 1000 && $line < $total) {
        $query .= ",\n";
      }
      else {
        $query .= ";\n";
        $n = 1;
      }
    }

    file_put_contents($this->import_file, "{$query}\n", FILE_APPEND);
  }

  /**
   * Browse the given node's children, and get the content
   *
   * @param array   $note The note's data
   * @param DOMNode $node The node
   *
   * @return string
   */
  protected function parseNoteContent($note, $node) {
    $content = '';

    if ($node instanceof DOMNode && $node->childNodes instanceof DOMNodeList) {
      foreach ($node->childNodes as $_node) {
        if ($_node->nodeType == XML_TEXT_NODE) {
          $content .= $this->decode($_node->wholeText);
        }
        elseif ($_node->nodeType == XML_ELEMENT_NODE) {
          switch ($_node->tagName) {
            case 'List':
              $content .= $this->parseListElement($note, $_node);
              break;
            case 'Para':
              $content .= $this->parseParaElement($note, $_node);
              break;
            case 'Reference':
              $content .= $this->parseReferenceElement($note, $_node);
              break;
            case 'Table':
              $content .= $this->parseTableElement($note, $_node);
              break;
            case 'Term':
              $content .= $this->parseTermElement($_node);
              break;
            default:
          }
        }
      }
    }

    return $content;
  }

  /**
   * Get the content from the Fragment elements
   *
   * @param array       $note     The note's data
   * @param DOMNodeList $elements The list element
   *
   * @return string
   */
  protected function parseFragmentItemsElements($note, $elements) {
    $start_tag = '<p>';
    $end_tag ='<p>';
    $content = '';
    foreach ($elements as $element) {
      $content .= $this->parseNoteContent($note, $element);
      if ($element->hasAttribute('usage')) {
        $content .= "&dagger;";
      }
    }

    return $start_tag . $content . $end_tag;
  }

  /**
   * Get the content from a List element
   *
   * @param array       $note      The note's data
   * @param DOMNodeList $elements  The list element
   * @param array       $fragments The list element
   *
   * @return string
   */
  protected function parseFragmentListElements($note, $elements, &$fragments) {
    $header = '';
    $content = '';
    $type = $note['type'];
    foreach ($elements as $element) {
      $text = str_replace(' :', ':', self::decode($element->textContent));

      if (strpos($text, ':') !== false) {
        $header = $text;
      }
      else {
        $content = $element;
      }
    }

    if (!array_key_exists($type, $fragments)) {
      $fragments[$type] = array();
    }

    if ($header) {
      if (!array_key_exists($header, $fragments[$type])) {
        $fragments[$type][$header] = array();
      }
      $fragments[$type][$header][] = $content;
    }
    else {
      $fragments[$type][] = $content;
    }
  }

  /**
   * Add the given fragments to the notes for the given owner
   *
   * @param array   $fragments  The fragments
   * @param string  $owner_type The owner's type
   * @param integer $owner_id   The owner's id
   *
   * @return void
   */
  protected function addFragments($fragments, $owner_type, $owner_id) {
    foreach ($fragments as $type => $_fragments) {
      foreach ($_fragments as $key => $value) {
        if (is_array($value)) {
          $parent_id = $this->note_id++;

          $this->notes[$parent_id] = array(
            'note_id'         => $parent_id,
            'owner_id'        => $owner_id,
            'owner_type'      => $owner_type,
            'type'            => $type,
            'content'         => "<p>$key</p><ul>[children]</ul>",
            'parent_id'       => null
          );

          foreach ($value as $content) {
            $note_id = $this->note_id++;

            $data = array(
              'note_id'         => $note_id,
              'owner_id'        => $owner_id,
              'owner_type'      => $owner_type,
              'type'            => $type,
              'content'         => null,
              'parent_id'       => $parent_id
            );

            $data['content'] = $this->parseNoteContent($data, $content);
            $this->notes[$note_id] = $data;
          }
        }
        else {
          $note_id = $this->note_id++;

          $data = array(
            'note_id'         => $note_id,
            'owner_id'        => $owner_id,
            'owner_type'      => $owner_type,
            'type'            => $type,
            'content'         => $value,
            'parent_id'       => null
          );

          $data['content'] = $this->parseNoteContent($data, $value);
          $this->notes[$note_id] = $data;
        }
      }
    }
  }

  /**
   * Get the content from a List element
   *
   * @param array      $note    The note's data
   * @param DOMElement $element The list element
   *
   * @return string
   */
  protected function parseListElement($note, $element) {
    switch ($element->getAttribute('class')) {
      case 'dash':
        $start_tag = '<dl>';
        $end_tag = '</dl>';
        break;
      case 'lowerpalpha':
        $start_tag = '<ol type="a">';
        $end_tag = '</ol>';
        break;
      case 'bullet':
      default:
        $start_tag = '<ul>';
        $end_tag = '</ul>';
    }

    $content = $start_tag;

    foreach ($element->childNodes as $childNode) {
      $text  = self::decode($childNode->textContent);
      if ($text != '') {
        if ($element->getAttribute('class') == 'dash') {
          $content .= "<dd>$text</dd>";
        }
        else {
          $content .= "<li>$text</li>";
        }
      }
    }

    return $content . $end_tag;
  }

  /**
   * Get the content from a Table element
   *
   * @param array      $note    The note's data
   * @param DOMElement $element The list element
   *
   * @return string
   */
  protected function parseTableElement($note, $element) {
    $content = '<table>';

    foreach ($element->childNodes as $childNode) {
      if ($childNode->nodeType != XML_TEXT_NODE) {
        switch ($childNode->tagName) {
          case 'THead':
            $content .= '<thead>';
            foreach ($childNode->childNodes as $_row) {
              if ($_row->nodeType != XML_TEXT_NODE) {
                $content .= $this->parseRowElement($note, $_row, true);
              }
            }
            $content .= '</thead>';
            break;
          case 'TBody':
          default:
            $content .= '<tbody>';
            foreach ($childNode->childNodes as $_row) {
              if ($_row->nodeType != XML_TEXT_NODE) {
                $content .= $this->parseRowElement($note, $_row);
              }
            }
            $content .= '</tbody>';
        }
      }
    }

    return $content . '</table>';
  }

  /**
   * Get the content from a Row element
   *
   * @param array      $note    The note's data
   * @param DOMElement $element The list element
   * @param boolean    $header  Indicate if the row is a header row
   *
   * @return string
   */
  protected function parseRowElement($note, $element, $header = false) {
    $content = '<tr>';

    foreach ($element->childNodes as $childNode) {
      if ($childNode->nodeType != XML_TEXT_NODE) {
        $content .= $header ? '<th' : '<td';

        if ($childNode->hasAttribute('rowspan')) {
          $content .= ' rowpsan="' . $childNode->getAttribute('rowspan') . '">';
        }
        elseif ($childNode->hasAttribute('colspan')) {
          $content .= ' colspan="' . $childNode->getAttribute('colspan') . '">';
        }
        else {
          $content .= '>';
        }

        $content .= $this->parseNoteContent($note, $childNode);
        $content .= $header ? '</th>' : '</td>';
      }
    }

    return $content . '</tr>';
  }

  /**
   * Get the content from a Para element
   *
   * @param array      $note    The note's data
   * @param DOMElement $element The list element
   *
   * @return string
   */
  protected function parseParaElement($note, $element) {
    $start_tag = '<p>';
    $end_tag = '</p>';
    $add_br = true;
    if ($element->hasAttribute('class') && $element->getAttribute('class') == 'headingIntro') {
      $start_tag = '<h2>';
      $end_tag = '</h2>';
      $add_br = false;
    }
    elseif ($element->hasAttribute('class') && $element->getAttribute('class') == 'termList2') {
      $start_tag = '<p>&nbsp;&nbsp;&nbsp;';
    }

    $content = $this->parseNoteContent($note, $element);
    if ($add_br) {
      $content = str_replace('. ', '.<br>', $this->parseNoteContent($note, $element));
    }

    return $start_tag . $content . $end_tag;
  }

  /**
   * Parse the given Reference node, add the data to the references list and return an html content
   *
   * @param integer    $note    The id of the parent note
   * @param DOMElement $element The DOMElement
   *
   * @return string
   */
  protected function parseReferenceElement($note, $element) {
    $content = '';
    $code = self::decode($element->textContent);

    $code_id = null;
    if (preg_match('/[A-Z][0-9]{2}-[A-Z][0-9]{2}/', $code)) {
      $type = 'chapter';
      if (array_key_exists($code, $this->chapter_codes_to_id)) {
        $code_id = $this->chapter_codes_to_id[$code];
      }
    }
    else {
      $type = 'code';
      if ($element->hasAttribute('code')) {
        $code = $element->getAttribute('code');
      }
      $code = str_replace('-', '', $code);

      if (substr($code, -1, 1) == '.') {
        $code = str_replace('.', '', $code);
      }

      if (array_key_exists($code, $this->codes_to_id)) {
        $code_id = $this->codes_to_id[$code];
      }
    }

    if ($code_id) {
      $reference_id       = $this->reference_id++;
      $this->references[] = array(
        'reference_id' => $reference_id,
        'note_id'      => $note['note_id'],
        'code_id'      => $code_id,
        'code_type'    => $type,
        'text'         => self::decode($element->textContent),
        'usage'        => self::getUsage($element)
      );

      $content = "<a class=\"cim10-code\" data-id=\"$reference_id\"></a>";
    }
    else {
      file_put_contents("{$this->output_base_path}/missing_ref.txt", "Missing code $code\n", FILE_APPEND);
    }

    return $content;
  }

  /**
   * Get the content from a Term element
   *
   * @param DOMElement $element The list element
   *
   * @return string
   */
  protected function parseTermElement($element) {
    switch ($element->getAttribute('class')) {
      case 'tab':
        $content = "&nbsp;&nbsp;" . self::decode($element->textContent);
        break;
      case 'bold':
        $content = "<strong>" . self::decode($element->textContent) . "</strong> ";
        break;
      case 'subscript':
        $content = "<sub>" . self::decode($element->textContent) . "<\sub>";
        break;
      case 'superscript':
        $content = "<sup>" . self::decode($element->textContent) . "<\sup>";
        break;
      case 'Zinkl':
        $content = '<em><strong>' . self::decode($element->textContent) . '</strong></em>';
        break;
      default:
        $content = self::decode($element->textContent);
    }

    return $content;
  }

  /**
   * Check if the given code element is modified, and apply the modifiers if necessary
   *
   * @param DOMElement $element The element
   * @param array      $data    The data of the code
   *
   * @return void
   */
  protected function applyModifiersTo($element, $data) {
    $modifiers = $this->xpath->query('ModifiedBy', $element);

    if ($modifiers->length) {
      $node = $modifiers->item(0);

      $modifier_code = $node->getAttribute('code');
      $this->addModifiedCodes($element, $modifier_code, $data);
    }
  }

  /**
   * Add the modified codes for the given code
   *
   * @param DOMElement $element       The class element
   * @param string     $modifier_code The modifier code
   * @param integer    $data          The data of the parent code
   * @param int        $level         The level of modifier applied
   *
   * @return void
   */
  protected function addModifiedCodes($element, $modifier_code, $data, $level = 0) {
    if (array_key_exists($modifier_code, $this->modifiers)) {
      $modifier = $this->modifiers[$modifier_code];

      foreach ($modifier as $sub_class) {
        if (empty($sub_class['exclusions']) || !array_key_exists(substr($data['code'], -1, 1), $sub_class['exclusions'])) {
          $sub_code = array();
          foreach ($data as $field => $value) {
            switch ($field) {
              case 'code_id':
                $sub_code[$field] = $this->code_id++;
                break;
              case 'code':
              case 'code_long':
                $sub_code[$field] = $value . $sub_class['code'];
                break;
              case 'parent_id':
                $sub_code[$field] = $data['code_id'];
                break;
              case 'notes':
                $label = '';
                $notes = array();

                foreach ($data['notes'] as $note) {
                  if ($note['type'] == 'preferred') {
                    $node = $note['content'];
                    $label = self::decode($node->textContent);
                  }
                  else {
                    $notes[] = $note;
                  }
                }
                foreach ($sub_class['notes'] as $note) {
                  if ($note['type'] == 'preferred') {
                    $node = $note['content'];
                    $label .= " " . self::decode($node->textContent);
                  }
                  else {
                    $notes[] = $note;
                  }
                }

                $node = $this->document->createElement('Rubric');
                $node->setAttribute('kind', 'preferred');
                $label_node = $this->document->createElement('Label', utf8_encode($label));
                $node->appendChild($label_node);
                $notes[] = array(
                  'type'    => 'preferred',
                  'content' => $node
                );

                $sub_code[$field] = $notes;
                break;
              default:
                $sub_code[$field] = $value;
                if (array_key_exists($field, $sub_class) && $sub_class[$field]) {
                  $sub_code[$field] = $sub_class[$field];
                }
            }
          }

          $this->codes_to_id[$sub_code['code_long']] = $sub_code['code_id'];
          $this->codes[$sub_code['code_id']]         = $sub_code;

          $modifiers = $this->xpath->query('ModifiedBy', $element);
          if ($modifiers->length == 2 && !$level) {
            $sub_modifier_code = $modifiers->item(1)->getAttribute('code');

            $this->addModifiedCodes($element, $sub_modifier_code, $sub_code, 1);
          }
        }
      }
    }
  }

  /**
   * Get the notes from the given DOMElement
   *
   * @param DOMElement $element The element
   *
   * @return array
   */
  protected function getNotesFromElement($element) {
    $notes = array();

    $nodes = $this->xpath->query('Rubric', $element);
    /** @var DOMElement $node */
    foreach ($nodes as $node) {
      $notes[] = array(
        'type'    => $node->getAttribute('kind'),
        'content' => $node
      );
    }

    return $notes;
  }

  /**
   * Return the database field name associated with the meta name attribute.
   * If the field is not handled, return null
   *
   * @param DOMElement $meta The meta element
   *
   * @return string
   */
  protected static function getMetaFieldName($meta) {
    $name = $meta->getAttribute('name');

    $field = null;
    if (array_key_exists($name, self::$meta_to_fields)) {
      $field = self::$meta_to_fields[$name];
    }

    return $field;
  }

  /**
   * Return the converted value from the meta element.
   *
   * @param DOMElement $meta The meta element
   *
   * @return string
   */
  protected static function getMetaValue($meta) {
    $name = $meta->getAttribute('name');

    switch ($name) {
      case 'Para295':
      case 'Para301':
      case 'SexReject':
      case 'AgeReject':
        $value = strtolower($meta->getAttribute('value'));
        break;
      case 'MortL1Code':
      case 'MortL2Code':
      case 'MortL3Code':
      case 'MortL4Code':
      case 'MortBCode':
        $value = strtolower($meta->getAttribute('value'));
        if ($value == 'undef') {
          $value = null;
        }
        break;
      case 'SexCode':
        $value = strtolower($meta->getAttribute('value'));
        $value = $value == '9' ? null : $value;
        break;
      case 'RareDisease':
      case 'Content':
      case 'Infectious':
      case 'EBMLabor':
        $value = $meta->getAttribute('value') == 'J' ? '1' : '0';
        break;
      case 'AgeLowDiff':
      case 'AgeHighDiff':
        $value = self::convertAgeValues($meta->getAttribute('value'));
        break;
      default:
        $value = null;
    }

    return $value;
  }

  /**
   * Get the value of the usage attribute in the given element (a Class or Reference element)
   *
   * @param DOMElement $element The element
   *
   * @return null|string
   */
  protected static function getUsage($element) {
    $usage = null;

    if ($element->hasAttribute('usage')) {
      $usage = $element->getAttribute('usage');
      if ($usage == 'aster') {
        $usage = 'asterisk';
      }
    }

    return $usage;
  }

  /**
   * Convert the ages values
   *
   * @param integer $value The value from the meta tag
   *
   * @return null|string
   */
  protected static function convertAgeValues($value) {
    $value = intval($value);

    $result = null;

    if ($value === 0) {
      $result = '0d';
    }
    elseif ($value >= 1 && $value <= 13) {
      $result = "{$value}j";
    }
    elseif ($value >= 101 && $value <= 111) {
      $result = ($value - 100) . 'm';
    }
    elseif ($value >= 201 && $value <= 324) {
      $result = ($value - 200) . 'y';
    }

    return $result;
  }

  /**
   * Prepare the value for the SQL query
   *
   * @param mixed $value The value
   *
   * @return mixed
   */
  protected static function prepareValue($value) {
    if ($value == '' || is_null($value)) {
      $value = 'NULL';
    }
    elseif (is_string($value)) {
      $value = "'" . addslashes($value) . "'";
    }

    return $value;
  }

  /**
   * Return the trimmed and decoded text
   *
   * @param string $text The text to decode
   *
   * @return string
   */
  protected static function decode($text) {
    /* UTF8 decode with handling of the oe ligatures and special quotes characters */
    $replacements = array(
      '%u0152' => 'OE', '%u008C' => 'OE', '%u0153' => 'oe', '%u009c' => 'oe', '%u0091' => "'", '%u0092' => "'", '%u0027' => "'"
    );

    return trim(iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", strtr($text, $replacements)));
  }

  /**
   * Create the archive
   *
   * @return void
   */
  protected function createArchive() {
    $this->out('Creating the new archive');
    $where_is = (stripos(PHP_OS, 'WIN') !== false) ? 'where' : 'which';
    exec("$where_is tar", $tar);
    $path = __DIR__ . '/../../../modules/dPcim10/base';
    if ($tar) {
      $cmd = "tar -czf {$path}/cim10_gm.tar.gz -C {$this->output_base_path} ./tables.sql ./data.sql";
      exec($cmd, $result);
    }
    else {
      $zip = new ZipArchive();
      $zip->open("{$path}/cim10_gm.zip", ZipArchive::OVERWRITE);
      $zip->addFile("{$this->output_base_path}/tables.sql", 'cim10_gm/tables.sql');
      $zip->addFile($this->import_file, 'cim10_gm/data.sql');
      $zip->close();
    }

    CMbPath::remove($this->output_base_path);
  }

  /**
   * An error handler that catch the error returned by the CMbPath functions and throws an exception
   *
   * @param integer $type    The PHP error type
   * @param string  $message The error message
   *
   * @return bool
   * @throws Exception
   */
  protected function handleFileSystemError($type, $message) {
    if ($type === E_USER_WARNING) {
      throw new Exception($message);
    }
    else {
      return false;
    }
  }



}
