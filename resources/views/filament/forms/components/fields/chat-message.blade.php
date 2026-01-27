@php
    $chatId = $field->getState();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @if($chatId)
        <div wire:ignore>
            @livewire('TransactionChat', ['chatId' => $chatId])
        </div>
    @else
        <div class="p-4 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-lg">
            Chưa có thông tin phòng chat (Chat ID is NULL).
        </div>
    @endif
</x-dynamic-component>