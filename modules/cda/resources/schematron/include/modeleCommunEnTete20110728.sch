<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    modeleCommunEnTete.sch :
    Contenu :
        Règles de contrôle de base de l'en-tête CDA  
    Paramètres d'appel :
        Néant
    Historique :
    31/05/11 : FMY ASIP/PRAS : Création
    08/07/11 : FMY ASIP/PRAS : Ajout contrôles sur documentationOf/serviceEvent
    28/07/11 : FMY ASIP/PRAS : Ajout contrôle de présence de componentOf (pour l'encounter)
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="modeleCommunEnTete">
    <p>Conformité de base de l'en-tête CDA au CI-SIS</p>
    <rule context="cda:ClinicalDocument">
        <assert test="cda:templateId[@root=$enteteHL7France]"> 
            Erreur de conformité HL7 France :
            L'élément ClinicalDocument/templateId doit être présent 
            avec @root = "<value-of select="$enteteHL7France"/>". 
        </assert>
        <assert test="cda:templateId[@root=$commonTemplate]"> 
            Erreur de conformité CI-SIS :
            L'élément ClinicalDocument/templateId doit être présent avec @root = "<value-of
            select="$commonTemplate"/>". 
        </assert>
        <assert test="cda:title and normalize-space(cda:title) and not(cda:title[@nullFlavor])">
            Erreur de conformité CI-SIS : 
            L'élément "title" doit être présent dans l'en-tête, 
            sans nullFlavor et doit contenir un titre non vide. 
        </assert>
        <assert test="cda:effectiveTime and not(cda:effectiveTime[@nullFlavor])"> 
            Erreur de conformité CI-SIS : 
            L'élément "effectiveTime" doit être présent dans l'en-tête, sans nullFlavor. 
        </assert>
        <assert test="cda:realmCode[@code='FR']"> 
            Erreur de conformité CI-SIS : 
            L'élément "realmCode" doit être présent et valorisé à "FR". 
        </assert>
        <assert test="not(cda:confidentialityCode[@nullFlavor])"> 
            Erreur de conformité CI-SIS :
            L'élément "confidentialityCode" (obligatoire dans CDAr2) doit être sans nullFlavor. 
        </assert>
        <assert test="cda:languageCode[@code='fr-FR']"> 
            Erreur de conformité CI-SIS : 
            L'élément "languageCode" doit être présent dans l'en-tête, valorisé à "fr-FR". 
        </assert>
        <assert test="not(cda:id[@nullFlavor])"> 
            Erreur de conformité CI-SIS : 
            L'élément "id", identifiant unique du document (obligatoire dans CDAr2) doit être sans nullFlavor. 
        </assert>
        <assert test="cda:legalAuthenticator and not(./cda:legalAuthenticator[@nullFlavor])">
            Erreur de conformité CI-SIS : 
            L'élément "legalAuthenticator" doit être présent dans l'en-tête, sans nullFlavor. 
        </assert>
        <assert test="not(cda:author[@nullFlavor]) and not(./cda:author/cda:assignedAuthor[@nullFlavor])"> 
            Erreur de conformité CI-SIS : 
            Les éléments "author" et "author/assignedAuthor" doivent être sans nullFlavor dans l'en-tête. 
        </assert>
        <assert test="not(cda:custodian[@nullFlavor]) and not(./cda:custodian/cda:assignedCustodian[@nullFlavor])"> 
            Erreur de conformité CI-SIS : 
            Les éléments "custodian" et "custodian/assignedCustodian" doivent être sans nullFlavor dans l'en-tête. 
       </assert>
        <assert
            test="(cda:documentationOf) and not(cda:documentationOf[@nullFlavor]) and 
                   not(cda:documentationOf/cda:serviceEvent[@nullFlavor])"
            > 
            Erreur de conformité CI-SIS : 
            L'en-tête doit comporter au moins un élément documentationOf
            et l'attribut nullFlavor n'est pas autorisé ni sur documentationOf ni sur son fils serviceEvent. 
        </assert>
        <assert test="cda:componentOf">
            Erreur de conformité CI-SIS : 
            Le document doit comporter dans son en-tête un componentOf/encompassingEncounter.
        </assert>
        <assert test="cda:componentOf/cda:encompassingEncounter/cda:effectiveTime/@nullFlavor or
                    cda:componentOf/cda:encompassingEncounter/cda:effectiveTime/cda:low/@value or
                    cda:componentOf/cda:encompassingEncounter/cda:effectiveTime/cda:high/@value
            ">
            Erreur de conformité CI-SIS : 
            L'élément componentOf/encompassingEncounter/effectiveTime doit comporter 
            soit un attribut nullFlavor soit l'un des éléments fils "low/@value" et "high/@value" 
            soit les deux.
        </assert>
        <assert test="cda:componentOf/cda:encompassingEncounter/cda:location/cda:healthCareFacility/cda:code">
            Erreur de conformité CI-SIS : 
            Le document doit comporter dans son en-tête un componentOf/encompassingEncounter/location/healthCareFacility/code.
        </assert>
    </rule>
</pattern>
