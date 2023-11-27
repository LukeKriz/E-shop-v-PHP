<?php


namespace App\EshopModule\Products\Controllers;

use App\CoreModule\System\Controllers\Controller;
use App\EshopModule\Products\Models\CategoryManager;

/**
 * Zpracovává požadavky na kategorie produktů
 */
class CategoryController extends Controller
{

	/**
	 * Správa kategorií
	 * @Action
	 */
	public function index()
	{
		$this->authUser(true);
		$categoryManager = new CategoryManager();
		$this->view = 'index';
		// Zpracování formuláře
		if (!empty($_POST['output']))
		{
			$json = $_POST['output'];
			$categories = json_decode($json, true);
			if ($categories)
			{
				try
				{
					$categoryManager->saveCategories($categories);
					$this->addMessage('Kategorie byly úspěšně uloženy.', self::MSG_SUCCESS);
					$this->redirect();
				}
				catch (UserException $ex)
				{
					$this->addMessage($ex->getMessage(), self::MSG_ERROR);
				}
			}
			else
				$this->addMessage('Nepodařilo se uložit kategorie.', self::MSG_ERROR);
		}
	}

	/**
	 * JSON s kategoriemi
	 * @ApiAction
	 */
	public function getJson()
	{
		header('Content-Type: text/json; charset=utf-8');
		$categoryManager = new CategoryManager();
		echo($categoryManager->getCategoriesJson());
	}

}
