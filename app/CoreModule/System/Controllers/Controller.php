<?php

/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |  LICENCE
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu a vznikl díky podpoře
 * našich členů. Je určen pouze pro osobní užití a nesmí být šířen.
 */

namespace App\CoreModule\System\Controllers;

use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use Exception;
use ItNetwork\UserException;
use ItNetwork\Utility\StringUtils;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Settings;

/**
 * Předek pro kontrolery v aplikaci
 */
abstract class Controller
{

	/**
	 * Zpráva typu informace
	 */
	const MSG_INFO = 'info';
	/**
	 * Zpráva typu úspěch
	 */
	const MSG_SUCCESS = 'success';
	/**
	 * Zpráva typy chyba
	 */
	const MSG_ERROR = 'danger';
	/**
	 * @var array Pole, jehož indexy jsou poté viditelné v šabloně jako běžné proměnné
	 */
	protected $data = array();
	/**
	 * @var string Název šablony bez přípony
	 */
	protected $view = "";
	/**
	 * @var bool Zda byl kontroler vytvořen API místo článkem
	 */
	protected $createdByApi;

	/**
	 * Inicializuje instanci
	 * @param bool $createdByApi Zda byl kontroler vytvořen API místo článkem
	 */
	public function __construct($createdByApi = false)
	{
		$this->createdByApi = $createdByApi;
	}

	/**
	 * Ošetří proměnnou pro výpis do HTML stránky
	 * @param mixed $x Proměnná k ošetření
	 * @return mixed Ošetřená proměnná
	 */
	private function protect($x = null)
	{
		if (!isset($x))
			return null;
		elseif (is_string($x))
			return htmlspecialchars($x, ENT_QUOTES);
		elseif (is_array($x))
		{
			foreach($x as $k => $v)
			{
				$x[$k] = $this->protect($v);
			}
			return $x;
		}
		else
			return $x;
	}

	/**
	 * Vyrenderuje pohled
	 */
	public function renderView()
	{
		if ($this->view)
		{
			extract($this->protect($this->data));
			extract($this->data, EXTR_PREFIX_ALL, "");

			// Nemůžeme použít funkci pro zjištění namespace protože by vrátila ten abstraktního kontroleru
			$reflect = new ReflectionClass(get_class($this));

			$path = str_replace('Controllers', 'Views', str_replace('\\', '/', $reflect->getNamespaceName()));
			$controllerName = str_replace('Controller', '', $reflect->getShortName());
			$path = '../a' . ltrim($path, 'A') . '/' . $controllerName . '/' . $this->view . '.phtml';

			require($path);
		}
	}

	/**
	 * Přidá zprávu pro uživatele
	 * @param string $content Obsah zprávy
	 * @param string $type Typ zprávy
	 */
	public function addMessage($content, $type = self::MSG_INFO)
	{
		$message = array(
			'content' => $content,
			'type' => $type,
		);
		if (isset($_SESSION['messages']))
			$_SESSION['messages'][] = $message;
		else
			$_SESSION['messages'] = array($message);
	}

	/**
	 * Vrátí zprávy pro uživatele
	 * @return array Zprávy pro uživatele
	 */
	public function getMessages()
	{
		if (isset($_SESSION['messages']))
		{
			$messages = $_SESSION['messages'];
			unset($_SESSION['messages']);
			return $messages;
		}
		else
			return array();
	}

	/**
	 * Přesměruje na dané URL
	 * @param string $url URL
	 */
	public function redirect($url = '')
	{
		if (!$url)
			$url = ArticleManager::$article['url'];
		header("Location: /$url");
		header("Connection: close");
		exit;
	}

	/**
	 * Ověří, zda je přihlášený uživatel, případně přesměruje na login
	 * @param bool $admin Zda musí být uživatel administrátorem
	 */
	public function authUser($admin = false)
	{
		$user = UserManager::$user;
		if (!$user || ($admin && !$user['admin']))
		{
			// Pokud byl požadavek na autentizaci z článku, přesměrujeme na přihlášení
			if (!$this->createdByApi)
			{
				$this->addMessage('Nejsi přihlášený nebo nemáš dostatečná oprávnění.', self::MSG_ERROR);
				$this->redirect('prihlaseni');
			}
			else // Pokud byl požadavek z API, vrátíme chybový kód
			{
				header('HTTP/1.1 401 Unauthorized');
				die('Nedostatečná oprávnění');
			}
		}
	}

	/**
	 * Spustí akci kontroleru podle parametrů z URL adresy
	 * @param array $params Parametry z URL adresy, prvním je název akce. Pokud není uveden, předpokládá se akce index()
	 * @param bool $api Zda chceme renderovat jako API, tedy bez layoutu
	 * @throws Exception
	 */
	public function callActionFromParams($params, $api = false)
	{
		$action = StringUtils::hyphensToCamel($params ? array_shift($params) : 'index');

		// Získání informací o metodě
		try
		{
			$method = new ReflectionMethod(get_class($this), $action);
		}
		catch (ReflectionException $exception)
		{
			$this->throwRoutingException("Neplatná akce - $action");
		}

		// Kontrola přístupu
		$phpDoc = $method->getDocComment();
		$annotation = $api ? '@ApiAction' : '@Action';
		if (mb_strpos($phpDoc, $annotation) === false)
			$this->throwRoutingException("Neplatná akce - $action");

		$requiredParamsCount = $method->getNumberOfRequiredParameters();
		if (count($params) < $requiredParamsCount)
			$this->throwRoutingException("Akci nebyly předány potřebné parametry ($requiredParamsCount)");

		$method->invokeArgs($this, $params);
	}

	/**
	 * V debug módu vyvolá výjimku, jinak přesměruje na 404
	 * @param string $message Zpráva ve výjimce
	 * @throws Exception
	 */
	private function throwRoutingException($message)
	{
		if (Settings::$debug)
			throw new Exception($message);
		else
			$this->redirect('chyba');
	}

}
