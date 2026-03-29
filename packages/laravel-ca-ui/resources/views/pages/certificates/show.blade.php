@extends('ca::layouts.app')

@section('page-title', 'Certificate: ' . ($certificate->subject_dn['CN'] ?? 'Unknown'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('ca.certificates.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Back to Certificates
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Main details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Subject Info --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Certificate Details</h2>
                    @include('ca::components.status-badge', ['status' => $certificate->status->slug])
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">UUID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $certificate->uuid }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->type->slug }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Serial Number</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $certificate->serial_number ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Fingerprint (SHA-256)</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono break-all text-xs">{{ $certificate->fingerprint_sha256 ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Valid From</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->not_before?->format('Y-m-d H:i:s T') ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Valid Until</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $certificate->not_after?->format('Y-m-d H:i:s T') ?? 'N/A' }}
                                @if($certificate->status->slug === 'active' && $certificate->not_after)
                                    <span class="ml-2 text-xs {{ $certificate->daysUntilExpiry() <= 30 ? 'text-red-600' : 'text-gray-500' }}">
                                        ({{ $certificate->daysUntilExpiry() }} days remaining)
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Issuing CA</dt>
                            <dd class="mt-1 text-sm">
                                @if($certificate->certificateAuthority)
                                    <a href="{{ route('ca.authorities.show', $certificate->certificateAuthority->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $certificate->certificateAuthority->subject_dn['CN'] ?? $certificate->certificateAuthority->id }}
                                    </a>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certificate->created_at?->format('Y-m-d H:i:s T') }}</dd>
                        </div>
                    </dl>

                    @if($certificate->isRevoked())
                        <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-200">
                            <h4 class="text-sm font-medium text-red-800">Revocation Information</h4>
                            <dl class="mt-2 grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs text-red-600">Reason</dt>
                                    <dd class="text-sm text-red-800">{{ $certificate->revocation_reason ?? 'Unspecified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-red-600">Revoked At</dt>
                                    <dd class="text-sm text-red-800">{{ $certificate->revoked_at?->format('Y-m-d H:i:s T') ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Validity bar --}}
            @if($certificate->not_before && $certificate->not_after)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Validity Period</h3>
                @php
                    $totalDays = $certificate->not_before->diffInDays($certificate->not_after);
                    $elapsedDays = $certificate->not_before->diffInDays(now());
                    $progress = $totalDays > 0 ? min(100, max(0, ($elapsedDays / $totalDays) * 100)) : 0;
                @endphp
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>{{ $certificate->not_before->format('Y-m-d') }}</span>
                    <span>{{ $certificate->not_after->format('Y-m-d') }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $certificate->isExpired() ? 'bg-gray-400' : ($progress > 90 ? 'bg-red-500' : ($progress > 75 ? 'bg-yellow-500' : 'bg-green-500')) }}"
                         style="width: {{ $progress }}%"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    {{ $totalDays }} days total, {{ max(0, $totalDays - $elapsedDays) }} days remaining
                </p>
            </div>
            @endif

            {{-- Subject DN --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Subject Distinguished Name</h3>
                @include('ca::components.dn-display', ['dn' => $certificate->subject_dn ?? []])
            </div>

            {{-- SAN --}}
            @if($certificate->san && count($certificate->san) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Subject Alternative Names</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($certificate->san as $sanEntry)
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                            @if(is_array($sanEntry))
                                {{ $sanEntry['type'] ?? '' }}: {{ $sanEntry['value'] ?? json_encode($sanEntry) }}
                            @else
                                {{ $sanEntry }}
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Extensions --}}
            @if($certificate->key_usage || $certificate->extended_key_usage)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Extensions</h3>
                @if($certificate->key_usage)
                    <div class="mb-4">
                        <h4 class="text-xs font-medium text-gray-500 uppercase mb-2">Key Usage</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($certificate->key_usage as $usage)
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                    {{ $usage }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($certificate->extended_key_usage)
                    <div>
                        <h4 class="text-xs font-medium text-gray-500 uppercase mb-2">Extended Key Usage</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($certificate->extended_key_usage as $eku)
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                    {{ $eku }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- Certificate Chain --}}
            @if($certificate->chains && $certificate->chains->count() > 0)
                @include('ca::components.certificate-chain', ['chains' => $certificate->chains])
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('ca.certificates.export', ['uuid' => $certificate->uuid, 'format' => 'pem']) }}"
                       class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                        <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export PEM
                    </a>
                    <a href="{{ route('ca.certificates.export', ['uuid' => $certificate->uuid, 'format' => 'der']) }}"
                       class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                        <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export DER
                    </a>

                    @if($certificate->key)
                        <a href="{{ route('ca.keys.show', $certificate->key->uuid) }}"
                           class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                            View Key
                        </a>
                    @endif

                    @if($certificate->csr)
                        <a href="{{ route('ca.csrs.show', $certificate->csr->uuid) }}"
                           class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors">
                            View CSR
                        </a>
                    @endif
                </div>
            </div>

            {{-- Revoke --}}
            @if($certificate->status->slug === 'active')
            <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                <h3 class="text-sm font-semibold text-red-700 mb-4">Revoke Certificate</h3>
                <form method="POST" action="{{ route('ca.certificates.revoke', $certificate->uuid) }}"
                      onsubmit="return confirm('Are you sure you want to revoke this certificate? This action cannot be undone.');">
                    @csrf
                    <div class="mb-3">
                        <label for="reason" class="block text-xs font-medium text-gray-600 mb-1">Revocation Reason</label>
                        <select name="reason" id="reason" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                            @foreach($revocationReasons as $reason)
                                <option value="{{ $reason->slug }}">{{ $reason->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-3 py-2 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition-colors">
                        Revoke Certificate
                    </button>
                </form>
            </div>
            @endif

            {{-- Metadata --}}
            @if($certificate->metadata)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Metadata</h3>
                <pre class="text-xs text-gray-600 bg-gray-50 p-3 rounded overflow-x-auto">{{ json_encode($certificate->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>
@endsection
