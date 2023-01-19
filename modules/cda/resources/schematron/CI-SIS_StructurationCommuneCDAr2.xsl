<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<xsl:stylesheet xmlns:xhtml="http://www.w3.org/1999/xhtml"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:saxon="http://saxon.sf.net/"
                xmlns:xs="http://www.w3.org/2001/XMLSchema"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                xmlns:schold="http://www.ascc.net/xml/schematron"
                xmlns:iso="http://purl.oclc.org/dsdl/schematron"
                xmlns:cda="urn:hl7-org:v3"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:jdv="http://esante.gouv.fr"
                xmlns:svs="urn:ihe:iti:svs:2008"
                version="2.0"><!--Implementers: please note that overriding process-prolog or process-root is 
    the preferred method for meta-stylesheets to use where possible. -->
<xsl:param name="archiveDirParameter"/>
   <xsl:param name="archiveNameParameter"/>
   <xsl:param name="fileNameParameter"/>
   <xsl:param name="fileDirParameter"/>
   <xsl:variable name="document-uri">
      <xsl:value-of select="document-uri(/)"/>
   </xsl:variable>

   <!--PHASES-->


<!--PROLOG-->
<xsl:output xmlns:svrl="http://purl.oclc.org/dsdl/svrl" method="xml"
               omit-xml-declaration="no"
               standalone="yes"
               indent="yes"/>

   <!--XSD TYPES FOR XSLT2-->


<!--KEYS AND FUNCTIONS-->


<!--DEFAULT RULES-->


<!--MODE: SCHEMATRON-SELECT-FULL-PATH-->
<!--This mode can be used to generate an ugly though full XPath for locators-->
<xsl:template match="*" mode="schematron-select-full-path">
      <xsl:apply-templates select="." mode="schematron-get-full-path-2"/>
   </xsl:template>

   <!--MODE: SCHEMATRON-FULL-PATH-->
