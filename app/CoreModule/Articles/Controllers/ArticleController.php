<?php

namespace App\CoreModule\Articles\Controllers;



use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use App\CoreModule\System\Controllers\Controller;

/**
 * Zpracovává požadavky na článek
 */
class ArticleController extends Controller
{
    /**
     * @var Controller Instance článkového kontroleru
     */
    protected $controller;

    /**
     * Načtení článku
     * @param array $parameters Pole parametrů pro kontroler článku, pokud nějaký má
     */
    public function index($parameters)
    {
		// Vytvoření instance modelu, který nám umožní pracovat s články
		$articleManager = new ArticleManager();
		$this->data['admin'] = UserManager::$user && UserManager::$user['admin'];

        // Získání článku podle URL
        $articleManager->loadArticle($parameters[0]);
        // Pokud nebyl článek s danou URL nalezen, přesměrujeme na ChybaKontroler
        if (!ArticleManager::$article)
            $this->redirect('chyba');

        // Volání vnořeného kontroleru
        if (ArticleManager::$article['controller'])
        {
            $fullName = 'App\\' . ArticleManager::$article['controller'] . 'Controller';
            $controller = new $fullName;
            array_shift($parameters); // mezi parametry nepatří URL článku
            $controller->callActionFromParams($parameters);

            $this->data['controller'] = $controller;
        }
        else
            $this->data['controller'] = null;

        // Naplnění proměnných pro šablonu
        $this->data['title'] = ArticleManager::$article['title'];
        $this->data['content'] = ArticleManager::$article['content'];

        // Nastavení šablony
        $this->view = 'index';
    }
}