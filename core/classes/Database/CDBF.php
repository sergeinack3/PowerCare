<?php

namespace Ox\Core\Database;

/**
 * DBF reader Class v0.04  by Faro K Rasyid (Orca)
 * orca75_at_dotgeek_dot_org
 * v0.05 by Nicholas Vrtis
 * vrtis_at_vrtisworks_dot_com
 * 1) changed to not read in complete file at creation.
 * 2) added function to read individual rows
 * 3) added support for Memo fields in dbt files.
 * 4) See: http://www.clicketyclick.dk/databases/xbase/format/dbf.html#DBF_STRUCT
 * for some additional information on XBase structure...
 * 5) NOTE: the whole file (and the memo file) is read in at once.  So this could
 * take a lot of memory for large files.
 *
 * Input    : name of the DBF( dBase III plus) file
 * Output  :  - dbf_num_rec, the number of records
 * - dbf_num_field, the number of fields
 * - dbf_names, array of field information ('name', 'len', 'type')
 *
 * Usage  example:
 * $file= "your_file.dbf";//WARNING !!! CASE SENSITIVE APPLIED !!!!!
 * $dbf = new dbf_class($file);
 * $num_rec=$dbf->dbf_num_rec;
 * $num_field=$dbf->dbf_num_field;
 *
 * for($i=0; $i<$num_rec; $i++){
 * $row = $dbf->getRow($i);
 * for($j=0; $j<$num_field; $j++){
 * echo $row[$j].' ');
 * }
 * echo('<br>');
 * }
 *
 * Thanks to :
 * - Willy
 * - Miryadi
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU  Lesser General Public License for more details.
 */
class CDBF
{
    /** @var float|int Number of records in the file */
    public $dbf_num_rec;
    /** @var false|float Number of columns in each row */
    public $dbf_num_field;
    /** @var array Information on each column ['name'],['len'],['type'] */
    public $dbf_names = [];   //Information on each column ['name'],['len'],['type']

    /** @var false|string The raw input file */
    private $_raw;
    /** @var float|int Length of each row */
    private $_rowsize;
    /** @var float|int Length of the header information (offset to 1st record) */
    private $_hdrsize;
    /** @var false|string The raw memo file (if there is one). */
    private $_memos;

    public function __construct(string $filename)
    {
        if (!file_exists($filename)) {
            echo 'Not a valid DBF file !!!';
            exit;
        }
        $tail = substr($filename, -4);
        if (strcasecmp($tail, '.dbf') != 0) {
            echo 'Not a valid DBF file !!!';
            exit;
        }

        //Read the File
        $handle = fopen($filename, "r");
        if (!$handle) {
            echo "Cannot read DBF file";
            exit;
        }
        $filesize   = filesize($filename);
        $this->_raw = fread($handle, $filesize);
        fclose($handle);
        //Make sure that we indeed have a dbf file...
        if (!(ord($this->_raw[0]) == 3 || ord($this->_raw[0]) == 131) && ord($this->_raw[$filesize]) != 26) {
            echo 'Not a valid DBF file !!!';
            exit;
        }
        // 3= file without DBT memo file; 131 ($83)= file with a DBT.
        $arrHeaderHex = [];
        for ($i = 0; $i < 32; $i++) {
            $arrHeaderHex[$i] = str_pad(dechex(ord($this->_raw[$i])), 2, "0", STR_PAD_LEFT);
        }
        //Initial information
        $line = 32;//Header Size
        //Number of records
        $this->dbf_num_rec = hexdec($arrHeaderHex[7] . $arrHeaderHex[6] . $arrHeaderHex[5] . $arrHeaderHex[4]);
        $this->_hdrsize    = hexdec($arrHeaderHex[9] . $arrHeaderHex[8]);//Header Size+Field Descriptor
        //Number of fields
        $this->_rowsize      = hexdec($arrHeaderHex[11] . $arrHeaderHex[10]);
        $this->dbf_num_field = floor(($this->_hdrsize - $line) / $line);//Number of Fields

        //Field properties retrieval looping
        for ($j = 0; $j < $this->dbf_num_field; $j++) {
            $name = '';
            $beg  = $j * $line + $line;
            for ($k = $beg; $k < $beg + 11; $k++) {
                if (ord($this->_raw[$k]) != 0) {
                    $name .= $this->_raw[$k];
                }
            }
            $this->dbf_names[$j]['name'] = $name;//Name of the Field
            $this->dbf_names[$j]['len']  = ord($this->_raw[$beg + 16]);//Length of the field
            $this->dbf_names[$j]['type'] = $this->_raw[$beg + 11];
        }
        if (ord($this->_raw[0]) == 131) { //See if this has a memo file with it...
            //Read the File
            $tail = substr($tail, -1, 1);   //Get the last character...
            if ($tail == 'F') {            //See if upper or lower case
                $tail = 'T';              //Keep the case the same
            } else {
                $tail = 't';
            }
            $memoname = substr($filename, 0, strlen($filename) - 1) . $tail;
            $handle   = fopen($memoname, "r");
            if (!$handle) {
                echo "Cannot read DBT file";
                exit;
            }
            $filesize     = filesize($memoname);
            $this->_memos = fread($handle, $filesize);
            fclose($handle);
        }
    }

    public function getRow(int $recnum): array
    {
        $recnum = intval($recnum);

        $memoeot = chr(26) . chr(26);
        $rawrow  = substr($this->_raw, $recnum * $this->_rowsize + $this->_hdrsize, $this->_rowsize);
        $rowrecs = [];
        $beg     = 1;
        if (ord($rawrow[0]) == 42) {
            return [];   //Record is deleted...
        }
        for ($i = 0; $i < $this->dbf_num_field; $i++) {
            $col = trim(substr($rawrow, $beg, $this->dbf_names[$i]['len']));

            if ($this->dbf_names[$i]['type'] != 'M') {
                $rowrecs[] = $col;
            } else {
                $memobeg   = intval($col) * 512;  //Find start of the memo block (0=header so it works)
                $memoend   = strpos($this->_memos, $memoeot, $memobeg);   //Find the end of the memo
                $rowrecs[] = substr($this->_memos, $memobeg, $memoend - $memobeg);
            }
            $beg += $this->dbf_names[$i]['len'];
        }

        return $rowrecs;
    }

    /*public function getRowAssoc(int $recnum): array
    {
        $rawrow  = substr($this->_raw, $recnum * $this->_rowsize + $this->_hdrsize, $this->_rowsize);
        $rowrecs = [];
        $beg     = 1;
        if (ord($rawrow[0]) == 42) {
            return false;   //Record is deleted...
        }

        for ($i = 0; $i < $this->dbf_num_field; $i++) {
            $col = trim(substr($rawrow, $beg, $this->dbf_names[$i]['len']));
            if ($this->dbf_names[$i]['type'] != 'M') {
                $rowrecs[$this->dbf_names[$i]['name']] = $col;
            } else {
                //Find start of the memo block (0=header so it works)
                $memobeg                               = $col * 512;
                //Find the end of the memo
                $memoend                               = strpos($this->_memos, $memoeot, $memobeg);
                $rowrecs[$this->dbf_names[$i]['name']] = substr($this->_memos, $memobeg, $memoend - $memobeg);
            }
            $beg += $this->dbf_names[$i]['len'];
        }

        return $rowrecs;
    }*/
}
