<?xml version="1.0" encoding="UTF-8"?>

<!--                  -=<<o#%@O[ CVACModeleEnTete.sch ]O@%#o>>=-
    
    Règles de contrôle de l'en-tête CDA d'un certificat CS9
    
    Historique :
    25/07/11 : CRI : Création
    
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="CVACModeleEnTete">
    <p>Conformité de l'en-tête CDA au modèle du CVAC</p>
    <rule context="cda:ClinicalDocument">
        <assert test="./cda:templateId[@root='1.3.6.1.4.1.19376.1.5.3.1.1.18.1.2']"> 
            Erreur de conformité PCC :
            L'élément ClinicalDocument/templateId doit être présent 
            avec @root="1.3.6.1.4.1.19376.1.5.3.1.1.18.1.2".</assert>
        <assert test="cda:templateId[@root='1.2.250.1.213.1.1.1.10']"> 
            Erreur de conformité CVAC: Le template parent "Carnet de Vaccination" (1.2.250.1.213.1.1.1.10) doit être présent.
        </assert>
        
        <assert test="./cda:code[@code='CERT_DECL' and @codeSystem='1.2.250.1.213.1.1.4.12']"> 
            Erreur de conformité CVAC : 
            L'élément code doit avoir @code ="CERT_DECL" et @codeSystem = "1.2.250.1.213.1.1.4.12"/>. </assert>
    </rule>
</pattern>
