<?php


namespace App\CoreModule\System\Controllers;

use App\CoreModule\Articles\Controllers\ArticleController;
use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use App\EshopModule\Accounting\Models\SettingsManager;
use App\EshopModule\Products\Models\CategoryManager;
use App\EshopModule\Products\Models\OrderManager;
use ItNetwork\Utility\DateUtils;
use Settings;

/**
 * Prvotní kontroler, na který se uživatel dostane po zadání URL adresy
 */
class RouterController extends Controller
{
	/**
	 * @var ArticleController Instance vnořeného kontroleru, který zpracovává článek
	 */
	protected $controller;

	/**
	 * Naparsuje URL adresu podle lomítek a vrátí pole parametrů
	 * @param string $url URL adresa
	 * @return array Naparsovaná URL adresa
	 */
	private function parseUrl($url)
	{
		// Naparsuje jednotlivé části URL adresy do asociativního pole
        $parsedUrl = parse_url($url);
		// Odstranění počátečního lomítka
		$parsedUrl["path"] = ltrim($parsedUrl["path"], "/");
		// Odstranění bílých znaků kolem adresy
		$parsedUrl["path"] = trim($parsedUrl["path"]);
		// Rozbití řetězce podle lomítek
		$splitPath = explode("/", $parsedUrl["path"]);
		return $splitPath;
	}

	/**
	 * Zpracuje dotaz na článek
	 * @param array $parameters Pole parametrů z URL adresy
	 */
	private function processArticleRequest($parameters)
	{
		$categoryManager = new CategoryManager();
		$orderManager = new OrderManager();
		$settingsManager = new SettingsManager();
		$admin = UserManager::$user && UserManager::$user['admin'];
		$this->data['categories'] = $categoryManager->getCategories($admin);

		if (isset($_POST['search-phrase']))
		{
			$this->redirect('produkty?phrase=' . $_POST['search-phrase']);
		}

		// Volání controlleru
		$this->controller = new ArticleController();
		$this->controller->index($parameters);

		// Nastavení proměnných pro šablonu
		$this->data['domain'] = Settings::$domain;
		$this->data['title'] = ArticleManager::$article['title'];
		$this->data['description'] = ArticleManager::$article['description'];
		$this->data['messages'] = $this->getMessages();
		$this->data['settings'] = $settingsManager->getSettings(DateUtils::dbNow());
		$this->data['cart'] = $orderManager->getOrderSummary();
		// Nastavení hlavní šablony
		$this->view = 'layout';
	}

	/**
	 * Zpracuje dotaz na API
	 * @param array $parameters Pole parametrů z URL adresy
	 */
	private function processApiRequest($parameters)
	{
		// Rozbití jmenných prostorů podle "-" a přidání "Controllers"
		$pieces = explode('-', array_shift($parameters));
		array_splice($pieces, count($pieces) - 1, 0, 'Controllers');

		$controllerPath = 'App\\' . implode('\\', $pieces);
		$controllerPath .= 'Controller';
		// Bezpečnostní kontrola cesty
		if (preg_match('/^[a-zA-Z0-9\\\\]*$/u', $controllerPath))
		{
			$controller = new $controllerPath(true);

			$controller->callActionFromParams($parameters, true);
			$controller->renderView();
		}
		else
			$this->redirect('error');
	}

	/**
	 * Naparsování URL adresy a vytvoření příslušného controlleru
	 * @param array $parameters Pod indexem 0 se očekává URL adresa ke zpracování
	 */
    public function index($parameters)
    {
		$parsedUrl = $this->parseUrl($parameters[0]);

		$userManager = new UserManager();
		$userManager->loadUser();

		if (empty($parsedUrl[0]))
			$parsedUrl[0] = 'uvod';

		if ($parsedUrl[0] == 'api') // Zpracováváme požadavek na API
		{
			array_shift($parsedUrl); // Odstranění prvního parametru "api"
			$this->processApiRequest($parsedUrl);
		}
		else
			$this->processArticleRequest($parsedUrl);
    }

}