<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Barcode parser mostly used in pharmaceutic stocks management industry
 * Responsibilities:
 *  - analyse barcoding types and variants
 *  - parse data structure
 */
class CBarcodeParser
{
    public static $code128separator = "@";

    // http://www.ciax.com/manuals/jitsap/uccean128.htm
    public static $code128prefixes = [
        "00"   => "Serial Shipping Container Code",
        "01"   => "Shipping Container Code",
        "10"   => "Batch or Lot Number",
        "11"   => "Production Date (YYMMDD)",
        "13"   => "Packaging Date (YYMMDD)",
        "15"   => "Best Before/Sell By Date (YYMMDD)",
        "17"   => "Sell By/Expiration Date (YYMMDD)",
        "20"   => "Product Variant",
        "21"   => "Serial Number",
        "22"   => "HIBCC; quantity, date, batch, and link",
        "23"   => "Lot number",
        "240"  => "Secondary product attributes",
        "250"  => "Secondary Serial number",
        "30"   => "Quantity each",
        "310"  => "Net Weight, kilograms",
        "311"  => "Length or first dimension, meters",
        "312"  => "Width, diameter, or 2nd dimension, meters",
        "313"  => "Depth, thickness, height, or 3rd dimension, meters",
        "314"  => "Area, square meters",
        "315"  => "Volume, liters",
        "316"  => "Volume, cubic meters",
        "320"  => "Net weight, pounds",
        "330"  => "Gross weight, kilograms",
        "331"  => "Length or first dimension, meters logistics",
        "332"  => "Width, diameter, or 2nd dimension, meters logistics",
        "333"  => "Depth, thickness, height, or 3rd dimension, meters logistics",
        "334"  => "Area, square meters logistics",
        "335"  => "Gross volume, liters logistics",
        "336"  => "Gross volume, cubic meters logistics",
        "340"  => "Gross weight, pounds",
        "400"  => "Customer purchase order number",
        "410"  => "Ship to location code (EAN-13 or DUNS)",
        "411"  => "Bill to location code (EAN-13 or DUNS)",
        "412"  => "Purchase from location code (EAN-13 or DUNS)",
        "420"  => "Ship to postal code",
        "421"  => "Ship to postal code with 3-digit ISO country code",
        "8001" => "Roll products => width, length, core diameter, direction, splices",
        "8002" => "Electronic serial number for cellular telephones",
        "90"   => "FACT identifiers (internal applications)",
        "91"   => "Internal use (raw materials, packaging, components)",
        "92"   => "Internal use (raw materials, packaging, components)",
        "93"   => "Internal use (product manufacturers)",
        "94"   => "Internal use (product manufacturers)",
        "95"   => "SCAC+Carrier PRO number",
        "96"   => "SCAC+Carrier assigned container ID",
        "97"   => "Internal use (wholesalers)",
        "98"   => "Internal use (retailers)",
        "99"   => "Mutually defined text",
    ];

    public static $code128table = [
        "01"  => "scc",
        "10"  => "lot",
        "17"  => "per",
        "20"  => "var",
        "21"  => "sn",
        "30"  => "qty",
        "11"  => "prod",
        "91"  => "internal",
        "240" => "add_infos",
    ];

