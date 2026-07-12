<?php
/**
 * AstraCampus - Root Entry Point
 *
 * If your web server's document root points at this project's ROOT folder
 * (instead of the /public folder, which is recommended), this file forwards
 * the request into public/index.php so the app still works.
 */
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
