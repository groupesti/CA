@extends('ca::layouts.app')

@section('page-title', 'CA: ' . ($authority->subject_dn['CN'] ?? 'Unknown'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('ca.authorities.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Back to Authorities
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- CA Info card --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Certificate Authority Details</h2>
                @include('ca::components.status-badge', ['status' => $authority->status->slug])
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $authority->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $authority->type->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Serial Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $authority->serial_number ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Key Algorithm</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $authority->key_algorithm ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Hash Algorithm</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $authority->hash_algorithm ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Path Length</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $authority->path_length !== null ? $authority->path_length : 'Unlimited' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Valid From</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $authority->not_before?->format('Y-m-d H:i:s T') ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Valid Until</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $authority->not_after?->format('Y-m-d H:i:s T') ?? 'N/A' }}</dd>
                    </div>
                </dl>

                {{-- Distinguished Name --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Subject Distinguished Name</h3>
                    @include('ca::components.dn-display', ['dn' => $authority->subject_dn ?? []])
                </div>

                {{-- Parent --}}
                @if($authority->parent)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Parent CA</h3>
                    <a href="{{ route('ca.authorities.show', $authority->parent->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        {{ $authority->parent->subject_dn['CN'] ?? $authority->parent->id }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Stats sidebar --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Statistics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Total Certificates</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $certificateCount }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Active Certificates</dt>
                        <dd class="text-sm font-semibold text-green-600">{{ $activeCertCount }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-600">Sub-CAs</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $authority->children->count() }}</dd>
                    </div>
                </dl>
            </div>

            {{-- CRL Status --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">CRL Status</h3>
                @if($latestCrl)
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">CRL Number</dt>
                            <dd class="text-xs font-mono text-gray-700">{{ $latestCrl->crl_number }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">Last Updated</dt>
                            <dd class="text-xs text-gray-700">{{ $latestCrl->this_update?->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">Next Update</dt>
                            <dd class="text-xs {{ $latestCrl->isExpired() ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                {{ $latestCrl->next_update?->format('Y-m-d H:i') }}
                                @if($latestCrl->isExpired())
                                    (Expired)
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-xs text-gray-500">Entries</dt>
                            <dd class="text-xs text-gray-700">{{ $latestCrl->entries_count }}</dd>
                        </div>
                    </dl>
                    <form method="POST" action="{{ route('ca.crls.generate', $authority->id) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                            Regenerate CRL
                        </button>
                    </form>
                @else
                    <p class="text-xs text-gray-500 mb-3">No CRL has been generated yet.</p>
                    <form method="POST" action="{{ route('ca.crls.generate', $authority->id) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition-colors">
                            Generate CRL
                        </button>
                    </form>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('ca.certificates.index', ['ca_id' => $authority->id]) }}"
                       class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                        View Certificates
                    </a>
                    <a href="{{ route('ca.authorities.create', ['parent_id' => $authority->id]) }}"
                       class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                        Create Sub-CA
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Children --}}
    @if($authority->children->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Subordinate CAs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Common Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Algorithm</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($authority->children as $child)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('ca.authorities.show', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $child->subject_dn['CN'] ?? 'Unnamed' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @include('ca::components.status-badge', ['status' => $child->status->slug])
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $child->not_after?->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $child->key_algorithm }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('ca.authorities.show', $child->id) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
