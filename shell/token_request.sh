#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

########
# Mediboard request launcher
########

announce_script "Mediboard lite request launcher"

if [ "$#" -lt 1 ]
then 
  echo "Usage: $0 <token> \[-u root_url\] \[-t times\] \[-d delay\] \[-f file\] \[-T delay\]"
  echo "  <token> is the user token"
  echo "  [-u root_url] is root url for mediboard, default value is http://localhost/mediboard/"
  echo "  [-t <times>] is the number of repetition, ie 4"
  echo "  [-d <delay>] is the time between each repetition, ie 2"
  echo "  [-f <file>] is the file for the output, ie log.txt"
  echo "  [-T <timeout>] is the time before stopping wget (server not responding or other problems)"
  exit 1
fi

times=1
delay=1
timeout=""
root_url="http://localhost/mediboard"

args=$(getopt u:t:d:f:T: $*)
if [ $? != 0 ] ; then
  echo "Invalid argument. Check your command line"; exit 0;
fi

set -- $args
for i; do
  case "$i" in
    -u) root_url=$2; shift 2;;
    -t) times=$2; shift 2;;
    -d) delay=$2; shift 2;;
    -f) file="-O $2"; shift 2;;
    -T) timeout="-T $2"; shift 2;;
    --) shift; break ;;
  esac
done

echo ${root_url}

token="token=$1"

url="$root_url/index.php?$token"

# Make mediboard path
MEDIBOARDPATH=/var/log/mediboard
force_dir $MEDIBOARDPATH

log=$MEDIBOARDPATH/jobs.log
force_file $log

mediboard_request() 
{
   wget $url\
         -qO-\
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