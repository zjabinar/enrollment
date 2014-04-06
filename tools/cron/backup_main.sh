#!/bin/sh

dateext=`date +%y%m%d%H%M%S`

# get present data
./backup.sh > sis_tmp.sql
result=$?

# only if backup.sh is successfull
if [ $result == 0 ] ; then
	# compress
	gzip sis_tmp.sql

	# increase sequence number of last 7 backup (remove 7th backup)
	for(( i=7,j=8; i>=1; i--,j-- ))
	do
		newname=`basename sis_*.$i.sql.gz .$i.sql.gz`.$j.sql.gz
		mv sis_*.$i.sql.gz $newname
	done
	rm -f sis_*.8.sql.gz

	# rename present data
	mv sis_tmp.sql.gz sis_$dateext.1.sql.gz
fi

exit 0
