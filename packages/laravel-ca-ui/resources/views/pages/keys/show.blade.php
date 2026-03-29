@extends('ca::layouts.app')

@section('page-title', 'Key Details')

@section('content')
    <div class="mb-6">
        <a href="{{ route('ca.keys.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Back to Keys
        </a>
    </div>

    <div class="max-w-3xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Key Information</h2>
                @include('ca::components.status-badge', ['status' => $key->status ?? 'unknown'])
            </div>
            <div class="p-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm text-yellow-800">Private key material is never exposed through the dashboard for security reasons.</p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">UUID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $key->uuid }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Algorithm</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $key->algorithm ?? 'N/A' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase">Fingerprint (SHA-256)</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $key->fingerprint_sha256 ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Usage</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $key->usage ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Encryption Strategy</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $key->encryption_strategy ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Associated CA</dt>
                        <dd class="mt-1 text-sm">
                            @if($key->certificateAuthority)
                                <a href="{{ route('ca.authorities.show', $key->certificateAuthority->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $key->certificateAuthority->subject_dn['CN'] ?? $key->certificateAuthority->id }}
                                </a>
                            @else
                                <span class="text-gray-500">None</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $key->created_at?->format('Y-m-d H:i:s T') }}</dd>
                    </div>
                </dl>

                {{-- Public key preview --}}
                @if($key->public_key_pem)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Public Key (PEM)</h3>
                    <pre class="text-xs text-gray-600 bg-gray-50 p-4 rounded-lg overflow-x-auto font-mono leading-relaxed">{{ $key->public_key_pem }}</pre>
                </div>
                @endif

                {{-- Parameters --}}
                @if($key->parameters)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Key Parameters</h3>
                    <pre class="text-xs text-gray-600 bg-gray-50 p-3 rounded overflow-x-auto">{{ json_encode($key->parameters, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
