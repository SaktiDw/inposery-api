@props(['class'])
<span {{ $attributes->merge(['class' => '$class']) }}>
    {{ number_format($slot, 2) }}
</span>
