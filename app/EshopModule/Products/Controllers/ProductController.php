<?php


namespace App\EshopModule\Products\Controllers;

use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\Users\Models\UserManager;
use App\EshopModule\Accounting\Models\SettingsManager;
use App\EshopModule\Products\Models\CategoryManager;
use App\EshopModule\Products\Models\OrderManager;
use App\EshopModule\Products\Models\ProductManager;
use App\CoreModule\System\Controllers\Controller;
use App\EshopModule\Products\Models\ReviewManager;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;
use ItNetwork\Utility\DateUtils;

class ProductController extends Controller
{

	/**
	 * Zpracování formuláře přidání do košíku
	 * @param string $url Url adresa produktu
	 */
	private function processCartForm($url)
	{
		if (isset($_POST['add_to_cart']) && isset($_POST['product_id']) && isset($_POST['quantity']))
		{
			$orderManager = new OrderManager();
			try
			{
				$orderManager->addProducts($_POST['product_id'], $_POST['quantity']);
				$this->redirect($url);
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage());
			}
		}
	}

	/**
	 * Zpracování formuláře pro naskladnění
	 * @param string $url Url adresa produktu
	 */
	private function processStockForm($url)
	{
		if (isset($_POST['add_to_stock']) && isset($_POST['product_id']) && isset($_POST['quantity']))
		{
			$this->authUser(true);
			$productManager = new ProductManager();
			$productManager->addToStock($_POST['product_id'], $_POST['quantity']);
			$this->addMessage('Počet produktů na skladě byl změněn.', self::MSG_SUCCESS);
			$this->redirect($url);
		}
	}

	/**
	 * Detail produktu
	 * @param string $url Url produktu
	 * @Action
	 */
	public function detail($url)
	{
		$this->view = 'detail';
		$productManager = new ProductManager();
		$reviewManager = new ReviewManager();
		$product = $productManager->getProduct($url);
		if (!$product)
			$this->redirect('chyba');

		$form = $this->getReviewForm($product['product_id']);

		// Zpracování formuláře s recenzí
		if ($form->isPostBack())
		{
			$this->authUser();
			try
			{
				$review = $form->getData();
				$reviewManager->addReview($review);
				$this->addMessage('Recenze byla úspěšně přidána, děkujeme.', self::MSG_SUCCESS);
				$this->redirect('produkty/detail/' . $url);
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}

		// Zpracování formuláře pro přidání do košíku a naskladnění
		$this->processCartForm('produkty/detail/' . $url);
		$this->processStockForm('produkty/detail/' . $url);

		ArticleManager::$article['title'] = $product['title'];
		ArticleManager::$article['description'] = $product['short_description'];
		$settingsManager = new SettingsManager();
		$this->data['settings'] = $settingsManager->getSettings(DateUtils::dbNow());
		$this->data['product'] = $product;
		$this->data['form'] = $form;
		$this->data['reviews'] = $reviewManager->getReviews($product['product_id']);
		$this->data['admin'] = UserManager::$user && UserManager::$user['admin'];
		$this->data['logged'] = (bool)UserManager::$user;
	}

	/**
	 * Výpis produktů
	 * Parametry metody jsou kategorie, zpracováván je pouze poslední parametr. Pokud není zadaný, vypíše se vše
	 * @Action
	 */
	public function index()
	{
		$productManager = new ProductManager();
		$categoryManager = new CategoryManager();
		$params = func_get_args();

		$category = $params ? $params[count($params) - 1] : null; // Vezme poslední část URL, URL jsou unikátní
		ArticleManager::$article['title'] = $categoryManager->getTitle($category) ?: 'Všechny produkty';
	
		$page = isset($_GET['page']) ? $_GET['page'] : 1;
		$phrase = isset($_GET['phrase']) ? $_GET['phrase'] : '';


		
		
		$admin = UserManager::$user && UserManager::$user['admin'];
		$this->data['categories'] = $categoryManager->getCategories($admin);

		


		$form = $this->getFilterForm($phrase);
		$loaded = false;
		if ($form->isPostBack())
		{
			try
			{
				$formData = $form->getData();
				$products = $productManager->getProducts($category, $page, $phrase, $formData['order_by'], $formData['start_price'], $formData['end_price'], $formData['in_stock']);
				$pages = $productManager->getProductPagesCount($category, $phrase, $formData['order_by'], $formData['start_price'], $formData['end_price'], $formData['in_stock']);
				$formData['form-name'] = 'filter-form';
				unset($formData['phrase']);
				$searchQuery = http_build_query($formData);
				$loaded = true;
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage());
			}
		}
		if (!$loaded)
		{
			$products = $productManager->getProducts($category, $page, $phrase);
			$pages = $productManager->getProductPagesCount($category, $phrase);
			$searchQuery = '';
		}

		// Zpracování formuláře pro přidání do košíku a naskladnění
		$this->processCartForm('produkty/index/' . implode('/', $params));
		$this->processStockForm('produkty/index/' . implode('/', $params));

		$settingsManager = new SettingsManager();
		$this->data['products'] = $products;
		$this->data['pages'] = $pages;
		$this->data['page'] = $page;
		$this->data['settings'] = $settingsManager->getSettings(DateUtils::dbNow());
		$this->view = 'index';
		$this->data['form'] = $form;
		$this->data['admin'] = UserManager::$user && UserManager::$user['admin'];
		$this->data['paginationUrl'] = 'produkty/index/' . implode('/', $params) . '?page={page}&phrase=' . $phrase . '&' . $searchQuery;
	}

	/**
	 * Odstranění produktu
	 * @Action
	 * @param string $productId ID produktu
	 */
	public function delete($productId)
	{
		$this->authUser(true);
		$productManager = new ProductManager();
		$categoryManager = new CategoryManager();
		$categoryUrl = $categoryManager->getProductCategory($productId);
		$productManager->cleanProduct($productId, true);
		$this->addMessage('Produkt byl úspěšně odstraněn.');
		$this->redirect('produkty/index/' . $categoryUrl); // Přesměruje do kategorie
	}

	/**
	 * Správa produktů
	 * @param string $url Url adresa produktu, který editujeme. Pokud není zadána, přidá se produktu jako nový
	 * @Action
	 */
	public function manage($url = '')
	{
		$this->authUser(true);
		$productManager = new ProductManager();
		$categoryManager = new CategoryManager();
		$form = $this->getProductForm();
		ArticleManager::$article['title'] = $url ? 'Editace produktu' : 'Nový produkt';

		if ($url) // Je zadána URL, editujeme produkt
		{
			$product = $productManager->getProduct($url);
			if (!$product)
				$this->redirect('chyba');
			$product['categories'] = $categoryManager->getProductCategories($product['product_id']);
			$form->setData($product);
		}
		else
			$product = null;
		$this->data['product'] = $product;
		$this->data['form'] = $form;
		$this->view = 'manage';

		// Zpracování formuláře
		if ($form->isPostBack())
		{
			try
			{
				$data = $form->getData();
				// Pokud není stará cena nastavená (není vyžadovaná), nastavíme ji na null
				$data['old_price'] = $data['old_price'] ?: null;
				$categories = $data['categories'];
				$images = $data['images'];
				unset($data['categories']);
				unset($data['images']);
				$imagesCount = $product ? $product['images_count'] : 0;
				$productId = $productManager->saveProduct($data);
				$categoryManager->updateProductCategories($productId, $categories);
				try
				{
					$productManager->saveProductImages($productId, $images, $data['product_id'], $imagesCount);
				}
				catch (UserException $ex)
				{
					$this->addMessage($ex->getMessage(), self::MSG_ERROR);
				}
				$this->addMessage('Produkt byl úspěšně uložen.', self::MSG_SUCCESS);
				$this->redirect('produkty/detail/' . $data['url']);
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage());
			}
		}
	}

	/**
	 * Odstranění náhledu produktu
	 * @param int $productId ID produktu
	 * @param int $imageIndex Index náhledu (0 je první)
	 * @ApiAction
	 */
	public function deleteImage($productId, $imageIndex)
	{
		$this->authUser(true);
		$productManager = new ProductManager();
		$productManager->removeProductImage($productId, $imageIndex);
	}

	/**
	 * Vrátí formulář pro správu produktu
	 * @return Form Formulář pro správu produktu
	 */
	public function getProductForm() {
		$categoryManager = new CategoryManager();
		$form = new Form('product');
		$form->addHiddenBox('product_id');
		$form->addTextBox('code', 'Kód produktu', true);
		$form->addTextBox('url', 'URL adresa', true)->addPatternRule('[A-Za-z0-9\\-]+');
		$form->addTextBox('title', 'Název produktu', true);
		$form->addTextBox('short_description', 'Text-náhled', true);
		$form->addTextArea('description', 'Tekt-Produkt');
		$form->addFileBox('images', 'Obrázky', false, 'image/*', true);
		$form->addListBox('categories', 'Kategorie', true, true)
			->setValues(
				$categoryManager->getCategoryLeafs()
			);
		$form->addTextBox('price', 'Cena', true)->addPatternRule('[-+]?[0-9]*.?[0-9]?');
		$form->addTextBox('old_price', 'Cena před slevou', false)->addPatternRule('[-+]?[0-9]*.?[0-9]?');
		$form->addButton('submit', 'Odeslat');
		return $form;
	}

	/**
	 * Vrátí formulář pro filtrování produktů
	 * @param string $phrase Vyhledávaná fráze
	 * @return Form Formulář pro filtrování produktů
	 */
	public function getFilterForm($phrase)
	{
		$form = new Form('filter-form', Form::METHOD_GET, true);
		$form->addHiddenBox('phrase', $phrase);
		$form->addComboBox('order_by', 'Řadit podle:', true)
			->setValues(array(
				'Hodnocení' => 'rating',
				'Nejnižší ceny' => 'lowest_price',
				'Nejvyšší ceny' => 'highest_price',
				'Nejnovější' => 'newest',
			));
		$form->addNumberBox('start_price', 'Cena od:');
		$form->addNumberBox('end_price', 'Cena do:');
		$form->addCheckBox('in_stock', 'Je skladem');
		$form->addButton('filter', 'Filtrovat');
		return $form;
	}

	/**
	 * Vrátí formulář pro hodnocení produktu
	 * @param int $productId ID produktu
	 * @return Form Formulář pro hodnocení produktu
	 */
	public function getReviewForm($productId)
	{
		$form = new Form('review-form');
		$form->addHiddenBox('rating');
		$form->addTextArea('content', 'Jak jste spokojeni?', true);
		$form->addHiddenBox('product_id', $productId);
		$form->addButton('send', 'Odeslat');
		return $form;
	}

}
