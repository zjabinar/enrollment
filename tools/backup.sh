echo 'SET AUTOCOMMIT=0;'
echo 'SET FOREIGN_KEY_CHECKS=0;'

mysqldump --opt -u accounting -p accounting

echo 'SET FOREIGN_KEY_CHECKS=1;'
echo 'COMMIT;'
echo 'SET AUTOCOMMIT=1;'

