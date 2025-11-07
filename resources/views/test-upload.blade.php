<!DOCTYPE html>
<html>
<head>
    <title>Test Upload</title>
    @livewireStyles
</head>
<body class="p-10 bg-gray-100 text-black">
    <h1 class="text-xl font-bold mb-4">Livewire File Upload Test</h1>

    @livewire('file-upload', ['streamId' => $streamId])

    @livewireScripts
    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
