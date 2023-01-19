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
use DOMXPath;
use Exception;
use Ox\Cli\MediboardCommand;
use Ox\Core\FileUtil\CDFMObject;
use PDO;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ORacle dump reader
 *
 * https://docs.oracle.com/cd/B19306_01/appdev.102/b14250/oci03typ.htm
 */
class OracleDumpReader extends MediboardCommand
{
    const EXPORT_HEADER      = "\x03\x00\x1F";
    const EXPORT_TABLE_START = "TABLE \"";
    const EXPORT_TABLE_END   = "ENDTABLE";

    const EXPDP_HEADER      = "\x01\x01\x4C";
    const EXPDP_TABLE_START = "\x00\x2E<?xml version=";
    const EXPDP_TABLE_END   = "</ROWSET>";

    protected $handle;
    protected $filesize;
    protected $filename;
    protected $type;

    protected $input;

    /** @var OutputInterface */
    protected $output;

    // https://docs.oracle.com/cd/B10501_01/appdev.920/a96583/cci04typ.htm#1005128
    const TYPE_VARCHAR2 = 0x01;
    const TYPE_NUMBER   = 0x02;
    const TYPE_LONG     = 0x08;
    const TYPE_DATE     = 0x0C;
    const TYPE_LONG_RAW = 0x18;
    const TYPE_CLOB     = 0x70;
    const TYPE_BLOB     = 0x71;

    /** @var string */
    public $version;

    public $data = [];

    protected $endReached = false;

    /** @var CDFMObject[] */
    protected $containerStack = [];

    protected $tablesDefs = [];
    protected $currentTable;


