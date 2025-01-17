@php
    $options = is_object($options) && method_exists($options, 'map') ? $options->map(function($label, $value) {
        return [
            'value' => $value,
            'label' => $label
        ];
    })->values()->toArray() : $options;

    $unpack = $unpack ?? true;
    $note = $note ?? false;
    $columns = $columns ?? 0;

    // do not use for now, but this will allow you to create a new option directly from the form
    $addNew = $addNew ?? false;
    $moduleName = $moduleName ?? null;
    $storeUrl = $storeUrl ?? '';
    $inModal = $fieldsInModal ?? false;
@endphp

@if ($unpack)
    <a17-multiselect
        label="{{ $label }}"
        @include('twill::partials.form.utils._field_name')
        :options="{{ json_encode($options) }}"
        :grid="true"
        :columns="{{ $columns }}"
        :inline="false"
        @if ($min ?? false) :min="{{ $min }}" @endif
        @if ($max ?? false) :max="{{ $max }}" @endif
        @if ($inModal) :in-modal="true" @endif
        @if ($addNew) add-new='{{ $storeUrl }}' @elseif ($note) note='{{ $note }}' @endif
        in-store="currentValue"
    >
        @if($addNew)
            <div slot="addModal">
                @partialView(($moduleName ?? null), 'create', ['renderForModal' => true, 'fieldsInModal' => true])
            </div>
        @endif
    </a17-multiselect>
@else
    <a17-vselect
        label="{{ $label }}"
        @include('twill::partials.form.utils._field_name')
        :options="{{ json_encode($options) }}"
        @if ($emptyText ?? false) empty-text="{{ $emptyText }}" @endif
        @if ($placeholder ?? false) placeholder="{{ $placeholder }}" @endif
        @if ($inModal) :in-modal="true" @endif
        @if ($addNew) add-new='{{ $storeUrl }}' @elseif ($note) note='{{ $note }}' @endif
        @if ($searchable ?? $endpoint ?? false) :searchable="true" @endif
        @if ($endpoint ?? false) endpoint="{{ $endpoint }}" @endif
        :multiple="true"
        in-store="inputValue"
    >
        @if($addNew)
            <div slot="addModal">
                @partialView(($moduleName ?? null), 'create', ['renderForModal' => true, 'fieldsInModal' => true])
            </div>
        @endif
    </a17-vselect>
@endif

@unless($renderForBlocks || $renderForModal || (!isset($item->$name) && null == $formFieldsValue = getFormFieldsValue($form_fields, $name)))
@push('vuexStore')
    window['{{ config('twill.js_namespace') }}'].STORE.form.fields.push({
        name: '{{ $name }}',
        value: {!! json_encode(isset($item) && isset($item->$name) ? Arr::pluck($item->$name, 'id') : $formFieldsValue) !!}
    });
@endpush
@endunless