    private static $code39ext = [
        '%U' => 0,

        '$A' => 1,
        '$B' => 2,
        '$C' => 3,
        '$D' => 4,
        '$E' => 5,
        '$F' => 6,
        '$G' => 7,
        '$H' => 8,
        '$I' => 6,
        '$J' => 10,
        '$K' => 11,
        '$L' => 12,
        '$M' => 13,
        '$N' => 14,
        '$O' => 15,
        '$P' => 16,
        '$Q' => 17,
        '$R' => 18,
        '$S' => 19,
        '$T' => 20,
        '$U' => 21,
        '$V' => 22,
        '$W' => 23,
        '$X' => 24,
        '$Y' => 25,
        '$Z' => 26,

        '%A' => 27,
        '%B' => 28,
        '%C' => 29,
        '%D' => 30,
        '%E' => 31,
        ' '  => 32,

        '/A' => 33,
        '/B' => 34,
        '/C' => 35,
        '/D' => 36,
        '/E' => 37,
        '/F' => 38,
        '/G' => 39,
        '/H' => 40,
        '/I' => 41,
        '/J' => 42,
        '/K' => 43,
        '/L' => 44,

        '-'  => 45,
        '.'  => 46,
        '/O' => 47,

        /* 0 to 9 */

        '/Z' => 58,

        '%F' => 59,
        '%G' => 60,
        '%H' => 61,
        '%I' => 62,
        '%J' => 63,

        '%V' => 64,

        /* A to Z */

        '%K' => 91,
        '%L' => 92,
        '%M' => 93,
        '%N' => 94,
        '%O' => 95,

        '%W' => 96,

        '+A' => 97,
        '+B' => 98,
        '+C' => 99,
        '+D' => 100,
        '+E' => 101,
        '+F' => 102,
        '+G' => 103,
        '+H' => 104,
        '+I' => 105,
        '+J' => 106,
        '+K' => 107,
        '+L' => 108,
        '+M' => 109,
        '+N' => 110,
        '+O' => 111,
        '+P' => 112,
        '+Q' => 113,
        '+R' => 114,
        '+S' => 115,
        '+T' => 116,
        '+U' => 117,
        '+V' => 118,
        '+W' => 119,
        '+X' => 120,
        '+Y' => 121,
        '+Z' => 122,

        '%P' => 123,
        '%Q' => 124,
        '%R' => 125,
        '%S' => 126,
        '%T' => 127,
        '%X' => 127,
        '%Y' => 127,
        '%Z' => 127,
    ];

    /**
     * Decode a barcode with the Code39 standard
     *
     * @param string $barcode The raw barcode
     *
     * @return string Decoded string
     * @link http://fr.wikipedia.org/wiki/Code_39
     */
    static function decodeCode39($barcode)
    {
        $chars = array_map("chr", self::$code39ext);

        return strtr($barcode, $chars);
    }

    /**
     * Renders a char code39 checksum of a given string
     *
     * @param string $string String
     *
     * @return string A single char
     * @todo Rename to checksum39 ?
     */
    static function checksum($string)
    {
        $checksum = 0;
        $length   = strlen($string);
        $charset  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';

        for ($i = 0; $i < $length; ++$i) {
            $checksum += strpos($charset, $string[$i]);
        }

        return substr($charset, ($checksum % 43), 1);
    }

    /**
     * Check a code39 barcodintegrity
     *
     * @param string $barcode The raw barcode
     *
     * @return bool
     */
    static function checkCode39($barcode)
    {
        return self::checksum(substr($barcode, 0, -1)) == substr($barcode, -1);
    }

    /**
     * Parse a date trying most date formats
     *
     * @param string $date Raw date
     * @param bool   $alt  Alternate semantic in some cases
     *
     * @return string ISO date equivalent
     */
    static function parsePeremptionDate($date, $alt = false)
    {
        // dates du type 18304 >> octobre 2018 (304 = jour dans l'année)

        // YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // DD/MM/YYYY
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            return CMbDT::dateFromLocale($date);
        }

        // YYYYMM
        if (preg_match('/^(20\d{2})(\d{2})$/', $date, $parts)) {
            $date = CMbDT::date("+1 MONTH", $parts[1] . "-" . $parts[2] . "-01");

            return CMbDT::date("-1 DAY", $date);
        }