    /**
     * @see parent::configure()
     */
    protected function configure()
    {
        $this
            ->setName('db:oradump')
            ->setDescription('Read an Oracle dump file')
            ->addArgument(
                'inputfile',
                InputArgument::REQUIRED,
                'Input dump file'
            )
            ->addOption(
                'action',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Action to do',
                "getschema"
            )
            ->addOption(
                'database',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Database path, in the form "mysql:host=hostname;dbname=dbname"',
                "mysql:host=localhost;dbname=oracledb"
            )
            ->addOption(
                'username',
                'u',
                InputOption::VALUE_OPTIONAL,
                'Database username',
                'root'
            )
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Database password'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit to N megabytes'
            )
            ->addOption(
                'defs',
                null,
                InputOption::VALUE_OPTIONAL,
                'File definition'
            )
            ->addOption(
                'tables',
                't',
                InputOption::VALUE_OPTIONAL,
                'Tables list, separated by commas'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output directory (CSV)'
            );
    }

    /**
     * @throws Exception
     * @see parent::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = $output;

        $filename = $input->getArgument('inputfile');
        $action   = $input->getOption('action');
        $limit    = $input->getOption('limit');
        $defs     = $input->getOption('defs');
        $db       = $input->getOption('database');
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        $tables   = $input->getOption('tables');
        $output   = $input->getOption('output');

        $this->filename = $filename;

        if (!file_exists($filename)) {
            throw new Exception("File not found: $filename");
        }

        $this->filesize = filesize($filename);
        $this->handle   = fopen($filename, "rb");

        $header = $this->readString(3);

        if ($header === self::EXPORT_HEADER) {
            $this->version = $this->readString(16);
            $this->type    = "EXPORT";
        } elseif ($header === self::EXPDP_HEADER) {
            $this->type = "EXPDP";
        } else {
            throw new Exception(
                "Wrong file format, expected '" . self::EXPDP_HEADER . "' or '" . self::EXPDP_HEADER . "' in header"
            );
        }

        switch ($action) {
            default:
            case "getschema":
                $defs = $filename . ".defs";
                if ($this->type === "EXPORT") {
                    $tables = $this->readTableDefs();
                } else {
                    $tables = $this->readTableDefsExpdp();
                }

                file_put_contents($defs, json_encode($tables, JSON_PRETTY_PRINT));
                break;

            case "import":
                if ($defs) {
                    $all_tables = $this->setTableDefs(json_decode(file_get_contents($defs), true));
                } else {
                    //$all_tables = $this->readTableDefs(1024 * 1024 * $limit);
                    $all_tables = $this->readTableDefs();
                }

                if ($tables) {
                    $tables = explode(",", $tables);
                } else {
                    $tables = array_keys($all_tables);
                }

                if ($output) {
                    $sep = ";";

                    foreach ($tables as $_table) {
                        if (!isset($this->tablesDefs[$_table])) {
                            $this->output->writeln("Table unknown: $_table");
                            continue;
                        }

                        $this->output->writeln("Go to table '$_table'");

                        if ($this->tablesDefs[$_table]["columns"] == false) {
                            $this->output->writeln("No column !");
                            continue;
                        }

                        $this->gotToTable($_table);

                        $f = fopen("$output/$_table.csv", "w");
                        fputcsv($f, array_keys($this->tablesDefs[$_table]["columns"]), $sep);

                        while (false !== ($row = $this->readRow())) {
                            fputcsv($f, $row, $sep);
                        }

                        fclose($f);
                    }
                } else {
                    $pdo = new PDO($db, $username, $password);

                    foreach ($tables as $_table) {
                        if (!isset($this->tablesDefs[$_table])) {
                            $this->output->writeln("Table unknown: $_table");
                            continue;
                        }

                        $this->output->writeln("Go to table '$_table'");

                        if ($this->tablesDefs[$_table]["columns"] == false) {
                            $this->output->writeln("No column !");
                            continue;
                        }

                        $this->gotToTable($_table);

                        try {
                            $pdo->query("TRUNCATE TABLE $_table");

                            $n = 1000;

                            $data = [];
                            $i    = 0;

                            while (false !== ($row = $this->readRow())) {
                                $data[] = $row;

                                $i++;

                                if (count($data) == $n) {
                                    $this->insertMulti($pdo, $_table, $data, 100);
                                    $this->output->writeln("Row #$i written");

                                    $data = [];
                                }
                            }

                            if (count($data)) {
                                $this->insertMulti($pdo, $_table, $data, 100);
                                $this->output->writeln("Row #$i written");
                            }
                        } catch (Exception $e) {
                            $this->output->writeln($e->getMessage());
                        }
                    }
                }

                break;
        }

        return self::SUCCESS;
    }

    /**
     * Insert data into the table, by chunks
     *
     * @param PDO    $pdo   PDO data source
     * @param string $table Table name
     * @param array  $data  Data to insert
     * @param int    $step  Chunk size
     *
     * @return void
     * @throws Exception
     */
    function insertMulti(PDO $pdo, $table, $data, $step)
    {
        $counter = 0;

        $keys   = array_keys(reset($data));
        $fields = "`" . implode("`, `", $keys) . "`";

        $count_data = count($data);
        $query      = "";

        foreach ($data as $_data) {
            if ($counter % $step == 0) {
                $query   = "INSERT INTO `$table` ($fields) VALUES ";
                $queries = [];
            }

            $_query = [];
            foreach ($_data as $_value) {
                if ($_value === null) {
                    $_query[] = "NULL";
                } else {
                    $_query[] = "'" . addslashes($_value) . "'";
                }
            }

            $queries[] = "(" . implode(", ", $_query) . ")";

            $counter++;

            if ($counter % $step == 0 || $counter == $count_data) {
                $query .= implode(",", $queries);
                $query .= ";";

                if (!$pdo->query($query)) {
                    $error = $pdo->errorInfo();
                    throw new Exception($error[2], $error[1]);
                }
            }
        }
    }

    /**
     * Read all table definitions
     *
     * @param int $upto Read N chars
     *
     * @return array
     */
    function readTableDefsExpdp($upto = null)
    {
        $this->output->writeln(sprintf("%16s | Table name", "Offset"));
        $this->output->writeln(str_repeat("-", 36));

        $table_prefix = self::EXPDP_TABLE_START;

        $len = strlen($table_prefix);

        $previous_table = null;

        $h = $this->handle;

        $info_step  = 1024 * 1024 * 50;
        $next_step  = $info_step;
        $first_char = $table_prefix[0];

        $this->seek(4096);

        while (($buffer = fread($h, 4096)) !== false) {
            if (strpos($buffer, $table_prefix) === 0) {
                $this->readTableInfoExpdp($previous_table, $buffer);
                continue;
            }

            $pos = $this->pos();
            if ($pos > $next_step) {
                $this->output->writeln(($next_step / (1024 * 1024)) . " MB processed");
                $next_step += $info_step;
            }
        }

        return $this->tablesDefs;
    }

    /**
     * Read all table definitions
     *
     * @param int $upto Read N chars
     *
     * @return array
     */
    function readTableDefs($upto = null)
    {
        $this->output->writeln(sprintf("%16s | Table name", "Offset"));
        $this->output->writeln(str_repeat("-", 36));

        $table_prefix = $this->type === "EXPORT" ? self::EXPORT_TABLE_START : self::EXPDP_TABLE_START;

        $len = strlen($table_prefix);

        $previous_table = null;

        $h = $this->handle;

        $info_step = 1024 * 1024 * 10;
        $next_step = $info_step;

        while (($buffer = fgets($h, 50)) !== false) {
            if (strpos($buffer, $table_prefix) === 0) {
                $this->readTableInfo($previous_table, substr($buffer, $len, -2));
                continue;
            }

            $pos = $this->pos();
            if ($pos > $next_step) {
                $this->output->writeln(($next_step / (1024 * 1024)) . " MB processed");
                $next_step += $info_step;
            }
        }

        return $this->tablesDefs;
    }

    function readTableInfo(&$previous_table, $table_name)
    {
        $previous_end = $this->pos() - (strlen(self::EXPORT_TABLE_START) + 1);

        $this->output->writeln(sprintf("%16s | %s", number_format($this->pos(), 0, ".", " "), $table_name));

        // Create table
        $create_table = $this->readUpToChar("\x0A");

        // Insert into
        $insert_into = $this->readUpToChar("\x0A");
        preg_match_all('/(?:\"(\w+)\")+/', $insert_into, $matches);
        $columns = array_slice($matches[1], 1);

        $table_def = $this->readTableDefinition();

        if ($table_def && $columns) {
            $table_def = array_combine($columns, $table_def);

            $this->tablesDefs[$table_name] = [
                "position" => $this->pos(),
                "end"      => null,
                "columns"  => $table_def,
                "create"   => $create_table,
                "insert"   => $insert_into,
            ];

            if ($previous_table) {
                $this->tablesDefs[$previous_table]["end"] = $previous_end;
            }

            $previous_table = $table_name;
        }
    }

    function readTableInfoExpdp(&$previous_table, $buffer)
    {
        $pos = strrpos($buffer, self::EXPDP_TABLE_END);
        if ($pos !== false) {
            $xml = substr($buffer, 2, $pos + strlen(self::EXPDP_TABLE_END) - 2);
        } else {
            while (($_buffer = fread($this->handle, 4096)) !== false) {
                $buffer .= $_buffer;

                $pos = strrpos($buffer, self::EXPDP_TABLE_END);
                if ($pos !== false) {
                    $xml = substr($buffer, 2, $pos + strlen(self::EXPDP_TABLE_END) - 2);
                    break;
                }
            }
        }

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);

        $table_name = $xpath->query("//STRMTABLE_T/NAME")->item(0)->nodeValue;

        $this->output->writeln(sprintf("%16s | %s", number_format($this->pos(), 0, ".", " "), $table_name));

        /** @var DOMElement[] $columns */
        $columns = $xpath->query("//STRMTABLE_T/COL_LIST/COL_LIST_ITEM");

        $table_def = [];

        foreach ($columns as $_column) {
            $_col_name             = $xpath->query("NAME", $_column)->item(0)->nodeValue;
            $table_def[$_col_name] = [
                "type"   => $xpath->query("TYPE_NUM", $_column)->item(0)->nodeValue,
                "params" => $xpath->query("LENGTH", $_column)->item(0)->nodeValue,
            ];
        }

        $this->tablesDefs[$table_name] = [
            "position" => $this->pos(),
            "end"      => null,
            "columns"  => $table_def,
            //"create"   => $create_table,
            //"insert"   => $insert_into,
        ];

        $this->tablesDefs[$table_name];
    }

    function setTableDefs($defs)
    {
        return $this->tablesDefs = $defs;
    }

    /**
     * Read table definition
     *
     * @return array
     */
    function readTableDefinition()
    {
        $count = $this->readUByte();
        $this->skip(1);
        $columns = [];

        for ($i = 0; $i < $count; $i++) {
            $t      = $this->readUByte();
            $type   = null;
            $params = null;

            switch ($t) {
                case self::TYPE_NUMBER:
                    $type = "NUMBER";
                    $this->skip(1);
                    $params = [dechex($this->readUByte()), $this->readUByte()];
                    break;

                case self::TYPE_VARCHAR2:
                    $type = "VARCHAR2";
                    $this->skip(1);
                    $params = [$this->readUByte()];
                    $this->skip(1);

                    $this->skip(4); // skip ?
                    break;

                case self::TYPE_DATE:
                    $type = "DATE";
                    $this->skip(1);
                    $params = [$this->readUByte()];
                    $this->skip(1);
                    break;

                case self::TYPE_LONG:
                    $type = "LONG";
                    $this->skip(3);
                    break;

                case self::TYPE_LONG_RAW:
                    $type = "LONG RAW";
                    $this->skip(3);
                    break;

                case self::TYPE_CLOB:
                    $type = "CLOB";
                    $this->skip(1);
                    $params = [$this->readUByte()];
                    $this->skip(1);

                    $this->skip(4); // skip ?
                    break;

                case self::TYPE_BLOB:
                    $type = "BLOB";
                    $this->skip(1);
                    $params = [$this->readUByte()];
                    $this->skip(1);
                    break;

                default:
                    $this->skip(3);

                    $this->output->writeln("$t UNKNOWN, pos=" . $this->pos());
                    break;
            }

            $column = [
                "type"   => $type,
                "params" => $params,
            ];

            $columns[] = $column;
        }

        $this->skip(4); // 00 00 00 00

        return $columns;
    }

    /**
     * Read until we fond the char
     *
     * @param string $last_char Char to read up to
     *
     * @return string
     */
    function readUpToChar($last_char)
    {
        $buffer = "";
        while (false !== ($c = $this->readByte())) {
            $buffer .= $c;

            if ($c === $last_char) {
                return $buffer;
            }
        }

        return $buffer;
    }

    /**
     * Move the stream pointer
     *
     * @param int $pos the position
     *
     * @return void
     */
    function seek($pos)
    {
        fseek($this->handle, $pos, SEEK_SET);
    }

    /**
     * Tell the current position in the file
     *
     * @return int
     */
    function pos()
    {
        return ftell($this->handle);
    }

    /**
     * Tell if end is reached
     *
     * @return bool
     */
    function isEndReached()
    {
        return $this->endReached || $this->pos() >= $this->filesize - 1;
    }

    /**
     * Read a byte and rewind to the position before the read
     *
     * @return int
     */
    function mungeByte()
    {
        $return = $this->readUByte();
        $this->skip(-1);

        return $return;
    }

    /**
     * Read a Pascal string (length followed wy the string)
     *
     * @return string
     */
    function readPascalString()
    {
        $l = $this->readUByte();

        if ($l == 0) {
            return "";
        }

        return $this->readString($l);
    }

    /**
     * Close file handle
     *
     * @return void
     */
    function close()
    {
        fclose($this->handle);
    }

    /**
     * Read a string
     *
     * @param int $length Size of the string to read
     *
     * @return string
     */
    function read($length)
    {
        return fread($this->handle, $length);
    }

    /**
     * Skip N bytes
     *
     * @param int $length Size of the jump
     *
     * @return void
     */
    function skip($length)
    {
        fseek($this->handle, $length, SEEK_CUR);
    }

    /**
     * Read a byte
     *
     * @return string
     */
    function readByte()
    {
        return fgetc($this->handle);
    }

    /**
     * Read unsiged byte
     *
     * @return int
     */
    function readUByte()
    {
        $d = $this->readByte();

        if ($d === false || $d === "") {
            return false;
        }

        $d = unpack("C", $d);

        return $d[1];
    }

    /**
     * Read string from file
     *
     * @param int $length The length of the string to read
     *
     * @return string
     */
    function readString($length)
    {
        $d = unpack("A*", $this->read($length));

        return $d[1];
    }

    /**
     * Read unsigned 16 bits numbers
     *
     * @return integer
     */
    function readUInt16()
    {
        $tmp = unpack("v", $this->read(2));

        return $tmp[1];
    }

    /**
     * Read 16 bits numbers.
     *
     * @return integer
     */
    function readInt16()
    {
        $int = $this->readUInt16();

        if ($int >= 0x8000) {
            $int -= 0x10000;
        }

        return $int;
    }

    /**
     * Read unsigned 32 bits numbers
     *
     * @return integer
     */
    function readUInt32()
    {
        $tmp = unpack("V", $this->read(4));

        return $tmp[1];
    }

    /**
     * Jump to the table data
     *
     * @param string $table_name Table name
     *
     * @return bool
     */
    function gotToTable($table_name)
    {
        if (!isset($this->tablesDefs[$table_name])) {
            return false;
        }

        $this->seek($this->tablesDefs[$table_name]["position"]);

        $this->currentTable = $table_name;

        return true;
    }

    /**
     * Read a NUMBER value
     *
     * @return int|float|null
     */
    function readNUMBER()
    {
        $size = $this->readUInt16();

        if ($size == 0xFF || $size == 0xFFFE) {
            return null;
        }

        $first_byte = $this->readUByte();
        $size--;

        if ($first_byte == 128) {
            return 0;
        }

        $positive = !!($first_byte & 0x80);

        $exponent = $first_byte & 0x7F;
        $exponent = $positive ? ($exponent - 65) : (128 - 65 - $exponent);

        $mantissa = "";

        $n = $size;
        while ($n-- > 0) {
            $byte = $this->readUByte();

            if ($positive) {
                $byte = $byte - 1;
            } else {
                $byte = 101 - $byte;
            }

            $byte = sprintf("%02d", $byte);

            $mantissa .= $byte;
        }

        return $mantissa * pow(100, $exponent - ($size - 1));
    }

    /**
     * Read a DATE
     *
     * @return null|string
     */
    function readDATE()
    {
        // https://docs.oracle.com/cd/B10501_01/appdev.920/a96583/cci04typ.htm#1015765
        $l = $this->readUInt16();

        if ($l == 0xFFFE) {
            return null;
        }

        $century = $this->readUByte();
        $year    = $this->readUByte();

        if ($century != 0) {
            $century -= 100;
        }

        if ($year != 0) {
            $year -= 100;
        }

        return sprintf(
            "%04d-%02d-%02d %02d:%02d:%02d",
            $century * 100 + $year,
            $this->readUByte(),
            $this->readUByte(),

            $this->readUByte() - 1,
            $this->readUByte() - 1,
            $this->readUByte() - 1
        );
    }

    /**
     * Read a LONG string
     *
     * @return null|string
     */
    function readLONG()
    {
        $l = $this->readUInt16();


        if ($l == 0xFFFE) {
            return null;
        }

        if ($l == 0) {
            return "";
        }

        if ($l == 0xFFFD) {
            $s = "";
            $l = $this->readUInt16();

            do {
                if ($l == 0) {
                    break;
                }

                $s .= $this->read($l);
            } while ($l = $this->readUInt16());

            return $s;
        }

        return $this->read($l);
    }

    /**
     * Read a VARCHAR2 string
     *
     * @return null|string
     */
    function readVARCHAR2()
    {
        $l = $this->readUInt16();

        if ($l == 0xFFFE) {
            return null;
        }

        if ($l == 0) {
            return "";
        }

        return $this->readString($l);
    }

    /**
     * Read a row
     *
     * @return array|bool
     */
    function readRow()
    {
        $def = $this->tablesDefs[$this->currentTable];

        $end_reached = $this->read(2);

        if ($end_reached == "\xFF\xFF") {
            return false;
        }

        $this->skip(-2);
        $debug = false;

        $data = [];
        foreach ($def["columns"] as $_name => $column) {
            $value = null;
            $pos   = $debug ? $this->pos() : null;

            switch ($column["type"]) {
                case "NUMBER":
                    $value = $this->readNUMBER();
                    break;

                case "VARCHAR2":
                    $value = $this->readVARCHAR2();
                    break;

                case "DATE":
                    $value = $this->readDATE();
                    break;

                case "LONG":
                case "LONG RAW":
                    $value = $this->readLONG();
                    break;

                /*case "BLOB":
                  $value = $this->readBLOB();
                  break;

                case "CLOB":
                  $value = $this->readCLOB();
                  break;*/

                default:
                    $this->output->writeln("Type unread: " . $column["type"] . ", pos=" . $this->pos());
            }

            if ($debug) {
                $data[$_name] = [$pos, $value];
            } else {
                $data[$_name] = $value;
            }
        }

        $this->skip(2); // 00 00

        return $data;
    }

    /**
     * Read the whole table into an array
     *
     * @param string $table_name Table name
     * @param int    $count      Get only the first $count rows, null if all the table
     *
     * @return array
     */
    function readTable($table_name, $count = 5)
    {
        if (!isset($this->tablesDefs[$table_name])) {
            return null;
        }

        $this->gotToTable($table_name);

        $data = [];
        $n    = $count;

        while (false !== ($row = $this->readRow())) {
            $data[] = $row;

            if ($count && $n-- <= 0) {
                break;
            }
        }

        return $data;
    }

    /**
     * Get table definition
     *
     * @param string $table_name Table name
     *
     * @return array
     */
    function getTableDef($table_name)
    {
        if (!isset($this->tablesDefs[$table_name])) {
            return null;
        }

        return $this->tablesDefs[$table_name];
    }
}
