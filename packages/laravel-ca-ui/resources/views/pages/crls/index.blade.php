@extends('ca::layouts.app')

@section('page-title', 'Certificate Revocation Lists')

@section('content')
    {{-- Generate CRL per CA --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Generate CRL</h2>
        <p class="text-sm text-gray-600 mb-4">Select a Certificate Authority to generate a new CRL.</p>
        <div class="flex flex-wrap gap-3">
            @foreach($authorities as $ca)
                <form method="POST" action="{{ route('ca.crls.generate', $ca->id) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors border border-gray-200"
                            onclick="return confirm('Generate a new CRL for ' + @js($ca->subject_dn['CN'] ?? $ca->id) + '?');">
                        <svg class="mr-1.5 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                        </svg>
                        {{ $ca->subject_dn['CN'] ?? \Illuminate\Support\Str::limit($ca->id, 12) }}
                    </button>
                </form>
            @endforeach
        </div>
    </div>

    {{-- CRL list --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">CRL History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CRL #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">This Update</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Update</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entries</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Algorithm</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($crls as $crl)
                    <tr class="hover:bg-gray-50 {{ $crl->isExpired() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 text-sm">
                            @if($crl->certificateAuthority)
                                <a href="{{ route('ca.authorities.show', $crl->certificateAuthority->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $crl->certificateAuthority->subject_dn['CN'] ?? 'N/A' }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ $crl->crl_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($crl->is_delta)
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">Delta</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Full</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $crl->this_update?->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 text-sm {{ $crl->isExpired() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                            {{ $crl->next_update?->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $crl->entries_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($crl->isExpired())
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Expired</span>
                            @elseif($crl->isCurrent())
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Current</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 text-xs">{{ $crl->signature_algorithm ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500">No CRLs found. Generate one using the buttons above.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($crls->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                @include('ca::components.pagination', ['paginator' => $crls])
            </div>
        @endif
    </div>
@endsection
