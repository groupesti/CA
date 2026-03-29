@if($paginator->hasPages())
    <nav class="flex items-center justify-between" aria-label="Pagination">
        <div class="hidden sm:block">
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-medium">{{ $paginator->total() }}</span>
                results
            </p>
        </div>
        <div class="flex flex-1 justify-between sm:justify-end space-x-2">
            @if($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-300 border border-gray-200 cursor-not-allowed">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">
                    Previous
                </a>
            @endif

            <div class="hidden md:flex space-x-1">
                @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 3), min($paginator->lastPage(), $paginator->currentPage() + 3)) as $page => $url)
                    @if($page === $paginator->currentPage())
                        <span class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            </div>

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 transition-colors">
                    Next
                </a>
            @else
                <span class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-300 border border-gray-200 cursor-not-allowed">
                    Next
                </span>
            @endif
        </div>
    </nav>
@endif
