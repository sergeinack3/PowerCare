#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

########
# Ping logger for server load analysis
########

announce_script "Ping logger"

if [ "$#" -lt 1 ]
then 
  echo "Usage: $0 <host>"
  echo "  <host> is the target of the ping, could be a hostname, domain name, or ip address, yet anything pingable, ie mediboard.org"
  exit 1
fi

host=$1

## Make the log line
dt=$(date '+%Y-%m-%dT%H:%M:%S'); 
ping=$(ping $host -c 4 | tr -s "\n" | tail -n 1); 

## Log the line
dir="/var/log/ping"
file="$dir/$host.log"
force_dir $dir
echo "$dt $ping" >> $file
check_errs $? "Failed to log ping" "Ping logged!"