<!--This mode can be used to generate an ugly though full XPath for locators-->
<xsl:template match="*" mode="schematron-get-full-path">
      <xsl:apply-templates select="parent::*" mode="schematron-get-full-path"/>
      <xsl:text>/</xsl:text>
      <xsl:choose>
         <xsl:when test="namespace-uri()=''">
            <xsl:value-of select="name()"/>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>*:</xsl:text>
            <xsl:value-of select="local-name()"/>
            <xsl:text>[namespace-uri()='</xsl:text>
            <xsl:value-of select="namespace-uri()"/>
            <xsl:text>']</xsl:text>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:variable name="preceding"
                    select="count(preceding-sibling::*[local-name()=local-name(current())                                   and namespace-uri() = namespace-uri(current())])"/>
      <xsl:text>[</xsl:text>
      <xsl:value-of select="1+ $preceding"/>
      <xsl:text>]</xsl:text>
   </xsl:template>
   <xsl:template match="@*" mode="schematron-get-full-path">
      <xsl:apply-templates select="parent::*" mode="schematron-get-full-path"/>
      <xsl:text>/</xsl:text>
      <xsl:choose>
         <xsl:when test="namespace-uri()=''">@<xsl:value-of select="name()"/>
         </xsl:when>
         <xsl:otherwise>
            <xsl:text>@*[local-name()='</xsl:text>
            <xsl:value-of select="local-name()"/>
            <xsl:text>' and namespace-uri()='</xsl:text>
            <xsl:value-of select="namespace-uri()"/>
            <xsl:text>']</xsl:text>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:template>

   <!--MODE: SCHEMATRON-FULL-PATH-2-->
<!--This mode can be used to generate prefixed XPath for humans-->
<xsl:template match="node() | @*" mode="schematron-get-full-path-2">
      <xsl:for-each select="ancestor-or-self::*">
         <xsl:text>/</xsl:text>
         <xsl:value-of select="name(.)"/>
         <xsl:if test="preceding-sibling::*[name(.)=name(current())]">
            <xsl:text>[</xsl:text>
            <xsl:value-of select="count(preceding-sibling::*[name(.)=name(current())])+1"/>
            <xsl:text>]</xsl:text>
         </xsl:if>
      </xsl:for-each>
      <xsl:if test="not(self::*)">
         <xsl:text/>/@<xsl:value-of select="name(.)"/>
      </xsl:if>
   </xsl:template>
   <!--MODE: SCHEMATRON-FULL-PATH-3-->
<!--This mode can be used to generate prefixed XPath for humans
	(Top-level element has index)-->
<xsl:template match="node() | @*" mode="schematron-get-full-path-3">
      <xsl:for-each select="ancestor-or-self::*">
         <xsl:text>/</xsl:text>
         <xsl:value-of select="name(.)"/>
         <xsl:if test="parent::*">
            <xsl:text>[</xsl:text>
            <xsl:value-of select="count(preceding-sibling::*[name(.)=name(current())])+1"/>
            <xsl:text>]</xsl:text>
         </xsl:if>
      </xsl:for-each>
      <xsl:if test="not(self::*)">
         <xsl:text/>/@<xsl:value-of select="name(.)"/>
      </xsl:if>
   </xsl:template>

   <!--MODE: GENERATE-ID-FROM-PATH -->
<xsl:template match="/" mode="generate-id-from-path"/>
   <xsl:template match="text()" mode="generate-id-from-path">
      <xsl:apply-templates select="parent::*" mode="generate-id-from-path"/>
      <xsl:value-of select="concat('.text-', 1+count(preceding-sibling::text()), '-')"/>
   </xsl:template>
   <xsl:template match="comment()" mode="generate-id-from-path">
      <xsl:apply-templates select="parent::*" mode="generate-id-from-path"/>
      <xsl:value-of select="concat('.comment-', 1+count(preceding-sibling::comment()), '-')"/>
   </xsl:template>
   <xsl:template match="processing-instruction()" mode="generate-id-from-path">
      <xsl:apply-templates select="parent::*" mode="generate-id-from-path"/>
      <xsl:value-of select="concat('.processing-instruction-', 1+count(preceding-sibling::processing-instruction()), '-')"/>
   </xsl:template>
   <xsl:template match="@*" mode="generate-id-from-path">
      <xsl:apply-templates select="parent::*" mode="generate-id-from-path"/>
      <xsl:value-of select="concat('.@', name())"/>
   </xsl:template>
   <xsl:template match="*" mode="generate-id-from-path" priority="-0.5">
      <xsl:apply-templates select="parent::*" mode="generate-id-from-path"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="concat('.',name(),'-',1+count(preceding-sibling::*[name()=name(current())]),'-')"/>
   </xsl:template>

   <!--MODE: GENERATE-ID-2 -->
<xsl:template match="/" mode="generate-id-2">U</xsl:template>
   <xsl:template match="*" mode="generate-id-2" priority="2">
      <xsl:text>U</xsl:text>
      <xsl:number level="multiple" count="*"/>
   </xsl:template>
   <xsl:template match="node()" mode="generate-id-2">
      <xsl:text>U.</xsl:text>
      <xsl:number level="multiple" count="*"/>
      <xsl:text>n</xsl:text>
      <xsl:number count="node()"/>
   </xsl:template>
   <xsl:template match="@*" mode="generate-id-2">
      <xsl:text>U.</xsl:text>
      <xsl:number level="multiple" count="*"/>
      <xsl:text>_</xsl:text>
      <xsl:value-of select="string-length(local-name(.))"/>
      <xsl:text>_</xsl:text>
      <xsl:value-of select="translate(name(),':','.')"/>
   </xsl:template>
   <!--Strip characters--><xsl:template match="text()" priority="-1"/>

   <!--SCHEMA SETUP-->
<xsl:template match="/">
      <xsl:processing-instruction xmlns:svrl="http://purl.oclc.org/dsdl/svrl" name="xml-stylesheet">type="text/xsl" href="rapportSchematronToHtml4.xsl"</xsl:processing-instruction>
      <xsl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl"/>
      <svrl:schematron-output xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                              title="Conformité d'un document CDAr2 au volet Structuration Commune des Documents Médicaux du CI-SIS"
                              schemaVersion="CI-SIS_StructurationCommuneCDAr2.sch">
         <xsl:attribute name="phase">latotale20121008</xsl:attribute>
         <xsl:attribute name="document">
            <xsl:value-of select="document-uri(/)"/>
         </xsl:attribute>
         <xsl:attribute name="dateHeure">
            <xsl:value-of select="format-dateTime(current-dateTime(), '[D]/[M]/[Y] à [H]:[m]:[s] (temps UTC[Z])')"/>
         </xsl:attribute>
         <xsl:text/>
         <svrl:active-pattern>
            <xsl:attribute name="id">addr</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M5"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">administrativeGenderCode</xsl:attribute>
            <svrl:text>Conformité du code sexe du patient ou du subject, nullFlavor autorisé</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M6"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">assignedEntity</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M7"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">authenticatorName</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M8"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">authorPersonName</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M9"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">authorSpecialty</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M10"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">authorTime</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS d'un élément de type IVL_TS ou TS du standard CDAr2 :
        L'élément doit porter soit un attribut "value" soit un intervalle éventuellement semi-borné de sous-éléments "low", "high". 
        Alternativement, si l'attribut nullFlavor est autorisé, il doit porter l'une des valeurs admises par le CI-SIS. 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M11"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">documentCode</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M12"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">documentEffectiveTime</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS d'un élément de type IVL_TS ou TS du standard CDAr2 :
        L'élément doit porter soit un attribut "value" soit un intervalle éventuellement semi-borné de sous-éléments "low", "high". 
        Alternativement, si l'attribut nullFlavor est autorisé, il doit porter l'une des valeurs admises par le CI-SIS. 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M13"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">healthcareFacilityTypeCode</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M14"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">informantAssignedPersonName</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M15"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">informantRelatedEntity</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M16"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">legalAuthenticatorName</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M17"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">legalAuthenticatorTime</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS d'un élément de type IVL_TS ou TS du standard CDAr2 :
        L'élément doit porter soit un attribut "value" soit un intervalle éventuellement semi-borné de sous-éléments "low", "high". 
        Alternativement, si l'attribut nullFlavor est autorisé, il doit porter l'une des valeurs admises par le CI-SIS. 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M18"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">modeleCommunEnTete</xsl:attribute>
            <svrl:text>Conformité de base de l'en-tête CDA au CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M19"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">nonXMLBody</xsl:attribute>
            <svrl:text>Conformité d'un document CDA avec nonXMLBody au profil IHE XDS-SD et vérification des formats et encodage autorisés</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M20"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">patient</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M21"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">patientBirthTime</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS d'un élément de type IVL_TS ou TS du standard CDAr2 :
        L'élément doit porter soit un attribut "value" soit un intervalle éventuellement semi-borné de sous-éléments "low", "high". 
        Alternativement, si l'attribut nullFlavor est autorisé, il doit porter l'une des valeurs admises par le CI-SIS. 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M22"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">patientId</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS :
        l'INS-C doit être une chaîne de 22 chiffres 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M23"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">patientName</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M24"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">practiceSettingCode</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M25"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">relatedDocument</xsl:attribute>
            <svrl:text>
        Si l'élément relatedDocument est présent alors son attribut typeCode doit valoir RPLC 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M26"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">relatedPersonName</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M27"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">serviceEventEffectiveTime</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS d'un élément de type IVL_TS ou TS du standard CDAr2 :
        L'élément doit porter soit un attribut "value" soit un intervalle éventuellement semi-borné de sous-éléments "low", "high". 
        Alternativement, si l'attribut nullFlavor est autorisé, il doit porter l'une des valeurs admises par le CI-SIS. 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M28"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">serviceEventPerformer</xsl:attribute>
            <svrl:text>
        Vérification de la présence et de la conformité de l'acte principal documenté 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M29"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">telecom</xsl:attribute>
            <svrl:text>
        Vérification de la conformité au CI-SIS d'un élément telecom (de type TEL) du standard CDAr2 :
        L'élément doit comporter un attribut "value" bien formaté avec les préfixes autorisés par le CI-SIS, 
        et optionnellement un attribut "use" (qui n'est pas contrôlé).
        Alternativement, si l'attribut nullFlavor est présent, il doit avoir l'une des valeurs admises par le CI-SIS. 
    </svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M30"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">abdomenPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M31"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">activeProblemSection-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M32"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">assessmentAndPlan-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M33"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">carePlan-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M34"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">childFunctionalStatusAssessment-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M35"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">childFunctionalStatusEatingSleeping-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M36"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">childFunctionalStatusPsychoMot-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M37"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">CodedAntenatalTestingAndSurveillance-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M38"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">codedPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M39"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">codedResults-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M40"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">codedSocialHistory-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M41"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">codedVitalSigns-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M42"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">EarsPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M43"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">encounterHistoriesSection-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M44"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">endocrinePhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M45"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">eyesPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M46"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">generalAppearancePhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M47"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">genitaliaPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M48"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">heartPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M49"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">historyOfPastIllness-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M50"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">immunizations-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M51"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">integumentaryPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M52"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">laborAndDeliverySection-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M53"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">lymphaticPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M54"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">musculoPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M55"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">neurologicPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M56"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">pregnancyHistorySection-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M57"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">prenatalEvents-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M58"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">proceduresSection-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M59"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">RespiratoryPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M60"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">teethPhysicalExam-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M61"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">ACPimageIllustrative</xsl:attribute>
            <svrl:text>Contrôle d'une image illustrative dans un élément observationMedia</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M62"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">ClinicalStatusCodes</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M63"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">codedAntenatalTestingAndSurveillanceOrg-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M64"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">codedVitalSignsOrg-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M65"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">comments-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M66"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">concernEntry-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M67"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">encountersEntry-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M68"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">HealthStatusCodes</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M69"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">immunizationsEnt-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M70"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">observationInterpretation</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M71"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">ProblemCodes</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M72"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">AllergyAndIntoleranceCodes</xsl:attribute>
            <svrl:text>Conformité d'un élément codé obligatoire par rapport à un jeu de valeurs du CI-SIS</svrl:text>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M73"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">problemConcernEntry-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M74"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">problemEntry-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M75"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">procedureEntry-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M76"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">simpleObservation-errors</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M77"/>
         <svrl:active-pattern>
            <xsl:attribute name="id">variables</xsl:attribute>
            <xsl:apply-templates/>
         </svrl:active-pattern>
         <xsl:apply-templates select="/" mode="M79"/>
      </svrl:schematron-output>
   </xsl:template>

   <!--SCHEMATRON PATTERNS-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">Conformité d'un document CDAr2 au volet Structuration Commune des Documents Médicaux du CI-SIS</svrl:text>

   <!--PATTERN addr-->


	<!--RULE -->
<xsl:template match="cda:addr" priority="1000" mode="M5">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:addr"/>
      <xsl:variable name="nba" select="count(@*)"/>
      <xsl:variable name="nbch" select="count(*)"/>
      <xsl:variable name="val" select="@*"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($nba = 0 and $nbch &gt; 0) or             ($nba and name(@*) = 'use' and $nbch &gt; 0) or              ($nba = 1 and name(@*) = 'nullFlavor' and $nbch = 0 and             ($val = 'UNK' or $val = 'NASK' or $val = 'ASKU' or $val = 'NAV' or $val = 'MSK'))              )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="name(.)"/>
                  <xsl:text/> ne contient pas un attribut autorisé pour une adresse, 
            ou est vide et sans nullFlavor, ou contient une valeur de nullFlavor non admise.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="$nbch = 0 or                         (cda:streetAddressLine and not(cda:postalCode) and not(cda:city) and not(cda:country) and not(cda:state)                         and not(cda:houseNumber) and not(cda:streetName)and not(cda:additionalLocator) and not(cda:unitID)                         and not(cda:postBox) and not(cda:precinct)) or                         (not(cda:streetAddressLine))                         "/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="name(.)"/>
                  <xsl:text/> doit être structuré : 
            - soit sous la formes de lignes d'adresse (streetAddressLine)
            - soit sous la forme de composants élémentaires d'adresse
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M5"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M5"/>
   <xsl:template match="@*|node()" priority="-2" mode="M5">
      <xsl:apply-templates select="*" mode="M5"/>
   </xsl:template>

   <!--PATTERN administrativeGenderCode-->


	<!--RULE -->
<xsl:template match="cda:administrativeGenderCode" priority="1000" mode="M6">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:administrativeGenderCode"/>
      <xsl:variable name="NF" select="@nullFlavor"/>
      <xsl:variable name="sex" select="@code"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="$sex = 'M' or $sex = 'F' or $sex = 'U' or $NF = 'UNK' or $NF = 'NASK' or $NF = 'ASKU' or $NF = 'NAV' or $NF = 'MSK'"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément administrativeGenderCode doit être présent, avec code sexe ou un nullFlavor autorisé 
            (valeur trouvée <xsl:text/>
                  <xsl:value-of select="@*"/>
                  <xsl:text/>).
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M6"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M6"/>
   <xsl:template match="@*|node()" priority="-2" mode="M6">
      <xsl:apply-templates select="*" mode="M6"/>
   </xsl:template>

   <!--PATTERN assignedEntity-->


	<!--RULE -->
<xsl:template match="cda:assignedEntity" priority="1000" mode="M7">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:assignedEntity"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="./cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> Erreur de conformité CI-SIS : L'élément "id" doit être présent sous
            assignedEntity. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:assignedPerson"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> Erreur de conformité CI-SIS : L'élément
            "assignedPerson" doit être présent sous assignedEntity (nullFlavor autorisé). </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:assignedPerson/cda:name or cda:assignedPerson/@nullFlavor"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            Si l'élément assignedPerson n'est pas vide avec un nullFlavor, alors il 
            doit comporter un élément fils "name" (nullFlavor autorisé). </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M7"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M7"/>
   <xsl:template match="@*|node()" priority="-2" mode="M7">
      <xsl:apply-templates select="*" mode="M7"/>
   </xsl:template>

   <!--PATTERN authenticatorName-->


	<!--RULE -->
<xsl:template match="cda:authenticator/cda:assignedEntity/cda:assignedPerson/cda:name"
                 priority="1000"
                 mode="M8">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:authenticator/cda:assignedEntity/cda:assignedPerson/cda:name"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(                         (name(@*) = 'nullFlavor' and 1 and                            (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or                         ((./cda:family) and                        ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'authenticator/assignedEntity/assignedPerson/name'"/>
                  <xsl:text/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <xsl:text/>
                  <xsl:value-of select="./cda:family"/>
                  <xsl:text/>. Valeur trouvée pour family@qualifier : <xsl:text/>
                  <xsl:value-of select="./cda:family/@qualifier"/>
                  <xsl:text/>
               </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M8"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M8"/>
   <xsl:template match="@*|node()" priority="-2" mode="M8">
      <xsl:apply-templates select="*" mode="M8"/>
   </xsl:template>

   <!--PATTERN authorPersonName-->


	<!--RULE -->
<xsl:template match="cda:assignedAuthor/cda:assignedPerson/cda:name" priority="1000"
                 mode="M9">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:assignedAuthor/cda:assignedPerson/cda:name"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(                         (name(@*) = 'nullFlavor' and 1 and                            (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or                         ((./cda:family) and                        ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'assignedAuthor/assignedPerson/name'"/>
                  <xsl:text/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <xsl:text/>
                  <xsl:value-of select="./cda:family"/>
                  <xsl:text/>. Valeur trouvée pour family@qualifier : <xsl:text/>
                  <xsl:value-of select="./cda:family/@qualifier"/>
                  <xsl:text/>
               </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M9"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M9"/>
   <xsl:template match="@*|node()" priority="-2" mode="M9">
      <xsl:apply-templates select="*" mode="M9"/>
   </xsl:template>

   <!--PATTERN authorSpecialty-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:author/cda:assignedAuthor/cda:code"
                 priority="1000"
                 mode="M10">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:author/cda:assignedAuthor/cda:code"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 1)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/author/assignedAuthor/code'"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (1 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/author/assignedAuthor/code'"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_authorSpecialty)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/author/assignedAuthor/code'"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_authorSpecialty"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M10"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M10"/>
   <xsl:template match="@*|node()" priority="-2" mode="M10">
      <xsl:apply-templates select="*" mode="M10"/>
   </xsl:template>

   <!--PATTERN authorTime-->


	<!--RULE -->
<xsl:template match="cda:author/cda:time" priority="1002" mode="M11">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:author/cda:time"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>
      <xsl:variable name="lDH1" select="string-length(substring-before(@value,'+'))"/>
      <xsl:variable name="lDH2" select="string-length(substring-before(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4) and $lDH1 &lt;= 14 and $lDH2 &lt;= 14)             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'author/time'"/>
                  <xsl:text/>/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(@* and not(*)) or (not(@*) and *)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'author/time'"/>
                  <xsl:text/> doit contenir soit un attribut soit des éléments fils.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (name(@*) = 'nullFlavor' and 1 and             (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or             (name(@*) != 'nullFlavor')              )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'author/time'"/>
                  <xsl:text/> contient un 'nullFlavor' non autorisé ou porteur d'une valeur non admise.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M11"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:author/cda:time/cda:low" priority="1001" mode="M11">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:author/cda:time/cda:low"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'author/time'"/>
                  <xsl:text/>/low/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M11"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:author/cda:time/cda:high" priority="1000" mode="M11">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:author/cda:time/cda:high"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'author/time'"/>
                  <xsl:text/>/high/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M11"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M11"/>
   <xsl:template match="@*|node()" priority="-2" mode="M11">
      <xsl:apply-templates select="*" mode="M11"/>
   </xsl:template>

   <!--PATTERN documentCode-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:code" priority="1000" mode="M12">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:code"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/code'"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/code'"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_typeCode)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/code'"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_typeCode"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M12"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M12"/>
   <xsl:template match="@*|node()" priority="-2" mode="M12">
      <xsl:apply-templates select="*" mode="M12"/>
   </xsl:template>

   <!--PATTERN documentEffectiveTime-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:effectiveTime" priority="1002" mode="M13">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:effectiveTime"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>
      <xsl:variable name="lDH1" select="string-length(substring-before(@value,'+'))"/>
      <xsl:variable name="lDH2" select="string-length(substring-before(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4) and $lDH1 &lt;= 14 and $lDH2 &lt;= 14)             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/effectiveTime'"/>
                  <xsl:text/>/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(@* and not(*)) or (not(@*) and *)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/effectiveTime'"/>
                  <xsl:text/> doit contenir soit un attribut soit des éléments fils.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (name(@*) = 'nullFlavor' and 0 and             (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or             (name(@*) != 'nullFlavor')              )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/effectiveTime'"/>
                  <xsl:text/> contient un 'nullFlavor' non autorisé ou porteur d'une valeur non admise.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M13"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:effectiveTime/cda:low" priority="1001"
                 mode="M13">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:effectiveTime/cda:low"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/effectiveTime'"/>
                  <xsl:text/>/low/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M13"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:effectiveTime/cda:high" priority="1000"
                 mode="M13">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:effectiveTime/cda:high"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/effectiveTime'"/>
                  <xsl:text/>/high/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M13"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M13"/>
   <xsl:template match="@*|node()" priority="-2" mode="M13">
      <xsl:apply-templates select="*" mode="M13"/>
   </xsl:template>

   <!--PATTERN healthcareFacilityTypeCode-->


	<!--RULE -->
<xsl:template match="cda:encompassingEncounter/cda:location/cda:healthCareFacility/cda:code"
                 priority="1000"
                 mode="M14">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:encompassingEncounter/cda:location/cda:healthCareFacility/cda:code"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'componentOf/encompassingEncounter/location/healtCareFacility/code'"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'componentOf/encompassingEncounter/location/healtCareFacility/code'"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_healthcareFacilityTypeCode)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'componentOf/encompassingEncounter/location/healtCareFacility/code'"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_healthcareFacilityTypeCode"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M14"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M14"/>
   <xsl:template match="@*|node()" priority="-2" mode="M14">
      <xsl:apply-templates select="*" mode="M14"/>
   </xsl:template>

   <!--PATTERN informantAssignedPersonName-->


	<!--RULE -->
<xsl:template match="cda:informant/cda:assignedEntity/cda:assignedPerson/cda:name"
                 priority="1000"
                 mode="M15">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:informant/cda:assignedEntity/cda:assignedPerson/cda:name"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(                         (name(@*) = 'nullFlavor' and 1 and                            (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or                         ((./cda:family) and                        ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'informant/assignedEntity/assignedPerson/name'"/>
                  <xsl:text/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <xsl:text/>
                  <xsl:value-of select="./cda:family"/>
                  <xsl:text/>. Valeur trouvée pour family@qualifier : <xsl:text/>
                  <xsl:value-of select="./cda:family/@qualifier"/>
                  <xsl:text/>
               </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M15"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M15"/>
   <xsl:template match="@*|node()" priority="-2" mode="M15">
      <xsl:apply-templates select="*" mode="M15"/>
   </xsl:template>

   <!--PATTERN informantRelatedEntity-->


	<!--RULE -->
<xsl:template match="cda:informant/cda:relatedEntity" priority="1000" mode="M16">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:informant/cda:relatedEntity"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="((name(@*) = 'classCode') and                          (@* = 'ECON' or @* = 'GUARD' or @* = 'POLHOLD' or @* = 'CON' or @* = 'QUAL')                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément informant/relatedEntity doit avoir un attribut classCode dont la valeur est dans l'ensemble :
            (ECON, GUARD, POLHOLD, CON, QUAL).
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="./cda:addr"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément informant/relatedEntity doit comporter une adresse géographique (nullFlavor autorisé)
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="./cda:telecom"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément informant/relatedEntity doit comporter une adresse telecom (nullFlavor autorisé)
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M16"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M16"/>
   <xsl:template match="@*|node()" priority="-2" mode="M16">
      <xsl:apply-templates select="*" mode="M16"/>
   </xsl:template>

   <!--PATTERN legalAuthenticatorName-->


	<!--RULE -->
<xsl:template match="cda:legalAuthenticator/cda:assignedEntity/cda:assignedPerson/cda:name"
                 priority="1000"
                 mode="M17">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:legalAuthenticator/cda:assignedEntity/cda:assignedPerson/cda:name"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(                         (name(@*) = 'nullFlavor' and 0 and                            (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or                         ((./cda:family) and                        ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'legalAuthenticator/assignedEntity/assignedPerson/name'"/>
                  <xsl:text/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <xsl:text/>
                  <xsl:value-of select="./cda:family"/>
                  <xsl:text/>. Valeur trouvée pour family@qualifier : <xsl:text/>
                  <xsl:value-of select="./cda:family/@qualifier"/>
                  <xsl:text/>
               </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M17"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M17"/>
   <xsl:template match="@*|node()" priority="-2" mode="M17">
      <xsl:apply-templates select="*" mode="M17"/>
   </xsl:template>

   <!--PATTERN legalAuthenticatorTime-->


	<!--RULE -->
<xsl:template match="cda:legalAuthenticator/cda:time" priority="1002" mode="M18">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:legalAuthenticator/cda:time"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>
      <xsl:variable name="lDH1" select="string-length(substring-before(@value,'+'))"/>
      <xsl:variable name="lDH2" select="string-length(substring-before(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4) and $lDH1 &lt;= 14 and $lDH2 &lt;= 14)             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'legalAuthenticator/time'"/>
                  <xsl:text/>/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(@* and not(*)) or (not(@*) and *)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'legalAuthenticator/time'"/>
                  <xsl:text/> doit contenir soit un attribut soit des éléments fils.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (name(@*) = 'nullFlavor' and 1 and             (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or             (name(@*) != 'nullFlavor')              )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'legalAuthenticator/time'"/>
                  <xsl:text/> contient un 'nullFlavor' non autorisé ou porteur d'une valeur non admise.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M18"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:legalAuthenticator/cda:time/cda:low" priority="1001" mode="M18">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:legalAuthenticator/cda:time/cda:low"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'legalAuthenticator/time'"/>
                  <xsl:text/>/low/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M18"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:legalAuthenticator/cda:time/cda:high" priority="1000" mode="M18">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:legalAuthenticator/cda:time/cda:high"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'legalAuthenticator/time'"/>
                  <xsl:text/>/high/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M18"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M18"/>
   <xsl:template match="@*|node()" priority="-2" mode="M18">
      <xsl:apply-templates select="*" mode="M18"/>
   </xsl:template>

   <!--PATTERN modeleCommunEnTete-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument" priority="1000" mode="M19">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:ClinicalDocument"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=$enteteHL7France]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité HL7 France :
            L'élément ClinicalDocument/templateId doit être présent 
            avec @root = "<xsl:text/>
                  <xsl:value-of select="$enteteHL7France"/>
                  <xsl:text/>". 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=$commonTemplate]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS :
            L'élément ClinicalDocument/templateId doit être présent avec @root = "<xsl:text/>
                  <xsl:value-of select="$commonTemplate"/>
                  <xsl:text/>". 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:title and normalize-space(cda:title) and not(cda:title[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : 
            L'élément "title" doit être présent dans l'en-tête, 
            sans nullFlavor et doit contenir un titre non vide. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime and not(cda:effectiveTime[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            L'élément "effectiveTime" doit être présent dans l'en-tête, sans nullFlavor. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:realmCode[@code='FR']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            L'élément "realmCode" doit être présent et valorisé à "FR". 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:confidentialityCode[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS :
            L'élément "confidentialityCode" (obligatoire dans CDAr2) doit être sans nullFlavor. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:languageCode[@code='fr-FR']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            L'élément "languageCode" doit être présent dans l'en-tête, valorisé à "fr-FR". 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:id[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            L'élément "id", identifiant unique du document (obligatoire dans CDAr2) doit être sans nullFlavor. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:legalAuthenticator and not(./cda:legalAuthenticator[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : 
            L'élément "legalAuthenticator" doit être présent dans l'en-tête, sans nullFlavor. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:author[@nullFlavor]) and not(./cda:author/cda:assignedAuthor[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            Les éléments "author" et "author/assignedAuthor" doivent être sans nullFlavor dans l'en-tête. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:custodian[@nullFlavor]) and not(./cda:custodian/cda:assignedCustodian[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            Les éléments "custodian" et "custodian/assignedCustodian" doivent être sans nullFlavor dans l'en-tête. 
       </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(cda:documentationOf) and not(cda:documentationOf[@nullFlavor]) and                     not(cda:documentationOf/cda:serviceEvent[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de conformité CI-SIS : 
            L'en-tête doit comporter au moins un élément documentationOf
            et l'attribut nullFlavor n'est pas autorisé ni sur documentationOf ni sur son fils serviceEvent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:componentOf"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : 
            Le document doit comporter dans son en-tête un componentOf/encompassingEncounter.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:componentOf/cda:encompassingEncounter/cda:effectiveTime/@nullFlavor or                     cda:componentOf/cda:encompassingEncounter/cda:effectiveTime/cda:low/@value or                     cda:componentOf/cda:encompassingEncounter/cda:effectiveTime/cda:high/@value             "/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : 
            L'élément componentOf/encompassingEncounter/effectiveTime doit comporter 
            soit un attribut nullFlavor soit l'un des éléments fils "low/@value" et "high/@value" 
            soit les deux.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:componentOf/cda:encompassingEncounter/cda:location/cda:healthCareFacility/cda:code"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : 
            Le document doit comporter dans son en-tête un componentOf/encompassingEncounter/location/healthCareFacility/code.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M19"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M19"/>
   <xsl:template match="@*|node()" priority="-2" mode="M19">
      <xsl:apply-templates select="*" mode="M19"/>
   </xsl:template>

   <!--PATTERN nonXMLBody-->


	<!--RULE -->
<xsl:template match="cda:nonXMLBody" priority="1000" mode="M20">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:nonXMLBody"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../../cda:templateId[@root=$XDS-SD]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité XDS-SD : Un document avec un 
            corps non xml doit comporter l'élément ClinicalDocument/templateId avec @root = 
            "<xsl:text/>
                  <xsl:value-of select="$XDS-SD"/>
                  <xsl:text/>".
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="./cda:text[@representation=&#34;B64&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CDAr2 : Un document avec un corps non xml
            doit encapsuler en format base64 son contenu dans l'élément text, avec @representation = "B64"
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="./cda:text[@mediaType='application/pdf' or              @mediaType='image/jpeg' or              @mediaType='image/tiff' or              @mediaType='text/plain' or              @mediaType='text/rtf']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : Un document avec un corps non xml
            doit encapsuler en format base64 son contenu dans l'élément text, avec @mediaType devant prendre 
            l'une de ces valeurs : {"text/plain", "application/pdf", "image/jpeg", "image/tiff", "text/rtf"}.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M20"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M20"/>
   <xsl:template match="@*|node()" priority="-2" mode="M20">
      <xsl:apply-templates select="*" mode="M20"/>
   </xsl:template>

   <!--PATTERN patient-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:recordTarget/cda:patientRole" priority="1000"
                 mode="M21">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:recordTarget/cda:patientRole"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:id[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément recordTarget/patientRole/id (obligatoire dans CDAr2), 
            doit être sans nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id[@root=$OIDINS-c]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole 
            doit comporter au moins un élément id contenant un INS-c du patient
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:patient/cda:birthTime"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/patient/birthTime doit être présent 
            avec une date de naissance ou un nullFlavor autorisé.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:addr"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/addr doit être présent 
            avec une adresse géographique ou un nullFlavor autorisé.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:telecom"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/telecom doit être présent 
            avec une adresse de télécommunication ou un nullFlavor autorisé.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:patient/cda:administrativeGenderCode"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/patient/administrativeGenderCode 
            doit être présent avec le code sexe ou un nullFlavor autorisé.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="                     not(cda:patient/cda:religiousAffiliationCode) and                     not(cda:patient/cda:raceCode) and                     not(cda:patient/cda:ethnicGroupCode)                      "/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : Un élément recordTarget/patientRole/patient 
            ne doit contenir ni race ni religion ni groupe ethnique.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:patient/cda:name/cda:given"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/patient/name/given doit être présent avec le prénom du patient ou un nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M21"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M21"/>
   <xsl:template match="@*|node()" priority="-2" mode="M21">
      <xsl:apply-templates select="*" mode="M21"/>
   </xsl:template>

   <!--PATTERN patientBirthTime-->


	<!--RULE -->
<xsl:template match="cda:patient/cda:birthTime" priority="1002" mode="M22">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:patient/cda:birthTime"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>
      <xsl:variable name="lDH1" select="string-length(substring-before(@value,'+'))"/>
      <xsl:variable name="lDH2" select="string-length(substring-before(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4) and $lDH1 &lt;= 14 and $lDH2 &lt;= 14)             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'patient/birthTime'"/>
                  <xsl:text/>/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(@* and not(*)) or (not(@*) and *)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'patient/birthTime'"/>
                  <xsl:text/> doit contenir soit un attribut soit des éléments fils.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (name(@*) = 'nullFlavor' and 1 and             (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or             (name(@*) != 'nullFlavor')              )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'patient/birthTime'"/>
                  <xsl:text/> contient un 'nullFlavor' non autorisé ou porteur d'une valeur non admise.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M22"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:patient/cda:birthTime/cda:low" priority="1001" mode="M22">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:patient/cda:birthTime/cda:low"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'patient/birthTime'"/>
                  <xsl:text/>/low/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M22"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:patient/cda:birthTime/cda:high" priority="1000" mode="M22">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:patient/cda:birthTime/cda:high"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'patient/birthTime'"/>
                  <xsl:text/>/high/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M22"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M22"/>
   <xsl:template match="@*|node()" priority="-2" mode="M22">
      <xsl:apply-templates select="*" mode="M22"/>
   </xsl:template>

   <!--PATTERN patientId-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:recordTarget/cda:patientRole/cda:id"
                 priority="1000"
                 mode="M23">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:recordTarget/cda:patientRole/cda:id"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="             (@root = $OIDINS-c and string-length(@extension) = 22 and number(@extension) &gt; 1)               or (@root != $OIDINS-c)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'INS-c doit contenir une chaine de 22 chiffres 
            (valeur trouvée dans le document : <xsl:text/>
                  <xsl:value-of select="@extension"/>
                  <xsl:text/>,
             OID trouvé : <xsl:text/>
                  <xsl:value-of select="@root"/>
                  <xsl:text/>)
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M23"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M23"/>
   <xsl:template match="@*|node()" priority="-2" mode="M23">
      <xsl:apply-templates select="*" mode="M23"/>
   </xsl:template>

   <!--PATTERN patientName-->


	<!--RULE -->
<xsl:template match="cda:patient/cda:name" priority="1000" mode="M24">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:patient/cda:name"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(                         (name(@*) = 'nullFlavor' and 0 and                            (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or                         ((./cda:family) and                        ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'patient/name'"/>
                  <xsl:text/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <xsl:text/>
                  <xsl:value-of select="./cda:family"/>
                  <xsl:text/>. Valeur trouvée pour family@qualifier : <xsl:text/>
                  <xsl:value-of select="./cda:family/@qualifier"/>
                  <xsl:text/>
               </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M24"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M24"/>
   <xsl:template match="@*|node()" priority="-2" mode="M24">
      <xsl:apply-templates select="*" mode="M24"/>
   </xsl:template>

   <!--PATTERN practiceSettingCode-->


	<!--RULE -->
<xsl:template match="cda:serviceEvent/cda:performer/cda:assignedEntity/cda:representedOrganization/cda:standardIndustryClassCode"
                 priority="1000"
                 mode="M25">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:serviceEvent/cda:performer/cda:assignedEntity/cda:representedOrganization/cda:standardIndustryClassCode"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'serviceEvent/performer/assignedEntity/representedOrganization/standardIndustryClassCode'"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'serviceEvent/performer/assignedEntity/representedOrganization/standardIndustryClassCode'"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_practiceSettingCode)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'serviceEvent/performer/assignedEntity/representedOrganization/standardIndustryClassCode'"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_practiceSettingCode"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M25"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M25"/>
   <xsl:template match="@*|node()" priority="-2" mode="M25">
      <xsl:apply-templates select="*" mode="M25"/>
   </xsl:template>

   <!--PATTERN relatedDocument-->


	<!--RULE -->
<xsl:template match="cda:relatedDocument" priority="1000" mode="M26">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:relatedDocument"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(count(@*) = 1 and name(@*) = 'typeCode' and @* = 'RPLC')"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : ClinicalDocument/relatedDocument ne contient pas l'attribut typeCode avec la seule valeur autorisée : RPLC.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M26"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M26"/>
   <xsl:template match="@*|node()" priority="-2" mode="M26">
      <xsl:apply-templates select="*" mode="M26"/>
   </xsl:template>

   <!--PATTERN relatedPersonName-->


	<!--RULE -->
<xsl:template match="cda:relatedPerson/cda:name" priority="1000" mode="M27">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:relatedPerson/cda:name"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(                         (name(@*) = 'nullFlavor' and 1 and                            (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or                         ((./cda:family) and                        ((./cda:family[@qualifier='BR' or @qualifier='SP' or @qualifier='CL']) or not(./cda:family[@qualifier])))                     )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'relatedPerson/name'"/>
                  <xsl:text/>/family doit être présent 
            avec un attribut qualifier valorisé dans : BR (nom de famille), SP (nom d'usage) ou CL (pseudonyme)
            ou sans attribut qualifier. Valeur trouvée pour family : <xsl:text/>
                  <xsl:value-of select="./cda:family"/>
                  <xsl:text/>. Valeur trouvée pour family@qualifier : <xsl:text/>
                  <xsl:value-of select="./cda:family/@qualifier"/>
                  <xsl:text/>
               </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M27"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M27"/>
   <xsl:template match="@*|node()" priority="-2" mode="M27">
      <xsl:apply-templates select="*" mode="M27"/>
   </xsl:template>

   <!--PATTERN serviceEventEffectiveTime-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime"
                 priority="1002"
                 mode="M28">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>
      <xsl:variable name="lDH1" select="string-length(substring-before(@value,'+'))"/>
      <xsl:variable name="lDH2" select="string-length(substring-before(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4) and $lDH1 &lt;= 14 and $lDH2 &lt;= 14)             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/documentationOf/serviceEvent/effectiveTime'"/>
                  <xsl:text/>/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(@* and not(*)) or (not(@*) and *)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/documentationOf/serviceEvent/effectiveTime'"/>
                  <xsl:text/> doit contenir soit un attribut soit des éléments fils.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (name(@*) = 'nullFlavor' and 1 and             (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or             (name(@*) != 'nullFlavor')              )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/documentationOf/serviceEvent/effectiveTime'"/>
                  <xsl:text/> contient un 'nullFlavor' non autorisé ou porteur d'une valeur non admise.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M28"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime/cda:low"
                 priority="1001"
                 mode="M28">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime/cda:low"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/documentationOf/serviceEvent/effectiveTime'"/>
                  <xsl:text/>/low/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M28"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime/cda:high"
                 priority="1000"
                 mode="M28">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:effectiveTime/cda:high"/>
      <xsl:variable name="L" select="string-length(@value)"/>
      <xsl:variable name="mm" select="number(substring(@value,5,2))"/>
      <xsl:variable name="dd" select="number(substring(@value,7,2))"/>
      <xsl:variable name="hh" select="number(substring(@value,9,2))"/>
      <xsl:variable name="lzp" select="string-length(substring-after(@value,'+'))"/>
      <xsl:variable name="lzm" select="string-length(substring-after(@value,'-'))"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             ($L = 0) or             ($L = 4) or              ($L = 6 and $mm &lt;= 12) or              ($L = 8 and $mm &lt;= 12 and $dd &lt;= 31) or              ($L &gt; 14 and $mm &lt;= 12 and $dd &lt;= 31 and $hh &lt; 24 and ($lzp = 4 or $lzm = 4))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="'ClinicalDocument/documentationOf/serviceEvent/effectiveTime'"/>
                  <xsl:text/>/high/@value = "<xsl:text/>
                  <xsl:value-of select="@value"/>
                  <xsl:text/>"  contient  
            une date et heure invalide, différente de aaaa ou aaaamm ou aaaammjj ou aaaammjjhh[mm[ss]][+/-]zzzz 
            en temps local du producteur.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M28"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M28"/>
   <xsl:template match="@*|node()" priority="-2" mode="M28">
      <xsl:apply-templates select="*" mode="M28"/>
   </xsl:template>

   <!--PATTERN serviceEventPerformer-->


	<!--RULE -->
<xsl:template match="cda:ClinicalDocument" priority="1001" mode="M29">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:ClinicalDocument"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="count(cda:documentationOf/cda:serviceEvent/cda:performer) = 1"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : l'en-tête CDA doit comporter un et un seul documentationOf/serviceEvent 
            avec un élément fils performer. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M29"/>
   </xsl:template>

	  <!--RULE -->
<xsl:template match="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:performer"
                 priority="1000"
                 mode="M29">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:performer"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(@nullFlavor)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément documentationOf/serviceEvent/performer doit être renseigné sans nullFlavor. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:effectiveTime/cda:low and                        not(../cda:effectiveTime[@nullFlavor]) and                       not(../cda:effectiveTime/cda:low[@nullFlavor])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément documentationOf/serviceEvent portant l'acte principal documenté
            doit comporter à la fois un fils performer et un petit-fils effectiveTime/low sans attribut nullFlavor. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:assignedEntity/cda:representedOrganization/cda:standardIndustryClassCode"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément documentationOf/serviceEvent/performer correspondant à l'acte principal documenté, 
            doit comporter un descendant assignedEntity/representedOrganization/standardIndustryClassCode. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M29"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M29"/>
   <xsl:template match="@*|node()" priority="-2" mode="M29">
      <xsl:apply-templates select="*" mode="M29"/>
   </xsl:template>

   <!--PATTERN telecom-->


	<!--RULE -->
<xsl:template match="cda:telecom" priority="1000" mode="M30">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl" context="cda:telecom"/>
      <xsl:variable name="prefix" select="substring-before(@value, ':')"/>
      <xsl:variable name="suffix" select="substring-after(@value, ':')"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (count(@*) = 1 and name(@*) = 'nullFlavor' and             (@* = 'UNK' or @* = 'NASK' or @* = 'ASKU' or @* = 'NAV' or @* = 'MSK')) or             ($suffix and (             $prefix = 'tel' or              $prefix = 'fax' or              $prefix = 'mailto' or              $prefix = 'http' or              $prefix = 'ftp' or              $prefix = 'mllp'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : <xsl:text/>
                  <xsl:value-of select="name(.)"/>
                  <xsl:text/> n'est pas conforme à une adresse de télécommunication préfixe:chaîne 
            (avec préfixe = tel, fax, mailto, http, ftp ou mllp) 
            ou est vide et sans nullFlavor, ou contient un nullFlavor non admis.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M30"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M30"/>
   <xsl:template match="@*|node()" priority="-2" mode="M30">
      <xsl:apply-templates select="*" mode="M30"/>
   </xsl:template>

   <!--PATTERN abdomenPhysicalExam-errorsIHE PCC v3.0 Abdomen Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Abdomen Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.31&#34;]"
                 priority="1000"
                 mode="M31">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.31&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: Cet élément ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;10191-5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: Le code de la section Abdomen doit être 10191-5
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: L'élément 'codeSystem' doit être codé à partir de la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M31"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M31"/>
   <xsl:template match="@*|node()" priority="-2" mode="M31">
      <xsl:apply-templates select="*" mode="M31"/>
   </xsl:template>

   <!--PATTERN activeProblemSection-errorsIHE PCC v3.0 Active Problems Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Active Problems Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.6&#34;]" priority="1000"
                 mode="M32">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.6&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Active Problems' ne peut être utilisé que comme section.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.11&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le templateId parent de la section 'Active Problems' (2.16.840.1.113883.10.20.1.11) doit être présent</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;11450-4&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: Le code de la section 'Active Problems' doit être '11450-4'</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> Error: The section
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 
            'Active Problems' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1)</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Une section "Active Problems" doit contenir des entrée de type "Problem Concern Entry"</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M32"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M32"/>
   <xsl:template match="@*|node()" priority="-2" mode="M32">
      <xsl:apply-templates select="*" mode="M32"/>
   </xsl:template>

   <!--PATTERN assessmentAndPlan-errorsIHE PCC v3.0 Assessment and Plan Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Assessment and Plan Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.13.2.5&#34;]"
                 priority="1000"
                 mode="M33">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.13.2.5&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: 'Assessment and Plan' ne peut être utilisé que comme section. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;51847-2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: Le code de la section 'Assessment and Plan' doit être 51847-2 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M33"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M33"/>
   <xsl:template match="@*|node()" priority="-2" mode="M33">
      <xsl:apply-templates select="*" mode="M33"/>
   </xsl:template>

   <!--PATTERN carePlan-errorsIHE PCC v3.0 Care Plan Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Care Plan Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.31&#34;]"
                 priority="1000"
                 mode="M34">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.31&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: 'Care Plan' ne peut être utilisé que comme section. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.10&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: L'OID du template parent de la section 'Care Plan' (2.16.840.1.113883.10.20.1.10) est absent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;18776-5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: Le code de la section 'Care Plan' doit être '18776-5' 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet PCC: L'attribut 'codeSystem' de la section a pour valeur '2.16.840.1.113883.6.1' (LOINC)  
            system (). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M34"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M34"/>
   <xsl:template match="@*|node()" priority="-2" mode="M34">
      <xsl:apply-templates select="*" mode="M34"/>
   </xsl:template>

   <!--PATTERN childFunctionalStatusAssessment-errorsIHE PCC v3.0 Child Functional Status Assessment-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Child Functional Status Assessment</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.3&#34;]"
                 priority="1000"
                 mode="M35">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.3&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Child Functional Status Assessment' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;47420-5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Child Functional Status Assessment' doit être '47420-5'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 'Child Functional Status Assessment' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root =&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.4&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: La section 'Child Functional Status Assessment' ne contient pas de sous-section'Psychomotor development'.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root =&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: La section 'Child Functional Status Assessment' ne contient pas de sous-section'Eating and sleeping Assessment'.        
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M35"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M35"/>
   <xsl:template match="@*|node()" priority="-2" mode="M35">
      <xsl:apply-templates select="*" mode="M35"/>
   </xsl:template>

   <!--PATTERN childFunctionalStatusEatingSleeping-errorsIHE PCC v3.0 Eating and sleeping Assessment-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Eating and sleeping Assessment</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.5&#34;]"
                 priority="1000"
                 mode="M36">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.5&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Eating and sleeping Assessment' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;47420-5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Eating and sleeping Assessment' doit être 'xx-MCH-PsychoMDev'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 'Eating and sleeping Assessment' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M36"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M36"/>
   <xsl:template match="@*|node()" priority="-2" mode="M36">
      <xsl:apply-templates select="*" mode="M36"/>
   </xsl:template>

   <!--PATTERN childFunctionalStatusPsychoMot-errorsIHE PCC v3.0 Psychomotor Development-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Psychomotor Development</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.4&#34;]"
                 priority="1000"
                 mode="M37">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.7.3.1.1.13.4&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: 'Psychomotor Development' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;xx-MCH-PsychoMDev&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: Le code de la section 'Psychomotor Development' doit être 'xx-MCH-PsychoMDev'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;1.2.250.1.213.1.1.4.14&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: L'élément 'codeSystem' de la section 'Psychomotor Deveopment' doit être codé dans la nomenclature TA_CS 
            (1.2.250.1.213.1.1.4.14). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M37"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M37"/>
   <xsl:template match="@*|node()" priority="-2" mode="M37">
      <xsl:apply-templates select="*" mode="M37"/>
   </xsl:template>

   <!--PATTERN CodedAntenatalTestingAndSurveillance-errorsIHE PCC v3.0 Coded Antenatal Testing and Surveillance Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Coded Antenatal Testing and Surveillance Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.5.1&#34;]"
                 priority="1000"
                 mode="M38">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.5.1&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Coded Antenatal Testing and Surveillance' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;57078-8&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Prenatal Events' doit être '57078-8'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 
            'Coded Antenatal Testing and Surveillance Section' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'OID du template parent de la section 'Coded physical Exam' est absent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.3.10&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Une section 'Antenatal Testing and Surveillance' doit contenir un élément 'Antenatal Testing and Surveillance Battery'.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M38"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M38"/>
   <xsl:template match="@*|node()" priority="-2" mode="M38">
      <xsl:apply-templates select="*" mode="M38"/>
   </xsl:template>

   <!--PATTERN codedPhysicalExam-errorsIHE PCC v3.0 Physical Exam Section - errors validation phase-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Physical Exam Section - errors validation phase</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.15.1&#34;]"
                 priority="1000"
                 mode="M39">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.15.1&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Coded physical Exam' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.15&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'OID du template parent de la section 'Coded physical Exam' est absent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.24&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: L'OID du template parent de la section 'Coded physical Exam' est absent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;29545-1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: Le code de la section 'Coded physical Exam' doit être '29545-1'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: L'élément 'codeSystem' de la section 'Coded physical exam' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M39"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M39"/>
   <xsl:template match="@*|node()" priority="-2" mode="M39">
      <xsl:apply-templates select="*" mode="M39"/>
   </xsl:template>

   <!--PATTERN codedResults-errorsIHE PCC Coded Results Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC Coded Results Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.28&#34;]"
                 priority="1000"
                 mode="M40">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.28&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité au volet CSE: 'Coded Results' ne peut être utilisé que comme section.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = '30954-2']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité au volet CSE: Le code de la section 'Coded Results' doit être '30954-2'</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: L'élément 'codeSystem' de la section 
            'Coded Results' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1).</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M40"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M40"/>
   <xsl:template match="@*|node()" priority="-2" mode="M40">
      <xsl:apply-templates select="*" mode="M40"/>
   </xsl:template>

   <!--PATTERN codedSocialHistory-errorsIHE PCC v3.0 Coded Social History Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Coded Social History Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.16.1&#34;]"
                 priority="1000"
                 mode="M41">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.16.1&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: le templateId de 'Coded Social History' ne peut être utilisé que pour une section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.16&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: L'OID du template parent de la section 'Coded Social History' est absent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;29762-2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: Le code de la section 'Coded Social History' doit être 29762-2
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet PCC: L'attribut 'codeSystem' de la section 'Coded Social History' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.13.4&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            
            Erreur de Conformité volet PCC: La section "Coded Social History"  doit contenir des éléments d'entrée "Social History Observation".
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M41"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M41"/>
   <xsl:template match="@*|node()" priority="-2" mode="M41">
      <xsl:apply-templates select="*" mode="M41"/>
   </xsl:template>

   <!--PATTERN codedVitalSigns-errorsIHE PCC v3.0 Coded Vital Signs Section - errors validation phase-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Coded Vital Signs Section - errors validation phase</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.5.3.2&#34;]"
                 priority="1000"
                 mode="M42">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.5.3.2&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet CSE: ce template ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;8716-3&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet CSE: Le code de la section Coded Vital signs doit être 8716-3
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet CSE: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.25&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité volet CSE: L'identifiant du template parent pour la section est absent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.13.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité volet CSE: Une section 'Coded Vital Signs' doit contenir un élément 'Vital Signs Organizer'.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M42"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M42"/>
   <xsl:template match="@*|node()" priority="-2" mode="M42">
      <xsl:apply-templates select="*" mode="M42"/>
   </xsl:template>

   <!--PATTERN EarsPhysicalExam-errorsIHE PCC v3.0 Ears Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Ears Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.21&#34;]"
                 priority="1000"
                 mode="M43">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.21&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Ce template ne peut être utilisé que pour une section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;10195-6&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Ears' doit être 10195-6
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 'Ears' 
            doit être codé à partir de la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M43"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M43"/>
   <xsl:template match="@*|node()" priority="-2" mode="M43">
      <xsl:apply-templates select="*" mode="M43"/>
   </xsl:template>

   <!--PATTERN encounterHistoriesSection-errorsIHE PCC v3.0 Encounter Histories Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Encounter Histories Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.5.3.3&#34;]"
                 priority="1000"
                 mode="M44">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.5.3.3&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: 'Encounter Histories' ne peut être utilisé que comme section.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.3&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: Les templateId des parents doivent être présents. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;46240-8&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: Le code de la section 'Encounter Histories' doit être '46240-8'</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = '2.16.840.1.113883.6.1']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 
            'Encounter Histories' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1).</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.14&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Une section "Encounter Histories" doit contenir des entrée de type "Encounters".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M44"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M44"/>
   <xsl:template match="@*|node()" priority="-2" mode="M44">
      <xsl:apply-templates select="*" mode="M44"/>
   </xsl:template>

   <!--PATTERN endocrinePhysicalExam-errorsIHE PCC v3.0 Endocrine system-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Endocrine system</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.25&#34;]"
                 priority="1000"
                 mode="M45">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.25&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Système Endocrinien et Métabolique ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;29307-6&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section Système Endocrinien et Métabolique doit être 29307-6
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M45"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M45"/>
   <xsl:template match="@*|node()" priority="-2" mode="M45">
      <xsl:apply-templates select="*" mode="M45"/>
   </xsl:template>

   <!--PATTERN eyesPhysicalExam-errorsIHE PCC v3.0 Eyes-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Eyes</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.19&#34;]"
                 priority="1000"
                 mode="M46">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.19&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Ce templateId 'Eyes' ne peut être utilisé que pour une section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;10197-2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Eyes' doit être 10197-2
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' de la section 'Eyes' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M46"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M46"/>
   <xsl:template match="@*|node()" priority="-2" mode="M46">
      <xsl:apply-templates select="*" mode="M46"/>
   </xsl:template>

   <!--PATTERN generalAppearancePhysicalExam-errorsIHE PCC v3.0 General appearance Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 General appearance Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.16&#34;]"
                 priority="1000"
                 mode="M47">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.16&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Cet élément ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;10210-3&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section Système cutané doit être 10210-3
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M47"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M47"/>
   <xsl:template match="@*|node()" priority="-2" mode="M47">
      <xsl:apply-templates select="*" mode="M47"/>
   </xsl:template>

   <!--PATTERN genitaliaPhysicalExam-errorsIHE PCC v3.0 Genitalia-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Genitalia</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.36&#34;]"
                 priority="1000"
                 mode="M48">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.36&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                Erreur de Conformité PCC: Cet élément ne peut être utilisé que comme section.
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;11400-9&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                Erreur de Conformité PCC: Le code de la section doit être 11400-9
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
                (2.16.840.1.113883.6.1). 
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M48"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M48"/>
   <xsl:template match="@*|node()" priority="-2" mode="M48">
      <xsl:apply-templates select="*" mode="M48"/>
   </xsl:template>

   <!--PATTERN heartPhysicalExam-errorsIHE PCC v3.0 Heart Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Heart Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.29&#34;]"
                 priority="1000"
                 mode="M49">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.29&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'entité 'Système Cardiaque' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;10200-4&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Système Cardiaque' doit être 10200-4
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'attribut 'codeSystem' de la section 'Système Cardiaque'doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M49"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M49"/>
   <xsl:template match="@*|node()" priority="-2" mode="M49">
      <xsl:apply-templates select="*" mode="M49"/>
   </xsl:template>

   <!--PATTERN historyOfPastIllness-errorsIHE PCC v3.0 History of Past Illness Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 History of Past Illness Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.8&#34;]" priority="1000"
                 mode="M50">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.8&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: History of Past Illness ne peut être utilisé que dans une section. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;11348-0&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section History of Past Illness doit être 11348-0 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            
            Erreur de Conformité PCC: History of Past Illness doit contenir des éléments Problem Concern Entry.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M50"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M50"/>
   <xsl:template match="@*|node()" priority="-2" mode="M50">
      <xsl:apply-templates select="*" mode="M50"/>
   </xsl:template>

   <!--PATTERN immunizations-errorsIHE PCC v3.0 Immunizations Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Immunizations Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.23&#34;]"
                 priority="1000"
                 mode="M51">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.3.23&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Immunizations ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.6&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'OID de l'élément parent n'est pas présent.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;11369-6&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section doit être 11369-6 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.12&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Une section Immunizations doit contenir au moins une entrée Immunization.           
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M51"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M51"/>
   <xsl:template match="@*|node()" priority="-2" mode="M51">
      <xsl:apply-templates select="*" mode="M51"/>
   </xsl:template>

   <!--PATTERN integumentaryPhysicalExam-errorsIHE PCC v3.0 Integumentary System-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Integumentary System</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.17&#34;]"
                 priority="1000"
                 mode="M52">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.17&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Cet élément ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;29302-7&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Système cutané' doit être 29302-7
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M52"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M52"/>
   <xsl:template match="@*|node()" priority="-2" mode="M52">
      <xsl:apply-templates select="*" mode="M52"/>
   </xsl:template>

   <!--PATTERN laborAndDeliverySection-errorsIHE PCC Labor and Delivery section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC Labor and Delivery section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.3&#34;]"
                 priority="1000"
                 mode="M53">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.3&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: le templateId de 'Labor and Delivery' ne peut être utilisé que pour une section. 
    </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;XX-LABORANDDELIVERY&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code utilisé pour la section "Labor and Delivery" doit être "XX-LABORANDDELIVERY" 
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: La nomenclature LOINC doit être utilisée pour coder la section "Labor and Delivery"  
        system (2.16.840.1.113883.6.1). 
    </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M53"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M53"/>
   <xsl:template match="@*|node()" priority="-2" mode="M53">
      <xsl:apply-templates select="*" mode="M53"/>
   </xsl:template>

   <!--PATTERN lymphaticPhysicalExam-errorsIHE PCC v3.0 Lymphatic System-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Lymphatic System</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.32&#34;]"
                 priority="1000"
                 mode="M54">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.32&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Cet élément ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;11447-0&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section Système cutané doit être 11447-0
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M54"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M54"/>
   <xsl:template match="@*|node()" priority="-2" mode="M54">
      <xsl:apply-templates select="*" mode="M54"/>
   </xsl:template>

   <!--PATTERN musculoPhysicalExam-errorsIHE PCC v3.0 Care Plan Section - errors validation phase-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Care Plan Section - errors validation phase</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.34&#34;]"
                 priority="1000"
                 mode="M55">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.34&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: l'élément 'Musculoskeletal system' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;11410-8&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Musculoskeletal system' doit être 11410-8
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'attribut 'codeSystem' de la section 'Musculoskeletal system' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M55"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M55"/>
   <xsl:template match="@*|node()" priority="-2" mode="M55">
      <xsl:apply-templates select="*" mode="M55"/>
   </xsl:template>

   <!--PATTERN neurologicPhysicalExam-errorsIHE PCC v3.0 Neurologic System-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Neurologic System</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.35&#34;]"
                 priority="1000"
                 mode="M56">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.35&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: "Neurologic System" ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;10202-0&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section "Neurologic System" doit être 10202-0
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'attribut 'codeSystem' de la section "Neurologic System" doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M56"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M56"/>
   <xsl:template match="@*|node()" priority="-2" mode="M56">
      <xsl:apply-templates select="*" mode="M56"/>
   </xsl:template>

   <!--PATTERN pregnancyHistorySection-errorsIHE PCC v3.0 Pregnancy History Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Pregnancy History Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.5.3.4&#34;]"
                 priority="1000"
                 mode="M57">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.5.3.4&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: le templateId de 'Pregnancy History' ne peut être utilisé que pour une section. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;10162-6&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code utilisé pour la section "Pregnancy History" doit être "10162-6" 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: La nomenclature LOINC doit être utilisée pour coder la section "Pregnancy History"  
            system (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root =&#34;1.3.6.1.4.1.19376.1.5.3.1.4.13.5&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Un section Pregnancy History doit comporter des entrées de type Pregnancy Observation</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M57"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M57"/>
   <xsl:template match="@*|node()" priority="-2" mode="M57">
      <xsl:apply-templates select="*" mode="M57"/>
   </xsl:template>

   <!--PATTERN prenatalEvents-errorsIHE PCC v3.0 Prenatal Events Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Prenatal Events Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.2&#34;]"
                 priority="1000"
                 mode="M58">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.2.2&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: 'Prenatal Events' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;57073-9&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: Le code de la section 'Prenatal Events' doit être '57073-9'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité au volet CSE: L'élément 'codeSystem' de la section 
            'Prenatal Events' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root =&#34;1.3.6.1.4.1.19376.1.5.3.1.3.28&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité au volet CSE: La section 'Prenatal Events' ne contient pas de sous-section'Coded Results'.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M58"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M58"/>
   <xsl:template match="@*|node()" priority="-2" mode="M58">
      <xsl:apply-templates select="*" mode="M58"/>
   </xsl:template>

   <!--PATTERN proceduresSection-errorsIHE PCC v3.0 Procedures Section IHE PCC v3.0 Procedures Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Procedures Section</svrl:text>
   <svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Procedures Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.13.2.11&#34;]"
                 priority="1000"
                 mode="M59">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.13.2.11&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                Erreur de Conformité PCC: 'Procedures' ne peut être utilisé que comme section
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;29544-3&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                Erreur de Conformité PCC: Le code de la section 'Procedures' doit être '29544-3'              
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                L'élément 'codeSystem' de la section 
                'Procedures' doit être codé dans la nomenclature LOINC (2.16.840.1.113883.6.1)
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.19&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
                
                Erreur de Conformité PCC: Une section "Procedures and Interventions" doit contenir des entrée de type "Procedures entry"
            </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M59"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M59"/>
   <xsl:template match="@*|node()" priority="-2" mode="M59">
      <xsl:apply-templates select="*" mode="M59"/>
   </xsl:template>

   <!--PATTERN RespiratoryPhysicalExam-errorsIHE PCC v3.0 Respiratory System-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Respiratory System</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.30&#34;]"
                 priority="1000"
                 mode="M60">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.30&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Cet élément ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;11412-4&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section doit être 11412-4
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M60"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M60"/>
   <xsl:template match="@*|node()" priority="-2" mode="M60">
      <xsl:apply-templates select="*" mode="M60"/>
   </xsl:template>

   <!--PATTERN teethPhysicalExam-errorsIHE PCC v3.0 Mouth, Throat and Teeth section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Mouth, Throat and Teeth section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.23&#34;]"
                 priority="1000"
                 mode="M61">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.9.23&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:section"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: section 'Mouth, Throat and Teeth' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code = &#34;10201-2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le code de la section 'Mouth, Throat and Teeth' doit être 10200-4
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem = &#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'codeSystem' doit être codé dans la nomenclature LOINC 
            (2.16.840.1.113883.6.1). 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M61"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M61"/>
   <xsl:template match="@*|node()" priority="-2" mode="M61">
      <xsl:apply-templates select="*" mode="M61"/>
   </xsl:template>

   <!--PATTERN ACPimageIllustrative-->


	<!--RULE -->
<xsl:template match="*[cda:templateId/@root = $templateObservationMedia]" priority="1000"
                 mode="M62">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root = $templateObservationMedia]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:observationMedia[@classCode = 'OBS' and @moodCode = 'EVN' and @ID]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité CI-SIS : 
            Le template d'image illustrative doit apparaître dans un élément observationMedia 
            de classCode 'OBS' et de moodCode 'EVN', et identifié par un attribut ID. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:value[(@mediaType = 'image/gif' or @mediaType = 'image/jpeg' or @mediaType = 'image/png') and @representation = 'B64']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité CI-SIS : 
            L'image doit être un gif, un png ou un jpeg, encodé en base 64 dans le sous-élément value. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M62"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M62"/>
   <xsl:template match="@*|node()" priority="-2" mode="M62">
      <xsl:apply-templates select="*" mode="M62"/>
   </xsl:template>

   <!--PATTERN ClinicalStatusCodes-->


	<!--RULE -->
<xsl:template match="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.1']/cda:value"
                 priority="1000"
                 mode="M63">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.1']/cda:value"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/entryRelationship/observation/entryRelationship/observation/value"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/entryRelationship/observation/entryRelationship/observation/value"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_ClinicalStatusCodes)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/entryRelationship/observation/entryRelationship/observation/value"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_ClinicalStatusCodes"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M63"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M63"/>
   <xsl:template match="@*|node()" priority="-2" mode="M63">
      <xsl:apply-templates select="*" mode="M63"/>
   </xsl:template>

   <!--PATTERN codedAntenatalTestingAndSurveillanceOrg-errorsIHE PCC v3.0 Coded Antenatal Testing and Surveillance Organizer-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Coded Antenatal Testing and Surveillance Organizer</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.3.10&#34;]"
                 priority="1000"
                 mode="M64">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.1.21.3.10&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:organizer"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Conformité PCC v3.0 (Erreur):' ne peut être utilisé que comme organizer.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code=&#34;XX-ANTENATALTESTINGBATTERY&#34; and              @displayName=&#34;ANTENATAL TESTING AND SURVEILLANCE BATTERY&#34; and             @codeSystem=&#34;2.16.840.1.113883.6.1&#34; and             @codeSystemName=&#34;LOINC&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: L'élément &lt;code&gt; de l'organizer "Antenatal Testing and Surveillance"est requis, et 
            identifie celui-ci comme un organizer contenant des données de test et de surveillance: &lt;code code='XX-ANTENATALTESTINGBATTERY'
            displayName='ANTENATAL TESTING AND SURVEILLANCE BATTERY' codeSystem='2.16.840.1.113883.6.1' codeSystemName="LOINC"</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:component/cda:observation/cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.13&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'Coded Antenatal Testing and Surveillance Organizer' doit 
            au moins contenir une entrée 'Simple Observation' (1.3.6.1.4.1.19376.1.5.3.1.4.13)
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: "Coded Antenatal Testing and Surveillance Organizer" aura nécessairement un identifiant &lt;id&gt;.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code=&#34;completed&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: La valeur de l'élément "statusCode" de "Coded Antenatal Testing and Surveillance Organizer" est fixée à "completed".
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: l'élément effectiveTime est requis. Il indique quand l'observation a été faite.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M64"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M64"/>
   <xsl:template match="@*|node()" priority="-2" mode="M64">
      <xsl:apply-templates select="*" mode="M64"/>
   </xsl:template>

   <!--PATTERN codedVitalSignsOrg-errorsIHE PCC v3.0 Vital Signs Organizer-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Vital Signs Organizer</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.13.1&#34;]"
                 priority="1000"
                 mode="M65">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.13.1&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:organizer"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: 'Coded physical Exam' ne peut être utilisé que comme section.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code =&#34;F-03400&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Le codage de l'élément 'Vital Signs Organizer' doit être 'F-03400'.           
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@codeSystem =&#34;1.2.250.1.213.2.12&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'attribut 'codeSystem' de l'élément 'Vital Signs Organizer' a pour valeur '1.2.250.1.213.2.12' (SNOMED 3.5)           
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root = &#34;2.16.840.1.113883.10.20.1.32&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'identifiant du template parent (2.16.840.1.113883.10.20.1.32) doit être présent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root = &#34;2.16.840.1.113883.10.20.1.35&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'identifiant du template parent (2.16.840.1.113883.10.20.1.35) doit être présent. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test=".//cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.13.2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: L'élément 'Vital Sign Organizer' doit au moins contenir une entrée 'Vital Sign Observation'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M65"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M65"/>
   <xsl:template match="@*|node()" priority="-2" mode="M65">
      <xsl:apply-templates select="*" mode="M65"/>
   </xsl:template>

   <!--PATTERN comments-errorsIHE PCC v3.0 Comments - errors validation phase-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Comments - errors validation phase</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.2&#34;]" priority="1000"
                 mode="M66">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.2&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.40&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: Le templateId CCD (2.16.840.1.113883.10.20.1.40) de l'entrée
                Comments doit être déclaré.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code=&#34;48767-8&#34; and                 @codeSystem=&#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: L'élément "code" pour l'entrée "Comments" est requis. Ses attributs "code" et "codeSystem"
                sont obligatoires (cf. CI-SIS Volet de contenu CDA)</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code = &#34;completed&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: La valeur de l'élément "code" de "statusCode" est toujours fixée à "completed". </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:author) or (                 cda:author/cda:time and                 cda:author/cda:assignedAuthor/cda:id and                 cda:author/cda:assignedAuthor/cda:addr and                 cda:author/cda:assignedAuthor/cda:telecom and                 cda:author/cda:assignedAuthor/cda:assignedPerson/cda:name and                 cda:author/cda:assignedAuthor/cda:representedOrganization/cda:name)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: Un élément "Comment" peut avoir un auteur.
                L'horodatage de la création de l'élément "Comment" est réalisé à partir de l'élément "time" lorsque l'élément "author" est présent.
                L'identifiant de l'auteur (id), son adresse (addr) et son numéro de téléphone (telecom) sont dans ce cas obligatoires. 
                Le nom de l'auteur et/ou celui de l'organisation qu'il représente doit être présent.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M66"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M66"/>
   <xsl:template match="@*|node()" priority="-2" mode="M66">
      <xsl:apply-templates select="*" mode="M66"/>
   </xsl:template>

   <!--PATTERN concernEntry-errorsIHE PCC v3.0 Concern Entry-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Concern Entry</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.1&#34;]"
                 priority="1000"
                 mode="M67">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.1&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:act"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: L'entrée "Concern Entry" ne peut être utilisée que comme un élément "act".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="../cda:act[@classCode=&#34;ACT&#34;] and ../cda:act[@moodCode=&#34;EVN&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: une entrée "Concern Entry" est l'acte ("act classCode='ACT'") qui consiste 
                à enregistrer un événement (moodCode='EVN') relatif à un problème, une allergie ou tout autre élément se rapportant
                à l'état clinique d'un patient.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.27&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: Ces élément templateId indiquent que l'entrée "Concern Entry" se conforme 
                au module de contenu Concern. Celui-ci hérite des contraintes du template HL7 CCD pour les "problem acts", 
                et déclarera sa conformité à patir du templateId 2.16.840.1.113883.10.20.1.27.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: L'entrée "Concern Entry" requiert un élément "id".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@nullFlavor=&#34;NAV&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: l'élément "code" n'est pas applicable à un élément "Concern Entry", et prendra la valeur nullFlavor='NAV'.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code=&#34;active&#34; or                  @code=&#34;suspended&#34; or                 @code=&#34;aborted&#34; or                 @code=&#34;completed&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: L'élément "statusCode" associé à tout élément concern doit prendre l'une des valeurs suivantes: 
                "active", "suspended", "aborted" ou "completed".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime/cda:low"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: l'élément "effectiveTime" indiquele début et la fin de la période durant laquelle l'élément "Concern Entry" était actif. 
                Son composant "low" sera au moins présent.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(cda:statusCode[@code=&#34;completed&#34; or @code=&#34;aborted&#34;] and cda:effectiveTime/cda:high) or                 (cda:statusCode[@code=&#34;active&#34; or @code=&#34;suspended&#34;] and not(cda:effectiveTime/cda:high))"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: l'élément "effectiveTime" indiquele début et la fin de la période durant laquelle l'élément 
                "Concern Entry" était actif. 
                Son composant "high" sera présent pour les es éléments "Concern entry" ayant un statut "completed" ou "aborted" 
                et sera absent dans tous les autres cas</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(cda:entryRelationship[@typeCode=&#34;SUBJ&#34;] and cda:entryRelationship/*/cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5&#34; or @root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.6&#34;]) or                   (cda:sourceOf[@typeCode=&#34;SUBJ&#34; and @inversionInd=&#34;false&#34;] and cda:sourceOf/*/cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5&#34; or @root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.6&#34;]) "/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur de Conformité PCC: Tout élément "Concern Entry" concerne un ou plusieurs problèmes ou allergies. 
                Cette entrée contient une ou plusieurs entrées qui se conforment aux spécifications de "Problem Entry" ou "Allergies and Intolerance Entry" 
                permettant à une série d'observations d'être regroupées en un unique élément "Concern Entry", ce à partir de liens de type entryRelationship 
                d'attribut typeCode='SUBJ' et inversionInd='false'</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M67"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M67"/>
   <xsl:template match="@*|node()" priority="-2" mode="M67">
      <xsl:apply-templates select="*" mode="M67"/>
   </xsl:template>

   <!--PATTERN encountersEntry-errorsIHE PCC v3.0 Encounters - errors validation phase-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Encounters - errors validation phase</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.14']"
                 priority="1000"
                 mode="M68">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.14']"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="@classCode='ENC'"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", l'attribut "classCode" sera fixé à la valeur "ENC". </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(@moodCode='EVN') or cda:templateId[@root='2.16.840.1.113883.10.20.1.21']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", le templateId indique que cet élément 
            se conforme aux contraintes de ce module de contenu.
            NOTE: Lorsque l'entrée "Encounters",est en mode événement, (moodCode='EVN'), cette entrée 
            se conforme au template CCD 2.16.840.1.113883.10.20.1.21, et dans les autres modes, 
            elle se conformera au template CCD 2.16.840.1.113883.10.20.1.25. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="@moodCode='EVN' or cda:templateId[@root='2.16.840.1.113883.10.20.1.25']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", le templateId indique que cet élément 
            se conforme aux contraintes de ce module de contenu.
            NOTE: Lorsque l'entrée "Encounters",est en mode événement, (moodCode='EVN'), cette entrée 
            se conforme au template CCD 2.16.840.1.113883.10.20.1.21, et dans les autres modes, 
            elle se conformera au template CCD 2.16.840.1.113883.10.20.1.25. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
        Erreur de Conformité PCC: Dans une entrée "Encounters", l'élément "id" est obligatoire. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:text/cda:reference"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", l'élément "text" contiendra
            une référence à la partie narrative décrivant l'événement. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(@moodCode = 'EVN' or @moodCode = 'APT') or cda:effectiveTime"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", l'élément "effectiveTime" 
            horodate l'événement (en mode EVN), ou la date désirée pour la rencontre, en mode ARQ or APT.
            En mode EVN ou APT, l'élément "effectiveTime" sera présent. En mode ARQ, l'élément "effectiveTime" 
            pourra être présent, et dans le cas contraire, l'élément "priorityCode" sera présent, 
            pour indiquer qu'un rappel est nécessaire pour fixer la date de rendez-vous pour la rencontre. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="@moodCode='ARQ' and (cda:effectiveTime or cda:priorityCode)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", en mode ARQ mood, si l'élément "effectiveTime" est absent,
            alors l'élément "priorityCode" sera présent. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:participant[@typeCode='LOC']) or                  cda:participant[@typeCode='LOC']/cda:participantRole[@classCode='SDLOC']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", un élément "participant" avec un attribut "typeCode" 
            LOC pourra être présent pour donner l'indication sur le lieu où la rencontre doit ou s'est tenue. 
            Cet élément aura un élément "participantRole" d'attribut classCode='SDLOC' décrivant la localisation du service. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:particpant[@typeCode='LOC']) or                 cda:participant[@typeCode='LOC']/cda:playingEntity/cda:name"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Dans une entrée "Encounters", un élément "participant" d'attribut "typeCode='LOC'" 
            désignera un élément "playingEntity" avec son nom. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(@moodCode='ARQ') or cda:effectiveTime"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC (alerte): Dans une entrée "Encounters", en mode ARQ, 
            l'élément "effectiveTime" doit être présent. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(@moodCode='EVN') or cda:performer"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC (alerte): Dans une entrée "Encounters", en mode EVN mood, au moisn
            un élément "performer" devrait être présentpour identifier la personne délivrant un service (soins, consultation...)
            durant la rencontre. Plus d'un élément "performer" pourront être présents. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:particpant[@typeCode='LOC']) or                 cda:participant[@typeCode='LOC']/cda:addr"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC (alerte): Dans une entrée "Encounters", un élément "addr" devrait être présent
            comme partie de l'élément "participant" d'attribut "typeCode='LOC'". </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:particpant[@typeCode='LOC']) or                 cda:participant[@typeCode='LOC']/cda:telecom"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC (alerte): Dans une entrée "Encounters", un élément "telecom" devrait être présent
            comme partie de l'élément "participant" d'attribut "typeCode='LOC'". </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M68"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M68"/>
   <xsl:template match="@*|node()" priority="-2" mode="M68">
      <xsl:apply-templates select="*" mode="M68"/>
   </xsl:template>

   <!--PATTERN HealthStatusCodes-->


	<!--RULE -->
<xsl:template match="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.2']/cda:value"
                 priority="1000"
                 mode="M69">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.2']/cda:value"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="entryRelationship/observation/value"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="entryRelationship/observation/value"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_HealthStatusCodes)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="entryRelationship/observation/value"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_HealthStatusCodes"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M69"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M69"/>
   <xsl:template match="@*|node()" priority="-2" mode="M69">
      <xsl:apply-templates select="*" mode="M69"/>
   </xsl:template>

   <!--PATTERN immunizationsEnt-errorsIHE PCC v3.0 Immunizations Section-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Immunizations Section</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.12']"
                 priority="1000"
                 mode="M70">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.12']"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="@negationInd=&#34;true&#34; or @negationInd=&#34;false&#34;"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            Une entrée 'Immunization' peut être le moyen de notifier qu'une vaccination spécifique n'a pas eu lieu, et pourquoi. 
            Dans ce cas, l'attribut negationInd prendra la valeur 'true' et dans tous les autres cas la valeur 'false'.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.24&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Immunization doit notifier l'OID du template CCD parent (2.16.840.1.113883.10.20.1.24).
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: Une vaccination aura un identifiant (id).
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code and @codeSystem]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            Cet élément obligatoire indique que l'acte effectué est une vaccination. 
            L'élément act substance administration doit présenter un élément 'code' avec des attributs 'code' et 'codeSystem' obligatoirement présents.
            Si aucun système de codage est utilisé, on utilisera les valeurs code='IMMUNIZ' codeSystem='2.16.840.1.113883.5.4' codeSystemName='ActCode'
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code=&#34;completed&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: L'élément 'statusCode' prendra la valeur 'completed' pour toutes les vaccinations.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime[@value or @nullFlavor]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            
            Dans Immunizations, l'élément 'effectiveTime' sera obligatoirement présent, indiquant l'horodatage de la vaccination.
            Si la date est inconnue, l'attribut nullFlavor sera utilisé.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:consumable//cda:manufacturedProduct//cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.7.2&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            Dans 'Immunizations', l'élément 'consumable' sera présent, and contiendra une entrée 'manufacturedProduc' se conformant au 
            template 'Product Entry template' (1.3.6.1.4.1.19376.1.5.3.1.4.7.2).
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:entryRelationship[@inversionInd=&#34;false&#34; and @typeCode=&#34;CAUS&#34;]) or             (cda:entryRelationship[@inversionInd=&#34;false&#34; and @typeCode=&#34;CAUS&#34;]//cda:observation/cda:id and             cda:entryRelationship[@inversionInd=&#34;false&#34; and @typeCode=&#34;CAUS&#34;]//cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.28&#34;] and             cda:entryRelationship[@inversionInd=&#34;false&#34; and @typeCode=&#34;CAUS&#34;]//cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5&#34;] and             cda:entryRelationship[@inversionInd=&#34;false&#34; and @typeCode=&#34;CAUS&#34;]//cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.54&#34;])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>   
            Erreur de Conformité PCC: 
            Dans 'Immunizations', un élément entryRelationship pourra être utilisé pour identifier d'éventuelles réactions adverses 
            causées par la vaccination.
            Dans ce cas l'identifiant (id) de l'observation est requis. 
            L'observation se conformara au template 'Problem Entry', ainsi qu'au template 'CCD Reaction'.

        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:entryRelationship/cda:observation/cda:code[@code=&#34;30973-2&#34; and @codeSystem=&#34;2.16.840.1.113883.6.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            dans l'élément entryRelationship permettant d'assigner le rang de la vaccination 
            dans une série de vaccinations effectuée (1ère vaccination, deuxième, etc), l'élément 'code' sera présent et ses 
            attributs prendront les valeurs (code='30973-2' displayName='Dose Number' codeSystem='2.16.840.1.113883.6.1' codeSystemName='LOINC')
            Cet élément indique que l'observation concerne le rand de la vaccination. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(ancestor::*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.12&#34;]) or              cda:statusCode[@code=&#34;completed&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            dans l'élément entryRelationship permettant d'assigner le rang de la vaccination 
            dans une série de vaccinations effectuée (1ère vaccination, deuxième, etc), l'élément 'statusCode', obligatoire, 
            prendra la valeur 'completed'.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:entryRelationship/cda:observation/cda:value[@value]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: 
            Dans une entrée 'Immunization', dans l'élément entryRelationship permettant d'assigner le rang de la vaccination 
            dans une série de vaccinations effectuée (1ère vaccination, deuxième, etc), l'élément 'value' sera présent et 
            indiquera le numéro de lot du vaccin.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M70"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M70"/>
   <xsl:template match="@*|node()" priority="-2" mode="M70">
      <xsl:apply-templates select="*" mode="M70"/>
   </xsl:template>

   <!--PATTERN observationInterpretation-->


	<!--RULE -->
<xsl:template match="cda:observation/cda:interpretationCode" priority="1000" mode="M71">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:observation/cda:interpretationCode"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 1)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'observation/interpretationCode'"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (1 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="'observation/interpretationCode'"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_observationInterpretation)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="'observation/interpretationCode'"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_observationInterpretation"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M71"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M71"/>
   <xsl:template match="@*|node()" priority="-2" mode="M71">
      <xsl:apply-templates select="*" mode="M71"/>
   </xsl:template>

   <!--PATTERN ProblemCodes-->


	<!--RULE -->
<xsl:template match="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.5' and not (cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.6')]/cda:code"
                 priority="1000"
                 mode="M72">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.5' and not (cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.6')]/cda:code"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/code"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/code"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_ProblemCodes)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/code"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_ProblemCodes"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M72"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M72"/>
   <xsl:template match="@*|node()" priority="-2" mode="M72">
      <xsl:apply-templates select="*" mode="M72"/>
   </xsl:template>

   <!--PATTERN AllergyAndIntoleranceCodes-->


	<!--RULE -->
<xsl:template match="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.6']/cda:code"
                 priority="1000"
                 mode="M73">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.6']/cda:code"/>
      <xsl:variable name="att_code" select="@code"/>
      <xsl:variable name="att_codeSystem" select="@codeSystem"/>
      <xsl:variable name="att_displayName" select="@displayName"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(not(@nullFlavor) or 0)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/code"/>
                  <xsl:text/>" ne doit pas comporter d'attribut nullFlavor.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             (@code and @codeSystem and @displayName) or             (0 and              (@nullFlavor='UNK' or @nullFlavor='NASK' or @nullFlavor='ASKU' or @nullFlavor='NAV' or @nullFlavor='MSK')) or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE'))             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de conformité CI-SIS : L'élément "<xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/code"/>
                  <xsl:text/>" doit avoir ses attributs 
            @code, @codeSystem et @displayName renseignés, ou si le nullFlavor est autorisé, une valeur admise pour cet attribut, ou un xsi:type différent de CD ou CE.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(             @nullFlavor or             (@xsi:type and not(@xsi:type = 'CD') and not(@xsi:type = 'CE')) or              (document($jdv_AllergyAndIntoleranceCodes)//svs:Concept[@code=$att_code and @codeSystem=$att_codeSystem])             )"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
        
            Erreur de conformité CI-SIS : L'élément <xsl:text/>
                  <xsl:value-of select="ClinicalDocument/component/structuredBody/component/section/component/section/entry/observation/code"/>
                  <xsl:text/>
            [<xsl:text/>
                  <xsl:value-of select="$att_code"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_displayName"/>
                  <xsl:text/>:<xsl:text/>
                  <xsl:value-of select="$att_codeSystem"/>
                  <xsl:text/>] 
            doit faire partie du jeu de valeurs <xsl:text/>
                  <xsl:value-of select="$jdv_AllergyAndIntoleranceCodes"/>
                  <xsl:text/>.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M73"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M73"/>
   <xsl:template match="@*|node()" priority="-2" mode="M73">
      <xsl:apply-templates select="*" mode="M73"/>
   </xsl:template>

   <!--PATTERN problemConcernEntry-errorsIHE PCC v3.0 Problem Concern Entry - errors validation phase-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Problem Concern Entry - errors validation phase</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.2&#34;]"
                 priority="1000"
                 mode="M74">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.2&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root = &#34;1.3.6.1.4.1.19376.1.5.3.1.4.5.1&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Problem Concern Entry a un template OID 1.3.6.1.4.1.19376.1.5.3.1.4.5.2. 
            Elle spécialise Concern Entry et doit donc se conformer à ses spécifications 
            en déclarant son template OID qui est 1.3.6.1.4.1.19376.1.5.3.1.4.5.1. Ces éléments 
            sont requis.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root = &#34;2.16.840.1.113883.10.20.1.27&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Le template parent de Problem Concern est absent.
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(cda:entryRelationship[@typeCode = &#34;SUBJ&#34;] and                 cda:entryRelationship//cda:templateId[@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.5&#34;] and                 cda:entryRelationship[@inversionInd=&#34;false&#34;])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur de Conformité PCC: Problem Concern Entry contiendra une ou plusieurs entrées qui se conformeront 
            au template Problem Entry (1.3.6.1.4.1.19376.1.5.3.1.4.5) et qui se matérialiseront sous 
            la forme d'éléments 'entryRelationship'. L'attribut 'typeCode' prend la valeur 'SUBJ'
            et l'attribut 'inversionInd' prend la valeur 'false'. 
        </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M74"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M74"/>
   <xsl:template match="@*|node()" priority="-2" mode="M74">
      <xsl:apply-templates select="*" mode="M74"/>
   </xsl:template>

   <!--PATTERN problemEntry-errorsIHE PCC v3.0 Problem Entry-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Problem Entry</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.5']" priority="1000"
                 mode="M75">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.5']"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="self::cda:observation[@classCode='OBS' and @moodCode='EVN']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur [problemEntry]: Dans l'élément "Problem Entry", le format de base utilisé pour 
            représenter un problème utilise l'élément CDA 'observation' d'attribut classCode='OBS' pour
            signifier qu'il s'agit l'observation d'un problème, et moodCode='EVN', pour exprimer 
            que l'événement a déjà eu lieu. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:templateId[@root='2.16.840.1.113883.10.20.1.28']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: Dans l'élément "Problem Entry", les éléments &lt;templateId&gt; 
            identifient l'entrée comme répondant aux spécifications de PCC et de CCD (2.16.840.1.113883.10.20.1.28). 
            Cette déclaration de conformité est requise.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="count(./cda:id) = 1"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: L'élément "Problem Entry" doit nécessairement avoir un identifiant (&lt;id&gt;) 
            qui est utilisé à des fins de traçage. Si la source d'information du SIS ne fournit pas d'identifiant, 
            un GUID sera affecté comme attribut "root", sans extension (ex: id root='CE1215CD-69EC-4C7B-805F-569233C5E159'). 
            Bien que CDA permette l'utilisation de plusieurs identifiants, "Problem Entry" impose qu'un identifiant 
            seulement soit présent. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code='completed']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: Un élément "Problem Entry" décrit l'observation d'un fait clinique. 
            Son composant "statutCode" sera donc toujours fixé à la valeur code='completed'. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--REPORT -->
<xsl:if test="cda:effectiveTime/cda:width or cda:effectiveTime/cda:center">
         <svrl:successful-report xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                                 test="cda:effectiveTime/cda:width or cda:effectiveTime/cda:center">
            <xsl:attribute name="location">
               <xsl:apply-templates select="." mode="schematron-select-full-path"/>
            </xsl:attribute>
            <svrl:text> 
            Erreur [problemEntry]: Bien que CDA permette de nombreuses modalités pour exprimer un intervalle de 
            temps (low/high, low/width, high/width, ou center/width), Problem Entry sera contraint à l'utilisation
            exclusive de la forme low/high.</svrl:text>
         </svrl:successful-report>
      </xsl:if>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime/cda:low[@value or @nullFlavor = 'UNK'] or cda:effectiveTime/cda:low[@value or @nullFlavor = 'NAV']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: La composante "low" de l'élément "effectiveTime" doit être exprimée dans 
            un élément "Problem Entry".
            Des exceptions sont cependant admises, comme dans le cas où le patient ne se souvient pas de 
            la date de survenue d'une affection (ex: rougeole dans l'enfance sans date précise).
            Dans ce cas, l'élément "low" aura pour attribut un "nullFlavor" fixé à la valeur 'UNK'. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:value[@xsi:type='CD']"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: L'élément "value" correspond à l'état (clinique) décritet est donc obligatoire.
            Cet élément est toujours codé et son type sera toujours de type 'CD' (xsi:type='CD'). </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="(cda:value[@code and @codeSystem]) or                     (not(cda:value[@code]) and not(cda:value[@codeSystem]))"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: Si l'élément "value" est codé, les attributs "code" et "codeSystem" 
            seront obligatoirement présents. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="count(cda:entryRelationship/cda:observation/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1']) &lt;= 1"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: Un et un seul élément évaluant la sévérité d'une affection 
            sera présent (entryRelationship) pour une entrée "Problem Entry" </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1']) or                     (cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1'] and                     cda:entryRelationship[@typeCode='SUBJ' and @inversionInd='true'])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: un élément "entryRelationship" optionnel peut être présent 
            et donner une indication sur la sévérité d'une affection. S'il est présent, cet élément 
            se conformera au template Severity Entry (1.3.6.1.4.1.19376.1.5.3.1.4.1).
            Son attribut 'typeCode' prendra alors la valeur 'SUBJ' et 'inversionInd' la valeur 'true'. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="count(cda:entryRelationship/cda:observation/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.1']) &lt;= 1"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: Un et un seul élément évaluant le statut d'une affection (Problem Status Observation)
            sera présent par le biais d'une relation "entryRelationship" pour toute entrée "Problem Entry"</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.1']) or                     (cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.1'] and                     cda:entryRelationship[@typeCode='REFR' and @inversionInd='false'])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: un élément "entryRelationship" optionnel peut être présent 
            et donner une indication sur le statut clinique d'une affection -- cf. value set "PCC_ClinicalStatusCodes" (1.2.250.1.213.1.1.4.2.283.2). 
            S'il est présent, cet élément se conformera au template "Problem Status Observation" (1.3.6.1.4.1.19376.1.5.3.1.4.1.1).
            Son attribut 'typeCode' prendra alors la valeur 'REFR' et 'inversionInd' la valeur 'false'.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="count(cda:entryRelationship/cda:observation/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.2']) &lt;= 1"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: Un et un seul élément évaluant le statut de l'état de santé 
            d'un patient (Health Status Observation) sera présent par le biais d'une relation "entryRelationship" 
            pour toute entrée "Problem Entry". </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.2']) or                     (cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.1.2'] and                     cda:entryRelationship[@typeCode='REFR' and @inversionInd='false'])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: un élément "entryRelationship" optionnel peut être présent et donner
            une indication sur le statut de l'état de santé d'un patient -- cf. value set "PCC_HealthStatusCodes" (1.2.250.1.213.1.1.4.2.283.1). 
            S'il est présent, cet élément se conformera au template "Health Status Observation" (1.3.6.1.4.1.19376.1.5.3.1.4.1.2).
            Son attribut 'typeCode' prendra alors la valeur 'REFR' et 'inversionInd' la valeur 'false'.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.2']) or                     (cda:entryRelationship/cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.4.2'] and                     cda:entryRelationship[@typeCode='SUBJ' and @inversionInd='true'])"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry]: un ou plusieurs éléments "entryRelationship" optionnels peuvent être présents et 
            permettre d'apporter des informations additionnelles sur le problème observé.
            S'il est présent, cet élément se conformera au template "Comment Entry" (1.3.6.1.4.1.19376.1.5.3.1.4.2).
            Son attribut 'typeCode' prendra alors la valeur 'SUBJ' et 'inversionInd' la valeur 'true'.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>  
            Erreur [problemEntry] (Alerte): L'élément code -- cf. jeu de valeurs "PCC_ProblemCodes" (1.2.250.1.213.1.1.4.2.283.3) 
            d'une entrée Problem Entry permet d'établir à quel stade diagnostique se positionne un problème : par exemple un diagnostic 
            est un stade plus évolué qu'un symptôme dans la description d'un problème. Cette évaluation est importante pour les cliniciens. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--REPORT -->
<xsl:if test="cda:uncertaintyCode">
         <svrl:successful-report xmlns:svrl="http://purl.oclc.org/dsdl/svrl" test="cda:uncertaintyCode">
            <xsl:attribute name="location">
               <xsl:apply-templates select="." mode="schematron-select-full-path"/>
            </xsl:attribute>
            <svrl:text> 
            Erreur [problemEntry] (Alerte): CDA permet à la description d'un état clinique un certain degré d'incertitude avec 
            l'élément "uncertaintyCode". En l'absence actuelle de consensus clairement établi sur le bon usage de cet élément, 
            PCC déconseille de l'utiliser dans le cadre d'une entrée Problem Entry.</svrl:text>
         </svrl:successful-report>
      </xsl:if>

		    <!--REPORT -->
<xsl:if test="cda:confidentialityCode">
         <svrl:successful-report xmlns:svrl="http://purl.oclc.org/dsdl/svrl" test="cda:confidentialityCode">
            <xsl:attribute name="location">
               <xsl:apply-templates select="." mode="schematron-select-full-path"/>
            </xsl:attribute>
            <svrl:text> 
            Erreur [problemEntry] (Alerte): CDA permet l'utilisation de l'élément "confidentialtyCode" pour une observation.
            PCC déconseille cependant pour des raisons pratiques de l'utiliser dans le cadre d'une entrée Problem Entry.
            Il y a en effet d'autres manières d'assurer la confidentialité des documents, qui pourront être résolus au sein
            du domaine d'affinité.</svrl:text>
         </svrl:successful-report>
      </xsl:if>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:value[@codeSystem]) or cda:value[@codeSystemName]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry] (Alerte): les attributs "codeSystem" et "codeSystemName" de l'élément "value" d'une 
            entrée Problem Entry devraient être présents pour une meilleure lisibilité, mais ne sont pas obligatoires. </svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(cda:value[@code]) or cda:value[@displayName]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text> 
            Erreur [problemEntry] (Alerte): l'attribut "displaySystemName" de l'élément "value" d'une 
            entrée Problem Entry devrait être présent pour une meilleure lisibilité, mais n'est pas obligatoire.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M75"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M75"/>
   <xsl:template match="@*|node()" priority="-2" mode="M75">
      <xsl:apply-templates select="*" mode="M75"/>
   </xsl:template>

   <!--PATTERN procedureEntry-errorsIHE PCC v3.0 Procedure Entry-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Procedure Entry</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.19&#34;]"
                 priority="1000"
                 mode="M76">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root=&#34;1.3.6.1.4.1.19376.1.5.3.1.4.19&#34;]"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="self::cda:procedure[@classCode=&#34;PROC&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry]: L'attribut "classCode" pour un élément "Procedure Entry" sera fixé à la valeur "PROC".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(./@moodCode=&#34;EVN&#34;) or                 cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.29&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry]: Lorsque l'élément "Procedure Entry" est en mode événement (moodCode='EVN'), 
                cette entrée se conforme au template CCD 2.16.840.1.113883.10.20.1.29</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(./@moodCode=&#34;INT&#34;) or                 cda:templateId[@root=&#34;2.16.840.1.113883.10.20.1.25&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry]: Lorsque l'élément "Procedure Entry" est en mode intention (moodCode='INT'),
                cette entrée se conforme au template CCD 2.16.840.1.113883.10.20.1.25.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry]: Un élément "Procedure Entry" comporte un identifiant "id".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur [procedureEntry]: Un élément "Procedure Entry" comporte un élément "code".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code = &#34;completed&#34; or                 @code = &#34;active&#34; or                 @code = &#34;aborted&#34; or                 @code = &#34;cancelled&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry]: L'élément "statusCode" sera présent.
                Il prendra la valeur "completed" pour les procédures réalisées, ou "active" pour les procédures 
                toujours en cours. Il prendra la valeur "aborted" por les procédures ayant été stoppées avant la fin 
                et "cancelled" pour celles qui ont été annulées (avant d'avoir débuté).</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="not(./@moodCode=&#34;INT&#34;) or                  (cda:effectiveTime or cda:priorityCode)"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry]: dans une entrée "Procedure Entry", l'élément "priorityCode" sera présent en mode "INT" 
                lorsque l'élément "effectiveTime" est omis.
                Il peut cependant exister dans d'autres modes, indiquant le degré de priorité de la procédure.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="./@moodCode = &#34;INT&#34; or ./@moodCode = &#34;EVN&#34;"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry] (Alerte): L'attribut "moodCode" d'une entrée "Procedure Entry" peut prendre la valeur "INT" 
                pour indiquer une procédure escomptée, ou "EVN" pour indiquer qu'elle a déjà été réalisée.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code[@code]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry] (Alerte): une entrée "Procedure Entry" devrait comporter un code décrivant le type de la procédure.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
                Erreur [procedureEntry] (Alerte): l'élément "effectiveTime" devrait être présent dans une entrée "Procedure Entry"
                pour horodater la procédure (en mode "EVN") ou la date escomptée pour la procédure (en mode "INT").</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M76"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M76"/>
   <xsl:template match="@*|node()" priority="-2" mode="M76">
      <xsl:apply-templates select="*" mode="M76"/>
   </xsl:template>

   <!--PATTERN simpleObservation-errorsIHE PCC v3.0 Simple Observation-->
<svrl:text xmlns:svrl="http://purl.oclc.org/dsdl/svrl">IHE PCC v3.0 Simple Observation</svrl:text>

	  <!--RULE -->
<xsl:template match="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.13']"
                 priority="1000"
                 mode="M77">
      <svrl:fired-rule xmlns:svrl="http://purl.oclc.org/dsdl/svrl"
                       context="*[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.13']"/>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:id"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: "Simple Observation" requiert un élément identifiant &lt;id&gt;.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:code"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: "Simple Observation" requiert un élément "code" décrivant ce qui est observé.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:statusCode[@code = &#34;completed&#34;]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: L'élément "statusCode" est requis dans "Simple Observations" 
            sont fixés à la valeur "completed".</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:effectiveTime[@value or @nullFlavor]"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: L'élément &lt;effectiveTime&gt; est requis dans "Simple Observations",
            et représentera la date et l'heure de la mesure effectuée. Cet élément devrait être précis au jour. 
            Si la date et l'heure sont inconnues, l'attribut nullFlavor sera utilisé.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>

		    <!--ASSERT -->
<xsl:choose>
         <xsl:when test="cda:value"/>
         <xsl:otherwise>
            <svrl:failed-assert xmlns:svrl="http://purl.oclc.org/dsdl/svrl">
               <xsl:attribute name="location">
                  <xsl:apply-templates select="." mode="schematron-select-full-path"/>
               </xsl:attribute>
               <svrl:text>
            Erreur de Conformité PCC: L'élément "value" d'un élément "Simple Observation" utilisera un 
            type de donnée approprié à l'observation.</svrl:text>
            </svrl:failed-assert>
         </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates select="*" mode="M77"/>
   </xsl:template>
   <xsl:template match="text()" priority="-1" mode="M77"/>
   <xsl:template match="@*|node()" priority="-2" mode="M77">
      <xsl:apply-templates select="*" mode="M77"/>
   </xsl:template>

   <!--PATTERN variables-->
<xsl:variable name="enteteHL7France" select="'2.16.840.1.113883.2.8.2.1'"/>
   <xsl:variable name="commonTemplate" select="'1.2.250.1.213.1.1.1.1'"/>
   <xsl:variable name="XDS-SD" select="'1.3.6.1.4.1.19376.1.2.20'"/>
   <xsl:variable name="OIDphysique" select="'1.2.250.1.71.4.2.1'"/>
   <xsl:variable name="OIDmorale" select="'1.2.250.1.71.4.2.2'"/>
   <xsl:variable name="OIDINS-c" select="'1.2.250.1.213.1.4.2'"/>
   <xsl:variable name="OIDLOINC" select="'2.16.840.1.113883.6.1'"/>
   <xsl:variable name="templateObservationMedia" select="'1.3.6.1.4.1.19376.1.8.1.4.10'"/>
   <xsl:variable name="jdv_authorSpecialty"
                 select="'../../../dmp/resources/valuesets/authorSpecialty.xml'"/>
   <xsl:variable name="jdv_confidentialityCode"
                 select="'../../../dmp/resources/valuesets/confidentialityCode.xml'"/>
   <xsl:variable name="jdv_healthcareFacilityTypeCode"
                 select="'../../../dmp/resources/valuesets/healthcareFacilityTypeCode.xml'"/>
   <xsl:variable name="jdv_observationInterpretation"
                 select="'../../../dmp/resources/valuesets/observationInterpretation.xml'"/>
   <xsl:variable name="jdv_practiceSettingCode"
                 select="'../../../dmp/resources/valuesets/practiceSettingCode.xml'"/>
   <xsl:variable name="jdv_typeCode" select="'../../../dmp/resources/valuesets/typeCode.xml'"/>
   <xsl:variable name="jdv_HealthStatusCodes"
                 select="'../../../dmp/resources/valuesets/HealthStatusCodes.xml'"/>
   <xsl:variable name="jdv_ClinicalStatusCodes"
                 select="'../../../dmp/resources/valuesets/ClinicalStatusCodes.xml'"/>
   <xsl:variable name="jdv_ProblemCodes" select="'../../../dmp/resources/valuesets/ProblemCodes.xml'"/>
   <xsl:variable name="jdv_AllergyAndIntoleranceCodes"
                 select="'../../../dmp/resources/valuesets/AllergyAndIntoleranceCodes.xml'"/>
   <xsl:template match="text()" priority="-1" mode="M79"/>
   <xsl:template match="@*|node()" priority="-2" mode="M79">
      <xsl:apply-templates select="*" mode="M79"/>
   </xsl:template>
</xsl:stylesheet>