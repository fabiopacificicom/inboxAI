<?php

namespace App\Livewire\AiReply;

use App\Models\Url;
use Livewire\Component;
use App\Traits\Helpers;
use Illuminate\Support\Facades\Cache;

class KnowledgeBaseComponent extends Component
{
    use Helpers;

    public $link;
    public $links;
    public function render()
    {
        return view('livewire.knowledge-base-component');
    }

    public function mount()
    {
        $this->links = Cache::get('links', []);
    }

    public function addLink()
    {
        $this->validate([
            'link' => ['required', 'url', 'unique:urls'],
        ]);
        //dd($this->link, 'here', $this->readWebPageFromUrl($this->link));
        // save the link in the database
        Url::create([
            'url' => $this->link,
            'content' => $this->readWebPageFromUrl($this->link),
        ]);

        // save the link in the array
        array_push($this->links, $this->link);
        // store the links to cache
        Cache::forever('links', $this->links);
        // clear the input field
        $this->link = '';
    }
}
