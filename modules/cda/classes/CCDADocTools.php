<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;


use Ox\Core\CMbObject;
use Ox\Core\CPerson;
use Ox\Interop\Cda\Components\Meta\CDAMetaAddress;
use Ox\Interop\Cda\Components\Meta\CDAMetaTelecom;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClinicalDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;

class CCDADocTools
{

    /**
     * Set element text in section
     *
     * @param CCDAClasseCda $element section
     * @param string        $content content
     *
     * @return object
     */
    public static function setText($element, string $content)
    {
        $text = new CCDAED();
        $text->setData($content);
        $element->setText($text);

        return $element;
    }

    /**
     * @param object $element
     * @param string $reference
     *
     * @return object
     */
    public static function setTextWithReference($element, string $reference)
    {
        $text_observation           = new CCDAED();
        $text_reference_observation = new CCDATEL();
        $text_reference_observation->setValue($reference);
        $text_observation->setReference($text_reference_observation);
        $element->setText($text_observation);

        return $element;
    }

    /**
     * Add value element and originalText element
     *
     * @param object      $element               element
     * @param string      $content_original_text value
     * @param string|null $code_value
     * @param string|null $displayName
     * @param string|null $codeSystem
     * @param string|null $codeSystemName
     *
     * @return object
     */
    public static function addValueOriginalText(
        $element,
        ?string $content_original_text,
        ?string $code_value = null,
        ?string $displayName = null,
        ?string $codeSystem = null,
        ?string $codeSystemName = null
    ) {
        $code = self::prepareCodeCD($code_value, $codeSystem, $displayName, $codeSystemName);
        if ($content_original_text) {
            $text_observation           = new CCDAED();
            $text_reference_observation = new CCDATEL();
            $text_reference_observation->setValue($content_original_text);
            $text_observation->setReference($text_reference_observation);
            $code->setOriginalText($text_observation);
        }

        $element->appendValue($code);

        return $element;
    }

    /**
     * @param             $object
     * @param CPerson     $person
     * @param CCDAFactory $factory
     */
    public static function setAddress($object, $person, CCDAFactory $factory = null): void
    {
        if (!method_exists($object, 'appendAddr')) {
            return;
        }

        $address = (new CDAMetaAddress($factory, $person))->build();
        $object->appendAddr($address);
    }

    /**
     * @param             $object
     * @param CPerson     $person
     * @param CCDAFactory $factory
     */
    public static function setTelecom($object, CPerson $person, CCDAFactory $factory = null): void
    {
        if (!method_exists($object, 'appendTelecom')) {
            return;
        }

        $telecoms = array_filter(
            [
                CDAMetaTelecom::TYPE_TELECOM_EMAIL  => $person->_p_email,
                CDAMetaTelecom::TYPE_TELECOM_MOBILE => $person->_p_mobile_phone_number,
                CDAMetaTelecom::TYPE_TELECOM_TEL    => $person->_p_phone_number,
            ]
        );

        $telecoms = $telecoms ?: [CDAMetaTelecom::TYPE_TELECOM_TEL => null];

        foreach ($telecoms as $type => $value) {
            $telecom = (new CDAMetaTelecom($factory, $person, $type))->build();
            $object->appendTelecom($telecom);
        }
    }

    /**
     * @param                     $object
     * @param CMediusers|CMedecin $user
     */
    public static function setNationalID($object, $user): void
    {
        if (!method_exists($object, 'appendId')) {
            return;
        }

        $rpps = $adeli = null;
        if ($user instanceof CMediusers || $user instanceof CMedecin) {
            $rpps  = $user->rpps;
            $adeli = $user->adeli;
        }

        // rpps
        if ($rpps) {
            $ii = new CCDAII();
            $ii->setRoot(CMedecin::OID_IDENTIFIER_NATIONAL);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $ii->setExtension("8$rpps");
            $object->appendId($ii);

            return;
        }

        // adeli
        if ($adeli) {
            $ii = new CCDAII();
            $ii->setRoot(CMedecin::OID_IDENTIFIER_NATIONAL);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $ii->setExtension("0$adeli");
            $object->appendId($ii);
        }
    }

