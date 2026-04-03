@props([
    'disabled' => false,
    'size' => null,
    'href' => false,
])

<x-form.button :disabled="$disabled" :size="$size" :href="$href" {{ $attributes }}>
    {{ $slot }}
</x-form.button>
