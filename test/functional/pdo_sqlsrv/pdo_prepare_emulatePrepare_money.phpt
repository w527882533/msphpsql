--TEST--
prepare with emulate prepare and binding integer
--SKIPIF--
<?php require('skipif_mid-refactor.inc'); ?>
--FILE--
<?php
require_once('MsCommon_mid-refactor.inc');

function printRow($row, $inputValues)
{
    if (empty($row)) {
        return; // do nothing
    }
    
    $key = 'c3_float';
    if (!compareFloats($inputValues[$key], $row[$key])) {
        echo "Expected $inputValues[$key] but got $row[$key]\n";
    }
    // should not expect the floats to exactly match, so
    // remove the last element from the array for printing
    array_pop($row);
    print_r($row);
}

try {
    $conn = connect("", array(), PDO::ERRMODE_SILENT);

    $tableName = "number_types";
    if (!isAEConnected()) {
        createTable($conn, $tableName, array("c1_decimal" => "decimal", "c2_money" => "money", "c3_float" => "float"));
    } else {
        // money is not supported for column encryption, use decimal(19,4) instead
        createTable($conn, $tableName, array("c1_decimal" => "decimal", "c2_money" => "decimal(19,4)", "c3_float" => "float"));
    }

    $inputValues = array( array('c1_decimal' => '411.1', 'c2_money' => '131.11', 'c3_float' => 611.111),
                          array('c1_decimal' => '422.2222', 'c2_money' => '132.222', 'c3_float' => 622.22),
                          array('c1_decimal' => '433.333', 'c2_money' => '133.3333', 'c3_float' => 633.33333));

    for ($i = 0; $i < count($inputValues); $i++) {
        insertRow($conn, $tableName, $inputValues[$i]);
    }

    $query = "SELECT * FROM [$tableName] WHERE c2_money = :c2";

    // prepare without emulate prepare
    print_r("Prepare without emulate prepare:\n");
    $options = array(PDO::ATTR_EMULATE_PREPARES => false);
    $stmt = $conn->prepare($query, $options);
    $c2 = '133.3333';
    $stmt->bindParam(':c2', $c2);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    printRow($row, $inputValues[2]);

    //with emulate prepare and no bind param options
    print_r("Prepare with emulate prepare and no bind param options:\n");
    if (!isAEConnected()) {
        // emulate prepare is not supported for encrypted columns
        $options = array(PDO::ATTR_EMULATE_PREPARES => true);
    }
    $stmt = $conn->prepare($query, $options);
    $stmt->bindParam(':c2', $c2);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    printRow($row, $inputValues[2]);

    //with emulate prepare and encoding SQLSRV_ENCODING_SYSTEM
    print_r("Prepare with emulate prepare and SQLSRV_ENCODING_SYSTEM:\n");
    $stmt = $conn->prepare($query, $options);
    $stmt->bindParam(':c2', $c2, PDO::PARAM_STR, 0, PDO::SQLSRV_ENCODING_SYSTEM);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    printRow($row, $inputValues[2]);

    //prepare with emulate prepare and encoding SQLSRV_ENCODING_UTF8
    print_r("Prepare with emulate prepare and SQLSRV_ENCODING_UTF8:\n");
    $stmt = $conn->prepare($query, $options);
    $stmt->bindParam(':c2', $c2, PDO::PARAM_STR, 0, PDO::SQLSRV_ENCODING_UTF8);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    printRow($row, $inputValues[2]);

    //prepare with emulate prepare and encoding SQLSRV_ENCODING_BINARY
    print_r("Prepare with emulate prepare and SQLSRV_ENCODING_BINARY:\n");
    $stmt = $conn->prepare($query, $options);
    $stmt->bindParam(':c2', $c2, PDO::PARAM_STR, 0, PDO::SQLSRV_ENCODING_BINARY);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    printRow($row, $inputValues[2]);
    if ($stmt->rowCount() == 0) {
        print_r("No results for this query\n");
    }

    dropTable($conn, $tableName);
    unset($stmt);
    unset($conn);
} catch (PDOException $e) {
    var_dump($e->errorInfo);
}
?>

--EXPECTF--
Prepare without emulate prepare:
Array
(
    [c1_decimal] => 433
    [c2_money] => 133.3333
)
Prepare with emulate prepare and no bind param options:
Array
(
    [c1_decimal] => 433
    [c2_money] => 133.3333
)
Prepare with emulate prepare and SQLSRV_ENCODING_SYSTEM:
Array
(
    [c1_decimal] => 433
    [c2_money] => 133.3333
)
Prepare with emulate prepare and SQLSRV_ENCODING_UTF8:
Array
(
    [c1_decimal] => 433
    [c2_money] => 133.3333
)
Prepare with emulate prepare and SQLSRV_ENCODING_BINARY:
No results for this query
