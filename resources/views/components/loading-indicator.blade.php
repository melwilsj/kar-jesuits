@props(['size' => '6', 'color' => 'white'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <div class="spinner-border animate-spin inline-block w-{{ $size }} h-{{ $size }} border-[3px] rounded-full text-{{ $color }}" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <span class="ml-2">{{ $slot }}</span>
</div>

<style>
.spinner-border {
    border-right-color: transparent;
}
</style> 