@extends('ca::layouts.app')

@section('page-title', 'Audit Log')

@section('content')
    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('ca.audit.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="w-48">
                <label for="action" class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                <select name="action" id="action" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label for="date_from" class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="w-44">
                <label for="date_to" class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
            </div>
            @if(request()->hasAny(['action', 'date_from', 'date_to']))
                <div>
                    <a href="{{ route('ca.audit.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors inline-block">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metadata</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ $log->performed_at?->format('Y-m-d H:i:s') }}
                            <div class="text-xs text-gray-400">{{ $log->performed_at?->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span class="text-gray-700">{{ class_basename($log->subject_type ?? 'Unknown') }}</span>
                            @if($log->subject_id)
                                <span class="block text-xs font-mono text-gray-400">{{ \Illuminate\Support\Str::limit($log->subject_id, 20) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($log->actor_type)
                                <span class="text-gray-700">{{ class_basename($log->actor_type) }}</span>
                                @if($log->actor_id)
                                    <span class="block text-xs font-mono text-gray-400">{{ \Illuminate\Support\Str::limit($log->actor_id, 12) }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono text-xs">{{ $log->ip_address ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($log->metadata)
                                <button type="button"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 underline"
                                        onclick="this.nextElementSibling.classList.toggle('hidden');">
                                    Show details
                                </button>
                                <pre class="hidden mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded max-w-xs overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No audit log entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                @include('ca::components.pagination', ['paginator' => $logs])
            </div>
        @endif
    </div>
@endsection
