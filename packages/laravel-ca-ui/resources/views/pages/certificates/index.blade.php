@extends('ca::layouts.app')

@section('page-title', 'Certificates')

@section('content')
    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('ca.certificates.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                       placeholder="Serial, subject, fingerprint...">
            </div>
            <div class="w-40">
                <label for="type" class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="type" id="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type->slug }}" {{ request('type') === $type->slug ? 'selected' : '' }}>{{ $type->slug }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->slug }}" {{ request('status') === $status->slug ? 'selected' : '' }}>{{ $status->slug }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label for="ca_id" class="block text-xs font-medium text-gray-600 mb-1">Issuing CA</label>
                <select name="ca_id" id="ca_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All CAs</option>
                    @foreach($authorities as $ca)
                        <option value="{{ $ca->id }}" {{ request('ca_id') === $ca->id ? 'selected' : '' }}>{{ $ca->subject_dn['CN'] ?? $ca->id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
            </div>
            @if(request()->hasAny(['search', 'type', 'status', 'ca_id']))
                <div>
                    <a href="{{ route('ca.certificates.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors inline-block">
                        Clear
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issuing CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($certificates as $cert)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('ca.certificates.show', $cert->uuid) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $cert->subject_dn['CN'] ?? 'N/A' }}
                            </a>
                            @if($cert->san && count($cert->san) > 0)
                                <p class="text-xs text-gray-400 mt-0.5">+{{ count($cert->san) }} SAN(s)</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cert->type->slug }}</td>
                        <td class="px-6 py-4 text-sm">
                            @include('ca::components.status-badge', ['status' => $cert->status->slug])
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($cert->certificateAuthority)
                                <a href="{{ route('ca.authorities.show', $cert->certificateAuthority->id) }}" class="text-gray-600 hover:text-indigo-600">
                                    {{ $cert->certificateAuthority->subject_dn['CN'] ?? 'N/A' }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cert->not_after?->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono text-xs">{{ \Illuminate\Support\Str::limit($cert->serial_number, 16) }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('ca.certificates.show', $cert->uuid) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No certificates found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                @include('ca::components.pagination', ['paginator' => $certificates])
            </div>
        @endif
    </div>
@endsection