    /**
     * Set ID on element
     *
     * @param object      $element element
     * @param string|null $root
     * @param string|null $extension
     *
     * @return object
     */
    public static function setId($element, ?string $root = null, ?string $extension = null)
    {
        if (!$root) {
            $root = CCDAActClinicalDocument::generateUUID();
        }

        $ii = new CCDAII();
        $ii->setRoot($root);
        if ($extension) {
            $ii->setExtension($extension);
        }
        $element->appendId($ii);

        return $element;
    }

    /**
     * Add templatesId on element
     *
     * @param object      $element      element
     * @param array       $templates_Id templatesId
     * @param CCDAFactory $factory      factory
     *
     * @return array
     */
    public static function addTemplatesId($element, array $templates_Id): array
    {
        $element_templates = [];
        foreach ($templates_Id as $template_id) {
            $element_templates[] = $template = self::createTemplateID($template_id);
            $element->appendTemplateId($template);
        }

        return $element_templates;
    }

    /**
     * Création de templateId
     *
     * @param String      $root      String
     * @param String|null $extension null
     *
     * @return CCDAII
     */
    public static function createTemplateID(string $root, ?string $extension = null): CCDAII
    {
        $ii = new CCDAII();
        $ii->setRoot($root);
        $ii->setExtension($extension);

        return $ii;
    }

    /**
     * Set element title in section
     *
     * @param CCDAPOCD_MT000040_Section $section    section
     * @param string                    $data_title data title
     *
     * @return CCDAPOCD_MT000040_Section
     */
    public static function setTitle(CCDAPOCD_MT000040_Section $section, string $data_title): CCDAPOCD_MT000040_Section
    {
        $title = new CCDAST();
        $title->setData($data_title);
        $section->setTitle($title);

        return $section;
    }

    /**
     * Set statusCode on element
     *
     * @param object $element element
     * @param string $value   value
     *
     * @return object
     */
    public static function setStatusCode($element, string $value)
    {
        $status = new CCDACS();
        $status->setCode($value);
        $element->setStatusCode($status);

        return $element;
    }

    /**
     * Get status entrance
     *
     * @param CMbObject $target target
     *
     * @return string
     */
    public static function getStatusEntranceMB(CMbObject $target): string
    {
        // ATTENTION : On peut utiliser que "active", "suspended", "aborted" ou "completed" comme valeur du JDV
        $status = null;
        switch ($target->_class) {
            case "CConsultation":
                switch ($target->_etat) {
                    case "Ann.":
                        $status = "cancelled";
                        break;
                    case "Plan.":
                        $status = "held";
                        break;
                    case "En cours":
                        $status = "active";
                        break;
                    case "Term.":
                        $status = "completed";
                        break;
                    default:
                        $status = "active";
                }
                break;
            case "CConsultAnesth":
                $consultation = $target->loadRefConsultation();
                switch ($consultation->_etat) {
                    case "Ann.":
                        $status = "cancelled";
                        break;
                    case "Plan.":
                        $status = "held";
                        break;
                    case "En cours":
                        $status = "active";
                        break;
                    case "Term.":
                        $status = "completed";
                        break;
                    default:
                        $status = "active";
                }
                break;
            case "COperation":
                if ($target->debut_op && $target->fin_op) {
                    $status = "completed";
                } elseif ($target->debut_op && !$target->fin_op) {
                    $status = "active";
                } else {
                    $status = "held";
                }

                break;
            case "CSejour":
                switch ($target->_etat) {
                    case "preadmission":
                        $status = "active";
                        break;
                    case "encours":
                        $status = "active";
                        break;
                    case "cloture":
                        $status = "completed";
                        break;
                    default:
                        $status = "completed";
                }

                if ($target->annule) {
                    $status = "cancelled";
                }
                break;
            default;
        }

        return $status;
    }


    /**
     * Add low time in effectiveTime element
     *
     * @param object $element element
     * @param string $lowTime low time
     *
     * @return object
     */
    public static function addLowTime($element, ?string $lowTime, ?string $nullFlavor = null)
    {
        $ivlTs = self::prepareLowTime($lowTime, null, $nullFlavor);
        $element->appendEffectiveTime($ivlTs);

        return $element;
    }

