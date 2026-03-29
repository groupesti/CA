@extends('ca::layouts.app')

@section('page-title', 'Create Certificate Authority')

@section('content')
    <div class="mb-6">
        <a href="{{ route('ca.authorities.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Back to Authorities
        </a>
    </div>

    <div class="max-w-3xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">New Certificate Authority</h2>
                <p class="mt-1 text-sm text-gray-500">Create a new Root or Intermediate Certificate Authority.</p>
            </div>

            <form method="POST" action="{{ route('ca.authorities.store') }}" class="p-6 space-y-6">
                @csrf

                {{-- CA Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CA Type</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="type" value="root"
                                   class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                   {{ old('type', request('parent_id') ? 'intermediate' : 'root') === 'root' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Root CA</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="type" value="intermediate"
                                   class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                   {{ old('type', request('parent_id') ? 'intermediate' : '') === 'intermediate' ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Intermediate CA</span>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Parent CA --}}
                <div id="parent-ca-field">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent CA</label>
                    <select name="parent_id" id="parent_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Select Parent CA --</option>
                        @foreach($parentCas as $parentCa)
                            <option value="{{ $parentCa->id }}" {{ old('parent_id', request('parent_id')) === $parentCa->id ? 'selected' : '' }}>
                                {{ $parentCa->subject_dn['CN'] ?? $parentCa->id }}
                                ({{ $parentCa->type->slug }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <hr class="border-gray-200">

                <h3 class="text-sm font-semibold text-gray-700">Subject Distinguished Name</h3>

                {{-- Common Name --}}
                <div>
                    <label for="common_name" class="block text-sm font-medium text-gray-700">Common Name (CN) *</label>
                    <input type="text" name="common_name" id="common_name" value="{{ old('common_name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                           placeholder="My Root CA">
                    @error('common_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Organization --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="organization" class="block text-sm font-medium text-gray-700">Organization (O)</label>
                        <input type="text" name="organization" id="organization" value="{{ old('organization') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="My Company Inc.">
                        @error('organization')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="organizational_unit" class="block text-sm font-medium text-gray-700">Organizational Unit (OU)</label>
                        <input type="text" name="organizational_unit" id="organizational_unit" value="{{ old('organizational_unit') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="IT Security">
                        @error('organizational_unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Location --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Country (C)</label>
                        <input type="text" name="country" id="country" value="{{ old('country') }}" maxlength="2"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="US">
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">State (ST)</label>
                        <input type="text" name="state" id="state" value="{{ old('state') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="California">
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="locality" class="block text-sm font-medium text-gray-700">Locality (L)</label>
                        <input type="text" name="locality" id="locality" value="{{ old('locality') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="San Francisco">
                        @error('locality')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-gray-200">

                <h3 class="text-sm font-semibold text-gray-700">Key & Validity</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Key Algorithm --}}
                    <div>
                        <label for="key_algorithm" class="block text-sm font-medium text-gray-700">Key Algorithm *</label>
                        <select name="key_algorithm" id="key_algorithm" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach($algorithms as $algo)
                                <option value="{{ $algo->slug }}" {{ old('key_algorithm', 'rsa-4096') === $algo->slug ? 'selected' : '' }}>
                                    {{ $algo->slug }} ({{ $algo->getKeySize() }} bits)
                                </option>
                            @endforeach
                        </select>
                        @error('key_algorithm')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Validity --}}
                    <div>
                        <label for="validity_days" class="block text-sm font-medium text-gray-700">Validity (days) *</label>
                        <input type="number" name="validity_days" id="validity_days" value="{{ old('validity_days', 3650) }}" required min="1" max="36500"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('validity_days')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Path Length --}}
                <div>
                    <label for="path_length" class="block text-sm font-medium text-gray-700">Path Length Constraint</label>
                    <input type="number" name="path_length" id="path_length" value="{{ old('path_length') }}" min="0"
                           class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                           placeholder="Leave empty for unlimited">
                    <p class="mt-1 text-xs text-gray-500">Maximum number of subordinate CAs allowed below this CA. Leave empty for no constraint.</p>
                    @error('path_length')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('ca.authorities.index') }}"
                       class="px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                        Create Certificate Authority
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const parentField = document.getElementById('parent-ca-field');

            function toggleParent() {
                const selected = document.querySelector('input[name="type"]:checked');
                if (selected && selected.value === 'root') {
                    parentField.style.display = 'none';
                } else {
                    parentField.style.display = 'block';
                }
            }

            typeRadios.forEach(function(radio) {
                radio.addEventListener('change', toggleParent);
            });

            toggleParent();
        });
    </script>
    @endpush
@endsection
