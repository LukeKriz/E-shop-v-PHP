<?php


namespace App\CoreModule\System\Controllers;

/**
 * Zpracovává chybovou stránku
 */
class ErrorController extends Controller
{
    /**
     * Odešle chybovou hlavičku
     * @Action
     */
    public function index()
    {
        // Hlavička požadavku
        header("HTTP/1.0 404 Not Found");
    }
}