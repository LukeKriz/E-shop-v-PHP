<?php


namespace App\CoreModule\Users\Controllers;

use App\CoreModule\System\Controllers\Controller;
use App\CoreModule\Users\Models\UserManager;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;

/**
 * Zpracovává přihlašování uživatelů
 */
class LoginController extends Controller
{
	/**
	 * Přihlášení
	 * @Action
	 */
    public function index()
    {
		$userManager = new UserManager();
		if (UserManager::$user)
			$this->redirect('administrace');

		$form = $this->getLoginForm();
		$this->data["form"] = $form;

		if ($form->isPostBack())
		{
			try
			{
				$data = $form->getData();
				$userManager->login($data['email'], $data['password']);
				$this->addMessage('Byl jste úspěšně přihlášen.', self::MSG_SUCCESS);
				$this->redirect('administrace');
			}
			catch (UserException $error)
			{
				$this->addMessage($error->getMessage(), self::MSG_ERROR);
			}
		}
		// Nastavení šablony
		$this->view = 'index';
    }

	/**
	 * Vrátí přihlašovací formulář
	 * @return Form Přihlašovací formulář
	 */
	private function getLoginForm() {
		$form = new Form('login');
		$form->addEmailBox('email', 'Email', true);
		$form->addPasswordBox('password', 'Heslo', true)
			->setTooltip('Zadejte heslo alespoň s 6 znaky');
		$form->addButton('submit', 'Přihlásit');
		return $form;
	}
}
