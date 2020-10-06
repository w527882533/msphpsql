--TEST--
disables the default error-handling behaviour using configure
--SKIPIF--
<?php require('skipif.inc'); ?>
--FILE--
<?php
/* Connect to the local server using Windows Authentication. */
require('connect.inc');
$connectionInfo = array( "Database"=>"$databaseName", "UID"=>"$uid", "PWD"=>"$pwd");
$conn = sqlsrv_connect( $server, $connectionInfo);
if( $conn === false )
{
     echo "Could not connect.\n";
     die( print_r( sqlsrv_errors(), true));
}

/* The Transact-SQL PRINT statement can be used to return 
informational or warning messages*/
$tsql = "PRINT 'The PRINT statement can be used ";
$tsql .= "to return user-defined warnings.'";

/* Execute the query and print any errors. */
$stmt1 = sqlsrv_query( $conn, $tsql);
if($stmt1 === false)
{
     echo "By default, warnings are treated as errors:\n";
     /* Dump errors in the error collection. */
     print_r(sqlsrv_errors(SQLSRV_ERR_ERRORS));
}

/* Disable warnings as errors behavior. */
sqlsrv_configure("WarningsReturnAsErrors", 0);

/* Execute the same query and print any errors. */
$stmt2 = sqlsrv_query( $conn, $tsql);
if($stmt2 === false)
{
     /* Dump errors in the error collection. */
     /* Since the warning generated by the query will not be treated as 
        an error, this block of code will not be executed. */
     print_r(sqlsrv_errors(SQLSRV_ERR_ERRORS));
}
else
{
     echo "After calling ";
     echo "sqlsrv_configure('WarningsReturnAsErrors', 0), ";
     echo "warnings are not treated as errors.";
}

/*Close the connection. */
sqlsrv_close($conn);
?>
--EXPECTREGEX--
By default, warnings are treated as errors:
Array
\(
    \[0\] => Array
        \(
            \[0\] => 01000
            \[SQLSTATE\] => 01000
            \[1\] => 0
            \[code\] => 0
            \[2\] => \[Microsoft\]\[ODBC Driver 1[1-9] for SQL Server\]\[SQL Server\]The PRINT statement can be used to return user-defined warnings.
            \[message\] => \[Microsoft\]\[ODBC Driver 1[1-9] for SQL Server\]\[SQL Server\]The PRINT statement can be used to return user-defined warnings.
        \)

\)
After calling sqlsrv_configure\('WarningsReturnAsErrors', 0\), warnings are not treated as errors.