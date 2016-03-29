#!/bin/bash
while read line;  do 	
	echo "Deleting core:  $line"
	curl http://192.168.174.8/crkc_solr_test/${line}/update?commit=true --data '<delete><query>*:*</query></delete>' -H 'Content-type:text/xml; charset=utf-8'
done < inputfile.txt