<?php
/**
 * Main entry point for the Skincare Formulation App
 * Serves the frontend with PHP backend support
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Load the HTML content
include 'index.html';
?>