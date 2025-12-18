<div>

    <h2>Upload Profile Picture</h2>

    <div class="flex gap-2 items-center">
        <img src="{{ asset('storage/' . Auth::user()->profile_image )}}" width="40" height="40" class="w-9 h-9object-cover rounded-full">

        <form class="flex items-center gap-2" enctype="multipart/form-data" wire:submit.prevent="update">
            <input type="file" name="picture" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" wire:model="profile_picture" required>
            <button type="submit" class="bg-gray-800 text-white p-2 rounded-lg">Upload</button>
        </form>

    </div>

</div>
