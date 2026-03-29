@extends('ca::layouts.app')

@section('page-title', 'Keys')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Cryptographic Keys</h2>
            <p class="mt-1 text-sm text-gray-500">Private key material is never displayed. Only public key metadata is shown.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fingerprint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Algorithm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($keys as $key)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono text-xs">
                            <a href="{{ route('ca.keys.show', $key->uuid) }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ \Illuminate\Support\Str::limit($key->fingerprint_sha256 ?? 'N/A', 24) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $key->algorithm ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @include('ca::components.status-badge', ['status' => $key->status ?? 'unknown'])
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $key->usage ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($key->certificateAuthority)
                                <a href="{{ route('ca.authorities.show', $key->certificateAuthority->id) }}" class="text-gray-600 hover:text-indigo-600">
                                    {{ $key->certificateAuthority->subject_dn['CN'] ?? 'N/A' }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $key->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('ca.keys.show', $key->uuid) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No keys found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($keys->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                @include('ca::components.pagination', ['paginator' => $keys])
            </div>
        @endif
    </div>
@endsection
