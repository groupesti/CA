<div class="{{ $depth > 0 ? 'ml-8 border-l-2 border-gray-200 pl-4' : '' }} mb-4">
    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                @if($ca->isRoot())
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100 text-indigo-600">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                        </svg>
                    </span>
                @else
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </span>
                @endif
            </div>
            <div>
                <a href="{{ route('ca.authorities.show', $ca->id) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                    {{ $ca->subject_dn['CN'] ?? 'Unnamed CA' }}
                </a>
                <div class="flex items-center space-x-3 mt-1">
                    <span class="text-xs text-gray-500">{{ $ca->type->slug }}</span>
                    @include('ca::components.status-badge', ['status' => $ca->status->slug])
                    <span class="text-xs text-gray-400">Expires: {{ $ca->not_after?->format('Y-m-d') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        <a href="{{ route('ca.authorities.show', $ca->id) }}"
           class="text-sm text-gray-400 hover:text-gray-600">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </a>
    </div>

    @if($ca->children && $ca->children->count() > 0)
        @foreach($ca->children as $child)
            @include('ca::pages.authorities._ca-tree-node', ['ca' => $child, 'depth' => $depth + 1])
        @endforeach
    @endif
</div>
