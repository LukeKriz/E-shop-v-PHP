<?php


namespace App\EshopModule\Accounting\Controllers;

use App\EshopModule\Accounting\Models\SettingsManager;
use App\EshopModule\Persons\Models\PersonManager;
use App\CoreModule\System\Controllers\Controller;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;
use ItNetwork\Utility\ArrayUtils;

/**
 * Zpracovává požadavky na účetní nastavení
 */
class SettingsController extends Controller
{

	/**
	 * Administrace nastavení
	 * @Action
	 */
	public function index()
	{
		$this->authUser(true);
		$settingsManager = new SettingsManager();
		$form = $this->getForm();
		$allSettings = $settingsManager->getAllSettings();

		$this->data['form'] = $form;
		$this->data['allSettings'] = $allSettings;
		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				$data = $form->getData();
				$signature = $data['signature']['tmp_name'];
				unset($data['signature']);
				$settingsManager->addSettings($data, $signature);
				$this->addMessage('Nastavení bylo přidáno.', self::MSG_SUCCESS);
				$this->redirect();
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
		$this->view = 'index';
	}

	/**
	 * Odstranění nastavení
	 * @Action
	 * @param int $settingsId ID nastavení
	 */
	public function delete($settingsId)
	{
		$this->authUser(true);
		$settingManager = new SettingsManager();
		$settingManager->deleteSettings($settingsId);
		$this->addMessage('Nastavení bylo úspěšně odstraněno.', self::MSG_SUCCESS);
		$this->redirect();
	}

	/**
	 * Vrátí formulář k přidání nastavení
	 * @return Form Formulář k přidání nastavení
	 */
	private function getForm()
	{
		$personManager = new PersonManager();
		$admins = $personManager->getAdmins();
		$form = new Form('settings_form');
		$form->addDatePicker('valid_from', 'Platné od', true);
		$form->addDatePicker('valid_to', 'Platné do', true);
		$form->addNumberBox('vat', 'Sazba DPH v %', true);
		$form->addComboBox('accountant_detail_id', 'Účetní', true)
			->setValues(ArrayUtils::mapPairs($admins, 'name', 'person_detail_id'));
		$form->addComboBox('seller_id', 'Prodejce', true)
			->setValues(ArrayUtils::mapPairs($admins, 'name', 'person_id'));
		$form->addFileBox('signature', 'Podpis', true);
		$form->addButton('add', 'Přidat');
		return $form;
	}

}