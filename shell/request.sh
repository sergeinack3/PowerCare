#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

########
# Mediboard request launcher
########

announce_script "Mediboard request launcher"

if [ "$#" -lt 4 ]
then 
  echo "Usage: $0 <root_url> <username> <password> \"<param>\" \[-t times\] \[-d delay\] \[-f file\] \[-T delay\]"
  echo "  <root_url> is root url for mediboard, ie https://localhost/mediboard"
  echo "  <username> is the name of the user requesting, ie cron"
  echo "  <password is the password of the user requesting, ie ****"
  echo "  <params> is the GET param string for request, ie m=dPpatients&tab=vw_medecins"
  echo "  [-t <times>] is the number of repetition, ie 4"
  echo "  [-d <delay>] is the time between each repetition, ie 2"
  echo "  [-f <file>] is the file for the output, ie log.txt"
  echo "  [-T <delay>] is the time before stopping wget (server not responding or other problems)"
  exit 1
fi

times=1
delay=1
timeout=""

args=$(getopt t:d:f:T: $*)
if [ $? != 0 ] ; then
  echo "Invalid argument. Check your command line"; exit 0;
fi

set -- $args
for i; do
  case "$i" in
    -t) times=$2; shift 2;;
    -d) delay=$2; shift 2;;
    -f) file="-O $2"; shift 2;;
    -T) timeout="-T $2"; shift 2;;
    --) shift; break ;;
  esac
done

root_url=$1
login="login=1"
user=username=$2
pass=password=$3
params=$4

url="$root_url/index.php?$login&$user&$pass&$params"

# Make mediboard path
MEDIBOARDPATH=/var/log/mediboard
force_dir $MEDIBOARDPATH

log=$MEDIBOARDPATH/jobs.log
force_file $log

mediboard_request() 
{
   wget $url\
        --append-output="$log"\
        --force-directories\
        --no-check-certificate\
        $timeout\
        $file
   check_errs $? "Failed to request to Mediboard" "Mediboard requested!"   
   echo "wget URL : $url."
}

if [ $times -gt 1 ]
then
  while [ $times -gt 0 ]
  do
    times=$(($times - 1))
    mediboard_request &
    sleep $delay
  done
else
  mediboard_request
fi