    /**
     * Add high time in effectiveTime element
     *
     * @param object $element  element
     * @param string $highTime low time
     *
     * @return object
     */
    public static function addHighTime($element, ?string $highTime, ?string $nullFlavor = null)
    {
        $ivlTs = self::prepareHighTime($highTime, null, $nullFlavor);
        $element->appendEffectiveTime($ivlTs);

        return $element;
    }

    /**
     * Set high time in effectiveTime element
     *
     * @param object $element  element
     * @param string $highTime low time
     *
     * @return object
     */
    public static function setHighTime($element, ?string $highTime, ?string $nullFlavor = null)
    {
        $ivlTs = self::prepareHighTime($highTime, null, $nullFlavor);
        $element->setEffectiveTime($ivlTs);

        return $element;
    }

    /**
     * Set high time in effectiveTime element
     *
     * @param object $element element
     * @param string $lowTime low time
     *
     * @return object
     */
    public static function setLowTime($element, ?string $lowTime, ?string $nullFlavor = null)
    {
        $ivlTs = self::prepareLowTime($lowTime, null, $nullFlavor);
        $element->setEffectiveTime($ivlTs);

        return $element;
    }

    /**
     * Set low and high time in effectiveTime element
     *
     * @param object $element    element
     * @param string $date_start date start
     * @param string $date_end   date end
     *
     * @return object
     */
    public static function setLowAndHighTime(
        $element,
        ?string $date_start,
        ?string $date_end = null,
        ?string $nullFlavor = null
    ) {
        $ivlTs = self::prepareLowTime($date_start, null, $nullFlavor);
        $ivlTs = self::prepareHighTime($date_end ?: $date_start, $ivlTs, $nullFlavor);
        $element->setEffectiveTime($ivlTs);

        return $element;
    }

    /**
     * add low and high time in effectiveTime element
     *
     * @param object $element    element
     * @param string $date_start date start
     * @param string $date_end   date end
     *
     * @return object
     */
    public static function addLowAndHighTime(
        $element,
        ?string $date_start,
        ?string $date_end = null,
        ?string $nullFlavorLow = null,
        ?string $nullFlavorHigh = null
    ) {
        $ivlTs = self::prepareLowTime($date_start, null, $nullFlavorLow);
        $ivlTs = self::prepareHighTime($date_end, $ivlTs, $nullFlavorHigh);
        $element->appendEffectiveTime($ivlTs);

        return $element;
    }

    /**
     * @param CCDAIVL_TS $element
     * @param            $lowTime
     *
     * @return CCDAIVL_TS
     */
    public static function prepareLowTime(
        ?string $lowTime,
        ?CCDAIVL_TS $element = null,
        ?string $nullFlavor = null
    ): CCDAIVL_TS {
        if (!$element) {
            $element = new CCDAIVL_TS();
        }

        $ivxbL = new CCDAIVXB_TS();
        $nullFlavor ? $ivxbL->setNullFlavor($nullFlavor) : $ivxbL->setValue($lowTime);
        $element->setLow($ivxbL);

        return $element;
    }

    /**
     * @param string|null     $highTime
     *
     * @param CCDAIVL_TS|null $element
     * @param string|null     $nullFlavor
     *
     * @return CCDAIVL_TS
     */
    public static function prepareHighTime(
        ?string $highTime,
        ?CCDAIVL_TS $element = null,
        ?string $nullFlavor = null
    ): CCDAIVL_TS {
        if (!$element) {
            $element = new CCDAIVL_TS();
        }

        $ivxbL = new CCDAIVXB_TS();
        $nullFlavor ? $ivxbL->setNullFlavor($nullFlavor) : $ivxbL->setValue($highTime);
        $element->setHigh($ivxbL);

        return $element;
    }

    /**
     * Set element code in section
     *
     * @param object $element        element
     * @param string $code_loinc     code loinc
     * @param string $codeSystem     code system
     * @param string $displayName    display name
     * @param string $codeSystemName code system name
     *
     * @return object
     */
    public static function setCodeCD(
        $element,
        ?string $code_loinc = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $codeSystemName = null
    ) {
        $code = self::prepareCodeCD($code_loinc, $codeSystem, $displayName, $codeSystemName);
        $element->setCode($code);

        return $element;
    }

