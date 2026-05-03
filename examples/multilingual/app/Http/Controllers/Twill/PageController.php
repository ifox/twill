<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;

class PageController extends BaseModuleController
{
    protected function setUpController(): void
    {
        $this->setModuleName('pages');
        $this->enableReorder();
    }

    public function getForm(TwillModelContract $model): Form
    {
        return Form::make([
            Input::make()
                ->name('title')
                ->label('Title')
                ->translatable()
                ->required(),
            Wysiwyg::make()
                ->name('description')
                ->label('Description')
                ->translatable(),
            Wysiwyg::make()
                ->name('content')
                ->label('Content')
                ->translatable(),
            Medias::make()
                ->name('cover')
                ->label('Cover Image'),
            BlockEditor::make(),
        ]);
    }
}
