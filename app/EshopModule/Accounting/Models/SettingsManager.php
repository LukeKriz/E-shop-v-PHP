<?php

namespace App\EshopModule\Accounting\Models;

use ItNetwork\Db;
use ItNetwork\Image;
use ItNetwork\UserException;

/**
 * Spravuje účetní nastavení e-shopu
 */
class SettingsManager
{

	/**
	 * Vrátí účetní nastavení pro dané datum
	 * @param string $date Datum ve formátu yyyy-mm-dd
	 * @return array mixed Účetní nastavení pro dané období
	 * @throws UserException
	 */
	public function getSettings($date)
	{
		$settings = Db::queryOne('
			SELECT settings_id, vat, accountant_detail_id, seller_id, (tax_number != "") AS vat_payer
			FROM accounting_settings
			JOIN person ON (person_id = seller_id)
			JOIN person_detail USING (person_detail_id)
			WHERE valid_from <= DATE(?) AND valid_to >= DATE(?)
		', array($date, $date));
		if (!$settings)
			throw new UserException('Nenalezeno nastavení pro dané období');
		return $settings;
	}

	/**
	 * Vrátí nastavení pro všechna období
	 * @return array Nastavení pro všechna období
	 */
	public function getAllSettings()
	{
		return Db::queryAll('
			SELECT settings_id, valid_from, valid_to, vat,
				   CONCAT_WS(" ", accountant_detail.first_name, accountant_detail.last_name,  seller_detail.company_name) AS accountant,
				   CONCAT_WS(" ", seller_detail.first_name, seller_detail.last_name, seller_detail.company_name) AS seller
			FROM accounting_settings
			JOIN person ON (person_id = seller_id)
			JOIN person_detail AS seller_detail ON (person.person_detail_id = seller_detail.person_detail_id)
			JOIN person_detail AS accountant_detail ON (accountant_detail.person_detail_id = accountant_detail_id)
			ORDER BY valid_from DESC
		');
	}

	/**
	 * Přidá nastavení
	 * @param array $settings Nastavení
	 * @param string $signature Cesta k obrázku s podpisem
	 * @throws UserException
	 */
	public function addSettings($settings, $signature)
	{
		if (!Image::isImage($signature))
			throw new UserException('Neplatný obrázek podpisu.');
		Db::insert('accounting_settings', $settings);
		$image = new Image($signature);
		$image->resizeToWidth(250);
		$image->save('images/signatures/' . Db::getLastId() . '.png', Image::IMAGETYPE_PNG);
	}

	/**
	 * Odstraní dané nastavení
	 * @param int $settingsId ID nastavení
	 */
	public function deleteSettings($settingsId)
	{
		Db::query('DELETE FROM accounting_settings WHERE settings_id = ?', array($settingsId));
		// Odstranění podpisu
		$path = 'images/signatures/' . (int)$settingsId . '.png';
		if (file_exists($path))
			unlink($path);
	}

}