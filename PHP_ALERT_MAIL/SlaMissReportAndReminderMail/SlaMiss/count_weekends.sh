#!/bin/bash

startdate=$1
enddate=$2
var=0
if [ $startdate == $enddate ]
then
var=0
echo $var
exit
fi

d=; 
n=0; 
until [ "$d" = "$enddate" ]; do ((n++)); d=$(date -d "$startdate + $n days" +%Y-%m-%d); 

#echo "$d ";
DAYOFWEEK=$(date -d "$d" "+%u")
#echo $DAYOFWEEK
if [ $DAYOFWEEK -eq 6 ]
then
var=$((var+1))
fi
if [ $DAYOFWEEK -eq 7 ]
then
var=$((var+1))
fi
done

echo $var

