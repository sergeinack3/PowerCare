<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    patient.sch :
    Contenu :
        Contrôle du patient dans l'en-tête CDA  
    Paramètres d'appel :
        Néant
    Historique :
    31/05/11 : FMY ASIP/PRAS : Création
    28/07/11 : FMY ASIP/PRAS : Contrôle de la présence d'adresse géographique (addr) et télécom (telecom)
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="patient">
    <rule context="cda:ClinicalDocument/cda:recordTarget/cda:patientRole">
        <assert test="not(cda:id[@nullFlavor])">
            Erreur de conformité CI-SIS : L'élément recordTarget/patientRole/id (obligatoire dans CDAr2), 
            doit être sans nullFlavor.
        </assert>
        <assert test="cda:id[@root=$OIDINS-c]">
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole 
            doit comporter au moins un élément id contenant un INS-c du patient
        </assert>
        <assert test="cda:patient/cda:birthTime">
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/patient/birthTime doit être présent 
            avec une date de naissance ou un nullFlavor autorisé.
        </assert>
        <assert test="cda:addr">
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/addr doit être présent 
            avec une adresse géographique ou un nullFlavor autorisé.
        </assert>
        <assert test="cda:telecom">
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/telecom doit être présent 
            avec une adresse de télécommunication ou un nullFlavor autorisé.
        </assert>
        <assert test="cda:patient/cda:administrativeGenderCode">
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/patient/administrativeGenderCode 
            doit être présent avec le code sexe ou un nullFlavor autorisé.
        </assert>
        <assert test="
                    not(cda:patient/cda:religiousAffiliationCode) and
                    not(cda:patient/cda:raceCode) and
                    not(cda:patient/cda:ethnicGroupCode) 
                    ">
            Erreur de conformité CI-SIS : Un élément recordTarget/patientRole/patient 
            ne doit contenir ni race ni religion ni groupe ethnique.
        </assert>
        <assert test="cda:patient/cda:name/cda:given">
            Erreur de conformité CI-SIS : l'élément recordTarget/patientRole/patient/name/given doit être présent avec le prénom du patient ou un nullFlavor.
        </assert>
    </rule>
</pattern>