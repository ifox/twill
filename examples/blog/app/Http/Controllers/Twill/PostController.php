<?php

namespace App\Http\Controllers\Twill;

use A17\Twill\Http\Controllers\Admin\ModuleController as BaseModuleController;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Services\Forms\Fields\BlockEditor;
use A17\Twill\Services\Forms\Fields\Browser;
use A17\Twill\Services\Forms\Fields\DatePicker;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Medias;
use A17\Twill\Services\Forms\Fields\MultiSelect;
use A17\Twill\Services\Forms\Fields\Wysiwyg;
use A17\Twill\Services\Forms\Form;
use App\Models\Author;
use App\Models\Category;

class PostController extends BaseModuleController
{
    protected function setUpController(): void
    {
        $this->setModuleName('posts');
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
                ->name('excerpt')
                ->label('Excerpt')
                ->translatable(),
            Wysiwyg::make()
                ->name('description')
                ->label('Description')
                ->translatable(),
            Browser::make()
                ->name('author')
                ->label('Author')
                ->moduleName('authors')
                ->max(1),
            MultiSelect::make()
                ->name('categories')
                ->label('Categories')
                ->options(
                    Category::published()
                        ->get()
                        ->mapWithKeys(fn($cat) => [$cat->id => $cat->title])
                        ->toArray()
                ),
            DatePicker::make()
                ->name('publish_start_date')
                ->label('Publish Date'),
            Medias::make()
                ->name('cover')
                ->label('Cover Image'),
            BlockEditor::make(),
        ]);
    }
}
