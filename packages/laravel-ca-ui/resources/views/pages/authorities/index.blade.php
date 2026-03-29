@extends('ca::layouts.app')

@section('page-title', 'Certificate Authorities')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-gray-600">Manage your certificate authority hierarchy.</p>
        <a href="{{ route('ca.authorities.create') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
            <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create CA
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">CA Hierarchy</h2>
        </div>
        <div class="p-6">
            @forelse($rootCas as $rootCa)
                @include('ca::pages.authorities._ca-tree-node', ['ca' => $rootCa, 'depth' => 0])
            @empty
                <p class="text-sm text-gray-500 text-center py-8">No Certificate Authorities found. Create your first Root CA to get started.</p>
            @endforelse
        </div>
    </div>
@endsection