        // YYMMDD
        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $date, $parts)) {
            if ($alt) {
                $date = CMbDT::date("+1 MONTH", "20" . $parts[3] . "-" . $parts[1] . "-01");
            } else {
                $date = CMbDT::date("+1 MONTH", "20" . $parts[1] . "-" . $parts[2] . "-01");
            }

            return CMbDT::date("-1 DAY", $date);
        }

        // YYNNN
        if (preg_match('/^(\d{2})(\d{3})$/', $date, $parts)) {
            $date = CMbDT::date("+{$parts[2]} DAYS", "20{$parts[1]}-01-01");

            return CMbDT::date("-1 DAY", $date);
        }

        // MMYY
        if (preg_match('/^(\d{2})(\d{2})$/', $date, $parts)) {
            $date = CMbDT::date("+1 MONTH", "20" . $parts[2] . "-" . $parts[1] . "-01");

            return CMbDT::date("-1 DAY", $date);
        }

        return null;
    }

    /**
     * Parse a date
     *
     * @param string $date Raw date
     * @param bool   $alt  Alternate semantic in some cases
     *
     * @return string ISO date equivalent
     */
    static function parseProductionDate($date, $alt = false)
    {
        // YYMMDD
        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $date, $parts)) {
            if ($alt) {
                $date = CMbDT::date("20" . $parts[3] . "-" . $parts[1] . "-" . $parts[3]);
            } else {
                $date = CMbDT::date("20" . $parts[1] . "-" . $parts[2] . "-" . $parts[3]);
            }

            return $date;
        }

        return null;
    }

    /**
     * Main parse function, trying all barcode standards sequentially
     *
     * @param string $barcode Row barcode
     *
     * @return array Array of data
     * @todo Way too long, explode in smaller functions
     */
    static function parse($barcode)
    {
        $orig_barcode = $barcode;
        $barcode      = str_replace("    ", "\t", $barcode);

        $comp = [];

        $type = "raw";
        $patt = "";

        if (!$barcode) {
            return [
                "type" => $type,
                "comp" => $comp,
                "patt" => $patt,
            ];
        }

        $parts = [];

        // code 128 with sepataror char
        $separator = self::$code128separator;
        if (preg_match('/^[0-9a-z]+' . $separator . '[0-9a-z]+[0-9a-z\\' . $separator . ']*$/ims', $barcode)) {
            $type  = "code128";
            $parts = explode($separator, $barcode);

            foreach ($parts as $p) {
                foreach (self::$code128prefixes as $code => $text) {
                    //if (strpos($p, $code) === 0) { // strpos won't work :(
                    if (substr($p, 0, strlen($code)) == $code) {
                        $comp[self::$code128table[$code]] = substr($p, strlen($code), strlen($p) - strlen($code));
                        break;
                    }
                }
            }
        }


        //                $config = new \Lamoda\GS1Parser\Parser\ParserConfig();
        //                $parser = new \Lamoda\GS1Parser\Parser\Parser($config);
        //
        //                $validatorConfig = new \Lamoda\GS1Parser\Validator\ValidatorConfig();
        //                $validator       = new \Lamoda\GS1Parser\Validator\Validator($parser, $validatorConfig);
        //
        //                $barcode = ']C1'.$barcode;
        //                $resolution = $validator->validate($barcode);
        //
        //                if ($resolution->isValid()) {
        //                    $result = $parser->parse($barcode);
        //                    var_dump('result');
        //                    var_dump($result);
        //                } else {
        //                    var_dump('barcode');
        //                    var_dump($barcode);
        //                    var_dump($resolution->getErrors());
        //                }

        // code 128
        if (empty($comp) &&
            preg_match('/^(?:(01))(\d{14})((?:17))(\d{6})((?:21))([a-z0-9\/-]{8}?)((?:10))([a-z0-9\/-]{1,20}?)$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(10)([a-z0-9\/-]{4,20})[^a-z0-9\/-]?(17)(\d{6})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(10)([a-z0-9\/-]{8})?(17)(\d{6})(240)([a-z0-9\/-]{4,20})?$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(10)([a-z0-9\/-]{7})?(17)(\d{6})(21)([a-z0-9]{10})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(11)(\d{6})?(17)(\d{6})(10)([a-z0-9\/-]{4,20})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(10)([a-z0-9]{7})(91)([a-z0-9]{4})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(10)([a-z0-9]{8})?(20)(\d{2})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(10)([a-z0-9]{5})(21)([a-z0-9]{10})(11)([a-z0-9]{6})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(10)([a-z0-9\/-]{4,20})(20)([a-z0-9\/-]{2})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(10)([a-z0-9\/-]{8})(21)([a-z0-9\/-]{4,20})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(10)([a-z0-9\/-]{4,20})[^a-z0-9\/-]?$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(21)([a-z0-9]{6,20})(30)(\d{1,2})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(17)(\d{6})(21)([a-z0-9\/-]{6,20})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{22}))?(17)(\d{6})?(21)(\d{10})?(10)([a-z0-9]{5})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{22}))?(17)(\d{6})?(21)(\d{8})(240)([a-z0-9]{6})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{14}))?(10)([a-z0-9\/-]{4,20})?(17)(\d{6})(21)(\d{10})$/ims', $barcode, $parts) ||
            preg_match('/^(?:(01)(\d{22}))?(17)(\d{6})(21)([a-z0-9\/-]{6,20})$/ims', $barcode, $parts) ||
            preg_match('/^(01)(\d{14})$/i', $barcode, $parts)
        ) {
            $type = "code128";
            $prop = null;
            foreach ($parts as $p) {
                if (array_key_exists($p, self::$code128table)) {
                    $prop = $p;
                } else {
                    if ($prop) {
                        $comp[self::$code128table[$prop]] = $p;
                    } else {
                        $prop = null;
                    }
                }
            }
        }

        // EAN code (13 digits)
        $pattern = '/^(\d{13})$/ims';
        if (empty($comp) && preg_match($pattern, $barcode, $parts)) {
            $type        = "ean13";
            $comp["scc"] = "0{$parts[1]}";
        }

        // 2016-08
        if (empty($comp) && preg_match('/^(20\d{2})-(\d{2})$/ms', $barcode, $parts)) {
            $type        = "date";
            $date        = CMbDT::date("+1 MONTH", $parts[1] . "-" . $parts[2] . "-01");
            $comp["per"] = CMbDT::date("-1 DAY", $date);
        }

        // 130828
        /*if (empty($comp) && preg_match('/^(\d{2})(\d{2})(\d{2})$/ms', $barcode, $parts)){
          $type = "date";
          $comp = CMbDT::date("+1 MONTH", "20".$parts[1]."-".$parts[2]."-01");
          $comp = CMbDT::date("-1 DAY", $comp);
        }*/

        if (empty($comp) && $barcode[0] === "+") {
            $type    = "code39";
            $barcode = self::decodeCode39($barcode);

            //     _PER__ __LOT__
            // +$$3130331 3414899 .
            if (empty($comp) && preg_match('/^\+?\$\$[23456789](\d{6})(\d+).{2}$/ms', $barcode, $parts)) {
                $comp["per"] = $parts[1];
                $comp["lot"] = $parts[2];
            }

            //        _LOT__
            // +$$03151005377M
            //        __LOT___
            // +$$01150910199AD6
            if (empty($comp) && preg_match('/^\+?\$\$(\d{4})([A-Z0-9]+).{2}$/ms', $barcode, $parts)) {
                $comp["per"] = $parts[1];
                $comp["lot"] = $parts[2];
            }

            //       __REF___    PER_ __LOT__
            // +M423104003921/$$081309091602Y
            if (empty($comp) && preg_match('/^[a-z]\d{3}(\d+).\/\$\$(\d{4})(.+).$/ms', $barcode, $parts)) {
                $comp["ref"] = $parts[1];
                $comp["per"] = $parts[2];
                $comp["lot"] = $parts[3];
            }

            //       __REF______  ____LOT___
            // +M114EC1YHPAL2301/$1089171008M
            if (empty($comp) && preg_match('/^[a-z]\d{3}.([^\/]+)\/\$(.+).$/ms', $barcode, $parts)) {
                $comp["ref"] = $parts[1];
                $comp["lot"] = $parts[2];
            }

            //      ___REF___  PER_ __LOT___
            // +H7036307002101/1830461324862J09C
            // +H703630701210 1/1827361332390I09C
            if (empty($comp) && preg_match('/^[a-z](\d{3})(\d+.)(\d)\/(\d{5})([A-Z0-9]+)(.{4})$/ms', $barcode, $parts)) {
                $comp["ref"] = $parts[2];
                $comp["per"] = $parts[4];
                $comp["lot"] = $parts[5];
            }

            // Medacta
            //      ___REF____
            // +EMIN012654MBTL15
            if (empty($comp) && preg_match('/^eMIN(.{4,})(.{2})$/ms', $barcode, $parts)) {
                $comp["ref"] = $parts[1];
            }

            // Alcon SN60
            //      ___REF___
            // +H530SN60WF170P1W
            // +H530SN60WF230P1T
            if (empty($comp) && preg_match('/^h\d{3}SN60WF(\d{3})P\d[a-z]$/i', $barcode, $parts)) {
                $type        = "alcon";
                $comp["ref"] = "SN60WF.$parts[1]";
            }

            //      __REF__
            // +H920246020502
            //      ____REF____
            // +M412RM51100004B1D
            // +M412RM45320004C1L
            if (empty($comp) && preg_match('/^[a-z](\d{3})([A-Z0-9]+)\s?.{2}$/ms', $barcode, $parts)) {
                $comp["ref"] = $parts[2];
            }

            //  _PER_ ____LOT___
            // +1512021009296068W$
            // +1530460548095J06RE
            if (empty($comp) && preg_match('/^\+(\d{5})(\d{4,})[A-Z0-9\$]{2,5}$/ms', $barcode, $parts)) {
                $comp["per"] = self::parsePeremptionDate($parts[1], true);
                $comp["lot"] = $parts[2];
            }

            //   __SN___
            // +$11393812M  // $ or \v
            if (empty($comp) && preg_match('/^\+.(.+).{2}$/ms', $barcode, $parts)) {
                $comp["lot"] = $parts[1];
            }
        }

        //  __REF______ __LOT___
        // EC1YHPAL20011964210120813E
        if (empty($comp) && preg_match('/^[A-Z](C1YHPAL\d{4})(\d{9})(\d{4})[A-Z]$/ms', $barcode, $parts)) {
            $comp["ref"] = $parts[1];
            $comp["lot"] = $parts[2];
            $comp["per"] = $parts[3];
        }

        // __LOT___ _SN__
        // 09091602/00736
        if (empty($comp) && preg_match('/^(\d{8})\/(\d{5})$/', $barcode, $parts)) {
            $type        = "unknown";
            $comp["lot"] = $parts[1];
        }

        // _PER__ _LOT__
        // 032015 A00798
        if (empty($comp) && preg_match('/^(\d{6})([A-Z]\d{5})$/', $barcode, $parts)) {
            $type        = "code39";
            $comp["per"] = self::parsePeremptionDate($parts[1], true);
            $comp["lot"] = $parts[2];
        }

        //    _REF__ _LOT__
        // SEM241320^P32072L
        if (empty($comp) && preg_match('/^SEM(\d{6,8})\^(P\d+)[A-Z]$/', $barcode, $parts)) {
            $type        = "sem";
            $comp["ref"] = $parts[1];
            $comp["lot"] = $parts[2];
        }

        // CIP
        if (empty($comp) && preg_match('/^([3569]\d{6})$/', $barcode, $parts)) {
            $type        = "cip";
            $comp["cip"] = $parts[1];
        }

        // Medicament
        if (empty($comp) && preg_match('/^([2459])(\d{7})(\d{6})(0[01])$/', $barcode, $parts)) {
            $type          = "med";
            $comp["remb"]  = $parts[1];
            $comp["cip"]   = $parts[2];
            $comp["price"] = $parts[3];
            $comp["key"]   = $parts[4];
        }

        // Arthrex specific
        // REF : PAR-1934BF-2   >>  AR-1934BF-2  (without leading P)
        if (empty($comp) && preg_match('/^P(AR-[A-Z0-9-]+)$/', $barcode, $parts)) {
            $type        = "arthrex";
            $comp["ref"] = $parts[1];
        }
        // LOT : T314998   >>  314998  (without leading T)
        if (empty($comp) && preg_match('/^T(\d{4,9})$/', $barcode, $parts)) {
            $type        = "arthrex";
            $comp["lot"] = $parts[1];
        }
        // QTY : Q1   >>  1  (without leading T)
        if (empty($comp) && preg_match('/^Q(\d{1})$/', $barcode, $parts)) {
            $type        = "arthrex";
            $comp["qty"] = $parts[1];
        }

        // Physiol
        // __REF___ __SN__ __STE_ _ __PER_ _
        // 28081230 053653 100609 1 130630 1
        if (empty($comp) && preg_match('/^(\d{4}[012]\d{3})(\d{6})([0123]\d[01]\d\d\d)\d([0123]\d[01]\d\d\d)\d$/', $barcode, $parts)) {
            $type        = "physiol";
            $comp["ref"] = $parts[1];
            $comp["sn"]  = $parts[2];
            $comp["per"] = $parts[4];
        }

        // Invent (Karl Zeiss)
        //             _REF__
        // +M303INVENT-ZO24.0124
        if (empty($comp) && preg_match('/^[a-z]\d{3}INVENT-ZO(\d{2}\.\d)\d{3}$/i', $barcode, $parts)) {
            $type        = "karl_zeiss";
            $comp["ref"] = "invent zo +$parts[1]";
        }

        // Quatuorevo
        // QUATUOREVO +15.0 1199914028 2016-08
        if (empty($comp) && preg_match('/^(QUATUOREVO \+\d{2}\.[05]) (\d{10}) (\d{4}-\d{2})$/ms', $barcode, $parts)) {
            $comp["ref"] = $parts[1];
            $comp["lot"] = $parts[2];
            $comp["per"] = $parts[3];
        }

        //  ___LOT__
        // S12345678
        if (empty($comp) && preg_match('/^(S\d{8})$/', $barcode, $parts)) {
            $type        = "medicalens";
            $comp["lot"] = $parts[1];
        }

        //   ___SN__
        // SN0123456
        if (empty($comp) && preg_match('/^SN(\d{7})$/', $barcode, $parts)) {
            $type       = "sn";
            $comp["sn"] = $parts[1];
        }

        // Mediboard
        //   ___ID___
        // MB01234567
        if (empty($comp) && preg_match('/^MB(\d{8})$/', $barcode, $parts)) {
            $type       = "mb";
            $comp["id"] = intval($parts[1]);
        }

        // final process
        if (isset($comp["per"])) {
            $comp["per"] = self::parsePeremptionDate($comp["per"]);
        }

        if (isset($comp["prod"])) {
            $comp["prod"] = self::parseProductionDate($comp["prod"]);
        }

        if (isset($comp["scc"])) {
            preg_match('/\d{3}(\d{5})(\d{5})\d/', $comp["scc"], $parts);
            $comp["scc_manuf"] = $parts[1];
            $comp["scc_part"]  = $parts[2];
            $comp["scc_prod"]  = $parts[1] . $parts[2];
        }

        if (isset($comp["sn"]) && empty($comp["lot"])) {
            $comp["lot"] = $comp["sn"];
        }

        $comp["raw"] = $orig_barcode;

        $comp += [
            "raw"       => null,
            "ref"       => null,
            "lot"       => null,
            "per"       => null,
            "sn"        => null,
            "scc"       => null,
            "scc_manuf" => null,
            "scc_prod"  => null,
            "scc_part"  => null,
            "remb"      => null,
            "cip"       => null,
            "price"     => null,
            "key"       => null,
            "qty"       => null,
        ];

        return [
            "type" => $type,
            "comp" => $comp,
        ];
    }
}
