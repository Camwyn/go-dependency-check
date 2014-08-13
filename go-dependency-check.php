<?php
/**
 * Plugin Name: Gigaom Dependency Check
 * Plugin URI: http://gigaom.com
 * Description: A plugin to centralize our plugin dependency checking.
 * Version: 1.0
 * Author: Gigaom
 * Author URI: http://gigaom.com/
 */

require_once __DIR__ . '/components/class-go-dependency-check.php';
go_dependency_check();