    /**
     * Set element code in section
     *
     * @param object $element        element
     * @param string $code_loinc     code loinc
     * @param string $codeSystem     code system
     * @param string $displayName    display name
     * @param string $codeSystemName code system name
     *
     * @return object
     */
    public static function setCodeCE(
        $element,
        ?string $code_loinc = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $codeSystemName = null
    ) {
        $code = self::prepareCodeCE($code_loinc, $codeSystem, $displayName, $codeSystemName);
        $element->setCode($code);

        return $element;
    }

    /**
     * @param             $element
     * @param string      $code_loinc
     * @param string|null $alt_libelle
     */
    public static function setCodeLoinc($element, string $code_loinc, ?string $alt_libelle = null): void
    {
        $loinc = CLoinc::get($code_loinc);

        $code = self::prepareCodeCE(
            $loinc->code,
            $loinc::$oid_loinc,
            $alt_libelle ?: $loinc->libelle_fr,
            $loinc::$name_loinc,
        );
        $element->setCode($code);
    }


    /**
     * @param string|null $code_loinc
     * @param string|null $codeSystem
     * @param string|null $displayName
     * @param string|null $codeSystemName
     *
     * @return CCDACD
     */
    public static function prepareCodeCD(
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $codeSystemName = null
    ) {
        $cd = new CCDACD();
        $cd->setCode($code);
        $cd->setCodeSystem($codeSystem);
        $cd->setDisplayName($displayName);
        $cd->setCodeSystemName($codeSystemName);

        return $cd;
    }

    /**
     * @param string|null $code_loinc
     * @param string|null $codeSystem
     * @param string|null $displayName
     * @param string|null $codeSystemName
     *
     * @return CCDACD
     */
    public static function prepareCodeCE(
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $codeSystemName = null
    ) {
        $cd = new CCDACE();
        $cd->setCode($code);
        $cd->setCodeSystem($codeSystem);
        $cd->setDisplayName($displayName);
        $cd->setCodeSystemName($codeSystemName);

        return $cd;
    }

    /**
     * Set code on element
     *
     * @param object $element    element
     * @param string $nullFlavor null flavor
     *
     * @return object
     */
    public static function setCodeNullFlavor($element, string $nullFlavor = 'NA')
    {
        $cd = new CCDACD();
        $cd->setNullFlavor($nullFlavor);
        $element->setCode($cd);

        return $element;
    }

    /**
     * Set code snomed in element like Observation
     *
     * @param object $element               element
     * @param string $code                  code
     * @param string $codeSystem            code system
     * @param string $displayName           display name
     * @param bool   $append                append | set code
     * @param string $content_original_text content original text
     * @param string $codeSystemName        code system name
     *
     * @return object
     */
    public static function setCodeSnomed(
        $element,
        ?string $code,
        ?string $codeSystem,
        ?string $displayName,
        ?string $content_original_text = null,
        ?string $codeSystemName = "SNOMED 3.5"
    ) {
        $code_element = self::prepareCodeSnomed(
            $code,
            $codeSystem,
            $displayName,
            $content_original_text,
            $codeSystemName
        );
        $element->setCode($code_element);

        return $element;
    }

    /**
     * Add code snomed in element like Observation
     *
     * @param object $element               element
     * @param string $code                  code
     * @param string $codeSystem            code system
     * @param string $displayName           display name
     * @param bool   $append                append | set code
     * @param string $content_original_text content original text
     * @param string $codeSystemName        code system name
     *
     * @return object
     */
    public static function addCodeSnomed(
        $element,
        ?string $code,
        ?string $codeSystem,
        ?string $displayName,
        ?string $content_original_text = null,
        ?string $codeSystemName = "SNOMED 3.5"
    ) {
        $code_element = self::prepareCodeSnomed(
            $code,
            $codeSystem,
            $displayName,
            $content_original_text,
            $codeSystemName
        );
        $element->appendValue($code_element);

        return $element;
    }


