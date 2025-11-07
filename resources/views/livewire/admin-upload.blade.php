@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        {{-- Header Section --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-2">
                        <svg width="20" height="20" class="inline-block shrink-0 text-blue-600"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Document Upload
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">Manage documents for Career Services</p>
                </div>
            </div>
        </div>

        {{-- Alert Messages --}}
        @if (session('message'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <svg width="20" height="20" class="inline-block shrink-0 text-green-500 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
                <div class="flex items-center">
                    <svg width="20" height="20" class="inline-block shrink-0 text-red-500 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Upload Form Section --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-blue-100 rounded-full p-2">
                    <svg width="16" height="16" class="inline-block shrink-0 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Upload New File</h2>
            </div>

            <form wire:submit.prevent="save" enctype="multipart/form-data" class="space-y-5">
                {{-- Description Input --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg width="14" height="14" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        File Description
                    </label>
                    <input
                        type="text"
                        wire:model.defer="description"
                        placeholder="e.g., Certification Form or Endorsement Letter"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400"
                    />
                    @error('description')
                        <div class="mt-2 flex items-center text-red-600 text-sm font-medium">
                            <svg width="14" height="14" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- File Input --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <svg width="14" height="14" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                        </svg>
                        Select File
                    </label>
                    <div class="relative">
                        <input
                            type="file"
                            wire:model="file"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                        />
                    </div>
                    @error('file')
                        <div class="mt-2 flex items-center text-red-600 text-sm font-medium">
                            <svg width="14" height="14" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror

                    <div wire:loading wire:target="file" class="mt-2 flex items-center text-blue-600 text-sm font-medium">
                        <svg width="14" height="14" class="inline-block shrink-0 mr-2 animate-spin" viewBox="0 0 24 24" fill="none" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading file...
                    </div>

                    <p class="mt-2 text-xs text-gray-500">
                        Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG
                    </p>
                </div>

                {{-- Submit Button --}}
                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-300 to-blue-400 hover:from-blue-500 hover:to-blue-500 text-blue-500 font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                    >
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                            <svg width="16" height="16" class="inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Upload File
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg width="16" height="16" class="inline-block shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Uploaded Files Section --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="bg-purple-100 rounded-full p-2">
                        <svg width="16" height="16" class="inline-block shrink-0 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Uploaded Files</h2>
                </div>
                <span class="text-sm text-gray-600 font-medium">
                    {{ count($documents) }} {{ count($documents) === 1 ? 'file' : 'files' }}
                </span>
            </div>

            <div class="space-y-4">
                @forelse ($documents as $doc)
                    <div class="relative overflow-hidden border-2 border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200"
                         wire:key="doc-row-{{ $doc->id }}">

                        {{-- Actions Dropdown (fixed top-right) --}}
                        <div x-data="{ open: false }" class="absolute top-2 right-2" x-cloak>
                            <button
                                type="button"
                                @click="open = !open"
                                @keydown.escape.window="open = false"
                                class="p-1.5 rounded-full hover:bg-gray-100 text-gray-600 hover:text-gray-900 transition-colors"
                                aria-haspopup="menu"
                                :aria-expanded="open"
                                aria-label="Document actions"
                            >
                                <svg width="16" height="16" class="inline-block shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                    <path d="M12 6.75a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm0 7.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm0 7.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                @click.away="open = false"
                                class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden"
                            >
                                <button
                                    type="button"
                                    @click="open = false; $wire.startEdit({{ $doc->id }})"
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 text-gray-700 text-sm font-medium transition-colors flex items-center gap-2"
                                >
                                    <svg width="14" height="14" class="inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                              d="M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L9.75 17.963 6 18.75l.787-3.75L16.862 3.487z"/>
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                              d="M19.5 10.5V18A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6.75A2.25 2.25 0 0 1 6.75 4.5H14.25"/>
                                    </svg>
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    @click="if(confirm('Are you sure you want to delete this document?')){ open = false; $wire.delete({{ $doc->id }}) }"
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-red-50 text-red-600 text-sm font-medium transition-colors flex items-center gap-2"
                                >
                                    <svg width="14" height="14" class="inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            {{-- File Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-3">
                                    {{-- File Icon --}}
                                    <div class="flex-shrink-0 mt-0.5">
                                        @php
                                            $ext = pathinfo($doc->filename, PATHINFO_EXTENSION);
                                            $iconColor = match(strtolower($ext)) {
                                                'pdf' => 'text-red-500',
                                                'doc', 'docx' => 'text-blue-500',
                                                'jpg', 'jpeg', 'png' => 'text-green-500',
                                                default => 'text-gray-500'
                                            };
                                        @endphp
                                        <svg width="22" height="22" class="inline-block shrink-0 {{ $iconColor }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        {{-- Title: wrap on mobile (max 3 lines), single line on desktop --}}
                                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 break-words break-all whitespace-normal line-clamp-3 sm:line-clamp-1 pr-8 leading-snug">
                                            {{ $doc->title ?? basename($doc->filename) }}
                                        </h3>

                                        {{-- Description --}}
                                        @if(!empty($doc->description))
                                            <p class="text-sm text-gray-600 mt-1 break-words line-clamp-3">
                                                {{ $doc->description }}
                                            </p>
                                        @endif

                                        {{-- Meta Info --}}
                                        <div class="flex flex-wrap gap-2 mt-2 text-xs text-gray-500">
                                            <span class="inline-flex items-center">
                                                <svg width="12" height="12" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                ID: {{ $doc->id }}
                                            </span>
                                            <span class="text-gray-400">•</span>
                                            <span class="break-all sm:truncate sm:max-w-xs max-w-full" title="{{ $doc->filename }}">
                                                {{ basename($doc->filename) }}
                                            </span>
                                        </div>

                                        {{-- View Link --}}
                                        <div class="mt-3">
                                            @if($doc->filename && Storage::disk('public')->exists($doc->filename))
                                                <a href="{{ route('files.show', $doc->id) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium text-sm hover:underline transition-colors">
                                                    <svg width="14" height="14" class="inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View File
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-red-600 text-sm font-medium">
                                                    <svg width="14" height="14" class="inline-block shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    File missing
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Inline Edit Form --}}
                        @if($editingId === $doc->id)
                            <div class="bg-gray-50 border-2 border-blue-300 rounded-lg p-6 shadow-sm"
                                 wire:key="doc-edit-{{ $doc->id }}">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg width="16" height="16" class="inline-block shrink-0 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                              d="M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L9.75 17.963 6 18.75l.787-3.75L16.862 3.487z"/>
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                              d="M19.5 10.5V18A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6.75A2.25 2.25 0 0 1 6.75 4.5H14.25"/>
                                    </svg>
                                    Edit Document
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">File Title</label>
                                        <input type="text"
                                               wire:model="editTitle"
                                               class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                                        @error('editTitle')
                                            <div class="mt-2 flex items-center text-red-600 text-sm font-medium">
                                                <svg width="14" height="14" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                        <textarea wire:model="editDescription"
                                                  rows="3"
                                                  class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"></textarea>
                                        @error('editDescription')
                                            <div class="mt-2 flex items-center text-red-600 text-sm font-medium">
                                                <svg width="14" height="14" class="inline-block shrink-0 mr-1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                        <button type="button"
                                                wire:click="updateDocument"
                                                class="flex-1 sm:flex-none px-6 py-2.5 bg-blue-500 hover:bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                                            <svg width="16" height="16" class="inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Save Changes
                                        </button>
                                        <button type="button"
                                                wire:click="cancelEdit"
                                                class="flex-1 sm:flex-none px-6 py-2.5 bg-red-500 hover:bg-red-500 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                                            <svg width="16" height="16" class="inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg width="40" height="40" class="mx-auto text-gray-300 mb-4 inline-block shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" preserveAspectRatio="xMidYMid meet">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">No files uploaded yet</p>
                        <p class="text-gray-400 text-sm mt-1">Upload your first document using the form above</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
