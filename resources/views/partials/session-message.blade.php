@if (session()->has('message'))
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded fixed top-40 left-5 z-50"
        role="alert">
        {{ session('message')}}

    </div>
@endif
