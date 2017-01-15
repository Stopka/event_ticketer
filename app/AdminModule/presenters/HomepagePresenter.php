<?php

namespace App\AdminModule\Presenters;

use App\Model;


class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
