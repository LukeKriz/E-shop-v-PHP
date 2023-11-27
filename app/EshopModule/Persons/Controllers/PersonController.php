<?php

/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|                          _ _ _        LICENCE
 *        ___ ___    ___    ___ ___ ___ ___| | |_|___ ___
 *       |   | . |  |___|  |  _| -_|_ -| -_| | | |   | . |
 *       |_|_|___|         |_| |___|___|___|_|_|_|_|_|_  |
 *                                                   |___|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu s omezeným
 * přeprodáváním a vznikl díky podpoře našich členů. Je určen
 * pouze pro osobní užití a nesmí být šířen. Může být použit
 * v jednom uzavřeném komerčním projektu, pro širší využití je
 * dostupná licence Premium commercial. Více informací na
 * http://www.itnetwork.cz/licence
 */

namespace App\EshopModule\Persons\Controllers;

use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use App\EshopModule\Persons\Models\PersonManager;
use App\EshopModule\Products\Models\OrderManager;
use App\CoreModule\System\Controllers\Controller;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;

/**
 * Zpracovává požadavky na osoby v e-shopu
 */
class PersonController extends Controller
{

	/**
	 * Registrace nové osoby během dokončení objednávky
	 * @Action
	 */
	public function register()
	{
		$personManager = new PersonManager();
		$orderManager = new OrderManager();
		ArticleManager::$article['title'] = 'Registrace';
		$form = $this->getForm(true, false, true, false);

		$this->data['form'] = $form;
		$this->data['displayState'] = true; // Zobrazovat grafický stav objednávky
		$this->data['createAccount'] = true; // Zobrazovat možnost vytvořit uživatelský účet
		$this->data['forceAccount'] = false; // Vynutit vytvoření uživatelského účtu spolu s osobou

		$this->data['admin'] = false;
		$this->data['antispam'] = true;
		$this->view = 'manage';

		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				$person = $form->getData();
				if ($person['y'] != date('Y'))
					throw new UserException('Špatně vyplněný antispam.');

				$person['person_id'] = null;
				$userManager = new UserManager();
				if ($person['create_account'])
				{
					if (!$person['password'])
						throw new UserException('Nebylo zadáno heslo.');
					if ($personManager->doesPersonWithEmailExist($person['email']))
						throw new UserException('Účet se zadanou emailovou adresou již existuje.');
					$userId = $userManager->register(null, $person['password'], $person['password_repeat'], $person['y']);
					$this->addMessage('Byl jste úspěšně registrován.', self::MSG_SUCCESS);
				}
				else
					$userId = null;

				$personId = $personManager->savePerson($person, $userId);

				$orderManager->setPerson($personId);
				$this->redirect('objednavka/payment');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
		else // Vyplnění formuláře daty
		{
			$order = $orderManager->getOrder();
			$person = $personManager->getPerson($order['buyer_id']);
			if ($person)
			{
				$person['omit_delivery_address'] = !(bool)$person['delivery_address_id'];
				$form->setData($person);
			}
		}

	}

	/**
	 * Registrace nového uživatelského účtu spolu s osobou
	 * @Action
	 */
	public function registerAccount()
	{
		$personManager = new PersonManager();
		ArticleManager::$article['title'] = 'Registrace';
		$form = $this->getForm(true, false, true, true);

		$this->data['form'] = $form;
		$this->data['displayState'] = false; // Zobrazovat grafický stav objednávky
		$this->data['createAccount'] = true; // Zobrazovat možnost vytvořit uživatelský účet
		$this->data['forceAccount'] = true; // Vynutit vytvoření uživatelského účtu spolu s osobou

		$this->data['admin'] = false;
		$this->data['antispam'] = true;
		$this->view = 'manage';

		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				$person = $form->getData();
				$person['person_id'] = null;
				$userManager = new UserManager();

				if (!$person['password'])
					throw new UserException('Nebylo zadáno heslo.');
				if ($person['y'] != date('Y'))
					throw new UserException('Špatně vyplněný antispam.');
				if ($personManager->doesPersonWithEmailExist($person['email']))
					throw new UserException('Účet se zadanou emailovou adresou již existuje.');

				$userId = $userManager->register(null, $person['password'], $person['password_repeat'], $person['y']);
				$this->addMessage('Byl jste úspěšně registrován, přihlaste se níže.', self::MSG_SUCCESS);

				$personManager->savePerson($person, $userId);
				$this->redirect('prihlaseni');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
	}

	/**
	 * Editace dat osoby uživatelem
	 * @Action
	 */
	public function manage()
	{
		$this->authUser();

		$personManager = new PersonManager();
		$person = $personManager->getPerson($personManager->getPersonId(UserManager::$user['user_id']));
		$form = $this->getForm(false, UserManager::$user['admin'], false);

		$this->data['form'] = $form;
		$this->data['register'] = false;
		$this->data['admin'] = UserManager::$user['admin'];
		$this->data['displayState'] = false; // Zobrazovat grafický stav objednávky
		$this->data['createAccount'] = false; // Zobrazovat možnost vytvořit uživatelský účet
		$this->data['antispam'] = false;
		$this->view = 'manage';

		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				$data = $form->getData();
				$data['person_id'] = $personManager->getPersonId(UserManager::$user['user_id']);
				if ($personManager->doesPersonWithEmailExist($data['email']) && $data['email'] != $person['email'])
					throw new UserException('Účet se zadanou emailovou adresou již existuje.');

				$personManager->savePerson($data, null);
				$this->addMessage('Osoba byla úspěšně uložena.', self::MSG_SUCCESS);
				$this->redirect('osoby/manage');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
		else // Vyplnění formuláře daty
		{
			$person['omit_delivery_address'] = !(bool)$person['delivery_address_id'];
			$form->setData($person);
		}
	}

