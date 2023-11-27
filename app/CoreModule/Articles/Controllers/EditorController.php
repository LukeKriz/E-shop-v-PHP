<?php
namespace App\CoreModule\Articles\Controllers;



use App\CoreModule\Articles\Models\ArticleManager;
use App\CoreModule\System\Controllers\Controller;
use ItNetwork\Forms\Form;
use ItNetwork\UserException;

/**
 * Zpracovává požadavky na editaci článků
 */
class EditorController extends Controller
{

	/**
	 * Editace článku
	 * @param string $url Unikátní URL článku pro editování
	 * @Action
	 */
    public function index($url = '')
    {
		// Editor smí používat jen administrátoři
		$this->authUser(true);
		// Vytvoření instance modelu
		$articleManager = new ArticleManager();

		$form = $this->getEditorForm();
		$this->data['form'] = $form;

		// Je odeslán formulář
		if ($form->isPostBack())
		{
			// Uložení článku do DB
			try
			{
				$article = $form->getData();
				$articleManager->saveArticle($article);
				$this->addMessage('Článek byl úspěšně uložen.', self::MSG_SUCCESS);
				$this->redirect($article['url']);
			}
			catch (UserException $ex)
			{
				$this->addMessage($ex->getMessage(), self::MSG_ERROR);
			}
		}
		// Je zadané URL článku k editaci
		else if ($url)
		{
			$loadedArticle = $articleManager->getArticle($url);
			if ($loadedArticle)
				$form->setData($loadedArticle);
			else
				$this->addMessage('Článek nebyl nalezen', self::MSG_ERROR);
		}

		$this->view = 'index';
    }

	/**
	 * Vrátí formulář pro editor článků
	 * @return Form Formulář pro editor článků
	 */
	private function getEditorForm() {
		$form = new Form('editor');
		$form->addHiddenBox('article_id');
		$form->addTextBox('title', 'Titulek', true);
		$form->addTextBox('url', 'URL', true);
		$form->addTextBox('description', 'Popisek', true);
		$form->addTextBox('controller', 'Kontroler');
		$form->addTextArea('content', '');
		$form->addButton('submit', 'Uložit článek');
		return $form;
	}
}
