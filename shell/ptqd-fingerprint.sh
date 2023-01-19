#! /bin/sh

if [[ "$#" < 3 ]]; then
  echo "Please specifiy at least three arguments:"
  echo "1. the fingerprint to filter on, eg D81DDCC7E3DE8201"
  echo "2. the field you want to group by, eg object_class"
  echo "3+. any number of log files, eg *.log" 
  exit 1
fi

fingerprint=$1; shift;
fieldgrouper=$1; shift;
logfiles="$@"

echo "-- Detailed report for fingerprint '$fingerprint' on field grouper '$fieldgrouper'"
echo "-- Parsing log files:  '$logfiles'"

# Filter on the query you wish to have extra information and build a query log for it 
pt-query-digest $logfiles --output slowlog --no-report --filter '$event->{fingerprint} && make_checksum($event->{fingerprint}) eq "'${fingerprint}'"' > fingerprint-$fingerprint.log

# Append acutal object_class to `object_class` column name
sed -E -i.source 's/`?'$fieldgrouper'`? *([^ ]*) *'\''(.*)'\''/'$fieldgrouper'_\2 \1 "\2"/' fingerprint-$fingerprint.log

# Digest the filteres query log
pt-query-digest fingerprint-$fingerprint.log --limit 100%:20 > fingerprint-$fingerprint.$fieldgrouper.report.txt
