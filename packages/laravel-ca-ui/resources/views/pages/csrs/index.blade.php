@extends('ca::layouts.app')

@section('page-title', 'Certificate Signing Requests')

@section('content')
    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('ca.csrs.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-48">
                <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->slug }}" {{ request('status') === $status->slug ? 'selected' : '' }}>{{ ucfirst($status->slug) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
            </div>
            @if(request()->hasAny(['status']))
                <div>
                    <a href="{{ route('ca.csrs.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors inline-block">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($csrs as $csr)
                    <tr class="hover:bg-gray-50 {{ $csr->status === 'pending' ? 'bg-yellow-50' : '' }}">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('ca.csrs.show', $csr->uuid) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                {{ $csr->subject_dn['CN'] ?? 'N/A' }}
                            </a>
                            @if($csr->san && count($csr->san) > 0)
                                <p class="text-xs text-gray-400 mt-0.5">+{{ count($csr->san) }} SAN(s)</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @include('ca::components.status-badge', ['status' => $csr->status])
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($csr->certificateAuthority)
                                <a href="{{ route('ca.authorities.show', $csr->certificateAuthority->id) }}" class="text-gray-600 hover:text-indigo-600">
                                    {{ $csr->certificateAuthority->subject_dn['CN'] ?? 'N/A' }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $csr->requested_by ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $csr->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            @if($csr->isPending())
                                <form method="POST" action="{{ route('ca.csrs.approve', $csr->uuid) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 font-medium">Approve</button>
                                </form>
                            @endif
                            <a href="{{ route('ca.csrs.show', $csr->uuid) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No CSRs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($csrs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                @include('ca::components.pagination', ['paginator' => $csrs])
            </div>
        @endif
    </div>
@endsection
