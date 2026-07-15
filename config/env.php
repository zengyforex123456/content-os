<?php
/**
 * 环境配置 — 从 .env 加载或使用默认值
 */
declare(strict_types=1);

define('APP_ENV',  getenv('APP_ENV')  ?: 'production');
define('APP_URL',  getenv('APP_URL')  ?: 'http://localhost');
define('DB_HOST',  getenv('DB_HOST')  ?: '127.0.0.1');
define('DB_NAME',  getenv('DB_NAME')  ?: 'my_app');
define('DB_USER',  getenv('DB_USER')  ?: 'root');
define('DB_PASS',  getenv('DB_PASS')  ?: '');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: APP_URL);
define('ASSETS_BASE_URL', APP_BASE_URL);
define('FEATURE_DOCK_NAV', true);
define('ROOT_PATH', dirname(__DIR__));
