#!/bin/sh
echo 'SET AUTOCOMMIT=0;'
echo 'SET FOREIGN_KEY_CHECKS=0;'

mysqldump --opt -h db.ssu -u accountingread -paccountingread accounting
exitcode=$?

echo 'SET FOREIGN_KEY_CHECKS=1;'
echo 'COMMIT;'
echo 'SET AUTOCOMMIT=1;'

exit $exitcode
