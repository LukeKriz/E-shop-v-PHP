<?php


namespace App\CoreModule\Articles\Controllers;

use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use App\CoreModule\System\Controllers\Controller;

/**
 * Zpracovává požadavky na seznam článků
 */
class ArticleListController extends Controller {

	/**
	 * Odstranění článku
	 * @param string $url Unikátní URL adresa článku k odstranění
	 * @Action
	 */
	public function remove($url)
	{
		$articleManager = new ArticleManager();
		$this->authUser(true);
		$articleManager->removeArticle($url);
		$this->addMessage('Článek byl úspěšně odstraněn', self::MSG_SUCCESS);
		$this->redirect('seznam-clanku');
	}

	/**
	 * Seznam článků
	 * @Action
	 */
	public function index()
	{
		$articleManager = new ArticleManager();
		$this->data['admin'] = UserManager::$user && UserManager::$user['admin'];
		$this->data['articles'] = $articleManager->getArticles();
		$this->view = 'index';
	}

}
