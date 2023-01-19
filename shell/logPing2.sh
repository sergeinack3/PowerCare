#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

########
# Ping logger for server load analysis
########

announce_script "Ping logger"

if [ "$#" -lt 1 ]
then
  echo "Usage: $0 <host> <host>"
  echo "  <host> is the target of the ping, could be a hostname, domain name, or ip address, yet anything pingable, ie openxtrem.com"
  exit 1
fi

if [ "$#" -gt 2 ]
then
  echo "Usage: $0 <host> <host>"
  echo "  <host> is the target of the ping, could be a hostname, domain name, or ip address, yet anything pingable, ie openxtrem.com"
  exit 1
fi

host1=$1
host2=$2

for host in $@; do
    ## Make the log line
    date=$(date '+%Y-%m-%d %H:%M:%S');
    ping=$(ping -c 60 -i 60 -q $host | tr -s "\n" | tail -n 1);

    ## Log the line
    dir="/var/log/mediboard"
    file="$dir/$host1-ping.log"
    force_dir $dir
    echo "$date $ping" >> $file
    check_errs $? "Failed to log ping" "Ping logged!"
done