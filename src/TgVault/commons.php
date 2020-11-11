<?php 

if (!defined('VAULT_OK')) {
    define('VAULT_OK',                  0);
    define('VAULT_ERR_CONFIG_EMPTY',    1);
    define('VAULT_ERR_CONFIG_TYPE',     2);
    define('VAULT_ERR_TYPE_EMPTY',      3);
    define('VAULT_ERR_TYPE_NOT_FOUND',  4);
    define('VAULT_ERR_RENEWAL',         5);
    define('VAULT_ERR_TOKEN_EXPIRED',   6);
    define('VAULT_ERR_NO_TOKEN',        7);
    define('VAULT_ERR_SECRET_INTERNAL', 8);
    define('VAULT_ERR_SECRET',          9);
    define('VAULT_ERR_CONFIG',         10);
    define('VAULT_ERR_NULL',           11);
    define('VAULT_ERR_NOT_FOUND',      12);
    define('VAULT_ERR_FILE_NOT_FOUND', 13);
    define('VAULT_ERR_HTTP_BASE',       0);
    define('VAULT_ERR_CURL_BASE',    1000);
}