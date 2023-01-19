# Script Python de conversion de fichiers reconnus par OpenOffice en PDF
 
import uno
import sys
import time
import traceback

def main():
  try:
    # Log
    f = open('/var/log/mediboard/doctopdf', 'a');
    f.write('---' + time.strftime('%d/%m/%y %H:%M',time.localtime()) + '---\n')
    f.write('Convert '+ sys.argv[1] + ' to ' + sys.argv[2] + '\n');
    
    local = uno.getComponentContext()
    resolver = local.ServiceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", local)
    context = resolver.resolve("uno:socket,host=localhost,port=8100;urp;StarOffice.ComponentContext")
    desktop = context.ServiceManager.createInstanceWithContext("com.sun.star.frame.Desktop", context)
    
    document = desktop.loadComponentFromURL("file://"+sys.argv[1], "_blank", 0, ())
    
    from com.sun.star.beans import PropertyValue
    property = (
      PropertyValue("FilterName", 0, "writer_pdf_Export", 0),
    )
    
    document.storeToURL("file://"+sys.argv[2], property)
    
    document.close(True)
    print '1'
    
    # Fin de log
    f.write('Successfully converted \n')
    f.close()
  except Exception as inst:
    f.write(traceback.format_exc() + '\n')
    f.close()
    print '0'
main()