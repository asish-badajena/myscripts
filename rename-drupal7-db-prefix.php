<?php 
// *************************************************
// * @Filename: rename-drupal7-db-prefix           *
// *                                               *
// * File for renaming drupal7 database table      *
// * prefix. To execute this script the mysql user *
// * must have previllage to rename the table.     *
// *************************************************
// * Author: Asish Badajena <asish@gmail.com>      *
// *************************************************

$input = $argv;

# The first argument is script name.
# so remove it from the list.
unset($input[0]); 
$data = [];
foreach ($input as $val) {
    $dArr = explode("=", $val, 2);
    $data[$dArr[0]] = $dArr[1];
}
$givenKeys    = array_keys($data);
$requiredKeys = [
    "host", 
    "dbname", 
    "uname", 
    "password", 
    "old-prefix", 
    "new-prefix"
];
if (array_diff($requiredKeys, $givenKeys)) {
    print "
host=[databe host name]\n\n 
dbane=[Database name]\n\n
uname=[database username]\n\n
password=[database server password. If password is empty, provide password=\"\"]\n\n
old-prefix=[Old prefix of tables. If prefix is empty, provide old-prefix=\"\"]\n\n
new-prefix=[New prefix of tables. If prefix is empty, provide new-prefix=\"\"]\n 
";
exit;
} 
 
function showLog($msg, $mode="Error") {
    print date("Y-m-d H:i:s")."::$mode: $msg\n";
}

try {
    $dsn = "mysql:host={$data['host']};dbname={$data['dbname']};";
    $pdo = new PDO($dsn, $data["uname"], $data["password"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    # First get all the tables present in
    # the database
    $stmt = $pdo->query("SHOW TABLES"); 
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $query ="RENAME TABLE ";
    $tableName = [];
    foreach ($tables as $table) {
        if (empty($data["old-prefix"])) {
            $actualTableName = $table;
        } else {
            $tmp = explode($data["old-prefix"], $table, 2);
            if (count($tmp) != 2) {
                showLog("Old prefix is wrong.");
                exit;
            }
            $actualTableName = $tmp[1];
        } 
        $newTableName = $data["new-prefix"].$actualTableName;
        showLog("$table will renamed to $newTableName", "INFO");
        $tableName[] = "$table to $newTableName";
    }
    $query .= implode(", ", $tableName);
    $pdo->query($query);
    showLog("All tables renamed successfully.", "SUCCESS"); 
} catch (Exception $e) {
    print "Error: {$e->getMessage()}.\n";
    exit;
}
?>