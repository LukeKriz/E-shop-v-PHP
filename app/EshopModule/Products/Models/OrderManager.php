<?php

namespace App\EshopModule\Products\Models;

use App\EshopModule\Products\Controllers\OrderController;
use DateTime;
use FormatHelper;
use ItNetwork\Db;
use ItNetwork\EmailSender;
use ItNetwork\UserException;
use ItNetwork\Utility\DateUtils;
use ItNetwork\Utility\StringUtils;
use ProductHelper;
use Settings;

/**
 * Správce objednávek
 */
class OrderManager
{
	/**
	 * Objednávka byla vytvořena
	 */
	const STATE_CREATED = 'created';
	/**
	 * Objednávka byla dokončena
	 */
	const STATE_COMPLETED = 'completed';
	/**
	 * Objednávka byla přijata ke zpracování
	 */
	const STATE_ACCEPTED = 'accepted';
	/**
	 * Objednávka byla odeslána
	 */
	const STATE_SENT = 'sent';
	/**
	 * Objednávka byla pozastavena
	 */
	const STATE_SUSPENDED = 'suspended';
	/**
	 * Objednávka byla zamítnuta
	 */
	const STATE_CANCELED = 'canceled';
	/**
	 * @var int ID košíku zákazníka nebo NULL pokud nemá ještě vytvořený košík
	 */
	private $orderId = null;

	/**
	 * Vytvoří uživateli nový košík
	 * @return int ID košíku
	 */
	public function createOrder()
	{
		$token = md5(microtime());
		Db::insert('e_order', array(
			'token' => $token,
			'created' => DateUtils::dbNow(),
		));
		$orderId = Db::getLastId();
		$queryString = http_build_query(array(
			'order_id' => $orderId,
			'token' => $token,
		));
		setcookie("order", $queryString, time() + (3600 * 24 * 365), '/', '', null, true);
		return $orderId;
	}

