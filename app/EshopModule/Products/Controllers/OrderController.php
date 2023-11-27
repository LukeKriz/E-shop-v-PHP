<?php



namespace App\EshopModule\Products\Controllers;

use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use App\EshopModule\Accounting\Models\SettingsManager;
use App\EshopModule\Persons\Models\PersonManager;
use App\EshopModule\Products\Models\CategoryManager;
use App\EshopModule\Products\Models\OrderManager;
use App\EshopModule\Products\Models\ProductManager;
use App\CoreModule\System\Controllers\Controller;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;
use ItNetwork\Utility\ArrayUtils;
use ItNetwork\Utility\DateUtils;

/**
 * Zpracovává požadavky na objednávku
 */
class OrderController extends Controller
{

	/**
	 * Košík
	 * @Action
	 */
	public function index()
	{
		$orderManager = new OrderManager();
		$personManager = new PersonManager();
		$products = $orderManager->getProducts();

		$form = $this->getManagementForm($products);

		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				// Přepočítání položek v košíku
				$formData = $form->getData();
				$orderManager->updateCart($formData);
				$sentButton = $form->getSentButton();
				if ($sentButton == 'continue')
					$this->redirect('uvod');
				elseif ($sentButton == 'checkout')
				{
					if (UserManager::$user)
					{
						$orderManager->setPerson($personManager->getPersonId(UserManager::$user['user_id']));
						$this->redirect('objednavka/payment');
					}
					else
						$this->redirect('osoby/register');
				}
				else
					$this->redirect('objednavka');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage());
			}
		}

		$settingsManager = new SettingsManager();
		$this->data['settings'] = $settingsManager->getSettings(DateUtils::dbNow());
		$this->data['form'] = $form;
		$this->data['products'] = $products;
		$this->data['logged'] = (bool)UserManager::$user;
		$this->data['summary'] = $orderManager->getOrderSummary();
		$this->view = 'index';
	}

	/**
	 * Výběr platební metody
	 * @Action
	 */
	public function payment()
	{
		ArticleManager::$article['title'] = 'Platba';
		$form = $this->getPaymentForm();
		$this->view = 'payment';
		if ($form->isPostBack())
		{
			try
			{
				$orderManager = new OrderManager();
				$data = $form->getData();
				$orderManager->setDeliveryProduct($data['delivery_product_id']);
				$this->redirect('objednavka/summary');
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage());
			}
		}
		$this->data['logged'] = (bool)UserManager::$user;
		$this->data['form'] = $form;
	}

	/**
	 * Rekapitulace objednávky
	 * @param string $buy Pokud je parametr vyplněný, objednávka se potvrdí
	 * @Action
	 */
	public function summary($buy = '')
	{
		$orderManager = new OrderManager();
		$personManager = new PersonManager();
		$productManager = new ProductManager();
		$settingsManager = new SettingsManager();
		// Produkty
		$products = $orderManager->getProducts();
		if (!$products)
		{
			$this->addMessage('Máte prázdný košík', self::MSG_ERROR);
			$this->redirect('uvod');
		}
		$this->data['products'] = $products;
		// Objednávka
		$order = $orderManager->getOrder();
		$deliveryProduct = $productManager->getProductFromId($order['delivery_product_id']);
		if (!$deliveryProduct)
		{
			$this->addMessage('Vyberte prosím způsob dopravy.', self::MSG_ERROR);
			$this->redirect('objednavka/payment');
		}
		$deliveryProduct['quantity'] = 1;
		$this->data['products'][] = $deliveryProduct;
		// Osoba
		if (UserManager::$user) // Reset adresy pro případ že ji změnil
		{
			$personId = $personManager->getPersonId(UserManager::$user['user_id']);
			$orderManager->setPerson($personId);
			$order['buyer_id'] = $personId;
		}
		$person = $personManager->getPerson($order['buyer_id']);
		if (!$person)
		{
			$this->addMessage('Vyplňte prosím údaje zákazníka.', self::MSG_ERROR);
			$this->redirect('osoby/register');
		}
		$this->data['person'] = $person;
		$this->data['settings'] = $settingsManager->getSettings(DateUtils::dbNow());
		$this->data['deliveryAddress'] = ArrayUtils::removePrefix('delivery_', ArrayUtils::filterKeysPrefix('delivery_', $person));
		$this->view = 'summary';
		$summary = $orderManager->getOrderSummary();
		$summary['price'] += $deliveryProduct['price'];
		$summary['products'] += 1;
		$this->data['summary'] = $summary;
		$this->data['logged'] = (bool)UserManager::$user;
		if ($buy)
		{
			$orderManager->completeOrder($order['delivery_product_id']);
			$this->addMessage('Děkujeme za Váš nákup, na email Vám bylo odesláno potvrzení objednávky.', self::MSG_SUCCESS);
			$this->redirect('uvod');
		}
		$this->data['email'] = false; // Aby se vyrenderoval pohled jako pro prohlížeč
	}

	/**
	 * Akce vyplní šablonu pro rekapitulaci objednávky pro odeslání emailem
	 * @param $orderId
	 * @param $message
	 * Akce je spouštěna OrderManagerem
	 */
	public function email($orderId, $message)
	{
		$orderManager = new OrderManager();
		$personManager = new PersonManager();
		$productManager = new ProductManager();
		$settingsManager = new SettingsManager();
		// Produkty
		$products = $orderManager->getProducts($orderId);
		$this->data['products'] = $products;
		// Objednávka
		$order = $orderManager->getOrder($orderId);
		// Osoba
		$person = $personManager->getPerson($order['buyer_id']);
		$this->data['settings'] = $settingsManager->getSettings(DateUtils::dbNow());
		$this->data['person'] = $person;
		$this->data['deliveryAddress'] = ArrayUtils::removePrefix('delivery_', ArrayUtils::filterKeysPrefix('delivery_', $person));
		$this->data['summary'] = $orderManager->getOrderSummary($orderId);
		$this->data['email'] = true;
		$this->data['message'] = $message;
		$this->view = 'summary';
	}

	/**
	 * Vrátí formulář pro výběr platební metody
	 * @return Form Formulář pro výběr platební metody
	 */
	public function getPaymentForm()
	{
		$categoryManager = new CategoryManager();
		$settingsManager = new SettingsManager();
		$form = new Form('payment');
		$form->addRadioGroup('delivery_product_id', '')
			->setValues($categoryManager->getPaymentMethods($settingsManager->getSettings(DateUtils::dbNow())));
		$form->addButton('submit', 'Potvrdit');
		return $form;
	}

	/**
	 * Vrátí formulář pro správu košíku
	 * @param array $products Produkty v košíku
	 * @return Form Formulář pro správu košíku
	 */
	public function getManagementForm($products)
	{
		$form = new Form('cart-management');

		foreach ($products as $product)
		{
			$form->addNumberBox('quantity_' . $product['product_id'], '', true)
				->setText($product['quantity']);
		}

		$form->addButton('refresh', 'Přepočítat');
		$form->addButton('continue', 'Pokračovat v nákupu');
		$form->addButton('checkout', 'K pokladně');
		return $form;
	}

} 
