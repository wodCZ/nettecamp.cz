<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class HomepagePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @var \MailManager
	 * @inject
	 */
	public $mailManager;

	public $formSendSuccess = FALSE;


	public function renderDefault($sended = FALSE)
	{
		if ($sended) {
			$this->formSendSuccess = TRUE;
		}

		$this->template->formSend = $this->formSendSuccess;
	}


	public function createComponentRegistrationForm()
	{
		$form = new Form();

		$form->addHidden('email');
		$form->addText('name', "Jméno:")->setRequired('Vyplň jméno');
		$form->addText('liame', "Email:")->setRequired('Vyplň email');
		$form->addText('phone', "Telefon:")->setRequired('Vyplň telefon');

		$time = array(
			'ctvrte' => "přijedu ve čtvrtek",
			'patek' => 'přijedu v pátek',
		);

		$form->addSelect('from', 'Varianta:', $time);

		$form->addText('nickname', "Přezdívka:");

		$levels = array(
			'rookie' => "Rookie",
			'normal' => "Normal",
			'pro' => 'Pro',
			'allstart' => 'Allstar',
		);

		$form->addSelect('level', 'Schopnosti:', $levels)->setPrompt('Nette skills?')->setRequired('Zvolt svojí Nette dovednost');

		$form->addTextArea('note', 'Poznámka');

		$form->addSubmit('actionSend', 'Save');

		$form->onSuccess[] = array($this, 'registrationFormSubmitted');

		return $form;
	}


	public function registrationFormSubmitted(Form $form, $values)
	{
		if ($values->email == '') {

			$values->email = $values->liame;

			$template = $this->createTemplate();

			$status = $this->mailManager->sendOrderEmail($values, 'cs', $template);

			if ($status) {
				$this->formSendSuccess = TRUE;

				if ($this->isAjax()) {
					$this->redrawControl('order');
				} else {
					$this->redirect('this', array('sended' => TRUE));
				}
			} else {
				$this->flashMessage('Stala se tu chyba', 'error');
			}
		} else {
			$this->flashMessage('Stala se tu chyba', 'error');

		}
	}

}