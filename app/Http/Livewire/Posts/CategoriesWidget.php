<?php

namespace App\Http\Livewire\Posts;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class CategoriesWidget extends Component
{
    public $categories;
    public $categoryFilters = [];

    protected $listeners = [
        'showAllPostsEvent' => 'resetFilters',
        'showMyPostsEvent' => 'resetFilters',
        'createdPostEvent' => 'resetFilters',
    ];

    public function mount(): void
    {
        $this->categories = Category::select('id', 'name')->get()->toArray();
    }

    public function render(): View|Factory
    {
        return view('livewire.posts.categories-widget');
    }

    public function updatedCategoryFilters(): void
    {
        $this->emitTo('posts.show', 'filterPostsByCategoryEvent', $this->categoryFilters);
    }

    public function resetFilters(): void
    {
        $this->reset('categoryFilters');
    }
}
