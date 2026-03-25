@props(['href' => '#', 'active' => false, 'label' => ''])

<li>
    <a href="{{ $href }}"
       class="block px-4 py-2 rounded-lg text-sm
              {{ request()->url() === $href
                 ? 'bg-blue-600 text-white font-medium'
                 : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}">
        {{ $label }}
    </a>
</li>