	/**
	 * Vrátí formulář ke správě osoby
	 * @param bool $addRegisterControls Přidat kontrolky pro vytvoření uživatelského účtu
	 * @param bool $admin Registruje/Edituje se administrátor (zadává číslo účtu a kód banky)
	 * @param bool $antispam Přidat antispam
	 * @param bool $forceAccount Vynutit vytvoření uživatelského účtu spolu s vytvořením osoby
	 * @return Form Formulář ke správě osoby
	 */
	public function getForm($addRegisterControls = false, $admin = false, $antispam = true, $forceAccount = false)
	{
		$personManager = new PersonManager();
		$countries = $personManager->getCountries();
		$form = new Form('person-form');

		$form->addEmailBox('email', 'Email', true);
		if ($antispam)
			$form->addNumberBox('y', 'Zadej aktuální rok', true);

		if ($addRegisterControls)
		{
			if (!$forceAccount)
				$form->addCheckBox('create_account', 'Vytvořit zákaznický účet');
			$form->addPasswordBox('password', 'Heslo', false)
				->setTooltip('Zadejte heslo alespoň na 6 znaků');
			$form->addPasswordBox('password_repeat', 'Heslo znovu', false)
				->setTooltip('Zadejte heslo alespoň na 6 znaků');
			$form->addButton('submit', 'Registrovat');
		}

		$form->addHiddenBox('person_id');
		$form->addTextBox('first_name', 'Jméno', false);
		$form->addTextBox('last_name', 'Příjmení', false);
		$form->addTextBox('company_name', 'Společnost', false);
		$form->addNumberBox('identification_number', 'IČ', false);
		$form->addTextBox('tax_number', 'DIČ', false);

		$form->addTextBox('phone', 'Telefon', false);
		$form->addTextBox('fax', 'Fax', false);

		$form->addTextBox('street', 'Ulice', true);
		$form->addTextBox('registry_number', 'Číslo popisné', true);
		$form->addTextBox('house_number', 'Číslo orientační', false);
		$form->addTextBox('city', 'Město', true);
		$form->addTextBox('zip', 'PSČ', true);
		$form->addComboBox('country_id', 'Stát', true)
			->setValues($countries);

		$form->addCheckBox('omit_delivery_address', 'Dodací adresa se shoduje s fakturační', true);
		$form->addTextBox('delivery_street', 'Ulice', false);
		$form->addTextBox('delivery_registry_number', 'Číslo popisné', false);
		$form->addTextBox('delivery_house_number', 'Číslo orientační', false);
		$form->addTextBox('delivery_city', 'Město', false);
		$form->addTextBox('delivery_zip', 'PSČ', false);
		$form->addComboBox('delivery_country_id', 'Stát', false)
			->setValues($countries);

		if ($admin)
		{
			$form->addNumberBox('bank_code', 'Kód banky');
			$form->addNumberBox('account_number', 'Číslo účtu');
			$form->addTextArea('registry_entry', 'Spisová značka');
		}

		if (!$addRegisterControls)
			$form->addButton('odeslat', 'Odeslat');
		return $form;
	}

	/**
	 * Editace dat osoby v objednávce administrátorem
	 * Akce je spouštěna OrderManagementControllerem
	 * @param int $orderId ID objednávky
	 */
	public function editOrderPerson($orderId)
	{
		$this->authUser(true);

		$orderManager = new OrderManager();
		$personManager = new PersonManager();
		$form = $this->getForm(false, false, false);
		$order = $orderManager->getOrder($orderId);

		$this->data['form'] = $form;
		$this->data['displayState'] = false; // Zobrazovat grafický stav objednávky
		$this->data['createAccount'] = false; // Zobrazovat možnost vytvořit uživatelský účet
		$this->data['forceAccount'] = false; // Vynutit vytvoření uživatelského účtu spolu s osobou
		$this->data['admin'] = false;
		$this->data['antispam'] = false;
		$this->view = 'manage';

		// Zpracování formuláře
		if ($form->isPostBack())
		{
			$person = $form->getData();
			$personId = $personManager->savePerson($person);
			$orderManager->setPerson($personId, $orderId, $order['buyer_id']);
			$personManager->deletePerson($personId);
			$this->addMessage('Osoba bylo úspěšně uložena.', self::MSG_SUCCESS);
			$this->redirect('uvod');
		}
		else // Vyplnění formuláře daty
		{
			$person = $personManager->getCustomPerson(null, $order['buyer_person_detail_id'], $order['buyer_address_id'], $order['buyer_delivery_address_id'], null);
			if ($person)
			{
				$person['omit_delivery_address'] = $order['buyer_delivery_address_id'] == $order['buyer_address_id'];
				$form->setData($person);
			}
			else
				$this->addMessage('Osoba nebyla nalezena.', self::MSG_ERROR);
		}
	}

}