	/**
	 * Vrátí ID košíku zákazníka
	 * @param bool $create Zda se má košík v případě neexistence vytvořit
	 * @return int ID košíku zákazníka
	 */
	public function getOrderId($create = true)
	{
		if ($this->orderId)
			return $this->orderId;
		$parameters = array('order_id' => null, 'token' => null);
		if (isset($_COOKIE["order"]))
			parse_str($_COOKIE["order"], $parameters);

		if (!$create && !$parameters['order_id'])
			return null;

		$dbOrderId = Db::querySingle('
			SELECT e_order_id
			FROM e_order
			WHERE e_order_id = ? AND token = ? AND state = "created"
		', array($parameters['order_id'], $parameters['token']));
		if (!$dbOrderId)
			return $this->createOrder();
		return $dbOrderId;
	}

	/**
	 * Zjistí, zda je produkt možné vložit do košíku
	 * @param int $productId ID produktu
	 * @return bool Zda je produkt možné vložit do košíku
	 */
	private function isProductAvailable($productId)
	{
		return (bool)Db::querySingle('
			SELECT product_id
			FROM product
			JOIN product_category USING (product_id)
			JOIN category USING (category_id)
			WHERE product_id = ? AND NOT product.hidden AND NOT category.hidden
		', array($productId));
	}




	public static function pocet() 
	{
		$pocet=Db::querySingle('
		SELECT SUM(`quantity`) FROM product_e_order');
		
	
		print_r($pocet);


		
	}
	/**
	 * Přidá produkt do košíku
	 * @param int $productId ID produktu
	 * @param int $quantity Počet kusů
	 * @param int|null $orderId ID objednávky. Pokud není uvedeno, přidáváme do aktuální objednávky.
	 * @param bool $ignoreHiddenProducts Zda chceme povolit přidávání skrytých produktů
	 * @throws UserException
	 */
	public function addProducts($productId, $quantity, $orderId = null, $ignoreHiddenProducts = false)
	{
		if (!$orderId)
			$orderId = $this->getOrderId();
		if ($quantity <= 0)
			throw new UserException('Nelze vložit záporný počet položek.');
		if (!$ignoreHiddenProducts && !$this->isProductAvailable($productId))
			throw new UserException('Tento produkt není dostupný.');
		// Pokud je již položka v košíku, pouze změníme její počet
		$exists = Db::querySingle('
			SELECT e_order_id
			FROM product_e_order
			WHERE product_id = ? AND e_order_id = ?
		', array($productId, $orderId));

		if ($exists) // Zvyšujeme počet u položky v košíku
			Db::query('
				UPDATE product_e_order
				SET quantity = quantity + ?
				WHERE product_id = ? AND e_order_id = ?
			', array($quantity, $productId, $orderId));
		else // Vkládáme položku nově do košíku
			Db::insert('product_e_order', array(
				'product_id' => $productId,
				'e_order_id' => $orderId,
				'quantity' => $quantity,
			));
	}

	/**
	 * Vrátí přehled košíku
	 * @param null|int $orderId ID objednávky. Pokud není zadané, pracuje s aktuální objednávkou
	 * @return array Přehled košíku
	 */
	public function getOrderSummary($orderId = null)
	{
		if (!$orderId)
			$orderId = $this->getOrderId(false);
		if (!$orderId)
			return array(
				'products' => 0,
				'price' => 0,
			);
		return Db::queryOne('
			SELECT (
				SELECT SUM(quantity)
				FROM product_e_order
				WHERE e_order_id = ?
			) AS products, (
				SELECT SUM(price * quantity)
				FROM product_e_order
				JOIN product USING (product_id)
				WHERE e_order_id = ?
			) AS price
		', array($orderId, $orderId));
	}



	/**
	 * Vrátí produkty v košíku
	 * @param null|int $orderId ID objednávky. Pokud není zadané, pracuje s aktuální objednávkou
	 * @return array Produkty v košíku
	 */
	public function getProducts($orderId = null)
	{
		if (!$orderId)
			$orderId = $this->getOrderId(false);
		if (!$orderId)
			return array();
		return Db::queryAll('
			SELECT product_id, quantity, title, url, price
			FROM product_e_order
			JOIN product using (product_id)
			WHERE e_order_id = ?
			ORDER BY product_e_order_id
		', array($orderId));
	}

	/**
	 * Upraví produkt v objednávce
	 * @param int $productId ID produktu
	 * @param int $quantity Počet kusů. Pokud počet upravíme na 0, produkt se odebere.
	 * @param int $orderId ID objednávky
	 * @throws UserException
	 */
	public function updateProductInOrder($productId, $quantity, $orderId)
	{
		if ($quantity < 0)
			throw new UserException('Počet produktů nesmí být záporný');
		elseif ($quantity == 0) // Odstranění produktu z košíku
			Db::query('DELETE FROM product_e_order WHERE product_id = ? AND e_order_id = ?', array($productId, $orderId));
		else // Úprava počtu produktů
			Db::query('
				UPDATE product_e_order
				SET quantity = ?
				WHERE product_id = ? AND e_order_id = ?
			', array($quantity, $productId, $orderId));
	}

	/**
	 * Upraví počet položek v košíku dle dat odeslaných formulářem
	 * @param array $formData Data z formuláře
	 * @throws UserException
	 */
	public function updateCart($formData)
	{
		$orderId = $this->getOrderId(false);
		foreach ($formData as $controlName => $quantity)
		{
			$productId = str_replace('quantity_', '', $controlName);
			$this->updateProductInOrder($productId, $quantity, $orderId);
		}
	}

	/**
	 * Nastaví k objednávce osobu
	 * @param int $personId ID osoby
	 * @param null|int $orderId ID objednávky. Pokud není zadané, pracuje s aktuální objednávkou
	 * @param null|int $orderPersonId Umožňuje nastavit jiné ID zákazníka, než z jaké osoby pocházejí data.
	 * Pokud není zadané, předpokládá se stejné jako ID osoby.
	 */
	public function setPerson($personId, $orderId = null, $orderPersonId = null)
	{
		$orderManager = new OrderManager();
		if (!$orderId)
			$orderId = $orderManager->getOrderId(false);
		if (!$orderPersonId)
			$orderPersonId = $personId;
		Db::query('
			UPDATE e_order
			JOIN person ON (person_id = ?)
			SET
				buyer_delivery_address_id = COALESCE (delivery_address_id, address_id),
				buyer_address_id = address_id,
				buyer_person_detail_id = person_detail_id,
				buyer_id = ?
			WHERE e_order_id = ?
		', array($personId, $orderPersonId, $orderId));
	}

	/**
	 * Vrátí objednávku
	 * @param null|int $orderId ID objednávky
	 * @return array Objednávka
	 */
	public function getOrder($orderId = null)
	{
		if (!$orderId)
			$orderId = $this->getOrderId(true);
		return Db::queryOne('SELECT * FROM e_order WHERE e_order_id = ?', array($orderId));
	}

	/**
	 * Nastaví objednávce produkt, který reprezentuje způsob dopravy/platby
	 * @param $deliveryProductId
	 */
	public function setDeliveryProduct($deliveryProductId)
	{
		Db::querySingle('
			UPDATE e_order
			SET delivery_product_id = ?
			WHERE e_order_id = ?
		', array($deliveryProductId, $this->getOrderId(false)));
	}

	/**
	 * Dokončí objednávku
	 * @param int $deliveryProductId ID produktu, který představuje způsob dopravy/platby
	 * @throws UserException
	 */
	public function completeOrder($deliveryProductId) {
		$orderId = $this->getOrderId(false);
		$this->addProducts($deliveryProductId, 1, null, true);
		Db::query('
			UPDATE e_order
			JOIN accounting_settings ON (valid_from <= NOW() AND valid_to >= NOW())
			JOIN person ON (person_id = accounting_settings.seller_id)
			SET
				e_order.seller_id = person.person_id,
				seller_address_id = person.address_id,
				seller_person_detail_id = person.person_detail_id,
				seller_bank_account_id = person.bank_account_id,
				e_order.accountant_detail_id = accounting_settings.accountant_detail_id,
				e_order.state = "completed"
			WHERE e_order_id = ?
		', array($orderId));
		$this->sendOrderEmail($orderId, 'Dobrý den, velmi děkujeme za Vaši objednávku, která nyní čeká na vyřízení. Níže je její rekapitulace.');
	}

	/**
	 * Odešle objednávku emailem spolu se zprávou
	 * @param int $orderId ID objednávky
	 * @param string $message Zpráva
	 * @throws UserException
	 */
	public function sendOrderEmail($orderId, $message)
	{
		$email = Db::querySingle('
			SELECT email
			FROM e_order
			JOIN person_detail ON (buyer_person_detail_id = person_detail_id)
			WHERE e_order_id = ?
		', array($orderId));
		if (!$email)
			throw new UserException('Objednávka nebyla nalezena.');
		// Získání obsahu emailu z šablony kontroleru
		$orderController = new OrderController();
		$orderController->email($orderId, $message);
		ob_start();
		$orderController->renderView();
		$emailContent = ob_get_contents();
		ob_end_clean();

		$emailSender = new EmailSender();
		$emailSender->send($email, 'Změna stavu objednávky', $emailContent, Settings::$email);
	}

	/**
	 * Vrátí 30 posledních objednávek, které se dostaly dále, než k vytvoření
	 * @return array Objednávky
	 */
	public function getOrders()
	{
		return Db::queryAll('
			SELECT e_order_id, company_name, first_name, last_name, price, created, number, buyer_id, state
			FROM e_order
			LEFT JOIN (
				SELECT e_order_id, SUM(price * quantity) as price
				FROM product_e_order
				JOIN product USING (product_id)
				GROUP BY e_order_id
			) as prices USING (e_order_id)
			LEFT JOIN person ON (buyer_id = person_id)
			LEFT JOIN person_detail USING (person_detail_id)
			WHERE state != "created"
			ORDER BY e_order_id DESC LIMIT 30
		');
	}

	/**
	 * Vrátí stavy objednávky pro formulář
	 * @return array Stavy objednávky pro formulář
	 */
	public function getOrderStates()
	{
		return array(
			'Nová' => self::STATE_COMPLETED,
			'Přijatá' => self::STATE_ACCEPTED,
			'Expedovaná' => self::STATE_SENT,
			'Pozastavená' => self::STATE_SUSPENDED,
			'Zrušená' => self::STATE_CANCELED,
		);
	}

	/**
	 * Vrátí datum splatnosti faktury na základě data vystavení
	 * @param string $issued Datum vystavení faktury ve formátu yyyy-mm-dd
	 * @return string Datum splatnosti jako datum o 14 dní později než datum vystavení
	 */
	public function getDueDate($issued)
	{
		$dueDate = new DateTime($issued);
		return $dueDate->modify("+ 14 DAYS")->format(DateUtils::DB_DATE_FORMAT);
	}

	/**
	 * Vrátí číslo pro další fakturu
	 * @return string Číslo pro další fakturu
	 */
	public function getNextInvoiceNumber()
	{
		$lastNumber = Db::querySingle('
			SELECT max(number)
			FROM e_order
			JOIN accounting_settings ON (valid_from <= NOW() AND valid_to >= NOW())
			WHERE e_order.seller_id = accounting_settings.seller_id
		');
		$datePrefix = date('Ymd');
		// Poslední faktura byla vydána v dnešní den, zvýšíme číslo
		if (StringUtils::startsWith($lastNumber, $datePrefix))
			return $lastNumber + 1;
		return $datePrefix . '01';
	}

	/**
	 * Nastaví objednávce stav
	 * @param int $orderId ID objednávky
	 * @param string $state Stav objednávky
	 * @return mixed|string Číslo nově vygenerované faktury v případě, že bylo zboží odesláno
	 * @throws UserException
	 */
	public function setState($orderId, $state)
	{
		$order = array(
			'state' => $state,
		);
		// Jakmile expedujeme objednávku, nastaví se údaje pro fakturu
		if ($state == self::STATE_SENT)
		{
			$order['issued'] = DateUtils::dbNow();
			$order['due_date'] = $this->getDueDate($order['issued']);
			$order['number'] = $this->getNextInvoiceNumber();
			// Snížení počtu položek na skladě
			Db::query('
				UPDATE product
				JOIN product_e_order USING (product_id)
				SET stock=stock - quantity
				WHERE e_order_id = ?
			', array($orderId));
		}
		Db::update('e_order', $order, 'WHERE e_order_id = ?', array($orderId));
		// Odeslání emailu
		$stateTitle = array_search($state, $this->getOrderStates());
		$this->sendOrderEmail($orderId, 'Stav Vaší objednávky byl změněn na - ' . $stateTitle);
		if ($state == self::STATE_SENT)
			return $order['number'];
		return '';
	}

	/**
	 * Aktualizuje objednávku
	 * @param array $order Objednávka
	 */
	public function updateOrder($order)
	{
		Db::update('e_order', $order, 'WHERE e_order_id = ?', array($order['e_order_id']));
	}

	/**
	 * Vrátí data o produktu pro správu objednávky
	 * @param int $productId ID produktu
	 * @param int $quantity Počet kusů
	 * @param int $vat Sazba DPH v %
	 * @return array Data o produktu pro správu objednávky
	 */
	public function getProductManagementData($productId, $quantity, $vat)
	{
		$productManager = new ProductManager();
		$product = $productManager->getProductFromId($productId);
		return array(
			'product_id' => $productId,
			'title' => $product['title'],
			'quantity' => $quantity,
			'price' => FormatHelper::currency($product['price']),
			'vat' => $vat . '%',
			'price_total' => FormatHelper::currency($product['price'] * $quantity),
			'price_total_vat' => ProductHelper::priceWithVat($product['price'] * $quantity, $vat),
		);
	}

	/**
	 * Vrátí souhrnná data objednávky
	 * @param int $orderId ID objednávky
	 * @param int $vat Sazba DPH v %
	 * @return array Souhrnná data objednávky
	 */
	public function getOrderSummaryData($orderId, $vat)
	{
		$summary = $this->getOrderSummary($orderId);
		return array(
			'order_total' => FormatHelper::currency(round($summary['price'])),
			'order_vat' => ProductHelper::vatFromPrice($summary['price'], $vat),
			'order_total_vat' => ProductHelper::priceWithVat($summary['price'], $vat, true),
		);
	}

}