    /**
     * Prepare code snomed in element like Observation
     *
     * @param object $element               element
     * @param string $code                  code
     * @param string $codeSystem            code system
     * @param string $displayName           display name
     * @param bool   $append                append | set code
     * @param string $content_original_text content original text
     * @param string $codeSystemName        code system name
     *
     * @return CCDACD
     */
    private static function prepareCodeSnomed(
        ?string $code,
        ?string $codeSystem,
        ?string $displayName,
        ?string $content_original_text = null,
        ?string $codeSystemName = "SNOMED 3.5"
    ): CCDACD {
        $code_element = self::prepareCodeCD($code, $codeSystem, $displayName, $codeSystemName);
        if ($content_original_text) {
            $text_observation           = new CCDAED();
            $text_reference_observation = new CCDATEL();
            $text_reference_observation->setValue($content_original_text);
            $text_observation->setReference($text_reference_observation);
            $code_element->setOriginalText($text_observation);
        }

        return $code_element;
    }

    /**
     * @param object      $element
     * @param string|null $code
     * @param string|null $codeSystem
     * @param string|null $displayName
     * @param string|null $content_original_text
     * @param string|null $codeSystemName
     *
     * @return object
     */
    public static function setValueCodeCDCIM10(
        $element,
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $content_original_text = null,
        ?string $codeSystemName = "CIM10"
    ) {
        $code_element = new CCDACD();
        $code_element = self::prepareValueCodeCIM10(
            $code_element,
            $code,
            $codeSystem,
            $displayName,
            $content_original_text,
            $codeSystemName
        );

        $element->setCode($code_element);

        return $element;
    }

    /**
     * @param object      $element
     * @param string|null $code
     * @param string|null $codeSystem
     * @param string|null $displayName
     * @param string|null $content_original_text
     * @param string|null $codeSystemName
     *
     * @return object
     */
    public static function setValueCodeCECIM10(
        $element,
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $content_original_text = null,
        ?string $codeSystemName = "CIM10"
    ) {
        $code_element = new CCDACE();
        $code_element = self::prepareValueCodeCIM10(
            $code_element,
            $code,
            $codeSystem,
            $displayName,
            $content_original_text,
            $codeSystemName
        );
        $element->setCode($code_element);

        return $element;
    }

    /**
     * @param object      $element
     * @param string|null $code
     * @param string|null $codeSystem
     * @param string|null $displayName
     * @param string|null $content_original_text
     * @param string|null $codeSystemName
     *
     * @return object
     */
    public static function addValueCodeCECIM10(
        $element,
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $content_original_text = null,
        ?string $codeSystemName = "CIM10"
    ) {
        $code_element = new CCDACE();
        $code_element = self::prepareValueCodeCIM10(
            $code_element,
            $code,
            $codeSystem,
            $displayName,
            $content_original_text,
            $codeSystemName
        );

        $element->appendValue($code_element);

        return $element;
    }

    /**
     * @param object      $element
     * @param string|null $code
     * @param string|null $codeSystem
     * @param string|null $displayName
     * @param string|null $content_original_text
     * @param string|null $codeSystemName
     *
     * @return object
     */
    public static function addValueCodeCDCIM10(
        $element,
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $content_original_text = null,
        ?string $codeSystemName = "CIM10"
    ) {
        $code_element = new CCDACD();
        $code_element = self::prepareValueCodeCIM10(
            $code_element,
            $code,
            $codeSystem,
            $displayName,
            $content_original_text,
            $codeSystemName
        );

        $element->appendValue($code_element);

        return $element;
    }

    /**
     * Add code snomed in element like Observation
     *
     * @param CCDACD|CCDACE $code_element          element
     * @param string        $code                  code
     * @param string        $codeSystem            code system
     * @param string        $displayName           display name
     * @param bool          $append                append | set code
     * @param string        $content_original_text content original text
     * @param string        $codeSystemName        code system name
     *
     * @return CCDACD|CCDACE
     */
    private static function prepareValueCodeCIM10(
        $code_element,
        ?string $code = null,
        ?string $codeSystem = null,
        ?string $displayName = null,
        ?string $content_original_text = null,
        ?string $codeSystemName = "CIM10"
    ) {
        $code_element = $code_element instanceof CCDACE
            ? self::prepareCodeCE($code, $codeSystem, $displayName, $codeSystemName)
            : self::prepareCodeCD($code, $codeSystem, $displayName, $codeSystemName);

        if ($content_original_text) {
            $text_observation           = new CCDAED();
            $text_reference_observation = new CCDATEL();
            $text_reference_observation->setValue($content_original_text);
            $text_observation->setReference($text_reference_observation);
            $code_element->setOriginalText($text_observation);
        }

        return $code_element;
    }
}
