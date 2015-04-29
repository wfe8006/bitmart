<?php

$postgresql_hostname = "127.0.0.1";
$postgresql_port = 5432;
$postgresql_dbname = "bitmart";
$postgresql_username = "postgres";
$postgresql_password = 'postgres';
$sphinx_hostname = "192.168.0.5:9306";

$conn_string = "host=$postgresql_hostname port=$postgresql_port dbname=$postgresql_dbname user=$postgresql_username password=$postgresql_password";
$dbconn = pg_connect($conn_string);
if ( ! $dbconn)
{
    echo "An error occured.\n";
    exit;
}

$link = mysql_connect($sphinx_hostname, '', '') or die('cannot connect to mysql sphinx');
$result = pg_query($dbconn, "SELECT id, name, has_child, parent_id FROM categories_entity WHERE active = '1'");

while ($data = pg_fetch_object($result))
{
    $categories_entity_id = $data->id;
    $sub_result = mysql_query("SELECT id FROM categories_entity WHERE id = $categories_entity_id");
    $exist_obj = mysql_fetch_array($sub_result);
    $table_id = $exist_obj["id"];
    if ($table_id  == "")
    {
        $name = mysql_real_escape_string($data->name);
        $has_child = $data->has_child;
        $parent_id = $data->parent_id; 
        mysql_query("INSERT INTO categories_entity(id, name, has_child, parent_id) VALUES('$categories_entity_id', '$name', '$has_child', '$parent_id')");
        print"\nINSERT INTO categories_entity(id, name, has_child, parent_id) VALUES('$categories_entity_id', '$name', '$has_child', '$parent_id')";
        
    }
    else
    {
        echo "\nCategories: {$data->name}  exists in sphinx database";
    }
}
pg_free_result($result);

