<?php
namespace Config;
//此文件已经不再使用，现在全部使用thrift和rpc
class Db
{
    public $write = array(
        'search' => array(
            'dsn' => 'mysql:host=10.1.17.3;port=6006;dbname=search',
            'user' => 'search_swd',
            'password' => 'quite22-Gael',
            'confirm_link' => true,
             //required to set to TRUE in daemons.
            'options' => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                \PDO::ATTR_TIMEOUT => 3
            )
        ) ,
    );


    public $read = array(
        'jumei_product' => array(
            'dsn' => 'mysql:host=10.1.17.22;port=6006;dbname=jumei_product',
            'user' => 'search',
            'password' => 'quite22-Gael',
            'confirm_link' => true,
             //required to set to TRUE in daemons.
            'options' => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                \PDO::ATTR_TIMEOUT => 3
            )
        ) ,
        );
}
