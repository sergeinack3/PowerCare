<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    CI-SIS_StructurationCommuneCDAr2.sch - ASIP/PRAS
    ......................................................................................................................................................
    Vérification de la conformité sémantique au volet Structuration Commune des Documents Médicaux du CI-SIS.
    ......................................................................................................................................................
    Utilisation des répertoires sous testContenuCDA :
    - testContenuCDA : les documents CDA d'exemples conformes au CI-SIS, ainsi que la feuille de style par défaut cda_asip.xsl pour la visualisation
    - - documentsAnnexes : Des documents annexes liés aux exemples et des documents de référence
    - - infrastructure/cda : Le schéma XML CDA.xsd
    - - jeuxDeValeurs : les jeux de valeurs du CI-SIS dans le format SVS.xsd
    - - processable/coreschemas : Les sous-schémas XML de CDA.xsd (de l'édition normative HL7 v3 de 2005, correspondant à CDAr2)
    - - schematrons : les schématrons des volets du CI-SIS sous la forme source (<CI-SIS_<nom>.sch) et sous la forme compilée en xslt2 (<CI-SIS_<nom>.xsl)
    - - - abstract : les sous-schématrons de patterns abstraits (un fichier par abstract pattern)
    - - - include : les sous-schématrons de patterns concrets (un fichier par pattern)
    - - - - sections : les sous-schématrons de patterns pour la vérification de conformité des sections du corps CDA
    - - - - - entries : les sous-schématrons de patterns pour la vérification de conformité des entries du corps CDA
    - - - moteur : le moteur xslt2 de vérification de conformité sémantique d'un document d'exemple, 
                   avec ses différents composants : saxon9he.jar, script verif.bat et feuilles de transformation xslt2 intermédiaires
    - - - rapports : les rapports de vérification de conformité produits, et la feuille de style qui sert à leur visualisation
    ......................................................................................................................................................
    Pour réaliser le schematron d'un volet de contenu du CI-SIS :
        1) Recopier le présent schématron sous le nom CI-SIS_<nom du volet>.sch, dans le répertoire courant (schematrons)
        2) conserver les include, pattern existants, à l'exception du pattern et de l'include nonXMLBody inutiles pour un volet structuré.
        3) Créer dans le répertoire include les patterns concrets supplémentaires requis par le volet (ou réutiliser ceux qui sont réutilisables)
        4) le cas échéant créer dans le répertoire abstract les paterns abstraits supplémentaires requis par le volet
        5) Inclure tous les patterns supplémentaires dans le schématron CI-SIS_<nom du volet>.sch
        6) Activer dans la phase "latotale<aaaammjj>" les patterns concrets supplémentaires.
        7) La première opération de validation de conformité d'un document au volet compilera le schematron en xslt2 : CI-SIS_<nom du volet>.xsl
    ......................................................................................................................................................    
    Historique :
        27/05/11 : FMY : Création
        31/05/11 : FMY : Compléments
        15/06/11 : FMY : Compléments
        27/06/11 : FMY : publication dans la version 1.0.1 du CI-SIS
        22/07/11 : FMY : Mise à jour des commentaires ci-dessus (retirer le pattern et l'include nonXMLBody), ajout variable OIDLOINC
                         ajout pattern observationInterpretation
        27/07/11 : FMY : Ajout des patterns de sections et d'entries de PCC et du CI-SIS, transversaux à tous types de documents       
        19/12/11 : FMY : Ajout du contrôle / jeu de valeurs confidentialityCode
        08/10/12 : FMY : CI-SIS 1.3 => retrait des include assignedAuthor, confidentialityCode et custodianOrganization. 
                         Maj des include assignedEntity (addr & telecom optionnels) et addr (contrôle structure des adresses)
                         Actualisation nom de fichiers include problemEntry, concernentry, procedureentry 
-->
<schema xmlns="http://purl.oclc.org/dsdl/schematron" defaultPhase="latotale20121008"
    xmlns:cda="urn:hl7-org:v3" queryBinding="xslt2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" schemaVersion="CI-SIS_StructurationCommuneCDAr2.sch">
    <title>Conformité d'un document CDAr2 au volet Structuration Commune des Documents Médicaux du CI-SIS</title>
    <ns prefix="cda" uri="urn:hl7-org:v3"/>
    <ns prefix="xsi" uri="http://www.w3.org/2001/XMLSchema-instance"/>
    <ns prefix="jdv" uri="http://esante.gouv.fr"/>
    <ns prefix="svs" uri="urn:ihe:iti:svs:2008"/>

    <!-- Inclusions d'abstract patterns : -->
    <include href="abstract/dansJeuDeValeurs20110729.sch"/>
    <include href="abstract/IVL_TS20110627.sch"/>   
    <include href="abstract/personName20110627.sch"/>
    
    <!-- en-tête et génériques -->
    <include href="include/addr20121008.sch"/>    
    <include href="include/administrativeGenderCode20110627.sch"/>
    <include href="include/assignedEntity20121008.sch"/>
    <include href="include/authenticatorName20110627.sch"/>
    <include href="include/authorPersonName20110627.sch"/>
    <include href="include/authorSpecialty20110627.sch"/>
    <include href="include/authorTime20110627.sch"/>
    <include href="include/documentCode20110627.sch"/>
    <include href="include/documentEffectiveTime20110627.sch"/>
    <include href="include/healthcareFacilityTypeCode20110627.sch"/> 
    <include href="include/informantAssignedPersonName20110627.sch"/>
    <include href="include/informantRelatedEntity20110627.sch"/>
    <include href="include/legalAuthenticatorName20110627.sch"/>
    <include href="include/legalAuthenticatorTime20110627.sch"/>
    <include href="include/modeleCommunEnTete20110728.sch"/>
    <include href="include/nonXMLBody20110627.sch"/>
    <include href="include/patient20110728.sch"/>
    <include href="include/patientBirthTime20110627.sch"/>
    <include href="include/patientId20110627.sch"/>
    <include href="include/patientName20110627.sch"/>
    <include href="include/practiceSettingCode20110627.sch"/>
    <include href="include/relatedDocument20110627.sch"/>
    <include href="include/relatedPersonName20110627.sch"/>
    <include href="include/serviceEventEffectiveTime20110627.sch"/>
    <include href="include/serviceEventPerformer20110708.sch"/>
    <include href="include/telecom20110728.sch"/>   
    
    <!-- sections -->
    <include href="include/sections/abdomenPhysicalExam20110627.sch"/>
    <include href="include/sections/activeProblemSection20110725.sch"/>
    <include href="include/sections/assessmentAndPlan20110627.sch"/>
    <include href="include/sections/carePlan20110627.sch"/>
    <include href="include/sections/childFunctionalStatusAssessment20110627.sch"/>
    <include href="include/sections/childFunctionalStatusEatingSleeping20110627.sch"/>
    <include href="include/sections/childFunctionalStatusPsychomot20110627.sch"/>
    <include href="include/sections/CodedAntenatalTestingAndSurveillance20110725.sch"/>
    <include href="include/sections/codedPhysicalExam20110627.sch"/>
    <include href="include/sections/codedResults20110725.sch"/>
    <include href="include/sections/codedSocialHistory20110627.sch"/>
    <include href="include/sections/codedVitalSigns20110627.sch"/>
    <include href="include/sections/earsPhysicalExam20110627.sch"/>
    <include href="include/sections/encounterHistoriesSection20110725.sch"/>
    <include href="include/sections/endocrinePhysicalExam20110627.sch"/>
    <include href="include/sections/eyesPhysicalExam20110627.sch"/>
    <include href="include/sections/generalAppearancePhysicalExam20110627.sch"/>
    <include href="include/sections/genitaliaPhysicalExam20110627.sch"/>
    <include href="include/sections/heartPhysicalExam20110627.sch"/>
    <include href="include/sections/historyOfPastIllness20110627.sch"/>
    <include href="include/sections/immunizations20110627.sch"/>
    <include href="include/sections/integumentaryPhysicalExam20110627.sch"/>
    <include href="include/sections/laborAndDeliverySection20110725.sch"/>
    <include href="include/sections/lymphaticPhysicalExam20110627.sch"/>
    <include href="include/sections/musculoPhysicalExam20110627.sch"/>
    <include href="include/sections/neurologicPhysicalExam20110627.sch"/>
    <include href="include/sections/pregnancyHistorySection20110725.sch"/>
    <include href="include/sections/prenatalEvents20110725.sch"/> 
    <include href="include/sections/proceduresSection20110725.sch"/>
    <include href="include/sections/respiratoryPhysicalExam20110627.sch"/> 
    <include href="include/sections/teethPhysicalExam20110627.sch"/>
    
    <!-- entries -->
    <include href="include/sections/entries/ACPimageIllustrative20110727.sch"/>
    <include href="include/sections/entries/ClinicalStatusCodes20110728.sch"/>
    <include href="include/sections/entries/codedAntenatalTestingAndSurveillanceOrg20110725.sch"/>
    <include href="include/sections/entries/codedVitalSignsOrg20120306.sch"/>
    <include href="include/sections/entries/comments20110725.sch"/>
    <include href="include/sections/entries/concernEntry20120827.sch"/>
    <include href="include/sections/entries/encountersEntry20110725.sch"/>
    <include href="include/sections/entries/HealthStatusCodes20110728.sch"/>
    <include href="include/sections/entries/immunizationsEnt20110627.sch"/>
    <include href="include/sections/entries/observationInterpretation20110722.sch"/>
    <include href="include/sections/entries/ProblemCodes20110728.sch"/>
    <include href="include/sections/entries/AllergyAndIntoleranceCodes20110728.sch"/>
    <include href="include/sections/entries/problemConcernEntry20110627.sch"/>
    <include href="include/sections/entries/problemEntry20120827.sch"/>
    <include href="include/sections/entries/procedureEntry20120827.sch"/>
    <include href="include/sections/entries/simpleObservation200110725.sch"/>
    
    <!-- ::::::::::::::::::::::::::::::::::::: -->
    <!--           Phase en vigueur            -->    
    <!-- ::::::::::::::::::::::::::::::::::::: -->
    
    <phase id="latotale20121008">
        
        <!-- obligatoire dans tout schématron -->
        <active pattern="variables"/>
        
        <!-- en-tête et génériques -->
        <active pattern="addr"/>
        <active pattern="administrativeGenderCode"/>
        <active pattern="assignedEntity"/>
        <active pattern="authenticatorName"/>
        <active pattern="authorPersonName"/>
        <active pattern="authorSpecialty"/>
        <active pattern="authorTime"/>
        <active pattern="documentCode"/>
        <active pattern="documentEffectiveTime"/>
        <active pattern="healthcareFacilityTypeCode"/>
        <active pattern="informantAssignedPersonName"/>
        <active pattern="informantRelatedEntity"/>
        <active pattern="legalAuthenticatorName"/>
        <active pattern="legalAuthenticatorTime"/>
        <active pattern="modeleCommunEnTete"/>
        <active pattern="nonXMLBody"/>
        <active pattern="patient"/>
        <active pattern="patientBirthTime"/>
        <active pattern="patientId"/>
        <active pattern="patientName"/>
        <active pattern="practiceSettingCode"/>
        <active pattern="relatedDocument"/>
        <active pattern="relatedPersonName"/>
        <active pattern="serviceEventEffectiveTime"/>
        <active pattern="serviceEventPerformer"/>
        <active pattern="telecom"/>
        
        <!-- sections -->
        <active pattern="abdomenPhysicalExam-errors"/>
        <active pattern="activeProblemSection-errors"/>
        <active pattern="assessmentAndPlan-errors"/>
        <active pattern="carePlan-errors"/>
        <active pattern="childFunctionalStatusAssessment-errors"/>
        <active pattern="childFunctionalStatusEatingSleeping-errors"/>
        <active pattern="childFunctionalStatusPsychoMot-errors"/>
        <active pattern="CodedAntenatalTestingAndSurveillance-errors"/>  
        <active pattern="codedPhysicalExam-errors"/>
        <active pattern="codedResults-errors"/>
        <active pattern="codedSocialHistory-errors"/>
        <active pattern="codedVitalSigns-errors"/>
        <active pattern="EarsPhysicalExam-errors"/>
        <active pattern="encounterHistoriesSection-errors"/>
        <active pattern="endocrinePhysicalExam-errors"/> 
        <active pattern="eyesPhysicalExam-errors"/>
        <active pattern="generalAppearancePhysicalExam-errors"/>
        <active pattern="genitaliaPhysicalExam-errors"/>
        <active pattern="heartPhysicalExam-errors"/>
        <active pattern="historyOfPastIllness-errors"/>
        <active pattern="immunizations-errors"/>
        <active pattern="integumentaryPhysicalExam-errors"/>
        <active pattern="laborAndDeliverySection-errors"/>
        <active pattern="lymphaticPhysicalExam-errors"/>
        <active pattern="musculoPhysicalExam-errors"/>
        <active pattern="neurologicPhysicalExam-errors"/>
        <active pattern="pregnancyHistorySection-errors"/>
        <active pattern="prenatalEvents-errors"/>
        <active pattern="problemConcernEntry-errors"/>
        <active pattern="problemEntry-errors"/>
        <active pattern="proceduresSection-errors"/>   
        <active pattern="RespiratoryPhysicalExam-errors"/>
        <active pattern="teethPhysicalExam-errors"/>
        
        <!-- entries -->
        <active pattern="ACPimageIllustrative"/>
        <active pattern="ClinicalStatusCodes"/>
        <active pattern="codedAntenatalTestingAndSurveillanceOrg-errors"/> 
        <active pattern="codedVitalSignsOrg-errors"/>
        <active pattern="comments-errors"/>
        <active pattern="concernEntry-errors"/>
        <active pattern="encountersEntry-errors"/>
        <active pattern="HealthStatusCodes"/>
        <active pattern="immunizationsEnt-errors"/>
        <active pattern="observationInterpretation"/>
        <active pattern="ProblemCodes"/>
        <active pattern="AllergyAndIntoleranceCodes"/>
        <active pattern="problemConcernEntry-errors"/>
        <active pattern="problemEntry-errors"/>
        <active pattern="procedureEntry-errors"/>
        <active pattern="simpleObservation-errors"/>

    </phase>
    
    <!--    Historique des phases des versions antérieures, supprimé car alourdit la compilation    -->    

    <!-- ::::::::::::::::::::::::::::::::::::: -->
    <!--           Variables globales          -->
    <!-- ::::::::::::::::::::::::::::::::::::: -->
    
    <pattern id="variables">
        <let name="enteteHL7France" value="'2.16.840.1.113883.2.8.2.1'"/>               <!-- conformité guide en-tête CDA de HL7 France -->
        <let name="commonTemplate" value="'1.2.250.1.213.1.1.1.1'"/>                    <!-- conformité volet structuration commune -->
        <let name="XDS-SD" value="'1.3.6.1.4.1.19376.1.2.20'"/>                         <!-- conformité profil XDS-SD -->
        <let name="OIDphysique" value="'1.2.250.1.71.4.2.1'"/>                          <!-- OID PS personnes physiques -->
        <let name="OIDmorale" value="'1.2.250.1.71.4.2.2'"/>                            <!-- OID PS personnes morales -->
        <let name="OIDINS-c" value="'1.2.250.1.213.1.4.2'"/>                            <!-- OID de l'INS-c -->
        <let name="OIDLOINC" value="'2.16.840.1.113883.6.1'"/>                          <!-- OID de LOINC -->
        <let name="templateObservationMedia" value="'1.3.6.1.4.1.19376.1.8.1.4.10'"/>   <!-- conformité image illustrative dans observationMedia -->
        <!-- chemins relatifs des fichiers jeux de valeurs -->
        <let name="jdv_authorSpecialty" value="'../jeuxDeValeurs/CI-SIS_jdv_authorSpecialty.xml'"/>  
        <let name="jdv_confidentialityCode" value="'../jeuxDeValeurs/CI-SIS_jdv_confidentialityCode.xml'"/>  
        <let name="jdv_healthcareFacilityTypeCode" value="'../jeuxDeValeurs/CI-SIS_jdv_healthcareFacilityTypeCode.xml'"/>  
        <let name="jdv_observationInterpretation" value="'../jeuxDeValeurs/CI-SIS_jdv_observationInterpretation.xml'"/>
        <let name="jdv_practiceSettingCode" value="'../jeuxDeValeurs/CI-SIS_jdv_practiceSettingCode.xml'"/>  
        <let name="jdv_typeCode" value="'../jeuxDeValeurs/CI-SIS_jdv_typeCode.xml'"/>   
        <let name="jdv_HealthStatusCodes" value="'../jeuxDeValeurs/CI-SIS_jdv_HealthStatusCodes.xml'"/>
        <let name="jdv_ClinicalStatusCodes" value="'../jeuxDeValeurs/CI-SIS_jdv_ClinicalStatusCodes.xml'"/>
        <let name="jdv_ProblemCodes" value="'../jeuxDeValeurs/CI-SIS_jdv_ProblemCodes.xml'"/>
        <let name="jdv_AllergyAndIntoleranceCodes" value="'../jeuxDeValeurs/CI-SIS_jdv_AllergyAndIntoleranceCodes.xml'"/>
    </pattern>
 
</schema>
