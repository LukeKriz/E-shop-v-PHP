<?php


namespace App\EshopModule\Products\Controllers;

use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\System\Controllers\Controller;
use App\EshopModule\Accounting\Models\SettingsManager;
use App\EshopModule\Persons\Controllers\PersonController;
use App\EshopModule\Persons\Models\PersonManager;
use App\EshopModule\Products\Models\OrderManager;
use App\EshopModule\Products\Models\ProductManager;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;
use ItNetwork\Utility\ArrayUtils;
use ItNetwork\Utility\DateUtils;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Settings;

/**
 * Zpracovává požadavky na administraci objednávek
 */
class OrderManagementController extends Controller
{

	/**
	 * Výpis objednávek
	 * @Action
	 */
	public function index()
	{
		$this->authUser(true);
		$orderManager = new OrderManager();
		$this->data['orders'] = $orderManager->getOrders();
		$this->view = 'index';
	}

	/**
	 * Možné stavy objednávky
	 * @ApiAction
	 */
	public function orderStates()
	{
		$orderManager = new OrderManager();
		header('Content-Type: text/json; charset=utf-8');
		echo json_encode($orderManager->getOrderStates(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Změna stavu objednávky
	 * @param int $orderId ID objednávky
	 * @param string $state Stav objednávky
	 * @ApiAction
	 */
	public function setState($orderId, $state)
	{
		$this->authUser(true);
		$orderManager = new OrderManager();
		try
		{
			echo $orderManager->setState($orderId, $state);
		}
		catch (UserException $ex)
		{
			header('HTTP/1.1 400 Bad request');
			echo $ex->getMessage();
		}
	}

	/**
	 * Vyrenderuje PDF faktury
	 * @param int $orderId ID objednávky
	 * @ApiAction
	 * @throws UserException
	 * @throws MpdfException
	 */
	public function pdf($orderId)
	{
		$this->authUser(true);

		$this->view = 'detail';
		$personManager = new PersonManager();
		$orderManager = new OrderManager();
		$settingsManager = new SettingsManager();
		$order = $orderManager->getOrder($orderId);
		if (!$order)
		{
			$this->addMessage('Objednávka nebyla nalezena.', self::MSG_ERROR);
			$this->redirect('sprava-objednavek');
		}

		$order['taxable_supply_date'] = $order['issued'];
		$this->data['order'] = $order;
		$this->data['buyer'] = $personManager->getCustomPerson($order['buyer_id'], $order['buyer_person_detail_id'], $order['buyer_address_id'], null, null);
		$this->data['seller'] = $personManager->getCustomPerson($order['seller_id'], $order['seller_person_detail_id'], $order['seller_address_id'], null, $order['seller_bank_account_id']);
		$settings = $settingsManager->getSettings($order['issued']);
		$this->data['settings'] = $settings;
		$this->data['accountant'] = $personManager->getPersonDetail($order['accountant_detail_id']);
		$this->data['products'] = $orderManager->getProducts($orderId);
		$this->data['summary'] = $orderManager->getOrderSummary($orderId);

		ob_start();
		$this->renderView();
		$html = ob_get_contents();
		ob_end_clean();

		$mPdf = new mPDF();
		$mPdf->WriteHTML(file_get_contents('../public/css/invoice.css'), 1);
		$mPdf->WriteHTML($html, 2);

		$mPdf->Output('Faktura_' . $order['number'] . '.pdf', 'D');
	}

	/**
	 * Správa objednávky
	 * @param int $orderId ID objednávky
	 * @Action
	 * @throws UserException
	 */
	public function manage($orderId)
	{
		$this->authUser(true);

		$personManager = new PersonManager();
		$orderManager = new OrderManager();
		$this->view = 'manage';

		$settingsManager = new SettingsManager();
		$order = $orderManager->getOrder($orderId);
		if (!$order)
		{
			$this->addMessage('Objednávka nebyla nalezena.', self::MSG_ERROR);
			$this->redirect('sprava-objednavek');
		}

		ArticleManager::$article['title'] = 'Objednávka č. ' . $order['number'];
		$order['taxable_supply_date'] = $order['issued'];
		$addProductForm = $this->getAddProductForm();
		$addProductForm->setData($order);
		$this->data['addProductForm'] = $addProductForm;
		$orderForm = $this->getOrderForm();
		$orderForm->setData($order);
		$this->data['form'] = $orderForm;
		$this->data['order'] = $order;
		$buyer = $personManager->getCustomPerson($order['buyer_id'], $order['buyer_person_detail_id'], $order['buyer_address_id'], $order['buyer_delivery_address_id'], null);
		$this->data['buyer'] = $buyer;
		$this->data['seller'] = $personManager->getCustomPerson($order['seller_id'], $order['seller_person_detail_id'], $order['seller_address_id'], null, $order['seller_bank_account_id']);
		$this->data['buyerDeliveryAddress'] = ArrayUtils::removePrefix('delivery_', ArrayUtils::filterKeysPrefix('delivery_', $buyer));
		$settings = $settingsManager->getSettings($order['created']);
		$this->data['settings'] = $settings;
		$this->data['accountant'] = $personManager->getPersonDetail($settings['accountant_detail_id']);
		$this->data['products'] = $orderManager->getProducts($orderId);
		$this->data['summary'] = $orderManager->getOrderSummary($orderId);

		// Zpracování formuláře
		if ($orderForm->isPostBack())
		{
			try
			{
				$orderManager->updateOrder($orderForm->getData());
				$this->addMessage('Objednávka byla uložena.', self::MSG_SUCCESS);
				$this->redirect('sprava-objednavek/manage/' . $orderId);
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
	}

	/**
	 * Vrátí formulář k editaci objednávky
	 * @return Form Formulář k editaci objednávky
	 */
	public function getOrderForm()
	{
		$form = new Form('order_form');
		$form->addHiddenBox('e_order_id', '', true);
		$form->addDatePicker('issued', '', true);
		$form->addDatePicker('due_date', '', true);
		$form->addButton('change', 'Změnit');
		return $form;
	}

	/**
	 * Vrátí formulář k přidání produktu
	 * @return Form Formulář k přidání produktu
	 */
	public function getAddProductForm()
	{
		$productManager = new ProductManager();
		$form = new Form('add-product-form', Form::METHOD_POST, true);
		$form->addHiddenBox('e_order_id', '', true);
		$form->addComboBox('product_id', 'Produkt', true)
			->setValues($productManager->getProductList());
		$form->addNumberBox('quantity', 'Počet', true);
		$form->addButton('add', 'Přidat');
		return $form;
	}

	/**
	 * Přidání produktu do objednávky
	 * @ApiAction
	 */
	public function addProduct()
	{
		$this->authUser(true);
		$orderManager = new OrderManager();
		$settingsManager = new SettingsManager();
		$form = $this->getAddProductForm();
		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				$formData = $form->getData();
				$orderManager->addProducts($formData['product_id'], $formData['quantity'], $formData['e_order_id']);
				$settings = $settingsManager->getSettings(DateUtils::dbNow());
				$product = $orderManager->getProductManagementData($formData['product_id'], $formData['quantity'], $settings['vat']);
				$summary = $orderManager->getOrderSummaryData($formData['e_order_id'], $settings['vat']);
				header('Content-Type: application/json');
				echo json_encode(array(
					'product' => $product,
					'summary' => $summary,
				), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			}
			catch (UserException $ex)
			{
				header('HTTP/1.1 400 Bad request');
				echo $ex->getMessage();
			}
		}
	}

	/**
	 * Editace produktu v objednávce
	 * @param int $orderId ID objednávky
	 * @param int $productId ID produktu
	 * @param int $quantity Počet. Pokud je 0, produkt je z objednávky odstraněn.
	 * @ApiAction
	 */
	public function editProduct($orderId, $productId, $quantity)
	{
		$this->authUser(true);
		$orderManager = new OrderManager();
		$settingsManager = new SettingsManager();
		try
		{
			$orderManager->updateProductInOrder($productId, $quantity, $orderId);
			$settings = $settingsManager->getSettings(DateUtils::dbNow());
			$product = $orderManager->getProductManagementData($productId, $quantity, $settings['vat']);
			$summary = $orderManager->getOrderSummaryData($orderId, $settings['vat']);
			header('Content-Type: application/json');
			echo json_encode(array(
				'product' => $product,
				'summary' => $summary,
			), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		}
		catch (UserException $ex)
		{
			header('HTTP/1.1 400 Bad request');
			echo $ex->getMessage();
		}
	}

	/**
	 * Získej detail zákazníka
	 * @param int $orderId ID objednávky
	 * @ApiAction
	 */
	public function buyerDetail($orderId)
	{
		$this->authUser(true);
		$personManager = new PersonManager();
		$orderManager = new OrderManager();
		$order = $orderManager->getOrder($orderId);
		if (!$order)
			die('Objednávka nebyla nalezena.');
		$buyer = $personManager->getCustomPerson(null, $order['buyer_person_detail_id'], $order['buyer_address_id'], $order['buyer_delivery_address_id'], null);
		if (!$buyer)
			die('Osoba nebyla nalezena.');
		$this->data['order'] = $order;
		$this->data['buyer'] = $buyer;
		$this->data['buyerDeliveryAddress'] = ArrayUtils::removePrefix('delivery_', ArrayUtils::filterKeysPrefix('delivery_', $buyer));
		$this->view = 'buyer_detail';
	}

	/**
	 * Editace osoby v objednávce
	 * @param int $orderId ID objednávky
	 * @ApiAction
	 */
	public function editPerson($orderId)
	{
		$this->authUser(true);

		$personController = new PersonController();
		$personController->editOrderPerson($orderId);

		$this->data['domain'] = Settings::$domain;
		$this->data['controller'] = $personController;
		$this->view = 'person';
	}

}
