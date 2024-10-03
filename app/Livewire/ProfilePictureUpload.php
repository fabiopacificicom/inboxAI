<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ProfilePictureUpload extends Component
{

    use WithFileUploads;
    #[Validate('required')]
    public $profile_picture = null;
    public $fileName = '';
    public $updatedImage = false;

    public function render()
    {
        return view('livewire.profile-picture-upload');
    }

    // Update method is called when the form is submitted
    public function update()
    {
        //dd('here', $this->profile_picture);
        if (is_string($this->profile_picture)) {
            $file_path = Storage::disk('public')->putFile('images', $this->profile_picture);
        } elseif ($this->profile_picture instanceof \Illuminate\Http\UploadedFile) {
            $file_path = $this->profile_picture->storeAs('images', Str::random(10) . '.' .
                $this->profile_picture->getClientOriginalExtension());
        }

        if (is_null($file_path)) return;

        // Update the profile picture in the database
        $this->updateProfilePicture($file_path);

        $this->updatedImage = true;
    }

    private function updateProfilePicture($file_path)
    {
        // Assuming you have a user model with an image column
        $user = auth()->user();
        $image = $user->profile_image;

        if (!is_null($image)) {
            Storage::disk('public')->delete($image);
        }

        $path = Storage::disk('public')->putFile('images', $this->profile_picture);
        $user->profile_image = $path;
        $user->save();

        return true;
    }
}
