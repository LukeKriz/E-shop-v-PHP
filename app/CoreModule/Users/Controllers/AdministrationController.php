<?php


namespace App\CoreModule\Users\Controllers;

use App\CoreModule\Users\Models\UserManager;
use App\CoreModule\System\Controllers\Controller;


/**
 * Zpracovává přístup do administrační sekce
 */
class AdministrationController extends Controller
{
	/**
	 * Odhlášení
	 * @Action
	 */
	public function logout()
	{
		$userManager = new UserManager();
		$userManager->logout();
		$this->redirect('prihlaseni');
	}

	/**
	 * Administrační menu
	 * @Action
	 */
    public function index()
    {		
		// Do administrace mají přístup jen přihlášení uživatelé
		$this->authUser();
		// Získání dat o přihlášeném uživateli
		$this->data['email'] = UserManager::$user['email'];
		$this->data['admin'] = UserManager::$user['admin'];
		// Nastavení šablony
		$this->view = 'index';
    }
}