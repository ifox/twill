<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;

class AuthorController extends BaseModuleController
{
    protected function setUpController(): void
    {
        $this->setModuleName('authors');
    }

    public function getForm(TwillModelContract $model): Form
    {
        return Form::make([
            Input::make()
                ->name('name')
                ->label('Name')
                ->translatable()
                ->required(),
            Wysiwyg::make()
                ->name('bio')
                ->label('Bio')
                ->translatable(),
            Medias::make()
                ->name('avatar')
                ->label('Avatar'),
        ]);
    }
}
