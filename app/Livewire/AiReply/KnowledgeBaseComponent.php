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
        //Cache::forget('links');
        $links = Url::take(5)->get();
        $this->links = Cache::get('links', $links ?? []);
        //dd($this->links);
    }

    public function addLink()
    {
        $this->validate([
            'link' => ['required', 'url', 'unique:urls,url,except,id'],
        ], ['unique', 'The field must be unique']);
        //dd($this->link, 'here', $this->readWebPageFromUrl($this->link));

        // save the link in the database
        $link = Url::create([
            'url' => $this->link,
            'content' => $this->readWebPageFromUrl($this->link),
        ]);

        // save the link in the array
        $this->links->add($link);
        // store the links to cache
        Cache::forever('links', $this->links);
        // clear the input field
        $this->link = '';
    }


    public function delete($url)
    {
        //dd($url);
        $url->delete();
        return back()->with('message', 'deleted');
    }
}
