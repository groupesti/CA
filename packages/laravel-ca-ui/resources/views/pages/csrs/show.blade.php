@extends('ca::layouts.app')

@section('page-title', 'CSR: ' . ($csr->subject_dn['CN'] ?? 'Unknown'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('ca.csrs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 inline-flex items-center">
            <svg class="mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Back to CSRs
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">CSR Details</h2>
                    @include('ca::components.status-badge', ['status' => $csr->status])
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">UUID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono break-all">{{ $csr->uuid }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Certificate Authority</dt>
                            <dd class="mt-1 text-sm">
                                @if($csr->certificateAuthority)
                                    <a href="{{ route('ca.authorities.show', $csr->certificateAuthority->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $csr->certificateAuthority->subject_dn['CN'] ?? $csr->certificateAuthority->id }}
                                    </a>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Requested By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $csr->requested_by ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Approved By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $csr->approved_by ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Template</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $csr->template?->name ?? 'None' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $csr->created_at?->format('Y-m-d H:i:s T') }}</dd>
                        </div>
                        @if($csr->expires_at)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase">Expires</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $csr->expires_at->format('Y-m-d H:i:s T') }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if($csr->rejection_reason)
                        <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-200">
                            <h4 class="text-sm font-medium text-red-800">Rejection Reason</h4>
                            <p class="mt-1 text-sm text-red-700">{{ $csr->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Subject DN --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Subject Distinguished Name</h3>
                @include('ca::components.dn-display', ['dn' => $csr->subject_dn ?? []])
            </div>

            {{-- SAN --}}
            @if($csr->san && count($csr->san) > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Subject Alternative Names</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($csr->san as $sanEntry)
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

            {{-- Key info --}}
            @if($csr->key)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Associated Key</h3>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500">Algorithm</dt>
                        <dd class="text-sm text-gray-900">{{ $csr->key->algorithm ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Fingerprint</dt>
                        <dd class="text-sm text-gray-900 font-mono text-xs">{{ \Illuminate\Support\Str::limit($csr->key->fingerprint_sha256 ?? '', 32) }}</dd>
                    </div>
                </dl>
                <a href="{{ route('ca.keys.show', $csr->key->uuid) }}" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800 inline-block">
                    View Key Details
                </a>
            </div>
            @endif
        </div>

        {{-- Sidebar actions --}}
        <div class="space-y-6">
            @if($csr->isPending())
            {{-- Approve --}}
            <div class="bg-white rounded-lg shadow-sm border border-green-200 p-6">
                <h3 class="text-sm font-semibold text-green-700 mb-4">Approve CSR</h3>
                <p class="text-xs text-gray-600 mb-4">Approving this CSR will allow it to proceed to certificate issuance.</p>
                <form method="POST" action="{{ route('ca.csrs.approve', $csr->uuid) }}"
                      onsubmit="return confirm('Are you sure you want to approve this CSR?');">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                        <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Approve
                    </button>
                </form>
            </div>

            {{-- Reject --}}
            <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
                <h3 class="text-sm font-semibold text-red-700 mb-4">Reject CSR</h3>
                <form method="POST" action="{{ route('ca.csrs.reject', $csr->uuid) }}"
                      onsubmit="return confirm('Are you sure you want to reject this CSR?');">
                    @csrf
                    <div class="mb-3">
                        <label for="reason" class="block text-xs font-medium text-gray-600 mb-1">Rejection Reason *</label>
                        <textarea name="reason" id="reason" rows="3" required
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                  placeholder="Explain why this CSR is being rejected..."></textarea>
                    </div>
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                        <svg class="mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Reject
                    </button>
                </form>
            </div>
            @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Status</h3>
                <p class="text-sm text-gray-600">This CSR has been <strong>{{ $csr->status }}</strong> and can no longer be modified.</p>
            </div>
            @endif
        </div>
    </div>
@endsection
