<?php

namespace App\CoreModule\Users\Controllers;



use App\CoreModule\System\Controllers\Controller;
use App\CoreModule\Users\Models\UserManager;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;

/**
 * Zpracovává registraci uživatelů
 */
class RegisterController extends Controller
{
	/**
	 * Registrace
	 * @Action
	 */
    public function index()
    {
		$form = $this->getRegisterForm();
		$this->data['form'] = $form;
		if ($form->isPostBack())
		{
			try
			{
				$userManager = new UserManager();
				$data = $form->getData();
				$userManager->register($data['name'], $data['password'], $data['password_repeat'], $data['y']);
				$userManager->login($data['name'], $data['password']);
				$this->addMessage('Byl jste úspěšně zaregistrován.', self::MSG_SUCCESS);
				$this->redirect('administrace');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
		// Nastavení šablony
		$this->view = 'index';
    }

	/**
	 * Vrátí registrační formulář
	 * @return Form Registrační formulář
	 */
	private function getRegisterForm() {
		$form = new Form('register-form');
		$form->addTextBox('name', 'Jméno', true)
			 ->addMinLengthRule(3)
			 ->setTooltip('Zadejte uživatelské jméno alespoň na 3 znaky');
		$form->addPasswordBox('password', 'Heslo', true)
			 ->setTooltip('Zadejte heslo alespoň na 6 znaků');
		$form->addPasswordBox('password_repeat', 'Heslo znovu', true)
			 ->setTooltip('Zadejte heslo alespoň na 6 znaků');
		$form->addNumberBox('y', 'Zadej aktuální rok', true);
		$form->addButton('submit', 'Registrovat');
		return $form;
	}
}