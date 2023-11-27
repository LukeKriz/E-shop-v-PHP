<?php



namespace App\CoreModule\System\Controllers;

use ItNetwork\EmailSender;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;
use Settings;

/**
 * Zpracovává kontaktní formulář
 */
class ContactController extends Controller
{
	/**
	 * Kontakt
	 * @Action
	 */
	public function index()
	{
		$form = $this->getContactForm();
		$this->data['form'] = $form;

		if ($form->isPostBack())
		{
			try
			{
				$data = $form->getData();
				$emailSender = new EmailSender();
				$emailSender->sendWithAntispam($data['y'], Settings::$email, "Email z webu", $data['message'], $data['email']);
				$this->addMessage('Email byl úspěšně odeslán.', self::MSG_SUCCESS);
				$this->redirect('kontakt');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
		
		$this->view = 'index';
    }

	/**
	 * Vrátí kontaktní formulář
	 * @return Form Kontaktní formulář
	 */
	private function getContactForm() {
		$form = new Form('contact');
		$form->addEmailBox('email','E-mail:', true);
		
		$form->addTextBox('y', 'Aktuální rok', true);
		$form->addTextArea('message', 'Zpráva', true)->addMinLengthRule(10);
		$form->addButton('submit', 'Odeslat');
		return $form;
	}

}